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

    /**
     * Warna badge konsisten dengan step tracking pelanggan & admin.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-slate-100 text-slate-700',
            self::Paid => 'bg-emerald-50 text-emerald-700',
            self::Processing => 'bg-sky-50 text-sky-700',
            self::Ready => 'bg-amber-50 text-amber-700',
            self::Completed => 'bg-primary-soft text-primary',
            self::Cancelled => 'bg-red-50 text-red-700',
            self::PaymentFailed => 'bg-amber-50 text-amber-800',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PendingPayment, self::PaymentFailed], true);
    }
}
