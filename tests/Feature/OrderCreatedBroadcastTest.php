<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Events\OrderCreated;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderCreatedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_dispatches_order_created_event(): void
    {
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'phone' => '6281234567890',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '10.00-11.00',
        ])->assertRedirect();

        Event::assertDispatched(OrderCreated::class, function (OrderCreated $event) use ($user): bool {
            return $event->order->customer_id === $user->id
                && $event->broadcastAs() === 'OrderCreated'
                && $event->broadcastWith()['order_number'] === $event->order->order_number;
        });
    }

    public function test_checkout_does_not_dispatch_event_when_validation_fails(): void
    {
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'phone' => '6281234567890',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => null,
        ])->assertSessionHasErrors('time_slot');

        Event::assertNotDispatched(OrderCreated::class);
    }

    /**
     * phpunit memakai BROADCAST_CONNECTION=null; channel di routes/channels.php
     * terdaftar di driver null. Untuk uji auth HTTP, paksa reverb + daftar ulang.
     */
    private function useReverbForChannelAuth(): void
    {
        config(['broadcasting.default' => 'reverb']);

        Broadcast::connection('reverb')->channel('admins', function (User $user) {
            return $user->role === Role::Admin;
        });
    }

    public function test_admin_can_authorize_admins_broadcast_channel(): void
    {
        $this->useReverbForChannelAuth();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-admins',
                'socket_id' => '123.456',
            ])
            ->assertOk();
    }

    public function test_customer_cannot_authorize_admins_broadcast_channel(): void
    {
        $this->useReverbForChannelAuth();
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-admins',
                'socket_id' => '123.456',
            ])
            ->assertForbidden();
    }

    public function test_guest_cannot_authorize_admins_broadcast_channel(): void
    {
        $this->useReverbForChannelAuth();

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-admins',
            'socket_id' => '123.456',
        ])->assertForbidden();
    }
}
