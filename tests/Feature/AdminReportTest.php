<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_reports(): void
    {
        $this->get('/admin/laporan')->assertRedirect('/login');
    }

    public function test_customer_is_forbidden_from_admin_reports(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/laporan')->assertForbidden();
    }

    public function test_admin_can_view_reports_page_with_charts(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertSee('Laporan');
        $response->assertSee('Grafik pesanan');
        $response->assertSee('Status pesanan');
        $response->assertSee('Laris manis');
        $response->assertSee('Ringkasan status');
        $response->assertSee('Cetak / PDF');
        $response->assertSee('7h');
        $response->assertSee('14h');
        $response->assertSee('30h');
    }

    public function test_reports_period_filter_accepts_valid_values(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/laporan?periode=7');

        $response->assertOk();
        $response->assertSee('7 hari terakhir');
        $response->assertSee(route('admin.reports.index', ['periode' => 7]), false);
    }

    public function test_reports_period_filter_falls_back_on_invalid_value(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/laporan?periode=99');

        $response->assertOk();
        $response->assertSee('14 hari terakhir');
    }

    public function test_reports_show_status_summary_table_with_totals(): void
    {
        $admin = User::factory()->admin()->create();

        Order::factory()->completed()->create(['total' => 100000]);
        Order::factory()->completed()->create(['total' => 50000]);
        Order::factory()->pendingPayment()->create(['total' => 20000]);

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertSee('Ringkasan status');
        $response->assertSee('Selesai');
        $response->assertSee('Menunggu pembayaran');
        $response->assertSee('150.000', false);
        $response->assertSee('170.000', false);
    }

    public function test_reports_show_revenue_and_order_counts(): void
    {
        $admin = User::factory()->admin()->create();

        Order::factory()->completed()->create(['total' => 100000]);
        Order::factory()->completed()->create(['total' => 50000]);
        Order::factory()->pendingPayment()->create(['total' => 20000]);

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertSee('150.000', false);
        $response->assertSee('3', false);
        $response->assertSee('Selesai');
    }

    public function test_reports_show_top_services_from_order_items(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['name' => 'Fotokopi A4']);

        $order = Order::factory()->completed()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'service_name_snapshot' => 'Fotokopi A4',
            'quantity' => 12,
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertSee('Fotokopi A4');
        $response->assertSee('12 item');
    }

    public function test_reports_show_empty_state_when_no_orders_in_period(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertSee('Belum ada pesanan pada periode ini.');
    }

    public function test_reports_chart_shows_bars_when_orders_exist(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->paid()->create(['created_at' => now()]);
        Order::factory()->paid()->create(['created_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/laporan');

        $response->assertOk();
        $response->assertDontSee('Belum ada pesanan pada periode ini.');
        $response->assertSee('title="'.now()->locale('id')->isoFormat('DD MMM').': 2 pesanan"', false);
        $response->assertSee('Maks 2 order/hari', false);
    }

    public function test_reports_page_includes_nav_link(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(route('admin.reports.index'), false);
        $response->assertSee('Laporan');
    }
}
