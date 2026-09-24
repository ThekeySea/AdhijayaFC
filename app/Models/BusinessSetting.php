<?php

namespace App\Models;

use App\Services\OpeningHours;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'tagline',
    'about',
    'address',
    'latitude',
    'longitude',
    'phone',
    'whatsapp_number',
    'hours_weekday',
    'hours_sunday',
    'delivery_rate_per_km',
    'delivery_min_fee',
    'delivery_discount_per_100k',
    'delivery_max_radius_km',
])]
class BusinessSetting extends Model
{
    private static ?self $current = null;

    public static function current(): self
    {
        if (self::$current !== null && self::$current->exists) {
            return self::$current;
        }

        $settings = static::query()->first();

        if ($settings === null) {
            $settings = static::query()->create([
                'name' => config('app.name', 'Fotocopy Adhijaya'),
            ]);
        }

        return self::$current = $settings;
    }

    public static function flushCurrent(): void
    {
        self::$current = null;
    }

    protected function casts(): array
    {
        return [
            'delivery_rate_per_km' => 'decimal:2',
            'delivery_min_fee' => 'decimal:2',
            'delivery_discount_per_100k' => 'decimal:2',
            'delivery_max_radius_km' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCurrent());
        static::deleted(fn () => self::flushCurrent());
        static::saved(fn () => OpeningHours::flush());
        static::deleted(fn () => OpeningHours::flush());
    }

    public function displayName(): string
    {
        return $this->name !== '' ? $this->name : (string) config('app.name', 'Fotocopy Adhijaya');
    }
}
