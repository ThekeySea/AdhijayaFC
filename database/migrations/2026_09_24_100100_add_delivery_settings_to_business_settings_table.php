<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->decimal('delivery_rate_per_km', 10, 2)->default(3000)->after('hours_sunday');
            $table->decimal('delivery_min_fee', 10, 2)->default(5000)->after('delivery_rate_per_km');
            $table->decimal('delivery_discount_per_100k', 10, 2)->default(5000)->after('delivery_min_fee');
            $table->unsignedSmallInteger('delivery_max_radius_km')->default(20)->after('delivery_discount_per_100k');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_rate_per_km',
                'delivery_min_fee',
                'delivery_discount_per_100k',
                'delivery_max_radius_km',
            ]);
        });
    }
};
