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
        Schema::table('parkings', function (Blueprint $table) {
            $table->foreignId('parking_rate_id')->nullable()->constrained('parking_rates')->onDelete('set null')->after('kembalian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parkings', function (Blueprint $table) {
            $table->dropForeign(['parking_rate_id']);
            $table->dropColumn('parking_rate_id');
        });
    }
};
