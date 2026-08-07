<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->enum('rule_type', [
                'minimum_order',
                'customer_total_orders',
                'customer_total_spent',
                'customer_role',
                'customer_registration_date',
                'province',
                'city',
                'payment_method',
                'category',
                'product',
                'day_of_week',
                'first_order',
            ]);
            $table->enum('operator', ['equals', 'not_equals', 'greater_than', 'less_than', 'greater_or_equal', 'less_or_equal', 'in', 'not_in', 'contains', 'not_contains']);
            $table->json('value');
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['campaign_id', 'rule_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_rules');
    }
};
