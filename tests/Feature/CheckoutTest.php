<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\BusinessHour;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\OpeningHours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'phone' => '6281234567890',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ], $overrides);
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
        $response->assertSee('Nomor WhatsApp');
    }

    public function test_checkout_requires_phone_and_saves_it(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $service = Service::factory()->create(['price' => 500]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ])->assertSessionHasErrors('phone');

        $this->post('/checkout', [
            'phone' => '628111111111',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ])->assertSessionHasNoErrors();

        $this->assertSame('628111111111', $user->fresh()->phone);
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

        $response = $this->post('/checkout', $this->checkoutPayload([
            'customer_note' => 'Tolong cepat',
        ]));

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

        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '09.00-10.00',
        ]))->assertRedirect();

        $this->get('/keranjang')->assertSee('Keranjang masih kosong');
    }

    public function test_checkout_validates_pickup_fields(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', $this->checkoutPayload([
            'pickup_date' => now()->subDay()->toDateString(),
            'time_slot' => null,
        ]))->assertSessionHasErrors('pickup_date');

        $this->post('/checkout', $this->checkoutPayload([
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => 'bukan-slot',
        ]))->assertSessionHasErrors('time_slot');

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_uses_current_database_price_not_cart_snapshot(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 2);

        $service->update(['price' => 2500]);

        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '11.00-12.00',
        ]))->assertRedirect();

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

        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '13.00-14.00',
        ]));

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
        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '09.00-10.00',
        ]))->assertForbidden();
    }

    public function test_checkout_with_inactive_service_in_cart_redirects(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $service->update(['is_active' => false]);

        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '09.00-10.00',
        ]))->assertRedirect('/keranjang');

        $this->assertSame(0, Order::count());
    }

    public function test_order_statuses_and_payment_defaults(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', $this->checkoutPayload([
            'time_slot' => '14.00-15.00',
        ]));

        $order = Order::sole();

        $this->assertSame(OrderStatus::PendingPayment, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertTrue($order->status->canBeCancelled());
        $this->assertSame('pickup', $order->fulfillment_type instanceof FulfillmentType
            ? $order->fulfillment_type->value
            : $order->fulfillment_type);
    }

    public function test_checkout_delivery_requires_address_and_computes_fee(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 100000]);
        $settings = BusinessSetting::current();
        $settings->update([
            'latitude' => -6.914744,
            'longitude' => 107.609781,
            'delivery_rate_per_km' => 3000,
            'delivery_min_fee' => 5000,
            'delivery_discount_per_100k' => 0,
            'delivery_max_radius_km' => 20,
        ]);
        BusinessSetting::flushCurrent();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', $this->checkoutPayload([
            'fulfillment_type' => 'delivery',
            'pickup_date' => null,
            'time_slot' => null,
        ]))->assertSessionHasErrors(['delivery_address', 'delivery_latitude', 'delivery_longitude', 'delivery_mode']);

        $this->post('/checkout', $this->checkoutPayload([
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'delivery_latitude' => -6.915744,
            'delivery_longitude' => 107.610781,
            'delivery_mode' => 'asap',
            'pickup_date' => null,
            'time_slot' => null,
        ]))->assertSessionHasNoErrors();

        $order = Order::sole();
        $this->assertSame('delivery', $order->fulfillment_type instanceof FulfillmentType
            ? $order->fulfillment_type->value
            : $order->fulfillment_type);
        $this->assertSame('asap', $order->delivery_mode instanceof DeliveryMode
            ? $order->delivery_mode->value
            : $order->delivery_mode);
        $this->assertNotNull($order->delivery_fee);
        $this->assertGreaterThan(0, (float) $order->delivery_fee);
        $this->assertNull($order->booking->booking_date);
        $this->assertNull($order->booking->time_slot);
        $this->assertSame(100000 + (float) $order->delivery_fee, (float) $order->total, '', 0.01);
    }

    public function test_checkout_delivery_rejects_beyond_radius(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 5000]);
        $settings = BusinessSetting::current();
        $settings->update([
            'latitude' => -6.914744,
            'longitude' => 107.609781,
            'delivery_max_radius_km' => 1,
        ]);
        BusinessSetting::flushCurrent();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', $this->checkoutPayload([
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'Jauh sekali',
            'delivery_latitude' => -6.954744,
            'delivery_longitude' => 107.649781,
            'delivery_mode' => 'asap',
            'pickup_date' => null,
            'time_slot' => null,
        ]))->assertSessionHasErrors('delivery_address');

        $this->assertSame(0, Order::count());
    }

    public function test_closed_day_is_rejected_for_pickup(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $date = now()->addDay()->toDateString();
        $day = Carbon::parse($date)->dayOfWeek;

        BusinessHour::updateOrCreate(
            ['day_of_week' => $day],
            ['is_open' => false, 'opens_at' => null, 'closes_at' => null],
        );
        OpeningHours::flush();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->post('/checkout', $this->checkoutPayload([
            'pickup_date' => $date,
            'time_slot' => '10.00-11.00',
        ]))->assertSessionHasErrors('pickup_date');

        $this->assertSame(0, Order::count());
    }

    public function test_min_ready_minutes_filters_today_slots(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['min_ready_minutes' => 60]);

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $response = $this->get('/checkout');
        $response->assertOk();
        $response->assertSee('menit', false);
    }

    public function test_delivery_quote_endpoint_returns_fee(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 50000]);
        $settings = BusinessSetting::current();
        $settings->update([
            'latitude' => -6.914744,
            'longitude' => 107.609781,
            'delivery_rate_per_km' => 3000,
            'delivery_min_fee' => 5000,
            'delivery_discount_per_100k' => 5000,
            'delivery_max_radius_km' => 20,
        ]);
        BusinessSetting::flushCurrent();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $this->getJson('/checkout/delivery-quote?latitude=-6.915744&longitude=107.610781&subtotal=50000')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('within_radius', true);

        $this->assertIsNumeric($this->getJson('/checkout/delivery-quote?latitude=-6.915744&longitude=107.610781&subtotal=50000')->json('fee'));
    }

    public function test_slots_endpoint_follows_opening_hours(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $date = now()->addDay()->toDateString();
        $day = Carbon::parse($date)->dayOfWeek;

        BusinessHour::updateOrCreate(
            ['day_of_week' => $day],
            ['is_open' => true, 'opens_at' => '10:00:00', 'closes_at' => '14:00:00'],
        );
        OpeningHours::flush();

        $this->actingAs($user);
        $this->addServiceToCart($service, 1);

        $json = $this->getJson('/checkout/slots?date='.$date)->assertOk()->json();
        $this->assertTrue($json['open']);
        $this->assertContains('10.00-11.00', $json['slots']);
        $this->assertContains('13.00-14.00', $json['slots']);
        $this->assertNotContains('08.00-09.00', $json['slots']);
        $this->assertNotContains('14.00-15.00', $json['slots']);
    }
}
