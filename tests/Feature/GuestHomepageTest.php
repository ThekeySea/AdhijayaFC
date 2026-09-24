<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestHomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::flushCurrent();
    }

    public function test_guest_can_view_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fotocopy Adhijaya');
        $response->assertSee('Lihat layanan');
        $response->assertSee('Buka halaman kontak');
    }

    public function test_homepage_shows_business_stats_counts(): void
    {
        Service::factory()->create();
        Service::factory()->create();
        Service::factory()->inactive();
        Order::factory()->create();
        Order::factory()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Jasa Kami')
            ->assertSee('Diandalkan Oleh')
            ->assertSee('Telah berpengalaman');
    }

    public function test_guest_sees_floating_whatsapp_when_number_configured(): void
    {
        BusinessSetting::current()->update(['whatsapp_number' => '6281234567890']);

        $this->get('/')
            ->assertOk()
            ->assertSee('wa.me/6281234567890', false)
            ->assertSee('Chat WhatsApp', false);
    }

    public function test_guest_does_not_see_floating_whatsapp_when_not_configured(): void
    {
        BusinessSetting::current()->update(['whatsapp_number' => null]);
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER');
        unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);

        try {
            $this->get('/')
                ->assertOk()
                ->assertDontSee('Chat WhatsApp', false);
        } finally {
            if ($original !== false) {
                putenv('WHATSAPP_NUMBER='.$original);
                $_ENV['WHATSAPP_NUMBER'] = $original;
                $_SERVER['WHATSAPP_NUMBER'] = $original;
            } else {
                putenv('WHATSAPP_NUMBER');
            }
        }
    }

    public function test_homepage_contact_section_uses_secondary_cta_style(): void
    {
        $html = (string) $this->get('/')->getContent();

        $this->assertStringNotContainsString(
            'bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">Buka halaman kontak',
            $html
        );
    }

    public function test_guest_can_view_login_and_register(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
