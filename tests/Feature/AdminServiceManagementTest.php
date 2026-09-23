<?php

namespace Tests\Feature;

use App\Models\Service;
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

    public function test_admin_can_create_service(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/services', [
            'name' => 'Cetak banner',
            'description' => 'Cetak banner ukuran sedang. Harga contoh.',
            'category' => 'Print',
            'unit' => 'lembar',
            'price' => 25000,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/services');
        $this->assertDatabaseHas('services', [
            'name' => 'Cetak banner',
            'slug' => 'cetak-banner',
            'price' => 25000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_service(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put('/admin/services/'.$service->id, [
            'name' => 'Nama Baru',
            'description' => 'Diperbarui.',
            'category' => 'Fotokopi',
            'unit' => 'lembar',
            'price' => 900,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/services');
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Nama Baru',
            'slug' => 'nama-baru',
        ]);
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
            'unit' => '',
            'price' => 'abc',
        ]);

        $response->assertSessionHasErrors(['name', 'unit', 'price']);
    }
}
