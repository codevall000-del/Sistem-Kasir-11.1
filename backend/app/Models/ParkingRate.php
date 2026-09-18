<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingRate extends Model
{
    protected $fillable = [
        'kategori',
        'blok_jam_min',
        'blok_jam_max',
        'tarif',
        'is_active',
    ];

    /**
     * Get all parking sessions using this rate.
     */
    public function parkings(): HasMany
    {
        return $this->hasMany(Parking::class);
    }
}
