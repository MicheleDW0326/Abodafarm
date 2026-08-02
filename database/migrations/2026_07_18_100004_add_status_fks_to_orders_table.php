<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_status_id')->nullable()->after('status');
            $table->unsignedBigInteger('booking_status_id')->nullable()->after('payment_status_id');
            $table->unsignedBigInteger('shipment_status_id')->nullable()->after('booking_status_id');

            $table->index('payment_status_id');
            $table->index('booking_status_id');
            $table->index('shipment_status_id');

            $table->foreign('payment_status_id')
                ->references('id')
                ->on('order_statuses')
                ->onDelete('set null');

            $table->foreign('booking_status_id')
                ->references('id')
                ->on('booking_statuses')
                ->onDelete('set null');

            $table->foreign('shipment_status_id')
                ->references('id')
                ->on('shipment_statuses')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_status_id']);
            $table->dropForeign(['booking_status_id']);
            $table->dropForeign(['shipment_status_id']);

            $table->dropIndex(['payment_status_id']);
            $table->dropIndex(['booking_status_id']);
            $table->dropIndex(['shipment_status_id']);

            $table->dropColumn([
                'payment_status_id',
                'booking_status_id',
                'shipment_status_id',
            ]);
        });
    }
};
