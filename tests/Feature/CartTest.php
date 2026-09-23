<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_empty_cart(): void
    {
        $response = $this->get('/keranjang');

        $response->assertOk();
        $response->assertSee('Keranjang masih kosong');
    }

    public function test_service_detail_shows_add_to_cart_form_for_guest(): void
    {
        $service = Service::factory()->create(['slug' => 'print-dokumen']);

        $response = $this->get('/layanan/print-dokumen');

        $response->assertOk();
        $response->assertSee('Tambah ke keranjang', false);
        $response->assertSee('name="service_id"', false);
    }

    public function test_guest_can_add_service_to_cart(): void
    {
        $service = Service::factory()->create([
            'name' => 'Print dokumen',
            'price' => 500,
        ]);

        $response = $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 3,
            'detail' => 'A4, 2 sisi',
        ]);

        $response->assertRedirect('/keranjang');
        $response->assertSessionHas('status');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Print dokumen')
            ->assertSee('A4, 2 sisi')
            ->assertSee('Rp 1.500');
    }

    public function test_adding_same_service_twice_merges_quantity(): void
    {
        $service = Service::factory()->create(['price' => 1000]);

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 2])->assertRedirect('/keranjang');
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 3])->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Rp 5.000');
    }

    public function test_cart_total_recalculates_from_database_price(): void
    {
        $service = Service::factory()->create(['price' => 1000]);

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 2]);

        $service->update(['price' => 2500]);

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Rp 5.000')
            ->assertDontSee('Rp 2.000');
    }

    public function test_customer_can_update_quantity_and_detail(): void
    {
        $service = Service::factory()->create(['price' => 1000]);

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $response = $this->patch('/keranjang/'.$service->id, [
            'quantity' => 5,
            'detail' => 'Jilid spiral',
        ]);

        $response->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Rp 5.000')
            ->assertSee('Jilid spiral');
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $service = Service::factory()->create(['name' => 'Scan dokumen']);

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $response = $this->delete('/keranjang/'.$service->id);

        $response->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Keranjang masih kosong')
            ->assertDontSee('Scan dokumen');
    }

    public function test_add_to_cart_requires_valid_active_service(): void
    {
        $inactive = Service::factory()->inactive()->create();

        $this->post('/keranjang', ['service_id' => $inactive->id, 'quantity' => 1])
            ->assertNotFound();

        $this->post('/keranjang', ['service_id' => 999999, 'quantity' => 1])
            ->assertNotFound();
    }

    public function test_add_to_cart_validates_quantity(): void
    {
        $service = Service::factory()->create();

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 0])
            ->assertSessionHasErrors('quantity');

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 10000])
            ->assertSessionHasErrors('quantity');

        $this->get('/keranjang')->assertSee('Keranjang masih kosong');
    }

    public function test_nav_shows_cart_badge_with_item_count(): void
    {
        $service = Service::factory()->create();

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 4]);

        $this->get('/layanan')
            ->assertOk()
            ->assertSee('>4<', false);
    }

    public function test_inactive_service_in_cart_is_pruned(): void
    {
        $service = Service::factory()->create(['name' => 'Layanan Ditarik']);

        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $service->update(['is_active' => false]);

        Session::forget('status');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Keranjang masih kosong')
            ->assertDontSee('Layanan Ditarik');
    }

    public function test_cart_is_persisted_per_session_not_shared(): void
    {
        $service = Service::factory()->create(['price' => 1000]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/keranjang', ['service_id' => $service->id, 'quantity' => 2]);

        $this->flushSession();

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Keranjang masih kosong');
    }

    public function test_update_and_remove_unknown_cart_item_is_graceful(): void
    {
        $service = Service::factory()->create();

        $this->patch('/keranjang/'.$service->id, ['quantity' => 2])
            ->assertRedirect('/keranjang');

        $this->delete('/keranjang/'.$service->id)
            ->assertRedirect('/keranjang');

        $this->get('/keranjang')->assertSee('Keranjang masih kosong');
    }
}
