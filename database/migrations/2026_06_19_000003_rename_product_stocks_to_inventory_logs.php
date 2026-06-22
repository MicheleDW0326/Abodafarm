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
        // Check if we need to rename (product_stocks exists but inventory_logs doesn't)
        if (Schema::hasTable('product_stocks') && !Schema::hasTable('inventory_logs')) {
            Schema::rename('product_stocks', 'inventory_logs');
        }

        // Now modify the inventory_logs table if it exists
        if (Schema::hasTable('inventory_logs')) {
            $columns = Schema::getColumnListing('inventory_logs');

            Schema::table('inventory_logs', function (Blueprint $table) use ($columns) {
                // Remove supplier column if it exists
                if (in_array('supplier', $columns)) {
                    $table->dropColumn('supplier');
                }

                // Add type column if it doesn't exist
                if (!in_array('type', $columns)) {
                    $table->string('type', 50)->default('restock')->after('product_id');
                }

                // Add notes if it doesn't exist
                if (!in_array('notes', $columns)) {
                    $table->text('notes')->nullable()->after('date');
                }

                // Add health_status if it doesn't exist
                if (!in_array('health_status', $columns)) {
                    $table->string('health_status')->nullable()->after('notes');
                }

                // Add vaccination_records if it doesn't exist
                if (!in_array('vaccination_records', $columns)) {
                    $table->string('vaccination_records')->nullable()->after('health_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_logs')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_logs', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('inventory_logs', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('inventory_logs', 'health_status')) {
                    $table->dropColumn('health_status');
                }
                if (Schema::hasColumn('inventory_logs', 'vaccination_records')) {
                    $table->dropColumn('vaccination_records');
                }

                // Add supplier back
                if (!Schema::hasColumn('inventory_logs', 'supplier')) {
                    $table->string('supplier')->after('product_id');
                }
            });

            // Rename back if product_stocks doesn't exist
            if (!Schema::hasTable('product_stocks')) {
                Schema::rename('inventory_logs', 'product_stocks');
            }
        }
    }
};
