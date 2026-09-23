<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_options', function (Blueprint $table) {
            $table->foreignId('group_id')
                ->nullable()
                ->after('service_id')
                ->constrained('service_option_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_options', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
