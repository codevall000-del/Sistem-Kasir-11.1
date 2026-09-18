<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'nomor_invoice',
        'parking_id',
        'member_id',
        'payment_history_id',
        'tipe',
        'total_tagihan',
        'uang_tunai',
        'kembalian',
        'tanggal_invoice',
    ];

    /**
     * Get the parking session linked to this invoice.
     */
    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class);
    }

    /**
     * Get the member linked to this invoice.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the payment history linked to this invoice.
     */
    public function paymentHistory(): BelongsTo
    {
        return $this->belongsTo(PaymentHistory::class);
    }
}
