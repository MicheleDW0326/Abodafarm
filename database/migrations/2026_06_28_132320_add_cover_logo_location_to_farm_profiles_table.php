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
        Schema::table('farm_profiles', function (Blueprint $table) {
            // Cover image (banner)
            $table->string('cover_image')->nullable()->after('image');

            // Logo image
            $table->string('logo')->nullable()->after('cover_image');

            // Google Maps location
            $table->string('google_place_id')->nullable()->after('logo');
            $table->decimal('latitude', 10, 8)->nullable()->after('google_place_id');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_profiles', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'logo', 'google_place_id', 'latitude', 'longitude']);
        });
    }
};
