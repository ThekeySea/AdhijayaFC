<?php

namespace App\Models;

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

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCurrent());
        static::deleted(fn () => self::flushCurrent());
    }

    public function displayName(): string
    {
        return $this->name !== '' ? $this->name : (string) config('app.name', 'Fotocopy Adhijaya');
    }
}
