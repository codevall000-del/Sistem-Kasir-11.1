<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('kode_member')->unique();
            $table->string('token')->unique();
            $table->string('nama_member');
            $table->string('nama_perusahaan');
            $table->decimal('total_harga', 10, 2)->default(0);
            $table->decimal('kembalian', 10, 2)->default(0);
            $table->enum('status', ['lunas', 'belum_lunas'])->default('belum_lunas');
            $table->date('tanggal_mulai');
            $table->date('tanggal_expired'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
