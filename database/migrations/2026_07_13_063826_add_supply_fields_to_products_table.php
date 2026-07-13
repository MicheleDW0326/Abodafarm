<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add new fields for supply products
            $table->decimal('discount_price', 12, 2)->nullable()->after('price');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_price');
            $table->text('specifications')->nullable()->after('description');
            $table->text('how_to_use')->nullable()->after('specifications');
            $table->text('shipping_info')->nullable()->after('how_to_use');
            $table->string('unit', 50)->nullable()->after('stock');
        });

        // Modify the type enum to add 'supply' value
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('live', 'fresh', 'supply') NOT NULL DEFAULT 'fresh'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'discount_price',
                'discount_percent',
                'specifications',
                'how_to_use',
                'shipping_info',
                'unit',
            ]);
        });

        // Revert the type enum back to original values
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('live', 'fresh') NOT NULL DEFAULT 'fresh'");
    }
};
