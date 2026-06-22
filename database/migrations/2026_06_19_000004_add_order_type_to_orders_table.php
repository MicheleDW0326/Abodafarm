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
            // Tipe order: cart (pembelian) atau inquiry (pertanyaan/booking)
            $table->enum('order_type', ['cart', 'inquiry'])->default('cart')->after('status');

            // Tanggal pengiriman yang diinginkan (khusus untuk inquiry/live livestock)
            $table->date('preferred_delivery_date')->nullable()->after('order_type');

            // Lokasi pengambilan/pengiriman
            $table->string('pickup_location')->nullable()->after('preferred_delivery_date');

            // Catatan admin
            $table->text('admin_notes')->nullable()->after('pickup_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'preferred_delivery_date', 'pickup_location', 'admin_notes']);
        });
    }
};
