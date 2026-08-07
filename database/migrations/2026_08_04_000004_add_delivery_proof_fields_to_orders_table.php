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
        Schema::table('orders', function (Blueprint $table) {
            // Delivery proof fields
            $table->string('receiver_name')->nullable()->after('delivery_started_at');
            $table->string('delivery_photo')->nullable()->after('receiver_name');
            $table->text('delivery_note')->nullable()->after('delivery_photo');
            $table->timestamp('delivery_completed_at')->nullable()->after('delivery_note');
            $table->decimal('delivery_latitude', 10, 8)->nullable()->after('delivery_completed_at');
            $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'receiver_name',
                'delivery_photo',
                'delivery_note',
                'delivery_completed_at',
                'delivery_latitude',
                'delivery_longitude',
            ]);
        });
    }
};
