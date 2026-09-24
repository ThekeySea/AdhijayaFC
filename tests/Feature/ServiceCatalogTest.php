<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_service_catalog(): void
    {
        Service::factory()->create(['name' => 'Fotokopi hitam putih']);

        $response = $this->get('/layanan');

        $response->assertOk();
        $response->assertSee('Fotokopi hitam putih');
        $response->assertDontSee('>Harga contoh<', false);
    }

    public function test_service_card_shows_colored_category_badge(): void
    {
        Service::factory()->create(['name' => 'Print A4']);

        $response = $this->get('/layanan');

        $response->assertOk();
        $response->assertSee('ring-1', false);
        $response->assertSee('aspect-[4/3]', false);
        $response->assertSee('images/services/', false);
        $this->assertStringNotContainsString('>Harga contoh<', $response->getContent());
    }

    public function test_service_detail_shows_preview_photo(): void
    {
        $service = Service::factory()->create([
            'name' => 'Print A4 Premium',
            'slug' => 'print-a4-premium',
            'image_url' => 'images/services/digital-print.svg',
        ]);

        $response = $this->get('/layanan/print-a4-premium');

        $response->assertOk();
        $response->assertSee('images/services/digital-print.svg', false);
        $response->assertSee('Contoh hasil Print A4 Premium', false);
    }

    public function test_homepage_shows_hero_carousel_with_three_slides(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('images/hero/hero-1.jpg', false);
        $response->assertSee('images/hero/hero-2.jpg', false);
        $response->assertSee('images/hero/hero-3.jpg', false);
        $response->assertSee('Slide sebelumnya', false);
        $response->assertSee('Slide berikutnya', false);
        $response->assertSee('bg-linear-to-r from-[#0f172a]/95', false);
    }

    public function test_guest_can_view_active_service_detail(): void
    {
        $service = Service::factory()->create([
            'name' => 'Print dokumen',
            'slug' => 'print-dokumen',
        ]);

        $response = $this->get('/layanan/print-dokumen');

        $response->assertOk();
        $response->assertSee('Print dokumen');
        $response->assertSee($service->formattedPrice());
    }

    public function test_inactive_service_is_not_listed(): void
    {
        Service::factory()->inactive()->create(['name' => 'Layanan Rahasia']);

        $response = $this->get('/layanan');

        $response->assertOk();
        $response->assertDontSee('Layanan Rahasia');
    }

    public function test_inactive_service_detail_returns_404(): void
    {
        $service = Service::factory()->inactive()->create();

        $this->get('/layanan/'.$service->slug)->assertNotFound();
    }

    public function test_unknown_service_returns_404(): void
    {
        $this->get('/layanan/tidak-ada')->assertNotFound();
    }

    public function test_homepage_shows_active_services(): void
    {
        Service::factory()->create(['name' => 'Fotokopi warna']);
        Service::factory()->inactive()->create(['name' => 'Layanan Nonaktif']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fotokopi warna');
        $response->assertDontSee('Layanan Nonaktif');
    }

    public function test_guest_can_view_contact_page(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('Kontak');
    }
}
