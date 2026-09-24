<?php

namespace App\Models;

use App\Enums\DeliveryMode;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'order_number',
    'customer_id',
    'booking_id',
    'status',
    'payment_status',
    'subtotal',
    'additional_fee',
    'total',
    'amount_due',
    'remaining_amount',
    'customer_note',
    'fulfillment_type',
    'delivery_address',
    'delivery_latitude',
    'delivery_longitude',
    'delivery_distance_km',
    'delivery_fee',
    'delivery_mode',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'additional_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'fulfillment_type' => FulfillmentType::class,
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_distance_km' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'delivery_mode' => DeliveryMode::class,
        ];
    }

    public function isDelivery(): bool
    {
        return $this->fulfillment_type === FulfillmentType::Delivery
            || $this->fulfillment_type === 'delivery';
    }

    public function formattedDeliveryFee(): string
    {
        return 'Rp '.number_format((float) $this->delivery_fee, 0, ',', '.');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<OrderFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function hasDownPayment(): bool
    {
        return (float) $this->remaining_amount > 0;
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'FA-'.strtoupper(Str::random(4));
        } while (static::query()->where('order_number', $number)->exists());

        return $number;
    }

    public function formattedSubtotal(): string
    {
        return 'Rp '.number_format((float) $this->subtotal, 0, ',', '.');
    }

    public function formattedTotal(): string
    {
        return 'Rp '.number_format((float) $this->total, 0, ',', '.');
    }

    public function formattedAmountDue(): string
    {
        return 'Rp '.number_format((float) $this->amount_due, 0, ',', '.');
    }

    public function formattedRemaining(): string
    {
        return 'Rp '.number_format((float) $this->remaining_amount, 0, ',', '.');
    }
}
