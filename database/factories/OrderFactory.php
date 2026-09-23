<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'FA-'.strtoupper(fake()->unique()->bothify('####')),
            'customer_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => 0,
            'additional_fee' => 0,
            'total' => 0,
            'amount_due' => 0,
            'remaining_amount' => 0,
            'customer_note' => null,
        ];
    }

    public function pendingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'amount_due' => $attributes['total'] ?? 0,
            'remaining_amount' => 0,
        ]);
    }
}
