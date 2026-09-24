<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'category_id' => ServiceCategory::factory(),
            'type' => Service::TYPE_JASA,
            'unit' => fake()->randomElement(['lembar', 'halaman', 'isi']),
            'price' => fake()->randomFloat(2, 500, 50000),
            'is_active' => true,
            'image_url' => null,
            'min_quantity' => null,
            'file_requirement' => Service::FILE_NONE,
            'min_ready_minutes' => 30,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function jual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Service::TYPE_JUAL,
            'category_id' => null,
        ]);
    }
}
