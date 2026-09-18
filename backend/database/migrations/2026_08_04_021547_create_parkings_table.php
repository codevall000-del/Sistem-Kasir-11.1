<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable()->unique(); 
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->enum('kategori', ['motor', 'mobil'])->nullable();
            $table->string('no_plat')->nullable();
            $table->timestamp('jam_masuk')->useCurrent();
            $table->timestamp('jam_keluar')->nullable();
            $table->decimal('total_tagihan', 15, 2)->nullable();
            $table->decimal('uang_tunai', 15, 2)->nullable();
            $table->decimal('kembalian', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parkings');
    }
};