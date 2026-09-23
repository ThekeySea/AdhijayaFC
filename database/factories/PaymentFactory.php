<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'midtrans',
            'provider_transaction_id' => null,
            'snap_token' => null,
            'gross_amount' => 0,
            'transaction_status' => 'pending',
            'fraud_status' => null,
            'payment_type' => null,
            'paid_at' => null,
            'raw_notification' => null,
        ];
    }

    public function settled(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_status' => 'settlement',
            'provider_transaction_id' => 'midtrans-'.fake()->uuid(),
            'paid_at' => now(),
        ]);
    }
}
