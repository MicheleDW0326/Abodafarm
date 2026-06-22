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
        Schema::table('products', function (Blueprint $table) {
            // Tipe produk: live (ternak hidup) atau fresh (daging segar)
            $table->enum('type', ['live', 'fresh'])->default('fresh')->after('category_id');

            // Status ketersediaan
            $table->enum('status', ['available', 'pre_order', 'in_care', 'sold'])->default('available')->after('type');

            // Info ternak (untuk type='live')
            $table->decimal('weight', 8, 2)->nullable()->after('status'); // Berat dalam kg
            $table->string('age')->nullable()->after('weight'); // Contoh: "2 Tahun"
            $table->enum('sex', ['male', 'female', 'mixed'])->nullable()->after('age');
            $table->string('origin')->nullable()->after('sex'); // Lokasi asal
            $table->string('condition')->nullable()->after('origin'); // Kondisi kesehatan

            // Info daging (untuk type='fresh')
            $table->decimal('weight_per_unit', 8, 2)->nullable()->after('condition'); // Berat per unit dalam kg

            // Multiple images
            $table->json('images')->nullable()->after('image');

            // Featured flag
            $table->boolean('is_featured')->default(false)->after('images');

            // Pastikan stock tetap ada untuk produk fresh
            $table->integer('stock')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'status',
                'weight',
                'age',
                'sex',
                'origin',
                'condition',
                'weight_per_unit',
                'images',
                'is_featured'
            ]);
        });
    }
};
