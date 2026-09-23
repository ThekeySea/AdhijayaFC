<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount_due', 12, 2)->default(0)->after('total');
            $table->decimal('remaining_amount', 12, 2)->default(0)->after('amount_due');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('midtrans');
            $table->string('provider_transaction_id')->nullable()->index();
            $table->string('snap_token')->nullable();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->string('transaction_status')->default('pending');
            $table->string('fraud_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_notification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['amount_due', 'remaining_amount']);
        });
    }
};
