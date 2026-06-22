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
        Schema::create('farm_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'ternak_terjual', 'pelanggan', 'peternak_mitra', 'kepuasan'
            $table->string('value'); // Angka atau teks statistik
            $table->string('label'); // Label untuk ditampilkan
            $table->string('icon')->nullable(); // Icon class
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_statistics');
    }
};
