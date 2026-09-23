<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePriceTier;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TieredPricingTest extends TestCase
{
    use RefreshDatabase;

    private function serviceWithTiers(): Service
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'price' => 5000,
        ]);

        ServicePriceTier::create([
            'service_id' => $service->id,
            'min_qty' => 1,
            'max_qty' => 10,
            'unit_price' => 5000,
            'sort_order' => 0,
        ]);
        ServicePriceTier::create([
            'service_id' => $service->id,
            'min_qty' => 11,
            'max_qty' => 50,
            'unit_price' => 4000,
            'sort_order' => 1,
        ]);
        ServicePriceTier::create([
            'service_id' => $service->id,
            'min_qty' => 51,
            'max_qty' => 100,
            'unit_price' => 3500,
            'sort_order' => 2,
        ]);
        ServicePriceTier::create([
            'service_id' => $service->id,
            'min_qty' => 101,
            'max_qty' => null,
            'unit_price' => 3000,
            'sort_order' => 3,
        ]);

        return $service->fresh('priceTiers');
    }

    public function test_price_for_quantity_resolves_each_tier(): void
    {
        $service = $this->serviceWithTiers();

        $this->assertSame(5000.0, $service->priceForQuantity(1));
        $this->assertSame(5000.0, $service->priceForQuantity(10));
        $this->assertSame(4000.0, $service->priceForQuantity(11));
        $this->assertSame(4000.0, $service->priceForQuantity(50));
        $this->assertSame(3500.0, $service->priceForQuantity(51));
        $this->assertSame(3000.0, $service->priceForQuantity(150));
    }

    public function test_service_without_tiers_falls_back_to_base_price(): void
    {
        $service = Service::factory()->create(['price' => 2500]);

        $this->assertSame(2500.0, $service->priceForQuantity(99));
    }

    public function test_cart_uses_tier_price_for_quantity(): void
    {
        $service = $this->serviceWithTiers();

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 30,
        ])->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Rp 120.000');
    }

    public function test_checkout_snapshots_tier_unit_price(): void
    {
        $user = User::factory()->create();
        $service = $this->serviceWithTiers();

        $this->actingAs($user)->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 60,
        ]);

        $this->actingAs($user)->post('/checkout', [
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect();

        $item = Order::sole()->items()->sole();

        $this->assertSame(3500.0, (float) $item->unit_price_snapshot);
        $this->assertSame(60, $item->quantity);
    }

    public function test_service_detail_shows_tier_table(): void
    {
        $service = $this->serviceWithTiers();

        $this->get('/layanan/'.$service->slug)
            ->assertOk()
            ->assertSee('Harga per jumlah')
            ->assertSee('1-10')
            ->assertSee('>= 101');
    }

    public function test_seeder_creates_six_categories_and_jasa_tiers(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(6, ServiceCategory::count());
        $this->assertGreaterThanOrEqual(41, Service::query()->where('type', Service::TYPE_JASA)->count());
        $this->assertGreaterThanOrEqual(5, Service::query()->where('type', Service::TYPE_JUAL)->count());

        $printA4 = Service::query()->where('slug', 'print-a4')->sole();
        $this->assertSame(4, $printA4->priceTiers()->count());
        $this->assertSame(500.0, $printA4->priceForQuantity(5));
        $this->assertSame(400.0, $printA4->priceForQuantity(20));
    }
}
