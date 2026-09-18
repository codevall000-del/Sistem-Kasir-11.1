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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 50)->unique();
            $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('set null');
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->foreignId('payment_history_id')->nullable()->constrained('payment_history')->onDelete('set null');
            $table->enum('tipe', ['non_member', 'member_transaksi', 'member_bulanan']);
            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('uang_tunai', 15, 2);
            $table->decimal('kembalian', 15, 2);
            $table->timestamp('tanggal_invoice')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
