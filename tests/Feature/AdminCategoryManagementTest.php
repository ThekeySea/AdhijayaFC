<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_category_management(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_category_management(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_view_category_list(): void
    {
        $admin = User::factory()->admin()->create();
        ServiceCategory::factory()->create(['name' => 'Digital Print']);

        $this->actingAs($admin)->get('/admin/categories')
            ->assertOk()
            ->assertSee('Digital Print');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Media Promosi UV',
            'description' => 'Banner dan sejenisnya.',
            'sort_order' => 5,
            'is_active' => 1,
        ])->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('service_categories', [
            'name' => 'Media Promosi UV',
            'slug' => 'media-promosi-uv',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_and_toggle_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create(['name' => 'Lama', 'is_active' => true]);

        $this->actingAs($admin)->put('/admin/categories/'.$category->id, [
            'name' => 'Baru',
            'sort_order' => 2,
            'is_active' => 1,
        ])->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('service_categories', [
            'id' => $category->id,
            'name' => 'Baru',
            'slug' => 'baru',
        ]);

        $this->actingAs($admin)->post('/admin/categories/'.$category->id.'/toggle')->assertRedirect();
        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_category_requires_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => '',
        ])->assertSessionHasErrors('name');
    }

    public function test_admin_can_delete_category_and_services_lose_relation(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->delete('/admin/categories/'.$category->id)
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
        $this->assertNull($service->fresh()->category_id);
    }
}
