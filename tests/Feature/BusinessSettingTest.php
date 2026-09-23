<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::flushCurrent();
    }

    public function test_guest_cannot_edit_business_settings(): void
    {
        $this->get('/admin/info-usaha')->assertRedirect('/login');
    }

    public function test_customer_cannot_edit_business_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/info-usaha')->assertForbidden();
    }

    public function test_admin_can_view_business_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/info-usaha');

        $response->assertOk();
        $response->assertSee('Edit info usaha');
        $response->assertSee('Info Usaha');
    }

    public function test_admin_can_update_business_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put('/admin/info-usaha', [
            'name' => 'Adhijaya Fotocopy',
            'tagline' => 'Cetak cepat, rapi, terjangkau.',
            'about' => 'Kami melayani fotokopi sejak 2010.',
            'address' => 'Jl. Merdeka No. 1',
            'latitude' => -6.9147440,
            'longitude' => 107.6097810,
            'phone' => '0211234567',
            'whatsapp_number' => '6281234567890',
            'hours_weekday' => '08.00 – 21.00',
            'hours_sunday' => '09.00 – 15.00',
        ]);

        $response->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('business_settings', [
            'name' => 'Adhijaya Fotocopy',
            'tagline' => 'Cetak cepat, rapi, terjangkau.',
            'address' => 'Jl. Merdeka No. 1',
            'latitude' => -6.914744,
            'longitude' => 107.609781,
            'hours_weekday' => '08.00 – 21.00',
        ]);
    }

    public function test_business_settings_validation_rejects_invalid_coordinates(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from('/admin/info-usaha')
            ->put('/admin/info-usaha', [
                'name' => 'Adhijaya',
                'latitude' => 999,
                'longitude' => -999,
            ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_admin_info_usaha_page_shows_location_picker(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/info-usaha');

        $response->assertOk();
        $response->assertSee('Posisi lokasi di peta', false);
        $response->assertSee('id="location-picker"', false);
        $response->assertSee('name="latitude"', false);
        $response->assertSee('name="longitude"', false);
    }

    public function test_business_settings_validation_requires_name(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from('/admin/info-usaha')
            ->put('/admin/info-usaha', ['name' => '']);

        $response->assertSessionHasErrors('name');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_sidebar_shows_info_usaha_instead_of_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Info Usaha');
        $response->assertSee(route('admin.business-settings.edit'));
        $this->assertStringNotContainsString('Profil', $response->getContent());
    }

    public function test_sidebar_uses_dark_blue_and_white_nav_text(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('bg-foreground', false);
        $response->assertSee('text-white', false);
    }
}
