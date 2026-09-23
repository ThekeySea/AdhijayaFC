<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'service_id',
    'service_name_snapshot',
    'unit_price_snapshot',
    'quantity',
    'item_note',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'unit_price_snapshot' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function formattedUnitPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price_snapshot, 0, ',', '.');
    }

    public function formattedSubtotal(): string
    {
        return 'Rp '.number_format((float) $this->unit_price_snapshot * $this->quantity, 0, ',', '.');
    }
}
