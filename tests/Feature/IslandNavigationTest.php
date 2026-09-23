<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IslandNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::flushCurrent();
    }

    public function test_guest_header_uses_logo_island_and_kontak_pill_without_hamburger(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fotocopy Adhijaya');
        $response->assertSee(route('kontak'), false);
        $response->assertSee('Kontak', false);
        $response->assertDontSee('Buka menu navigasi', false);
        $response->assertDontSee('aria-label="Buka menu navigasi"', false);
    }

    public function test_desktop_main_nav_lives_inside_business_name_island(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('home'), false);
        $response->assertSee(route('services.index'), false);
        $response->assertSee(route('cart.index'), false);
        $response->assertSee('Fotocopy Adhijaya', false);
        $response->assertSee('hidden min-w-0 items-center sm:flex', false);
        $response->assertDontSee('hidden items-center gap-1 rounded-2xl', false);
        $response->assertDontSee('mt-2.5 hidden items-center', false);
    }

    public function test_customer_header_shows_orders_and_logout_without_hamburger(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee(route('orders.index'), false);
        $response->assertSee('Keluar', false);
        $response->assertDontSee('Buka menu navigasi', false);
    }

    public function test_kontak_page_shows_founder_story_section(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('Cerita singkat usaha');
        $response->assertSee('Fotocopy Adhijaya adalah usaha lokal');
        $response->assertSee('Info Usaha');
    }

    public function test_kontak_page_shows_map_placeholder_when_coordinates_missing(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('Lokasi toko');
        $response->assertSee('Peta lokasi belum diatur');
        $response->assertDontSee('id="kontak-map"', false);
    }

    public function test_kontak_page_shows_map_when_coordinates_set(): void
    {
        BusinessSetting::current()->update([
            'latitude' => -6.914744,
            'longitude' => 107.609781,
            'address' => 'Jl. Contoh No. 1',
        ]);
        BusinessSetting::flushCurrent();

        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('Lokasi toko');
        $response->assertSee('id="kontak-map"', false);
        $response->assertSee('Buka di Google Maps', false);
        $response->assertSee('Jl. Contoh No. 1');
        $response->assertDontSee('Peta lokasi belum diatur');
    }
}
