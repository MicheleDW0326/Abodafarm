<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farm_profiles', function (Blueprint $table) {
            // Trust & Reputation
            $table->float('trust_score')->default(50)->after('total_reviews');
            $table->float('response_rate')->default(0)->after('trust_score');
            $table->integer('response_time_hours')->default(0)->after('response_rate');
            $table->float('cancellation_rate')->default(0)->after('response_time_hours');

            // Statistics
            $table->integer('products_sold')->default(0)->after('cancellation_rate');
            $table->integer('livestock_sold')->default(0)->after('products_sold');
            $table->decimal('total_revenue', 15, 2)->default(0)->after('livestock_sold');
            $table->integer('completed_bookings')->default(0)->after('total_revenue');
            $table->integer('completed_orders')->default(0)->after('completed_bookings');
            $table->integer('repeat_customers')->default(0)->after('completed_orders');

            // Badges
            $table->boolean('badge_top_seller')->default(false)->after('repeat_customers');
            $table->boolean('badge_fast_response')->default(false)->after('badge_top_seller');
            $table->boolean('badge_trusted_farmer')->default(false)->after('badge_fast_response');
            $table->boolean('badge_organic_certified')->default(false)->after('badge_trusted_farmer');
            $table->boolean('badge_excellent_rating')->default(false)->after('badge_organic_certified');

            // Followers
            $table->integer('followers_count')->default(0)->after('badge_excellent_rating');

            // Timestamps
            $table->timestamp('last_response_at')->nullable()->after('followers_count');
            $table->timestamp('badges_updated_at')->nullable()->after('last_response_at');
        });
    }

    public function down(): void
    {
        Schema::table('farm_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'trust_score',
                'response_rate',
                'response_time_hours',
                'cancellation_rate',
                'products_sold',
                'livestock_sold',
                'total_revenue',
                'completed_bookings',
                'completed_orders',
                'repeat_customers',
                'badge_top_seller',
                'badge_fast_response',
                'badge_trusted_farmer',
                'badge_organic_certified',
                'badge_excellent_rating',
                'followers_count',
                'last_response_at',
                'badges_updated_at',
            ]);
        });
    }
};
