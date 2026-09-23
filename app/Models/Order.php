<?php

namespace App\Models;

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
        ];
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
