<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_order_pages(): void
    {
        $order = Order::factory()->create();

        $this->get('/pesanan')->assertRedirect('/login');
        $this->get('/pesanan/'.$order->id)->assertRedirect('/login');
    }

    public function test_customer_can_view_own_order_history(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'subtotal' => 1500,
            'total' => 1500,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_name_snapshot' => 'Print dokumen',
        ]);

        $response = $this->actingAs($user)->get('/pesanan');

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Print dokumen');
        $response->assertSee('Menunggu pembayaran');
        $response->assertSee('Rp 1.500');
    }

    public function test_customer_order_history_excludes_other_customers(): void
    {
        $user = User::factory()->create();
        $otherOrder = Order::factory()->create();

        $response = $this->actingAs($user)->get('/pesanan');

        $response->assertOk();
        $response->assertDontSee($otherOrder->order_number);
        $response->assertSee('Belum ada pesanan');
    }

    public function test_customer_can_view_own_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'subtotal' => 900,
            'total' => 900,
            'customer_note' => 'Tolong rangkum',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_name_snapshot' => 'Fotokopi hitam putih',
            'unit_price_snapshot' => 300,
            'quantity' => 3,
            'item_note' => 'A4, 2 sisi',
        ]);

        $response = $this->actingAs($user)->get('/pesanan/'.$order->id);

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Fotokopi hitam putih');
        $response->assertSee('A4, 2 sisi');
        $response->assertSee('Tolong rangkum');
        $response->assertSee('Menunggu pembayaran');
        $response->assertSee('Batalkan pesanan');
        $response->assertSee('confirm-cancel-order', false);
        $response->assertSee('Ya, batalkan pesanan');
        $response->assertSee('Lacak pesanan');
        $response->assertSee('Pesanan dibuat');
        $response->assertSee('Sekarang');
    }

    public function test_order_detail_tracking_shows_processing_progress(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('Lacak pesanan')
            ->assertSee('Sedang dikerjakan')
            ->assertSee('Sekarang')
            ->assertSee('Sudah dibayar');
    }

    public function test_order_detail_tracking_shows_cancelled_state(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Cancelled,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('Pesanan ini dibatalkan')
            ->assertDontSee('Sekarang');
    }

    public function test_customer_cannot_view_other_customer_order(): void
    {
        $user = User::factory()->create();
        $otherOrder = Order::factory()->create();

        $this->actingAs($user)
            ->get('/pesanan/'.$otherOrder->id)
            ->assertForbidden();
    }

    public function test_admin_can_view_any_order_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $this->actingAs($admin)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/cancel');

        $response->assertRedirect('/pesanan/'.$order->id);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_customer_cannot_cancel_paid_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/cancel')
            ->assertRedirect('/pesanan/'.$order->id);

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertNotNull(session('status'));
    }

    public function test_customer_cannot_cancel_other_customer_order(): void
    {
        $order = Order::factory()->pendingPayment()->create();

        $this->actingAs(User::factory()->create())
            ->post('/pesanan/'.$order->id.'/cancel')
            ->assertForbidden();

        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->status);
    }

    public function test_order_detail_shows_cancelled_status_label(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Cancelled,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertDontSee('Batalkan pesanan');
    }

    public function test_order_detail_shows_whatsapp_link_when_configured(): void
    {
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER=6289876543210');
        $_ENV['WHATSAPP_NUMBER'] = '6289876543210';
        $_SERVER['WHATSAPP_NUMBER'] = '6289876543210';

        try {
            $user = User::factory()->create();
            $order = Order::factory()->pendingPayment()->create([
                'customer_id' => $user->id,
            ]);

            $this->actingAs($user)
                ->get('/pesanan/'.$order->id)
                ->assertOk()
                ->assertSee('Hubungi admin', false)
                ->assertSee('wa.me/6289876543210', false)
                ->assertSee(rawurlencode($order->order_number), false);
        } finally {
            putenv($original === false ? 'WHATSAPP_NUMBER' : 'WHATSAPP_NUMBER='.$original);
            unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);
        }
    }

    public function test_order_detail_hides_whatsapp_when_not_configured(): void
    {
        $original = getenv('WHATSAPP_NUMBER');
        putenv('WHATSAPP_NUMBER');
        unset($_ENV['WHATSAPP_NUMBER'], $_SERVER['WHATSAPP_NUMBER']);

        try {
            $user = User::factory()->create();
            $order = Order::factory()->pendingPayment()->create([
                'customer_id' => $user->id,
            ]);

            $this->actingAs($user)
                ->get('/pesanan/'.$order->id)
                ->assertOk()
                ->assertDontSee('wa.me/', false);
        } finally {
            if ($original !== false) {
                putenv('WHATSAPP_NUMBER='.$original);
                $_ENV['WHATSAPP_NUMBER'] = $original;
                $_SERVER['WHATSAPP_NUMBER'] = $original;
            }
        }
    }
}
