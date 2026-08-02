<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label', 100);
            $table->string('label_en', 100)->nullable();
            $table->string('color', 20)->default('blue');
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('tracking_url_template', 255)->nullable();
            $table->boolean('customer_notified')->default(true);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_statuses');
    }
};
