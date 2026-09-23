<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    public function test_amount_due_is_full_when_total_at_or_below_threshold(): void
    {
        $service = app(MidtransService::class);

        $this->assertSame(100000.0, $service->amountDueFor(100000));
        $this->assertSame(90000.0, $service->amountDueFor(90000));
        $this->assertFalse($service->isDownPayment(100000));
    }

    public function test_amount_due_is_half_when_total_above_threshold(): void
    {
        $service = app(MidtransService::class);

        $this->assertSame(75000.0, $service->amountDueFor(150000));
        $this->assertSame(150000.0, $service->amountDueFor(300000));
        $this->assertTrue($service->isDownPayment(150000));
    }

    public function test_checkout_sets_down_payment_for_order_above_threshold(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 150000]);

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $this->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect();

        $order = Order::sole();

        $this->assertSame(150000.0, (float) $order->total);
        $this->assertSame(75000.0, (float) $order->amount_due);
        $this->assertSame(75000.0, (float) $order->remaining_amount);
        $this->assertTrue($order->hasDownPayment());
    }

    public function test_checkout_uses_full_payment_below_threshold(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 50000]);

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 2]);

        $this->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
        ])->assertRedirect();

        $order = Order::sole();

        $this->assertSame(100000.0, (float) $order->total);
        $this->assertSame(100000.0, (float) $order->amount_due);
        $this->assertSame(0.0, (float) $order->remaining_amount);
        $this->assertFalse($order->hasDownPayment());
    }

    public function test_checkout_page_shows_down_payment_breakdown(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 200000]);

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Uang muka (DP) 50%')
            ->assertSee('Rp 100.000')
            ->assertSee('Sisa Rp 100.000');
    }

    public function test_customer_can_create_custom_charge_qris(): void
    {
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'transaction_id' => 'mid-123',
                'transaction_status' => 'pending',
                'qr_string' => '000201010211',
                'qr_code' => 'https://cdn.example/qr.png',
            ], 201),
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'subtotal' => 50000,
            'total' => 50000,
            'amount_due' => 50000,
            'remaining_amount' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/pay', ['method' => 'qris']);

        $response->assertOk()
            ->assertJsonPath('method', 'qris')
            ->assertJsonPath('qr_string', '000201010211')
            ->assertJsonPath('qr_code', 'https://cdn.example/qr.png')
            ->assertJsonPath('amount_due', 50000);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v2/charge')
                && $request->data()['payment_type'] === 'qris';
        });
    }

    public function test_customer_can_create_custom_charge_bank_transfer(): void
    {
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'transaction_id' => 'mid-va-1',
                'transaction_status' => 'pending',
                'va_numbers' => [
                    ['bank' => 'bca', 'va_number' => '1234567890'],
                ],
            ], 201),
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/pay', [
                'method' => 'bank_transfer',
                'bank' => 'bca',
            ]);

        $response->assertOk()
            ->assertJsonPath('method', 'bank_transfer')
            ->assertJsonPath('bank', 'bca')
            ->assertJsonPath('va_number', '1234567890');

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $body['payment_type'] === 'bank_transfer'
                && ($body['bank_transfer']['bank'] ?? null) === 'bca';
        });
    }

    public function test_payment_status_endpoint_reports_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->getJson('/pesanan/'.$order->id.'/payment/status')
            ->assertOk()
            ->assertJsonPath('is_paid', true)
            ->assertJsonPath('payment_status', 'PAID');
    }

    public function test_customer_can_skip_payment_and_order_is_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/payment/skip')
            ->assertOk()
            ->assertJsonPath('is_paid', true);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPaid());
        $this->assertSame('skipped', $payment->payment_type);
    }

    public function test_skip_payment_is_idempotent_when_already_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/payment/skip')
            ->assertOk()
            ->assertJsonPath('is_paid', true);
    }

    public function test_skip_payment_rejects_other_customer(): void
    {
        $order = Order::factory()->pendingPayment()->create([
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/pesanan/'.$order->id.'/payment/skip')
            ->assertForbidden();

        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_rejects_unsupported_charge_method(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/pay', ['method' => 'credit_card'])
            ->assertUnprocessable();
    }

    public function test_order_detail_shows_custom_overlay_markup(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('payment-overlay-root', false)
            ->assertSee('paymentOverlay', false)
            ->assertSee('z-[100]', false)
            ->assertSee('QRIS')
            ->assertSee('Transfer bank')
            ->assertSee('Lewati pembayaran');
    }

    public function test_admin_cannot_request_charge(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pendingPayment()->create([
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($admin)
            ->postJson('/pesanan/'.$order->id.'/pay')
            ->assertForbidden();
    }

    public function test_other_customer_cannot_request_charge(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->postJson('/pesanan/'.$order->id.'/pay')
            ->assertForbidden();
    }

    public function test_notification_settlement_marks_order_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'subtotal' => 150000,
            'total' => 150000,
            'amount_due' => 75000,
            'remaining_amount' => 75000,
        ]);

        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => '75000',
            'transaction_status' => 'settlement',
        ]);

        $this->postJson('/midtrans/notification', $notification)
            ->assertOk();

        $order->refresh();

        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->status);

        $payment = Payment::sole();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('midtrans-tx-1', $payment->provider_transaction_id);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(75000.0, (float) $payment->gross_amount);
    }

    public function test_notification_settlement_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);
        Payment::factory()->settled()->create(['order_id' => $order->id]);

        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => '50000',
            'transaction_status' => 'settlement',
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertOk();

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(1, Payment::count());
    }

    public function test_notification_expire_marks_payment_failed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => '50000',
            'transaction_status' => 'expire',
            'status_code' => '407',
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertOk();

        $order->refresh();

        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
        $this->assertSame(OrderStatus::PaymentFailed, $order->status);
    }

    public function test_notification_does_not_revive_cancelled_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Unpaid,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $notification = $this->signedNotification([
            'order_id' => $order->order_number,
            'gross_amount' => '50000',
            'transaction_status' => 'settlement',
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_notification_rejects_invalid_signature(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->postJson('/midtrans/notification', [
            'order_id' => $order->order_number,
            'gross_amount' => '50000',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'signature_key' => str_repeat('a', 128),
        ])->assertForbidden();

        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_notification_returns_404_for_unknown_order(): void
    {
        $notification = $this->signedNotification([
            'order_id' => 'FA-XXXX',
            'gross_amount' => '1000',
        ]);

        $this->postJson('/midtrans/notification', $notification)->assertNotFound();
    }

    public function test_order_detail_shows_pay_button_when_unpaid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 150000,
            'amount_due' => 75000,
            'remaining_amount' => 75000,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('id="pay-now"', false)
            ->assertSee('Bayar sekarang')
            ->assertSee('Uang muka (DP) 50%')
            ->assertSee('QRIS');
    }

    public function test_order_detail_hides_pay_button_when_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertDontSee('id="pay-now"', false)
            ->assertDontSee('payment-overlay-root', false);
    }

    public function test_csrf_is_exempt_for_notification_webhook(): void
    {
        $serverKey = (string) config('midtrans.server_key');
        $order = Order::factory()->pendingPayment()->create([
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $notification = [
            'order_id' => $order->order_number,
            'gross_amount' => '50000',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'signature_key' => hash('sha512', implode('', [
                $order->order_number,
                '50000',
                '200',
                'settlement',
                $serverKey,
            ])),
        ];

        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson('/midtrans/notification', $notification)
            ->assertOk();

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_server_key_not_exposed_in_client_meta(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create([
            'customer_id' => $user->id,
            'total' => 50000,
            'amount_due' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/pesanan/'.$order->id);

        $response->assertOk();
        $response->assertDontSee((string) config('midtrans.server_key'), false);
    }
}
