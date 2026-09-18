<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'no_hp',
        'otp',
        'token',
        'expired_at',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'is_used'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
