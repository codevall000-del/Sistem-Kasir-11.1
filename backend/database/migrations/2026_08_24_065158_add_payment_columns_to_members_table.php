<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('jumlah_bayar', 15, 2)->default(0)->after('total_harga');
            $table->timestamp('tanggal_bayar')->nullable()->after('tanggal_expired');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['jumlah_bayar', 'tanggal_bayar']);
        });
    }
};