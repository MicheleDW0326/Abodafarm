<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            $table->enum('type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_purchase', 12, 2)->nullable();
            $table->decimal('maximum_discount', 12, 2)->nullable();

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            $table->index('code');
            $table->index('status');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
