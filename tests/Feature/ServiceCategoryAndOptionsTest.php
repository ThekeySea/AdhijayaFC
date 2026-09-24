<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryAndOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_hero_shows_buat_akun_cta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Buat akun', false)
            ->assertDontSee('Mulai cetak', false);
    }

    public function test_logged_in_customer_hero_shows_mulai_cetak_cta(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Mulai cetak', false)
            ->assertDontSee('>Buat akun<', false);
    }

    public function test_service_index_filters_by_category_slug(): void
    {
        $printCat = ServiceCategory::factory()->create(['name' => 'Digital Print', 'slug' => 'digital-print']);
        $bookCat = ServiceCategory::factory()->create(['name' => 'Cetak Buku', 'slug' => 'cetak-buku']);

        Service::factory()->create(['name' => 'Print A4', 'category_id' => $printCat->id, 'slug' => 'print-a4']);
        Service::factory()->create(['name' => 'Buku Yasin', 'category_id' => $bookCat->id, 'slug' => 'buku-yasin']);

        $this->get('/layanan?category=digital-print')
            ->assertOk()
            ->assertSee('Print A4')
            ->assertDontSee('Buku Yasin')
            ->assertSee('Semua', false);

        $this->get('/layanan?category=cetak-buku')
            ->assertOk()
            ->assertSee('Buku Yasin')
            ->assertDontSee('Print A4');
    }

    public function test_unknown_category_shows_all_services(): void
    {
        Service::factory()->create(['name' => 'Print A4', 'slug' => 'print-a4']);

        $this->get('/layanan?category=TidakAda')
            ->assertOk()
            ->assertSee('Print A4');
    }

    public function test_service_index_filters_jual_type_for_atk(): void
    {
        Service::factory()->jual()->create(['name' => 'Pulpen', 'slug' => 'pulpen']);
        Service::factory()->create(['name' => 'Print A4', 'slug' => 'print-a4']);

        $this->get('/layanan?type=jual')
            ->assertOk()
            ->assertSee('Pulpen')
            ->assertDontSee('Print A4');
    }

    public function test_homepage_shows_category_chips_and_sections(): void
    {
        $atkCat = ServiceCategory::factory()->create(['name' => 'Alat Tulis Stationery', 'slug' => 'alat-tulis-stationery', 'sort_order' => 4]);
        $printCat = ServiceCategory::factory()->create(['name' => 'Digital Print', 'slug' => 'digital-print', 'sort_order' => 1]);

        Service::factory()->jual()->create(['name' => 'Pulpen', 'slug' => 'pulpen']);
        Service::factory()->create(['name' => 'Print A4', 'category_id' => $printCat->id, 'slug' => 'print-a4']);
        Service::factory()->create(['name' => 'Stempel', 'category_id' => $atkCat->id, 'slug' => 'stempel']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Digital Print', false)
            ->assertSee('Alat Tulis Stationery', false)
            ->assertSee('Pesan ATK')
            ->assertSee('Print A0–A5 & scan copy', false);
    }

    public function test_homepage_atk_section_lists_jual_services_only(): void
    {
        Service::factory()->jual()->create(['name' => 'Pulpen', 'slug' => 'pulpen']);
        Service::factory()->create(['name' => 'Stempel', 'slug' => 'stempel']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Pesan ATK');
        $response->assertSee('Pulpen');
    }

    public function test_service_detail_shows_option_checkboxes(): void
    {
        $service = Service::factory()->create(['slug' => 'print-a4', 'name' => 'Print A4']);
        ServiceOption::factory()->create([
            'service_id' => $service->id,
            'name' => 'Jilid spiral',
            'price' => 5000,
            'pricing' => 'per_order',
        ]);
        ServiceOption::factory()->create([
            'service_id' => $service->id,
            'name' => 'Laminating A4',
            'price' => 3000,
            'pricing' => 'per_unit',
        ]);

        $this->get('/layanan/print-a4')
            ->assertOk()
            ->assertSee('Opsi tambahan')
            ->assertSee('Jilid spiral')
            ->assertSee('Laminating A4')
            ->assertSee('name="options[]"', false);
    }

    public function test_add_to_cart_with_per_order_option_adds_fixed_price_once(): void
    {
        $service = Service::factory()->create(['price' => 500]);
        $option = ServiceOption::factory()->create([
            'service_id' => $service->id,
            'name' => 'Jilid spiral',
            'price' => 5000,
            'pricing' => 'per_order',
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 10,
            'options' => [$option->id],
        ])->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Jilid spiral')
            ->assertSee('Rp 10.000');
    }

    public function test_add_to_cart_with_per_unit_option_multiplies_with_quantity(): void
    {
        $service = Service::factory()->create(['price' => 500]);
        $option = ServiceOption::factory()->create([
            'service_id' => $service->id,
            'name' => 'Laminating A4',
            'price' => 3000,
            'pricing' => 'per_unit',
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 2,
            'options' => [$option->id],
        ]);

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Laminating A4')
            ->assertSee('Rp 7.000');
    }

    public function test_invalid_option_ids_are_ignored(): void
    {
        $service = Service::factory()->create(['price' => 500]);
        $other = ServiceOption::factory()->create();
        $inactive = ServiceOption::factory()->create([
            'service_id' => $service->id,
            'is_active' => false,
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'options' => [$other->id, $inactive->id],
        ]);

        $this->get('/keranjang')
            ->assertOk()
            ->assertDontSee('Opsi:', false)
            ->assertSee('Rp 500');
    }

    public function test_checkout_snapshots_unit_price_including_per_unit_options(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 500, 'name' => 'Print A4']);
        $unitOption = ServiceOption::factory()->create([
            'service_id' => $service->id,
            'name' => 'Laminating A4',
            'price' => 3000,
            'pricing' => 'per_unit',
        ]);
        $fixedOption = ServiceOption::factory()->perOrder()->create([
            'service_id' => $service->id,
            'name' => 'Jilid spiral',
            'price' => 5000,
        ]);

        $this->actingAs($user)->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 2,
            'options' => [$unitOption->id, $fixedOption->id],
        ]);

        $this->actingAs($user)->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect();

        $order = Order::sole();

        $this->assertSame(7000.0, (float) $order->subtotal);
        $this->assertSame(5000.0, (float) $order->additional_fee);
        $this->assertSame(12000.0, (float) $order->total);

        $item = $order->items()->sole();
        $this->assertSame(3500.0, (float) $item->unit_price_snapshot);
        $this->assertSame(2, $item->quantity);
        $this->assertStringContainsString('Laminating A4', (string) $item->item_note);
        $this->assertStringContainsString('Jilid spiral', (string) $item->item_note);
    }

    public function test_laminating_exists_as_standalone_service_and_as_option(): void
    {
        $cat = ServiceCategory::factory()->create(['name' => 'Alat Tulis Stationery', 'slug' => 'alat-tulis-stationery']);
        Service::factory()->create([
            'name' => 'Laminating',
            'category_id' => $cat->id,
            'slug' => 'laminating',
        ]);

        $print = Service::factory()->create(['slug' => 'print-a4', 'name' => 'Print A4']);
        ServiceOption::factory()->create([
            'service_id' => $print->id,
            'name' => 'Laminating A4',
        ]);

        $this->get('/layanan?category=alat-tulis-stationery')
            ->assertOk()
            ->assertSee('Laminating');

        $this->get('/layanan/print-a4')
            ->assertOk()
            ->assertSee('Laminating A4');
    }

    public function test_admin_category_id_must_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Layanan Aneh',
            'type' => Service::TYPE_JASA,
            'category_id' => 999999,
            'unit' => 'pcs',
            'price' => 1000,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_admin_jasa_requires_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Tanpa Kategori',
            'type' => Service::TYPE_JASA,
            'category_id' => '',
            'unit' => 'pcs',
            'price' => 1000,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_service_detail_shows_category_name_from_relation(): void
    {
        $cat = ServiceCategory::factory()->create(['name' => 'Lain Lain', 'slug' => 'lain-lain']);
        Service::factory()->create([
            'name' => 'Stiker Custom',
            'category_id' => $cat->id,
            'slug' => 'stiker-custom',
        ]);

        $this->get('/layanan')
            ->assertOk()
            ->assertSee('Stiker Custom')
            ->assertSee('Lain Lain', false);
    }
}
