<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('delivery_code')->unique();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            // Tracking
            $table->string('tracking_number')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('vehicle_type')->nullable(); // motor, mobil, van
            $table->string('delivery_method')->nullable(); // delivery, pickup

            // Timing
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Current status
            $table->string('status')->default('preparing');
            $table->string('previous_status')->nullable();

            // Proof of delivery
            $table->string('proof_photo')->nullable();
            $table->string('customer_signature')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('receiver_name')->nullable();

            // Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Attempt tracking
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('failure_reason')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('assigned_staff_id');
            $table->index('delivery_code');
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
