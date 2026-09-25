<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Lib\WhatsApp;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Status tracking yang boleh di-set admin (bebas pilih di timeline).
     *
     * @var list<string>
     */
    private const ADMIN_STATUSES = [
        'PENDING_PAYMENT',
        'PAID',
        'PROCESSING',
        'READY',
        'COMPLETED',
        'PAYMENT_FAILED',
        'CANCELLED',
    ];

    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', '');
        $query = Order::query()
            ->with(['customer', 'booking', 'items'])
            ->withCount('items')
            ->latest();

        if ($filter !== '' && OrderStatus::tryFrom($filter) !== null) {
            $query->where('status', $filter);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'activeFilter' => $filter,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'booking', 'customer', 'files', 'payments' => fn ($q) => $q->latest()]);

        return view('admin.orders.show', [
            'order' => $order,
            'statusOptions' => $this->statusOptions(),
            'customerWaUrl' => $this->customerTrackingUrl($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(self::ADMIN_STATUSES)],
        ]);

        $target = (string) $request->input('status');
        $newStatus = OrderStatus::from($target);

        if ($order->status === $newStatus) {
            return back()->with(
                'status',
                'Status pesanan '.$order->order_number.' tetap '.$newStatus->label().'.'
            );
        }

        $previousStatus = $order->status;
        $previousPaymentStatus = $order->payment_status;

        $order->update(['status' => $newStatus]);
        $order->load('customer');

        $shareUrl = $this->customerTrackingUrl($order);

        try {
            OrderStatusUpdated::dispatch($order, $previousStatus, $previousPaymentStatus);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()
            ->with(
                'status',
                'Status pesanan '.$order->order_number.' diperbarui ke '.$newStatus->label().'.'
            )
            ->with('wa_share_ready', $shareUrl !== null)
            ->with('wa_share_url', $shareUrl);
    }

    /**
     * URL wa.me berisi pesan tracking untuk pelanggan (opsi A: 1 klik admin).
     */
    private function customerTrackingUrl(Order $order): ?string
    {
        $order->loadMissing('customer');

        $name = $order->customer?->name ?: 'Pelanggan';

        return WhatsApp::buildWhatsAppUrl(
            $order->customer?->phone,
            sprintf(
                'Halo %s, update pesanan %s: status terbaru "%s". Terima kasih.',
                $name,
                $order->order_number,
                $order->status->label()
            )
        );
    }

    /**
     * Semua opsi status untuk dropdown timeline admin.
     *
     * @return list<OrderStatus>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (string $value) => OrderStatus::from($value),
            self::ADMIN_STATUSES
        );
    }
}
