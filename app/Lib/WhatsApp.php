<?php

namespace App\Lib;

class WhatsApp
{
    public static function number(): ?string
    {
        $number = trim((string) env('WHATSAPP_NUMBER', ''));

        return $number === '' ? null : $number;
    }

    public static function isConfigured(): bool
    {
        return self::number() !== null;
    }

    /**
     * Bangun URL wa.me. Nomor hanya digits; pesan selalu urlencoded.
     * Nomor toko wajib lewat number() (env). Nomor pelanggan boleh dari database.
     */
    public static function buildWhatsAppUrl(?string $phone, string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.urlencode($message);
    }

    public static function url(string $message): ?string
    {
        return self::buildWhatsAppUrl(self::number(), $message);
    }
}
