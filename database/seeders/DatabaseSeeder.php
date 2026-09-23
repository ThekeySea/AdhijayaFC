<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! User::query()->where('email', 'admin@adhijaya.test')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Admin Adhijaya',
                'email' => 'admin@adhijaya.test',
            ]);
        }

        if (! User::query()->where('email', 'customer@adhijaya.test')->exists()) {
            User::factory()->create([
                'name' => 'Pelanggan Contoh',
                'email' => 'customer@adhijaya.test',
            ]);
        }

        if (Service::query()->doesntExist()) {
            $this->call([
                ServiceSeeder::class,
            ]);
        }
    }
}
