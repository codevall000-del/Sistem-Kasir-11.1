<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';

    protected $fillable = [
        'member_id',
        'petugas_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'uang_tunai',
        'kembalian',
        'periode_bulan',
        'status',
        'keterangan',
    ];

    /**
     * Get the member this payment belongs to.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the petugas who processed this payment.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get the invoice generated for this payment.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
