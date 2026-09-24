<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use App\Models\ServicePriceTier;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceOptionGroupsTest extends TestCase
{
    use RefreshDatabase;

    private function serviceWithGroups(array $serviceAttributes = []): Service
    {
        $service = Service::factory()->create($serviceAttributes);

        $group = ServiceOptionGroup::create([
            'service_id' => $service->id,
            'name' => 'Produk & Bahan',
            'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
            'is_required' => true,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        ServiceOption::create([
            'service_id' => $service->id,
            'group_id' => $group->id,
            'name' => 'HVS 80 gsm',
            'price' => 0,
            'pricing' => ServiceOption::PRICING_PER_UNIT,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        ServiceOption::create([
            'service_id' => $service->id,
            'group_id' => $group->id,
            'name' => 'Art Paper 150 gsm',
            'price' => 500,
            'pricing' => ServiceOption::PRICING_PER_UNIT,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $side = ServiceOptionGroup::create([
            'service_id' => $service->id,
            'name' => 'Cetak Berapa Sisi',
            'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
            'is_required' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceOption::create([
            'service_id' => $service->id,
            'group_id' => $side->id,
            'name' => 'Satu sisi',
            'price' => 0,
            'pricing' => ServiceOption::PRICING_PER_UNIT,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        ServiceOption::create([
            'service_id' => $service->id,
            'group_id' => $side->id,
            'name' => 'Dua sisi',
            'price' => 200,
            'pricing' => ServiceOption::PRICING_PER_UNIT,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return $service->fresh(['activeOptionGroups.options', 'priceTiers']);
    }

    public function test_service_detail_renders_option_groups_as_radio_inputs(): void
    {
        $service = $this->serviceWithGroups(['slug' => 'print-a4', 'name' => 'Print A4']);

        $response = $this->get('/layanan/print-a4');

        $response->assertOk()
            ->assertSee('Cetak Berapa Sisi', false)
            ->assertSee('Art Paper 150 gsm', false)
            ->assertSee('Dua sisi', false)
            ->assertSee('HVS 80 gsm', false);
    }

    public function test_required_single_group_must_be_selected_when_adding_to_cart(): void
    {
        $service = $this->serviceWithGroups();
        $sideGroup = $service->activeOptionGroups->firstWhere('name', 'Cetak Berapa Sisi');
        $paperGroup = $service->activeOptionGroups->firstWhere('name', 'Produk & Bahan');
        $satuSisi = $sideGroup->options->firstWhere('name', 'Satu sisi');

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'options' => [$satuSisi->id],
        ])->assertSessionHasErrors('options');

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'options' => [
                $paperGroup->options->first()->id,
                $satuSisi->id,
            ],
        ])->assertRedirect('/keranjang');

        $this->get('/keranjang')->assertOk()->assertSee($service->name);
    }

    public function test_min_quantity_is_enforced_on_cart_add(): void
    {
        $service = Service::factory()->create([
            'name' => 'Nota NCR',
            'min_quantity' => 5,
            'unit' => 'buku',
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 2,
        ])->assertSessionHasErrors('quantity');

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 5,
        ])->assertRedirect('/keranjang');

        $this->get('/keranjang')->assertOk()->assertSee('Nota NCR');
    }

    public function test_digital_print_has_no_min_quantity_from_seeder(): void
    {
        $this->seed(ServiceSeeder::class);

        $printCat = ServiceCategory::query()->where('slug', 'digital-print')->sole();
        $mins = Service::query()
            ->where('category_id', $printCat->id)
            ->pluck('min_quantity');

        $this->assertNotEmpty($mins);
        $mins->each(fn ($min) => $this->assertNull($min));
    }

    public function test_required_file_blocks_cart_add_without_upload(): void
    {
        $service = Service::factory()->create([
            'file_requirement' => Service::FILE_REQUIRED,
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('files');

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'files' => [UploadedFile::fake()->create('desain.pdf', 100, 'application/pdf')],
        ])->assertRedirect('/keranjang');
    }

    public function test_optional_file_allows_cart_add_without_upload(): void
    {
        $service = Service::factory()->create([
            'file_requirement' => Service::FILE_OPTIONAL,
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
        ])->assertRedirect('/keranjang');
    }

    public function test_file_none_service_rejects_upload(): void
    {
        $service = Service::factory()->create([
            'file_requirement' => Service::FILE_NONE,
        ]);

        $this->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'files' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors('files');
    }

    public function test_cart_file_moves_to_order_on_checkout(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Print A4',
            'file_requirement' => Service::FILE_REQUIRED,
        ]);

        $this->actingAs($user)->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
            'files' => [UploadedFile::fake()->create('poster.pdf', 50, 'application/pdf')],
        ])->assertRedirect('/keranjang');

        $this->actingAs($user)->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_files', [
            'file_name' => 'poster.pdf',
        ]);
    }

    public function test_checkout_blocks_when_required_file_missing_from_cart(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'file_requirement' => Service::FILE_REQUIRED,
        ]);

        // Bypass cart file check by inserting session cart without files via direct add
        // that would normally fail — simulate legacy cart by posting without file then
        // forcing service requirement after the fact.
        $service->update(['file_requirement' => Service::FILE_NONE]);
        $this->actingAs($user)->post('/keranjang', [
            'service_id' => $service->id,
            'quantity' => 1,
        ]);
        $service->update(['file_requirement' => Service::FILE_REQUIRED]);

        $this->actingAs($user)->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ])->assertSessionHasErrors('files');
    }

    public function test_seeder_creates_yasin_custom_tiers_and_option_groups(): void
    {
        $this->seed(ServiceSeeder::class);

        $yasin = Service::query()->where('slug', 'buku-yasin')->sole();

        $this->assertSame(4, $yasin->optionGroups()->count());
        $this->assertSame(10000.0, $yasin->priceForQuantity(50));
        $this->assertSame(7500.0, $yasin->priceForQuantity(150));
        $this->assertSame(6000.0, $yasin->priceForQuantity(300));
        $this->assertSame(5000.0, $yasin->priceForQuantity(600));
    }

    public function test_seeder_creates_print_option_groups_for_a0_a5(): void
    {
        $this->seed(ServiceSeeder::class);

        foreach (['print-a0', 'print-a1', 'print-a2', 'print-a3', 'print-a4', 'print-a5'] as $slug) {
            $service = Service::query()->where('slug', $slug)->sole();

            $this->assertSame(
                3,
                $service->optionGroups()->count(),
                $slug.' harus punya 3 group opsi'
            );
            $this->assertSame(Service::FILE_REQUIRED, $service->file_requirement);
            $this->assertNull($service->min_quantity);
        }
    }

    public function test_seeder_marks_bulk_services_with_min_quantity(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(50, Service::query()->where('slug', 'undangan')->sole()->min_quantity);
        $this->assertSame(5, Service::query()->where('slug', 'nota-ncr')->sole()->min_quantity);
        $this->assertSame(10, Service::query()->where('slug', 'buku-yasin')->sole()->min_quantity);
        $this->assertNull(Service::query()->where('slug', 'print-a4')->sole()->min_quantity);

        $jualMins = Service::query()->where('type', Service::TYPE_JUAL)->pluck('min_quantity');
        $this->assertNotEmpty($jualMins);
        $jualMins->each(fn ($min) => $this->assertNull($min));
    }

    public function test_admin_can_create_service_with_option_groups(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();

        $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Print Custom Baru',
            'type' => Service::TYPE_JASA,
            'category_id' => $category->id,
            'unit' => 'lembar',
            'price' => 1000,
            'file_requirement' => Service::FILE_REQUIRED,
            'min_quantity' => 10,
            'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => '', 'unit_price' => 1000],
            ],
            'option_groups' => [
                [
                    'name' => 'Bahan',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => 1,
                    'options' => [
                        ['name' => 'HVS', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Art Paper', 'price' => 500, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ],
        ])->assertRedirect('/admin/services');

        $service = Service::query()->where('slug', 'print-custom-baru')->sole();

        $this->assertSame(10, $service->min_quantity);
        $this->assertSame(Service::FILE_REQUIRED, $service->file_requirement);
        $this->assertSame(1, $service->optionGroups()->count());
        $this->assertSame(2, ServiceOption::where('service_id', $service->id)->count());
    }

    public function test_service_detail_shows_live_total_and_quantity_inputs(): void
    {
        $service = $this->serviceWithGroups(['slug' => 'print-a4', 'name' => 'Print A4', 'price' => 500]);

        ServicePriceTier::create([
            'service_id' => $service->id,
            'min_qty' => 1,
            'max_qty' => 10,
            'unit_price' => 500,
            'sort_order' => 0,
        ]);

        $this->get('/layanan/print-a4')
            ->assertOk()
            ->assertSee('name="quantity"', false)
            ->assertSee('Pesan untuk penjual')
            ->assertSee('Total')
            ->assertSee('Rp 500', false);
    }
}
