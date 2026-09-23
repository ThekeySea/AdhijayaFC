<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOption>
 */
class ServiceOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'group_id' => null,
            'name' => fake()->randomElement(['Jilid spiral', 'Laminating A4', 'Jilid ring']),
            'price' => fake()->randomFloat(2, 1000, 10000),
            'pricing' => ServiceOption::PRICING_PER_UNIT,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function perOrder(): static
    {
        return $this->state(fn () => [
            'pricing' => ServiceOption::PRICING_PER_ORDER,
        ]);
    }
}
