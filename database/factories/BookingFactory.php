<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'booking_date' => now()->addDay()->toDateString(),
            'time_slot' => fake()->randomElement(Booking::TIME_SLOTS),
            'note' => null,
            'status' => 'SCHEDULED',
        ];
    }
}
