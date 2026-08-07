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
            // Staff assignment fields for future use
            // These are nullable to maintain backward compatibility with existing orders
            $table->foreignId('assigned_staff_id')
                ->nullable()
                ->after('shipment_status_id')
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamp('assigned_at')
                ->nullable()
                ->after('assigned_staff_id');

            $table->foreignId('assigned_by')
                ->nullable()
                ->after('assigned_at')
                ->constrained('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_staff_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assigned_staff_id', 'assigned_at', 'assigned_by']);
        });
    }
};
