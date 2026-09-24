<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'day_of_week',
    'is_open',
    'opens_at',
    'closes_at',
])]
class BusinessHour extends Model
{
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
            'opens_at' => 'string',
            'closes_at' => 'string',
        ];
    }

    public function dayLabel(): string
    {
        return match ($this->day_of_week) {
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => '—',
        };
    }

    public function formattedRange(): string
    {
        if (! $this->is_open || $this->opens_at === null || $this->closes_at === null) {
            return 'Tutup';
        }

        return substr($this->opens_at, 0, 5).' – '.substr($this->closes_at, 0, 5);
    }
}
