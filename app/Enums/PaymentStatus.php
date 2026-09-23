<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'UNPAID';

    case Paid = 'PAID';

    case Failed = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum dibayar',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
        };
    }
}
