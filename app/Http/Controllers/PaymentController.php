<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
    ) {}

    /**
     * Charge Midtrans Core API (QRIS / VA) untuk overlay pembayaran custom.
     */
    public function create(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_if($request->user()->isAdmin(), 403);

        if ($order->payment_status === PaymentStatus::Paid) {
            return response()->json([
                'message' => 'Pesanan sudah dibayar.',
            ], 422);
        }

        if ($order->status === OrderStatus::Cancelled) {
            return response()->json([
                'message' => 'Pesanan sudah dibatalkan.',
            ], 422);
        }

        $validated = $request->validate([
            'method' => ['nullable', 'string', 'in:qris,bank_transfer'],
            'bank' => ['nullable', 'string', 'in:bca,bni,bri,mandiri'],
        ]);

        $method = (string) ($validated['method'] ?? 'qris');
        $bank = (string) ($validated['bank'] ?? 'bca');

        try {
            $charge = $this->midtrans->createCharge($order, $method, $bank);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json($charge);
    }

    /**
     * Poll status pembayaran (dipakai overlay custom).
     */
    public function status(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_if($request->user()->isAdmin(), 403);

        return response()->json([
            'payment_status' => $order->payment_status->value,
            'order_status' => $order->status->value,
            'is_paid' => $order->payment_status === PaymentStatus::Paid,
        ]);
    }

    /**
     * Lewati pembayaran (tutup overlay ✕/Tutup) — pesanan dianggap selesai dibayar.
     */
    public function skip(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_if($request->user()->isAdmin(), 403);

        if ($order->payment_status === PaymentStatus::Paid) {
            return response()->json([
                'message' => 'Pesanan sudah dibayar.',
                'is_paid' => true,
            ]);
        }

        if ($order->status === OrderStatus::Cancelled) {
            return response()->json([
                'message' => 'Pesanan sudah dibatalkan.',
            ], 422);
        }

        $previousStatus = $order->status;
        $previousPaymentStatus = $order->payment_status;

        DB::transaction(function () use ($order) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->latest()
                ->first() ?? new Payment(['order_id' => $order->id]);

            if (! $payment->isPaid()) {
                $payment->fill([
                    'provider' => 'manual',
                    'payment_type' => 'skipped',
                    'transaction_status' => 'settlement',
                    'gross_amount' => $order->amount_due,
                    'paid_at' => now(),
                ])->save();
            }

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentFailed], true)
                    ? OrderStatus::Paid
                    : $order->status,
            ]);
        });

        $this->broadcastStatusChange($order, $previousStatus, $previousPaymentStatus);

        return response()->json([
            'message' => 'Pembayaran dilewati. Pesanan dianggap selesai.',
            'is_paid' => true,
        ]);
    }

    /**
     * Notification webhook dari Midtrans. Diproses server-side, idempotent.
     */
    public function notification(Request $request): JsonResponse
    {
        $notification = $request->all();

        if (! $this->midtrans->isValidSignature($notification)) {
            abort(403, 'Signature tidak valid.');
        }

        $orderNumber = (string) ($notification['order_id'] ?? '');
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        $transactionStatus = (string) ($notification['transaction_status'] ?? '');
        $providerTransactionId = $notification['transaction_id'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;
        $paymentType = $notification['payment_type'] ?? null;

        $previousStatus = $order->status;
        $previousPaymentStatus = $order->payment_status;

        DB::transaction(function () use ($order, $notification, $transactionStatus, $providerTransactionId, $fraudStatus, $paymentType) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->latest()
                ->first();

            if ($payment === null) {
                $payment = new Payment(['order_id' => $order->id]);
            }

            $alreadyPaid = $payment->isPaid()
                || $order->payment_status === PaymentStatus::Paid;

            $payment->fill([
                'provider' => 'midtrans',
                'provider_transaction_id' => $providerTransactionId ?? $payment->provider_transaction_id,
                'gross_amount' => $notification['gross_amount'] ?? $payment->gross_amount,
                'transaction_status' => $transactionStatus !== '' ? $transactionStatus : $payment->transaction_status,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
                'raw_notification' => $notification,
            ]);

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && ! $alreadyPaid) {
                $payment->paid_at = now();
            }

            $payment->save();

            // Idempotent: jangan regresi status order yang sudah final.
            if ($order->status === OrderStatus::Cancelled) {
                return;
            }

            if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
                if ($order->payment_status !== PaymentStatus::Paid) {
                    $order->update([
                        'payment_status' => PaymentStatus::Paid,
                        'status' => $order->status === OrderStatus::PendingPayment
                            ? OrderStatus::Paid
                            : $order->status,
                    ]);
                }

                return;
            }

            if (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
                if ($order->payment_status !== PaymentStatus::Paid) {
                    $order->update([
                        'payment_status' => PaymentStatus::Failed,
                        'status' => $order->status === OrderStatus::PendingPayment
                            ? OrderStatus::PaymentFailed
                            : $order->status,
                    ]);
                }
            }
        });

        $this->broadcastStatusChange($order, $previousStatus, $previousPaymentStatus);

        return response()->json(['message' => 'OK']);
    }

    private function broadcastStatusChange(
        Order $order,
        OrderStatus $previousStatus,
        ?PaymentStatus $previousPaymentStatus,
    ): void {
        if ($order->status === $previousStatus
            && $order->payment_status === $previousPaymentStatus) {
            return;
        }

        try {
            OrderStatusUpdated::dispatch($order, $previousStatus, $previousPaymentStatus);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Halaman hasil pembayaran (status dibaca dari database).
     */
    public function result(Request $request, Order $order): RedirectResponse
    {
        abort_unless(
            $order->customer_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Status pembayaran diperbarui dari server.');
    }
}
