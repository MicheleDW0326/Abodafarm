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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0)->after('is_featured');
            $table->unsignedInteger('total_reviews')->default(0)->after('rating');
            $table->unsignedInteger('rating_1_count')->default(0)->after('total_reviews');
            $table->unsignedInteger('rating_2_count')->default(0)->after('rating_1_count');
            $table->unsignedInteger('rating_3_count')->default(0)->after('rating_2_count');
            $table->unsignedInteger('rating_4_count')->default(0)->after('rating_3_count');
            $table->unsignedInteger('rating_5_count')->default(0)->after('rating_4_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'rating',
                'total_reviews',
                'rating_1_count',
                'rating_2_count',
                'rating_3_count',
                'rating_4_count',
                'rating_5_count',
            ]);
        });
    }
};
