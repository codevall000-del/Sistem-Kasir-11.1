<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Parking extends Model
{
    protected $fillable = [
        'token',
        'member_id',
        'petugas_id',
        'kategori',
        'no_plat',
        'jam_masuk',
        'jam_keluar',
        'total_tagihan',
        'uang_tunai',
        'kembalian',
        'parking_rate_id',
    ];

    /**
     * Get the member associated with this parking session.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the petugas (user) who handled this parking.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get the parking rate applied to this session.
     */
    public function parkingRate(): BelongsTo
    {
        return $this->belongsTo(ParkingRate::class);
    }

    /**
     * Get the invoice for this parking session.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
