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
        // Check if columns already exist
        $columns = Schema::getColumnListing('categories');

        if (!in_array('slug', $columns)) {
            Schema::table('categories', function (Blueprint $table) use ($columns) {
                // Add slug as nullable first
                $table->string('slug')->nullable()->after('description');

                // Add other columns if they don't exist
                if (!in_array('icon', $columns)) {
                    $table->string('icon')->nullable()->after('slug');
                }
                if (!in_array('image', $columns)) {
                    $table->string('image')->nullable()->after('icon');
                }
                if (!in_array('is_active', $columns)) {
                    $table->boolean('is_active')->default(true)->after('image');
                }
                if (!in_array('sort_order', $columns)) {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            });

            // Generate slugs for existing categories
            \Illuminate\Support\Facades\DB::table('categories')
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->update([
                    'slug' => \Illuminate\Support\Facades\DB::raw('LOWER(REPLACE(name, " ", "-"))')
                ]);

            // Make slug unique
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique('slug', 'categories_slug_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropUnique('categories_slug_unique');
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('categories', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('categories', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
