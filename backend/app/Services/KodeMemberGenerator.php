<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Str;

class KodeMemberGenerator
{
    public static function generate(): string
    {
        do {
            $kode = 'MBR-' . strtoupper(Str::random(6));
        } while (Member::where('kode_member', $kode)->exists());

        return $kode;
    }
}