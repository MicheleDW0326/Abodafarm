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
        Schema::table('inventory_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_logs', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('date');
                $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_logs', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }
        });
    }
};
