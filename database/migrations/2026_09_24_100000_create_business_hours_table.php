<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique();
            $table->boolean('is_open')->default(true);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->timestamps();
        });

        // 0 = Sunday … 6 = Saturday (PHP date('w')). Default semua hari buka.
        foreach (range(0, 6) as $day) {
            DB::table('business_hours')->insert([
                'day_of_week' => $day,
                'is_open' => true,
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
