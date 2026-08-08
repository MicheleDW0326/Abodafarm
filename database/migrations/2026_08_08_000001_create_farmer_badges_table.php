<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('badge_type'); // verified, top_seller, fast_response, trusted_farmer, organic_certified, excellent_rating
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#10B981');
            $table->boolean('is_active')->default(true);
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge_type']);
            $table->index('badge_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_badges');
    }
};
