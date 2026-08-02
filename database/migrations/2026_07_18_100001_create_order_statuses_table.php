<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('type', 20); // 'payment'
            $table->string('label', 100);
            $table->string('label_en', 100)->nullable();
            $table->string('color', 20)->default('gray');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
