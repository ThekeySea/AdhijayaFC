<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderFile>
 */
class OrderFileFactory extends Factory
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
            'file_name' => 'dokumen-contoh.pdf',
            'storage_path' => 'orders/1/'.fake()->uuid().'-dokumen-contoh.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
        ];
    }
}
