<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->with(['booking', 'items'])
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless(
            $order->customer_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $order->load(['items', 'booking', 'customer', 'files']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        if (! $order->status->canBeCancelled()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('status', 'Pesanan tidak dapat dibatalkan karena status saat ini.');
        }

        $previousStatus = $order->status;
        $previousPaymentStatus = $order->payment_status;

        $order->update([
            'status' => OrderStatus::Cancelled,
        ]);

        try {
            OrderStatusUpdated::dispatch($order, $previousStatus, $previousPaymentStatus);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Pesanan '.$order->order_number.' dibatalkan.');
    }

    /**
     * Fragment HTML timeline tracking (di-swap realtime oleh pelanggan).
     */
    public function tracking(Request $request, Order $order): View
    {
        abort_unless(
            $order->customer_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        return view('orders.tracking-fragment', [
            'order' => $order,
        ]);
    }
}
