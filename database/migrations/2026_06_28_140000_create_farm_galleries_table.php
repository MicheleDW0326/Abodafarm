<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_profile_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['farm_profile_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_galleries');
    }
};