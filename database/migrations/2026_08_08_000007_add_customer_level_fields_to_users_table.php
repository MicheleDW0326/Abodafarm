<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('customer_level_id')->nullable()->after('id')->constrained('customer_levels')->nullOnDelete();
            $table->integer('loyalty_points')->default(0)->after('customer_level_id');
            $table->integer('total_orders')->default(0)->after('loyalty_points');
            $table->decimal('total_spent', 15, 2)->default(0)->after('total_orders');
            $table->integer('total_reviews')->default(0)->after('total_spent');
            $table->timestamp('last_order_at')->nullable()->after('total_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_level_id']);
            $table->dropColumn([
                'customer_level_id',
                'loyalty_points',
                'total_orders',
                'total_spent',
                'total_reviews',
                'last_order_at',
            ]);
        });
    }
};
