<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileBottomNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_has_bottom_nav_with_print_center_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Navigasi bawah', false);
        $response->assertSee('Print', false);
        $response->assertSee(route('services.index', ['category' => 'digital-print']), false);
        $response->assertSee('Masuk', false);
    }

    public function test_bottom_nav_shows_cart_badge_and_orders_for_customer(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Navigasi bawah', false);
        $response->assertSee('Pesanan', false);
        $response->assertSee(route('orders.index'), false);
    }

    public function test_bottom_nav_print_link_is_marked_active_on_digital_print_filter(): void
    {
        $response = $this->get('/layanan?category=digital-print');

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('services.index', ['category' => 'digital-print']), false);
    }

    public function test_bottom_nav_is_hidden_breakpoint_on_desktop_layout_body(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('sm:hidden', false);
        $response->assertSee('backdrop-blur-md', false);
    }
}
