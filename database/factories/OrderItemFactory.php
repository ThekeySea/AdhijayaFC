<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
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
            'service_id' => Service::factory(),
            'service_name_snapshot' => fake()->words(3, true),
            'unit_price_snapshot' => fake()->randomFloat(2, 500, 50000),
            'quantity' => fake()->numberBetween(1, 10),
            'item_note' => null,
        ];
    }
}
