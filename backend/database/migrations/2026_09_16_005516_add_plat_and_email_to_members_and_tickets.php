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
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'plat_nomor')) {
                $table->string('plat_nomor')->nullable()->after('nama_perusahaan');
            }
            if (!Schema::hasColumn('members', 'email')) {
                $table->string('email')->nullable()->after('nama_perusahaan');
            }
        });

        Schema::table('tiket_parkir', function (Blueprint $table) {
            if (!Schema::hasColumn('tiket_parkir', 'plat_nomor')) {
                $table->string('plat_nomor')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['plat_nomor', 'email']);
        });

        Schema::table('tiket_parkir', function (Blueprint $table) {
            $table->dropColumn(['plat_nomor']);
        });
    }
};
