<?php

namespace Tests\Unit;

use App\Lib\WhatsApp;
use PHPUnit\Framework\TestCase;

class WhatsAppTest extends TestCase
{
    public function test_build_whatsapp_url_encodes_message_and_strips_non_digits(): void
    {
        $url = WhatsApp::buildWhatsAppUrl('+62 812-3456-7890', 'Halo, pesanan #A1 & siap?');

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('Halo%2C+pesanan+%23A1+%26+siap%3F', $url);
    }

    public function test_build_whatsapp_url_returns_null_for_empty_phone(): void
    {
        $this->assertNull(WhatsApp::buildWhatsAppUrl('', 'Halo'));
        $this->assertNull(WhatsApp::buildWhatsAppUrl(null, 'Halo'));
        $this->assertNull(WhatsApp::buildWhatsAppUrl('+++', 'Halo'));
    }

    public function test_url_uses_env_number_only_when_configured(): void
    {
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER=628111111111');
        $_ENV['WHATSAPP_NUMBER'] = '628111111111';
        $_SERVER['WHATSAPP_NUMBER'] = '628111111111';

        try {
            $this->assertTrue(WhatsApp::isConfigured());
            $url = WhatsApp::url('Halo Admin');
            $this->assertNotNull($url);
            $this->assertStringStartsWith('https://wa.me/628111111111?text=', $url);
        } finally {
            putenv($original === false ? 'WHATSAPP_NUMBER' : 'WHATSAPP_NUMBER='.$original);
            unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);
        }
    }

    public function test_url_is_null_when_env_not_configured(): void
    {
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER');
        unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);

        try {
            $this->assertFalse(WhatsApp::isConfigured());
            $this->assertNull(WhatsApp::url('Halo Admin'));
        } finally {
            if ($original !== false) {
                putenv('WHATSAPP_NUMBER='.$original);
                $_ENV['WHATSAPP_NUMBER'] = $original;
                $_SERVER['WHATSAPP_NUMBER'] = $original;
            }
        }
    }
}
