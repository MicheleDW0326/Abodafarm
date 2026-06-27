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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Reviewer
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Order reference (verified purchase)
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Review type: 'product' or 'farmer'
            $table->enum('review_type', ['product', 'farmer']);

            // Product review (nullable)
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();

            // Farmer review (nullable)
            $table->unsignedBigInteger('farmer_id')->nullable();
            $table->foreign('farmer_id')->references('id')->on('users')->cascadeOnDelete();

            // Rating (1-5)
            $table->tinyInteger('rating')->unsigned();

            // Content
            $table->text('content')->nullable();
            $table->string('title', 255)->nullable();

            // Status for moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');

            // Admin notes
            $table->text('admin_notes')->nullable();

            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['review_type', 'product_id']);
            $table->index(['review_type', 'farmer_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);

            // One review per user per product per order
            $table->unique(['user_id', 'order_id', 'product_id'], 'unique_product_review');

            // One review per user per farmer per order
            $table->unique(['user_id', 'order_id', 'farmer_id'], 'unique_farmer_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
