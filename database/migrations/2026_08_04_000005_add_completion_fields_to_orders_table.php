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
            // Completion tracking fields
            $table->timestamp('completed_at')->nullable()->after('delivery_completed_at');
            $table->boolean('completed_by_customer')->default(false)->after('completed_at');
            $table->boolean('completed_automatically')->default(false)->after('completed_by_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'completed_at',
                'completed_by_customer',
                'completed_automatically',
            ]);
        });
    }
};
