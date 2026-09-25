<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly Order $order) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('admins')];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->order->loadMissing('customer');
        $this->order->loadCount('items');

        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer?->name ?? 'Pelanggan',
            'items_count' => (int) $this->order->items_count,
            'status_label' => $this->order->status->label(),
            'status_badge_class' => $this->order->status->badgeClass(),
            'total' => $this->order->formattedTotal(),
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
