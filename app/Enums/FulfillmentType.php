<?php

namespace App\Enums;

enum FulfillmentType: string
{
    case Pickup = 'pickup';

    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Ambil di tempat',
            self::Delivery => 'Delivery',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pickup => 'bg-emerald-50 text-emerald-700',
            self::Delivery => 'bg-sky-50 text-sky-700',
        };
    }
}
