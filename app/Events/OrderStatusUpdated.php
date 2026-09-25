<?php

namespace App\Events;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $previousStatus,
        public readonly ?PaymentStatus $previousPaymentStatus = null,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $this->order->loadMissing('customer');

        return [
            new PrivateChannel('admins'),
            new PrivateChannel('App.Models.User.'.$this->order->customer_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->order->loadMissing('customer');
        $this->order->loadCount('items');

        $current = $this->order->status;
        $previous = $this->previousStatus;
        $payment = $this->order->payment_status;

        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer?->name ?? 'Pelanggan',
            'items_count' => (int) $this->order->items_count,
            'total' => $this->order->formattedTotal(),
            'status' => $current->value,
            'status_label' => $current->label(),
            'status_badge_class' => $current->badgeClass(),
            'previous_status' => $previous->value,
            'previous_status_label' => $previous->label(),
            'previous_status_badge_class' => $previous->badgeClass(),
            'payment_status' => $payment->value,
            'payment_status_label' => $payment->label(),
            'previous_payment_status' => ($this->previousPaymentStatus ?? $payment)->value,
            'previous_payment_status_label' => ($this->previousPaymentStatus ?? $payment)->label(),
            'can_cancel' => $current->canBeCancelled(),
            'url' => route('admin.orders.show', $this->order),
            'tracking_url' => route('orders.tracking', $this->order),
        ];
    }
}
