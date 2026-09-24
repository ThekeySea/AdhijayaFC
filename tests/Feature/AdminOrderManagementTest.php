<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_orders(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
    }

    public function test_customer_is_forbidden_from_admin_orders(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($customer)->get('/admin/orders')->assertForbidden();
        $this->actingAs($customer)->get('/admin/orders/'.$order->id)->assertForbidden();
        $this->actingAs($customer)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PROCESSING'])
            ->assertForbidden();
    }

    public function test_admin_can_list_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pendingPayment()->create();

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Menunggu pembayaran');
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Order::factory()->pendingPayment()->create();
        $paid = Order::factory()->paid()->create(['total' => 50000]);

        $response = $this->actingAs($admin)->get('/admin/orders?status=PAID');

        $response->assertOk();
        $response->assertSee($paid->order_number);
        $response->assertDontSee($pending->order_number);
    }

    public function test_admin_can_view_order_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'total' => 150000,
            'amount_due' => 75000,
            'remaining_amount' => 75000,
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Status timeline')
            ->assertSee('Pelanggan')
            ->assertSee('Lacak pesanan');
    }

    public function test_admin_order_detail_shows_step_tracking_colors(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'status' => OrderStatus::Paid,
        ]);

        $html = (string) $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('bg-emerald-600', $html);
        $this->assertStringContainsString('text-emerald-700', $html);
        $this->assertStringContainsString('bg-slate-500', $html);
    }

    public function test_admin_order_list_uses_step_status_badge_colors(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->paid()->create(['total' => 50000]);
        Order::factory()->create(['status' => OrderStatus::Processing]);

        $html = (string) $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('bg-emerald-50 text-emerald-700', $html);
        $this->assertStringContainsString('bg-sky-50 text-sky-700', $html);
    }

    public function test_admin_can_advance_paid_order_to_processing(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PROCESSING'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);
    }

    public function test_admin_can_advance_processing_to_ready_to_completed(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create(['status' => OrderStatus::Processing]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'READY'])
            ->assertRedirect();

        $this->assertSame(OrderStatus::Ready, $order->fresh()->status);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'COMPLETED'])
            ->assertRedirect();

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }

    public function test_admin_can_set_any_tracking_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'COMPLETED'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }

    public function test_admin_can_cancel_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'CANCELLED'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_admin_order_detail_shows_all_status_options(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Status timeline')
            ->assertSee('Tentukan status')
            ->assertSee('Simpan status')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Sedang diproses')
            ->assertSee('Siap diambil')
            ->assertSee('Selesai')
            ->assertSee('Dibatalkan');
    }

    public function test_status_update_rejects_invalid_status_value(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'UNKNOWN'])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_status_update_with_same_status_is_noop(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PAID'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status')
            ->assertSessionMissing('wa_share_ready');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_status_update_sets_whatsapp_share_url_for_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => User::factory()->create(['phone' => '6289876543210'])->id,
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PROCESSING'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('wa_share_ready', true)
            ->assertSessionHas('wa_share_url');

        $url = (string) session('wa_share_url');
        $this->assertStringStartsWith('https://wa.me/6289876543210?text=', $url);
        $this->assertStringContainsString($order->order_number, urldecode($url));
        $this->assertStringContainsString('Sedang diproses', urldecode($url));
    }

    public function test_status_update_without_customer_phone_has_no_share_url(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => User::factory()->create(['phone' => null])->id,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PROCESSING'])
            ->assertSessionHas('wa_share_ready', false);

        $this->assertNull(session('wa_share_url'));

        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);
    }

    public function test_admin_order_detail_shows_share_button_after_status_update(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => User::factory()->create(['phone' => '6289876543210'])->id,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'READY']);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Siap dikirim')
            ->assertSee('Kirim update status ke pelanggan')
            ->assertSee('wa.me/6289876543210', false);
    }

    public function test_admin_order_detail_shows_share_hint_when_customer_has_phone(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => User::factory()->create(['phone' => '6289876543210'])->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Kirim status tracking via WhatsApp')
            ->assertSee('wa.me/6289876543210', false);
    }

    public function test_admin_order_detail_shows_whatsapp_link_when_configured(): void
    {
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER=6281234567890');
        $_ENV['WHATSAPP_NUMBER'] = '6281234567890';
        $_SERVER['WHATSAPP_NUMBER'] = '6281234567890';

        try {
            $admin = User::factory()->admin()->create();
            $order = Order::factory()->paid()->create();
            $order->customer->update(['phone' => '6289876543210']);
            $order->load('customer');

            $this->actingAs($admin)
                ->get('/admin/orders/'.$order->id)
                ->assertOk()
                ->assertSee('Hubungi via WhatsApp', false)
                ->assertSee('wa.me/6289876543210', false);
        } finally {
            putenv($original === false ? 'WHATSAPP_NUMBER' : 'WHATSAPP_NUMBER='.$original);
            unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);
        }
    }

    public function test_dashboard_shows_real_counts_and_recent_orders(): void
    {
        $admin = User::factory()->admin()->create();
        Service::factory()->create(['is_active' => true]);
        Order::factory()->pendingPayment()->create();
        Order::factory()->paid()->create(['total' => 50000]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Menunggu pembayaran');
        $response->assertSee('Pesanan terbaru');
        $response->assertSee('Booking mendatang');
        $response->assertDontSee('Kelola pesanan akan tersedia pada tahap berikutnya');
    }

    public function test_dashboard_shows_empty_states_when_no_orders(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Belum ada pesanan masuk')
            ->assertSee('Belum ada booking mendatang');
    }
}
