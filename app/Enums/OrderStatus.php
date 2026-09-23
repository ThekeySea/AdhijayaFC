<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'PENDING_PAYMENT';

    case Paid = 'PAID';

    case Processing = 'PROCESSING';

    case Ready = 'READY';

    case Completed = 'COMPLETED';

    case Cancelled = 'CANCELLED';

    case PaymentFailed = 'PAYMENT_FAILED';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu pembayaran',
            self::Paid => 'Sudah dibayar',
            self::Processing => 'Sedang diproses',
            self::Ready => 'Siap diambil',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::PaymentFailed => 'Pembayaran gagal',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PendingPayment, self::PaymentFailed], true);
    }
}
