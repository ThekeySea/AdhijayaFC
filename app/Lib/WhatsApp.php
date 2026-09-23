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

    public static function url(string $message): ?string
    {
        $number = self::number();

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }
}
