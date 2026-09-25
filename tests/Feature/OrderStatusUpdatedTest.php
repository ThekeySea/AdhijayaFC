<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderStatusUpdatedTest extends TestCase
{
    use RefreshDatabase;

    private function signedNotification(array $overrides = []): array
    {
        $serverKey = (string) config('midtrans.server_key');
        $notification = array_merge([
            'order_id' => '',
            'gross_amount' => '0',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-tx-1',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
        ], $overrides);

        $notification['signature_key'] = hash('sha512', implode('', [
            $notification['order_id'],
            $notification['gross_amount'],
            $notification['status_code'],
            $notification['transaction_status'],
            $serverKey,
        ]));

        return $notification;
    }

    public function test_admin_status_update_dispatches_order_status_updated(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PROCESSING'])
            ->assertRedirect();

        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) use ($order): bool {
            $payload = $event->broadcastWith();

            return $event->previousStatus === OrderStatus::Paid
                && $event->order->status === OrderStatus::Processing
                && $event->broadcastAs() === 'OrderStatusUpdated'
                && $payload['id'] === $order->id
                && $payload['previous_status'] === 'PAID'
                && $payload['status'] === 'PROCESSING'
                && $payload['previous_status_badge_class'] !== ''
                && $payload['status_badge_class'] !== ''
                && $payload['payment_status'] !== ''
                && $payload['previous_payment_status'] !== ''
                && $payload['can_cancel'] === false
                && $payload['tracking_url'] !== ''
                && count($event->broadcastOn()) === 2;
        });
    }

    public function test_admin_status_update_with_same_status_does_not_dispatch(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'PAID'])
            ->assertRedirect();

        Event::assertNotDispatched(OrderStatusUpdated::class);
    }

    public function test_customer_cancel_dispatches_order_status_updated(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $customer = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->post('/pesanan/'.$order->id.'/cancel')
            ->assertRedirect();

        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event): bool {
            $payload = $event->broadcastWith();

            return $event->previousStatus === OrderStatus::PendingPayment
                && $payload['status'] === 'CANCELLED'
                && $payload['status_label'] === 'Dibatalkan'
                && $payload['can_cancel'] === false;
        });
    }

    public function test_payment_skip_dispatches_order_status_updated(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $customer = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->postJson('/pesanan/'.$order->id.'/payment/skip')
            ->assertOk();

        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event): bool {
            $payload = $event->broadcastWith();

            return $event->previousPaymentStatus === PaymentStatus::Unpaid
                && $payload['payment_status'] === 'PAID'
                && $payload['previous_payment_status'] === 'UNPAID'
                && $payload['status'] === 'PAID'
                && $payload['previous_status'] === 'PENDING_PAYMENT';
        });
    }

    public function test_payment_webhook_dispatches_order_status_updated(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $order = Order::factory()->pendingPayment()->create();
        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => (string) $order->amount_due,
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertOk();

        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) use ($order): bool {
            $payload = $event->broadcastWith();

            return $event->order->is($order)
                && $payload['payment_status'] === 'PAID'
                && $payload['previous_payment_status'] === 'UNPAID'
                && $payload['status'] === 'PAID';
        });
    }

    public function test_payment_webhook_idempotent_does_not_dispatch(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $order = Order::factory()->paid()->create();
        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => (string) $order->amount_due,
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertOk();

        Event::assertNotDispatched(OrderStatusUpdated::class);
    }

    public function test_owner_can_load_tracking_fragment(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->get('/pesanan/'.$order->id.'/tracking')
            ->assertOk()
            ->assertSee('Lacak pesanan')
            ->assertSee($order->status->label());
    }

    public function test_other_customer_cannot_load_tracking_fragment(): void
    {
        $order = Order::factory()->pendingPayment()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get('/pesanan/'.$order->id.'/tracking')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_tracking_fragment(): void
    {
        $order = Order::factory()->pendingPayment()->create();

        $this->get('/pesanan/'.$order->id.'/tracking')->assertRedirect('/login');
    }
}
