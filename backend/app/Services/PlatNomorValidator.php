<?php

namespace App\Services;

use App\Models\Member;
use App\Models\TiketParkir;

class PlatNomorValidator
{
    /**
     * Bersihkan nomor plat: hilangkan semua karakter non-alfanumerik, ubah ke uppercase.
     * Contoh: "b  1234  abc" => "B1234ABC"
     */
    public static function clean(?string $plat): string
    {
        if (!$plat) {
            return '';
        }
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plat));
    }

    /**
     * Rapikan nomor plat: spasi tunggal antar bagian dan uppercase.
     * Contoh: "b  1234   abc" => "B 1234 ABC"
     */
    public static function normalize(?string $plat): string
    {
        if (!$plat) {
            return '';
        }
        return strtoupper(trim(preg_replace('/\s+/', ' ', $plat)));
    }

    /**
     * Cek apakah nomor plat sudah digunakan oleh member.
     */
    public static function findMemberByPlate(string $plat, ?int $excludeMemberId = null): ?Member
    {
        $clean = self::clean($plat);
        if ($clean === '') {
            return null;
        }

        return Member::when($excludeMemberId, fn($q) => $q->where('id', '!=', $excludeMemberId))
            ->whereNotNull('plat_nomor')
            ->where('plat_nomor', '!=', '')
            ->whereRaw("REPLACE(REPLACE(UPPER(plat_nomor), ' ', ''), '-', '') = ?", [$clean])
            ->first();
    }

    /**
     * Cek apakah nomor plat sedang aktif di dalam parkir (tiket berstatus masuk).
     */
    public static function findActiveTicketByPlate(string $plat, ?int $excludeTicketId = null): ?TiketParkir
    {
        $clean = self::clean($plat);
        if ($clean === '') {
            return null;
        }

        return TiketParkir::where('status', 'masuk')
            ->when($excludeTicketId, fn($q) => $q->where('id', '!=', $excludeTicketId))
            ->whereNotNull('plat_nomor')
            ->where('plat_nomor', '!=', '')
            ->whereRaw("REPLACE(REPLACE(UPPER(plat_nomor), ' ', ''), '-', '') = ?", [$clean])
            ->first();
    }

    /**
     * Validasi nomor plat untuk pendaftaran / update Member.
     * Mengembalikan pesan error (string) jika tidak valid, atau null jika valid.
     */
    public static function validateForMember(string $plat, ?int $excludeMemberId = null): ?string
    {
        $clean = self::clean($plat);
        if ($clean === '') {
            return 'Nomor plat kendaraan tidak boleh kosong.';
        }

        $existingMember = self::findMemberByPlate($plat, $excludeMemberId);
        if ($existingMember) {
            $formatted = self::normalize($plat);
            return "Nomor plat '{$formatted}' sudah terdaftar pada member {$existingMember->nama_member} ({$existingMember->kode_member}).";
        }

        $activeTicket = self::findActiveTicketByPlate($plat);
        if ($activeTicket) {
            $formatted = self::normalize($plat);
            return "Kendaraan dengan plat '{$formatted}' sedang berada di dalam area parkir sebagai non-member (Tiket: {$activeTicket->kode_tiket}).";
        }

        return null;
    }

    /**
     * Validasi nomor plat untuk tiket / sesi parkir Non-Member.
     * Mengembalikan pesan error (string) jika tidak valid, atau null jika valid.
     */
    public static function validateForNonMember(string $plat, ?int $excludeTicketId = null): ?string
    {
        $clean = self::clean($plat);
        if ($clean === '') {
            return 'Nomor plat kendaraan tidak boleh kosong.';
        }

        // Tidak boleh sama dengan plat milik member
        $existingMember = self::findMemberByPlate($plat);
        if ($existingMember) {
            $formatted = self::normalize($plat);
            return "Nomor plat '{$formatted}' terdaftar sebagai kartu member ({$existingMember->nama_member}). Silakan gunakan akses member.";
        }

        // Tidak boleh sama dengan tiket lain yang sedang aktif di dalam parkir
        $activeTicket = self::findActiveTicketByPlate($plat, $excludeTicketId);
        if ($activeTicket) {
            $formatted = self::normalize($plat);
            return "Kendaraan dengan plat '{$formatted}' sudah tercatat di dalam area parkir (Tiket: {$activeTicket->kode_tiket}).";
        }

        return null;
    }

    /**
     * Generate plat nomor simulasi kamera ANPR yang dijamin unik
     * (tidak bertabrakan dengan member terdaftar maupun tiket yang sedang parkir).
     */
    public static function generateUniqueNonMemberPlate(): string
    {
        $wilayahList = ['B', 'B', 'B', 'D', 'F', 'B'];
        $attempts = 0;

        do {
            $wilayah = $wilayahList[array_rand($wilayahList)];
            $nomorPlat = rand(1000, 9999);
            $hurufBelakang = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
            $candidate = "{$wilayah} {$nomorPlat} {$hurufBelakang}";

            $clean = self::clean($candidate);
            $isMember = Member::whereNotNull('plat_nomor')
                ->where('plat_nomor', '!=', '')
                ->whereRaw("REPLACE(REPLACE(UPPER(plat_nomor), ' ', ''), '-', '') = ?", [$clean])
                ->exists();

            $isTicket = TiketParkir::where('status', 'masuk')
                ->whereNotNull('plat_nomor')
                ->where('plat_nomor', '!=', '')
                ->whereRaw("REPLACE(REPLACE(UPPER(plat_nomor), ' ', ''), '-', '') = ?", [$clean])
                ->exists();

            $attempts++;
        } while (($isMember || $isTicket) && $attempts < 100);

        return $candidate;
    }
}
