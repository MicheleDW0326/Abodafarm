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
            // Delivery-specific fields (nullable for backward compatibility)
            $table->timestamp('estimated_delivery_time')
                ->nullable()
                ->after('delivered_at');

            $table->text('delivery_notes')
                ->nullable()
                ->after('estimated_delivery_time');

            $table->string('vehicle_info')
                ->nullable()
                ->after('delivery_notes');

            $table->string('courier_name')
                ->nullable()
                ->after('vehicle_info');

            $table->timestamp('delivery_started_at')
                ->nullable()
                ->after('courier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_delivery_time',
                'delivery_notes',
                'vehicle_info',
                'courier_name',
                'delivery_started_at',
            ]);
        });
    }
};
