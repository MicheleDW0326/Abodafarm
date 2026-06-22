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
            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        // Generate slugs for existing products
        \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->update([
                'slug' => \Illuminate\Support\Facades\DB::raw('LOWER(REPLACE(REPLACE(name, " ", "-"), "/", "-"))')
            ]);

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
