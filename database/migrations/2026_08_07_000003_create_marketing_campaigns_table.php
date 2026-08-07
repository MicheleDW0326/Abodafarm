<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('campaign_type', [
                'birthday',
                'new_customer',
                'loyal_customer',
                'weekend_sale',
                'flash_sale',
                'seasonal',
                'free_shipping',
                'cashback',
                'cart_abandonment',
                'review_reward',
                'manual',
            ]);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'ended'])->default('draft');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('priority')->default(0);
            $table->boolean('auto_generate_voucher')->default(false);
            $table->unsignedInteger('voucher_quantity')->nullable();
            $table->unsignedInteger('max_claim')->nullable();
            $table->decimal('budget_limit', 14, 2)->nullable();
            $table->decimal('budget_used', 14, 2)->default(0);
            $table->unsignedInteger('total_vouchers_generated')->default(0);
            $table->unsignedInteger('total_vouchers_claimed')->default(0);
            $table->unsignedInteger('total_vouchers_used')->default(0);
            $table->decimal('total_discount_given', 14, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('status');
            $table->index('campaign_type');
            $table->index(['start_date', 'end_date']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
