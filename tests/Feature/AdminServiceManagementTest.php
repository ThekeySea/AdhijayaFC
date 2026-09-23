<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_service_management(): void
    {
        $this->get('/admin/services')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_service_management(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/services')->assertForbidden();
    }

    public function test_admin_can_view_service_list(): void
    {
        $admin = User::factory()->admin()->create();
        Service::factory()->create();

        $this->actingAs($admin)->get('/admin/services')->assertOk();
    }

    public function test_admin_can_create_service_with_category_and_tiers(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Print Banner',
            'description' => 'Cetak banner ukuran sedang. Harga contoh.',
            'type' => Service::TYPE_JASA,
            'category_id' => $category->id,
            'unit' => 'lembar',
            'price' => 25000,
            'is_active' => 1,
            'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => 10, 'unit_price' => 25000],
                ['min_qty' => 11, 'max_qty' => 50, 'unit_price' => 20000],
                ['min_qty' => 101, 'max_qty' => '', 'unit_price' => 15000],
            ],
        ]);

        $response->assertRedirect('/admin/services');
        $this->assertDatabaseHas('services', [
            'name' => 'Print Banner',
            'slug' => 'print-banner',
            'category_id' => $category->id,
            'price' => 25000,
            'is_active' => true,
        ]);
        $this->assertDatabaseCount('service_price_tiers', 3);
    }

    public function test_admin_can_update_service(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put('/admin/services/'.$service->id, [
            'name' => 'Nama Baru',
            'description' => 'Diperbarui.',
            'type' => Service::TYPE_JASA,
            'category_id' => $category->id,
            'unit' => 'lembar',
            'price' => 900,
            'is_active' => 1,
            'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => 10, 'unit_price' => 900],
            ],
        ]);

        $response->assertRedirect('/admin/services');
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Nama Baru',
            'slug' => 'nama-baru',
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseCount('service_price_tiers', 1);
    }

    public function test_admin_can_create_jual_service_without_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Pulpen Promosi Toko',
            'type' => Service::TYPE_JUAL,
            'category_id' => '',
            'unit' => 'pcs',
            'price' => 3000,
            'is_active' => 1,
            'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => 10, 'unit_price' => 3000],
            ],
        ])->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', [
            'name' => 'Pulpen Promosi Toko',
            'type' => Service::TYPE_JUAL,
            'category_id' => null,
        ]);
    }

    public function test_admin_rejects_overlapping_price_tiers(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();

        $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Tier Aneh',
            'type' => Service::TYPE_JASA,
            'category_id' => $category->id,
            'unit' => 'pcs',
            'price' => 1000,
            'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => 50, 'unit_price' => 1000],
                ['min_qty' => 11, 'max_qty' => 60, 'unit_price' => 900],
            ],
        ])->assertSessionHasErrors('price_tiers.1.min_qty');
    }

    public function test_admin_can_toggle_service_status(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post('/admin/services/'.$service->id.'/toggle')
            ->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_admin_can_delete_service(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->delete('/admin/services/'.$service->id)
            ->assertRedirect('/admin/services');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_admin_service_requires_valid_data(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->from('/admin/services/create')->post('/admin/services', [
            'name' => '',
            'type' => '',
            'unit' => '',
            'price' => 'abc',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'unit', 'price']);
    }
}
