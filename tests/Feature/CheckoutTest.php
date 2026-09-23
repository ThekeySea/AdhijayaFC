<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function addServiceToCart(Service $service, int $quantity = 1, string $detail = ''): void
    {
        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => $quantity,
            'detail' => $detail,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_checkout(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
        $this->post('/checkout', [])->assertRedirect('/login');
    }

    public function test_customer_with_empty_cart_is_redirected_to_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/checkout')
            ->assertRedirect('/keranjang');
    }

    public function test_customer_can_view_checkout_page_with_cart_items(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => 'Print dokumen', 'price' => 500]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 3, 'A4, 2 sisi');

        $response = $this->get('/checkout');

        $response->assertOk();
        $response->assertSee('Print dokumen');
        $response->assertSee('A4, 2 sisi');
        $response->assertSee('Buat pesanan');
        $response->assertSee('Rp 1.500');
    }

    public function test_checkout_creates_order_with_price_snapshots(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Fotokopi warna',
            'price' => 1000,
        ]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 4, 'A3');

        $response = $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
            'customer_note' => 'Tolong cepat',
        ]);

        $order = Order::sole();

        $response->assertRedirect('/pesanan/'.$order->id);
        $response->assertSessionHas('status');

        $this->assertSame('PENDING_PAYMENT', $order->status->value);
        $this->assertSame('UNPAID', $order->payment_status->value);
        $this->assertSame($user->id, $order->customer_id);
        $this->assertSame(4000.0, (float) $order->subtotal);
        $this->assertSame(4000.0, (float) $order->total);
        $this->assertMatchesRegularExpression('/^FA-[A-Z0-9]{4}$/', $order->order_number);
        $this->assertSame('Tolong cepat', $order->customer_note);

        $item = $order->items()->sole();
        $this->assertSame('Fotokopi warna', $item->service_name_snapshot);
        $this->assertSame(1000.0, (float) $item->unit_price_snapshot);
        $this->assertSame(4, $item->quantity);
        $this->assertSame('A3', $item->item_note);

        $this->assertNotNull($order->booking);
        $this->assertSame('10.00-11.00', $order->booking->time_slot);
    }

    public function test_checkout_clears_cart_after_order_created(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 2);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect();

        $this->get('/keranjang')->assertSee('Keranjang masih kosong');
    }

    public function test_checkout_validates_pickup_fields(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', [
            'pickup_date' => now()->subDay()->toDateString(),
            'time_slot' => 'bukan-slot',
        ])->assertSessionHasErrors(['pickup_date', 'time_slot']);

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_uses_current_database_price_not_cart_snapshot(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 2);

        $service->update(['price' => 2500]);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '11.00-12.00',
        ])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(5000.0, (float) $order->subtotal);
        $this->assertSame(5000.0, (float) $order->items()->sole()->unit_price_snapshot * 2);
    }

    public function test_order_snapshot_survives_later_price_change(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => 'Jilid', 'price' => 5000]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '13.00-14.00',
        ]);

        $service->update(['price' => 9000, 'name' => 'Jilid Premium']);

        $order = Order::sole()->load('items');

        $this->assertSame('Jilid', $order->items->first()->service_name_snapshot);
        $this->assertSame(5000.0, (float) $order->items->first()->unit_price_snapshot);
        $this->assertSame(5000.0, (float) $order->total);
    }

    public function test_admin_cannot_checkout(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        $this->actingAs($admin);
        $this->addServiceToCart($service, 1);

        $this->get('/checkout')->assertForbidden();
        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertForbidden();
    }

    public function test_checkout_with_inactive_service_in_cart_redirects(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $service->update(['is_active' => false]);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect('/keranjang');

        $this->assertSame(0, Order::count());
    }

    public function test_order_statuses_and_payment_defaults(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '14.00-15.00',
        ]);

        $order = Order::sole();

        $this->assertSame(OrderStatus::PendingPayment, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertTrue($order->status->canBeCancelled());
    }
}
