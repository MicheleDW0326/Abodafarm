<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('voucher_code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping', 'cashback']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->decimal('minimum_order', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->enum('status', ['active', 'used', 'expired', 'revoked'])->default('active');
            $table->boolean('is_auto_applied')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['voucher_code', 'user_id']);
            $table->index(['status', 'valid_until']);
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_vouchers');
    }
};
