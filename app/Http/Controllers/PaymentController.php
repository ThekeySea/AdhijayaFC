<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
     * Buat Snap token untuk order (hanya milik customer, belum dibayar).
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

        try {
            $token = $this->midtrans->createSnapToken($order);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'snap_token' => $token,
            'client_key' => config('midtrans.client_key'),
            'amount_due' => (float) $order->amount_due,
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

        return response()->json(['message' => 'OK']);
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
