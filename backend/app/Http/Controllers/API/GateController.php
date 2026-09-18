<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiketParkir;
use App\Models\member;
use App\Models\Parking;
use App\Services\PlatNomorValidator;
use Carbon\Carbon;

class GateController extends Controller
{
    public function scanKeluar(Request $request)
    {
        $kode = trim((string) $request->input('kode'));

        if (!$kode) {
            return response()->json(['success' => false, 'message' => 'Kode tidak valid'], 400);
        }

        $kodeClean = $kode;
        $payload = json_decode($kode, true);
        if (is_array($payload) && !empty($payload['kode'])) {
            $kodeClean = $payload['kode'];
        }

        // Petugas wajib mengisi nomor plat kendaraan sebelum pemindaian keluar
        $platNomorInput = trim((string) $request->input('plat_nomor'));
        if (!$platNomorInput) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor plat kendaraan wajib diisi terlebih dahulu sebelum pemindaian keluar!',
            ], 422);
        }

        // 1. Cek apakah ini member (via kode_member langsung, token, atau hasil decode)
        $member = member::where('kode_member', $kodeClean)
            ->orWhere('kode_member', $kode)
            ->orWhere('token', $kode)
            ->first();
        
        if ($member) {
            // Logika Member
            if ($member->status !== 'lunas') {
                return response()->json(['success' => false, 'message' => 'Member belum melunasi tagihan!'], 400);
            }
            if (Carbon::now()->gt(Carbon::parse($member->tanggal_expired))) {
                return response()->json(['success' => false, 'message' => 'Kartu Member Kadaluarsa!'], 400);
            }

            // Normalisasi nomor plat yang diinput petugas
            $normalizedPlat = PlatNomorValidator::normalize($platNomorInput);

            // Validasi keunikan plat: tidak boleh sedang aktif di tiket parkir non-member yang belum keluar
            $activeTicket = PlatNomorValidator::findActiveTicketByPlate($platNomorInput);
            if ($activeTicket) {
                return response()->json([
                    'success' => false,
                    'message' => "Nomor plat '{$normalizedPlat}' sedang tercatat aktif pada tiket non-member ({$activeTicket->kode_tiket}).",
                ], 422);
            }

            // Validasi keunikan plat: tidak boleh sedang aktif di sesi parkir member lain
            $cleanPlat = PlatNomorValidator::clean($platNomorInput);
            $otherActiveParking = Parking::whereNull('jam_keluar')
                ->where('member_id', '!=', $member->id)
                ->whereNotNull('no_plat')
                ->whereRaw("REPLACE(REPLACE(UPPER(no_plat), ' ', ''), '-', '') = ?", [$cleanPlat])
                ->first();

            if ($otherActiveParking) {
                return response()->json([
                    'success' => false,
                    'message' => "Nomor plat '{$normalizedPlat}' sedang aktif parkir pada member lain.",
                ], 422);
            }

            // Cari sesi parkir aktif member ini
            $activeParking = Parking::where('member_id', $member->id)
                ->whereNull('jam_keluar')
                ->latest()
                ->first();

            $waktuKeluar = Carbon::now();
            $waktuMasuk = $activeParking && $activeParking->jam_masuk ? Carbon::parse($activeParking->jam_masuk) : Carbon::now()->subMinutes(30);

            if ($activeParking) {
                $activeParking->jam_keluar = $waktuKeluar;
                $activeParking->no_plat = $normalizedPlat; // Plat nomor dicatat saat keluar
                $activeParking->save();
            } else {
                $activeParking = Parking::create([
                    'member_id'     => $member->id,
                    'kategori'      => 'mobil',
                    'no_plat'       => $normalizedPlat,
                    'jam_masuk'     => $waktuMasuk,
                    'jam_keluar'    => $waktuKeluar,
                    'total_tagihan' => 0,
                ]);
            }

            $menit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $durasiJam = ceil($menit / 60);
            $durasiJam = $durasiJam < 1 ? 1 : $durasiJam;

            return response()->json([
                'success' => true,
                'message' => 'Member keluar, pintu terbuka',
                'data' => [
                    'is_member'    => true,
                    'kode_member'  => $member->kode_member,
                    'nama_member'  => $member->nama_member,
                    'plat_nomor'   => $normalizedPlat,
                    'waktu_masuk'  => $waktuMasuk->toDateTimeString(),
                    'waktu_keluar' => $waktuKeluar->toDateTimeString(),
                    'durasi'       => $durasiJam . ' Jam',
                    'status'       => 'Member Selesai'
                ]
            ]);
        }

        // 2. Jika bukan member, cek tiket biasa
        $tiket = TiketParkir::where('kode_tiket', $kode)->first();

        if ($tiket) {
            if ($tiket->status === 'keluar') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Tiket sudah digunakan dan kendaraan sudah keluar.'
                ], 409);
            }

            $platNomorInput = $request->input('plat_nomor');
            $normalizedPlat = null;
            if ($platNomorInput && filled($platNomorInput)) {
                $platError = PlatNomorValidator::validateForNonMember($platNomorInput, (int) $tiket->id);
                if ($platError) {
                    return response()->json([
                        'success' => false,
                        'message' => $platError,
                    ], 422);
                }
                $normalizedPlat = PlatNomorValidator::normalize($platNomorInput);
                // Plat nomor dicatat permanen saat pembayaran selesai dan status menjadi keluar
            }

            // Hitung Biaya
            $waktuMasuk = Carbon::parse($tiket->waktu_masuk ?? $tiket->created_at);
            $waktuSekarang = Carbon::now();
            $menit = $waktuMasuk->diffInMinutes($waktuSekarang);
            $jamParkir = ceil($menit / 60);
            $jamParkir = $jamParkir < 1 ? 1 : $jamParkir;
            $biaya = $jamParkir * 3000; // Rp 3.000 per jam

            return response()->json([
                'success' => true,
                'message' => 'Tiket valid, silakan bayar',
                'biaya' => $biaya,
                'durasi' => $jamParkir . ' Jam',
                'data' => [
                    'is_member' => false,
                    'kode_tiket' => $tiket->kode_tiket,
                    'plat_nomor' => $normalizedPlat ?: ($tiket->plat_nomor ?: '-'),
                    'waktu_masuk' => $waktuMasuk->toDateTimeString(),
                    'durasi' => $jamParkir . ' Jam',
                    'biaya' => $biaya,
                    'status' => 'Belum Bayar'
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
    }

    public function processPayment(Request $request)
    {
        $kode = trim((string) $request->input('kode'));
        $bayar = $request->input('bayar');
        $platNomor = $request->input('plat_nomor');

        $kodeClean = $kode;
        $payload = json_decode($kode, true);
        if (is_array($payload) && !empty($payload['kode'])) {
            $kodeClean = $payload['kode'];
        }

        $tiket = TiketParkir::where('kode_tiket', $kodeClean)
            ->orWhere('kode_tiket', $kode)
            ->first();

        if (!$tiket) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak valid'], 404);
        }

        // Perhitungan ulang untuk validasi
        $waktuMasuk = Carbon::parse($tiket->waktu_masuk ?? $tiket->created_at);
        $waktuSekarang = Carbon::now();
        $jamParkir = ceil($waktuMasuk->diffInMinutes($waktuSekarang) / 60);
        $jamParkir = $jamParkir < 1 ? 1 : $jamParkir;
        $biaya = $jamParkir * 3000;

        if ($bayar < $biaya) {
            return response()->json(['success' => false, 'message' => 'Uang pembayaran kurang!'], 400);
        }

        if ($platNomor && filled($platNomor)) {
            $platError = PlatNomorValidator::validateForNonMember($platNomor, (int) $tiket->id);
            if ($platError) {
                return response()->json([
                    'success' => false,
                    'message' => $platError,
                ], 422);
            }
            $tiket->plat_nomor = PlatNomorValidator::normalize($platNomor);
        }

        $tiket->status = 'keluar';
        $tiket->waktu_keluar = $waktuSekarang;
        $tiket->save();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil, pintu terbuka',
            'kembalian' => $bayar - $biaya
        ]);
    }
}
