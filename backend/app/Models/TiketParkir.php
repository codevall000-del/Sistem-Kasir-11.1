<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TiketParkir extends Model
{
    protected $table = 'tiket_parkir';

    protected $fillable = [
        'kode_tiket',
        'qr_code',
        'status',
        'plat_nomor',
        'waktu_masuk',
        'waktu_keluar',
    ];

    /**
     * Get the parking session linked to this ticket.
     */
    public function parking(): HasOne
    {
        return $this->hasOne(Parking::class);
    }
}
