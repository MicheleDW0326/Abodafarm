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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->text('customer_address');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->string('status', 30)->default('pending');
            $table->timestamp('placed_at');
            $table->timestamps();

            $table->index(['status', 'placed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
