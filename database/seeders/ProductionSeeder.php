<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production data without depending on faker/factories.
     */
    public function run(): void
    {
        if (! User::query()->where('email', 'admin@adhijaya.test')->exists()) {
            User::query()->create([
                'name' => 'Admin Adhijaya',
                'email' => 'admin@adhijaya.test',
                'phone' => '6281234567890',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'email_verified_at' => now(),
            ]);
        }

        if (Service::query()->doesntExist()) {
            $this->call(ServiceSeeder::class);
        }
    }
}
