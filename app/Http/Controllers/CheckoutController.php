<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Services\MidtransService;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            abort(403);
        }

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Keranjang masih kosong. Tambah layanan dulu sebelum checkout.');
        }

        $subtotal = (float) $lines->sum('subtotal');
        $amountDue = $this->midtrans->amountDueFor($subtotal);

        return view('checkout.create', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'amountDue' => $amountDue,
            'remaining' => $subtotal - $amountDue,
            'isDownPayment' => $this->midtrans->isDownPayment($subtotal),
            'timeSlots' => Booking::TIME_SLOTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', Rule::in(Booking::TIME_SLOTS)],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Keranjang masih kosong. Tambah layanan dulu sebelum checkout.');
        }

        $order = DB::transaction(function () use ($request, $validated, $lines) {
            $subtotal = 0.0;

            foreach ($lines as $line) {
                $subtotal += $line['price'] * $line['quantity'];
            }

            $amountDue = $this->midtrans->amountDueFor($subtotal);

            $booking = Booking::create([
                'customer_id' => $request->user()->id,
                'booking_date' => $validated['pickup_date'],
                'time_slot' => $validated['time_slot'],
                'note' => $validated['customer_note'] ?? null,
                'status' => 'SCHEDULED',
            ]);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $request->user()->id,
                'booking_id' => $booking->id,
                'status' => OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal' => $subtotal,
                'additional_fee' => 0,
                'total' => $subtotal,
                'amount_due' => $amountDue,
                'remaining_amount' => $subtotal - $amountDue,
                'customer_note' => $validated['customer_note'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'service_id' => $line['service']->id,
                    'service_name_snapshot' => $line['service']->name,
                    'unit_price_snapshot' => $line['price'],
                    'quantity' => $line['quantity'],
                    'item_note' => $line['detail'] !== '' ? $line['detail'] : null,
                ]);
            }

            Cart::clear();

            return $order;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Pesanan '.$order->order_number.' berhasil dibuat.');
    }
}
