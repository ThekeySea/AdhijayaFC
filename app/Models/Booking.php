<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'booking_date',
    'time_slot',
    'note',
    'status',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /** @var list<string> */
    public const TIME_SLOTS = [
        '09.00-10.00',
        '10.00-11.00',
        '11.00-12.00',
        '13.00-14.00',
        '14.00-15.00',
        '15.00-16.00',
        '16.00-17.00',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function formattedDate(): string
    {
        return $this->booking_date?->translatedFormat('d F Y') ?? '-';
    }
}
