<?php

namespace App\Enums;

enum DeliveryMode: string
{
    case Asap = 'asap';

    case Scheduled = 'scheduled';

    public function label(): string
    {
        return match ($this) {
            self::Asap => 'Segera setelah selesai',
            self::Scheduled => 'Jadwal (tanggal + jam)',
        };
    }
}
