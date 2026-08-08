<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // bronze, silver, gold, platinum
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color');
            $table->integer('min_points')->default(0);
            $table->integer('max_points')->nullable();
            $table->float('discount_percent')->default(0);
            $table->integer('free_shipping_threshold')->nullable();
            $table->integer('priority_support')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default levels
        DB::table('customer_levels')->insert([
            [
                'name' => 'bronze',
                'display_name' => 'Bronze',
                'description' => 'Pelanggan baru',
                'icon' => 'fa-solid faMedal',
                'color' => '#CD7F32',
                'min_points' => 0,
                'max_points' => 99,
                'discount_percent' => 0,
                'free_shipping_threshold' => null,
                'priority_support' => 0,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'silver',
                'display_name' => 'Silver',
                'description' => 'Pelanggan aktif',
                'icon' => 'fa-solid faMedal',
                'color' => '#C0C0C0',
                'min_points' => 100,
                'max_points' => 499,
                'discount_percent' => 2,
                'free_shipping_threshold' => 500000,
                'priority_support' => 0,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'gold',
                'display_name' => 'Gold',
                'description' => 'Pelanggan setia',
                'icon' => 'fa-solid faMedal',
                'color' => '#FFD700',
                'min_points' => 500,
                'max_points' => 999,
                'discount_percent' => 5,
                'free_shipping_threshold' => 300000,
                'priority_support' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'platinum',
                'display_name' => 'Platinum',
                'description' => 'Pelanggan VIP',
                'icon' => 'fa-solid faCrown',
                'color' => '#E5E4E2',
                'min_points' => 1000,
                'max_points' => null,
                'discount_percent' => 10,
                'free_shipping_threshold' => 0,
                'priority_support' => 2,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_levels');
    }
};
