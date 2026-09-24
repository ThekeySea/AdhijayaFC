<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_type')->default('pickup')->after('payment_status');
            $table->text('delivery_address')->nullable()->after('customer_note');
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_longitude');
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('delivery_distance_km');
            $table->string('delivery_mode')->nullable()->after('delivery_fee');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->change();
            $table->string('time_slot')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_type',
                'delivery_address',
                'delivery_latitude',
                'delivery_longitude',
                'delivery_distance_km',
                'delivery_fee',
                'delivery_mode',
            ]);
        });
    }
};
