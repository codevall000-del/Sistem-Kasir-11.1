<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'kode_member',
        'token',
        'nama_member',
        'nama_perusahaan',
        'plat_nomor',
        'email',
        'total_harga',
        'jumlah_bayar',
        'kembalian',
        'status',
        'tanggal_mulai',
        'tanggal_expired',
        'tanggal_bayar',
    ];

    protected $appends = [
        'nama',
        'perusahaan',
        'tagihan',
        'dibayar',
    ];

    public function getNamaAttribute(): ?string
    {
        return $this->nama_member;
    }

    public function getPerusahaanAttribute(): ?string
    {
        return $this->nama_perusahaan;
    }

    public function getTagihanAttribute(): mixed
    {
        return $this->total_harga;
    }

    public function getDibayarAttribute(): mixed
    {
        return $this->jumlah_bayar;
    }

    /**
     * Get all parkings for this member.
     */
    public function parkings(): HasMany
    {
        return $this->hasMany(Parking::class);
    }

    /**
     * Get all payment histories for this member.
     */
    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Get all invoices for this member.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}