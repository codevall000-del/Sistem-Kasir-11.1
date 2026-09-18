<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Parking;
use App\Services\KodeMemberGenerator;
use App\Services\PlatNomorValidator;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * GET /api/members?search=
     */
    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_member', 'LIKE', "%{$search}%")
                  ->orWhere('kode_member', 'LIKE', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 100);
        $members = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $members,
        ]);
    }

    /**
     * POST /api/members
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_member'     => 'required|string|max:255',
            'nama_perusahaan' => 'required|string|max:255',
            'plat_nomor'      => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'total_harga'     => 'nullable|numeric|min:0',
            'jumlah_bayar'    => 'nullable|numeric|min:0',
        ]);

        $formattedPlat = null;
        if ($request->filled('plat_nomor')) {
            // Validasi keunikan nomor plat jika diisi
            $platError = PlatNomorValidator::validateForMember($request->plat_nomor);
            if ($platError) {
                return response()->json([
                    'success' => false,
                    'message' => $platError,
                ], 422);
            }
            $formattedPlat = PlatNomorValidator::normalize($request->plat_nomor);
        }

        $kodeMember = KodeMemberGenerator::generate();

        $tokenPayload = json_encode([
            'type' => 'member',
            'kode' => $kodeMember,
        ]);

        $totalHarga  = (float) ($request->total_harga ?? 150000);
        $jumlahBayar = (float) ($request->jumlah_bayar ?? $totalHarga);

        if ($jumlahBayar < $totalHarga) {
            return response()->json([
                'success' => false,
                'message' => 'Uang pembayaran tunai kurang dari tarif langganan!',
            ], 422);
        }

        $kembalian   = $jumlahBayar > $totalHarga ? $jumlahBayar - $totalHarga : 0;

        $member = Member::create([
            'kode_member'     => $kodeMember,
            'token'           => $tokenPayload,
            'nama_member'     => $request->nama_member,
            'nama_perusahaan' => $request->nama_perusahaan,
            'plat_nomor'      => $formattedPlat,
            'email'           => $request->email ? trim($request->email) : null,
            'tanggal_mulai'   => now()->format('Y-m-d'),
            'tanggal_expired' => now()->addMonth()->format('Y-m-d'),
            'total_harga'     => $totalHarga,
            'jumlah_bayar'    => $jumlahBayar,
            'kembalian'       => $kembalian,
            'status'          => 'lunas',
            'tanggal_bayar'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge($member->toArray(), [
                'uang_diterima' => $jumlahBayar,
                'kembalian'     => $kembalian,
            ]),
        ], 201);
    }

    /**
 * GET /api/members/{id}
 */
/**
 * GET /api/members/{id}
 */
public function show(string $id)
{
    $member = Member::with(['parkings', 'paymentHistories'])->find($id);

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member tidak ditemukan',
        ], 404);
    }

    // Generate QR Code dengan Endroid
    $qrCodeData = json_encode([
        'type' => 'member',
        'kode' => $member->kode_member,
    ]);

    $qrCode = new QrCode($qrCodeData);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());

    $memberData = $member->toArray();
    $memberData['qr'] = $qrCodeBase64;

    return response()->json([
        'success' => true,
        'data'    => $memberData,
    ]);
}

    /**
     * PUT/PATCH /api/members/{id}
     * Update data umum member (bukan pembayaran)
     */
    public function update(Request $request, string $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'nama_member'     => 'sometimes|required|string|max:255',
            'nama_perusahaan' => 'sometimes|required|string|max:255',
            'plat_nomor'      => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
        ]);

        $data = [];
        if ($request->has('nama_member')) {
            $data['nama_member'] = $request->nama_member;
        }
        if ($request->has('nama_perusahaan')) {
            $data['nama_perusahaan'] = $request->nama_perusahaan;
        }
        if ($request->has('plat_nomor') && filled($request->plat_nomor)) {
            $platError = PlatNomorValidator::validateForMember($request->plat_nomor, (int) $id);
            if ($platError) {
                return response()->json([
                    'success' => false,
                    'message' => $platError,
                ], 422);
            }
            $data['plat_nomor'] = PlatNomorValidator::normalize($request->plat_nomor);
        }
        if ($request->has('email')) {
            $data['email'] = $request->email ? trim($request->email) : null;
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data profil member berhasil diperbarui',
            'data'    => $member->fresh(),
        ]);
    }

    /**
     * PUT /api/member/{id}/pembayaran
     * Update jumlah pembayaran member, otomatis hitung ulang kembalian & status
     */
    public function updatePembayaran(Request $request, string $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
            ], 404);
        }

        if ($member->status === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Member yang sudah lunas tidak dapat diedit',
            ], 422);
        }

        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:0',
        ]);

        $inputBayar  = (float) $request->jumlah_bayar;
        $totalHarga  = (float) $member->total_harga;
        $sudahBayar  = (float) $member->jumlah_bayar;
        $sisaTagihan = max(0, $totalHarga - $sudahBayar);

        // Jika request adalah pelunasan (is_pelunasan = true) atau pembayaran sisa tagihan
        $isPelunasan = $request->boolean('is_pelunasan') 
            || $request->input('mode') === 'pelunasan' 
            || ($inputBayar == $sisaTagihan && $inputBayar > 0);

        if ($isPelunasan) {
            $totalTerbayarBaru = $sudahBayar + $inputBayar;
            $kembalian         = $totalTerbayarBaru > $totalHarga ? $totalTerbayarBaru - $totalHarga : 0;
            $jumlahBayarFinal  = min($totalTerbayarBaru, $totalHarga);
            $status            = $totalTerbayarBaru >= $totalHarga ? 'lunas' : 'belum_lunas';
        } else {
            $jumlahBayarFinal = min($inputBayar, $totalHarga);
            $kembalian        = $inputBayar > $totalHarga ? $inputBayar - $totalHarga : 0;
            $status           = $jumlahBayarFinal >= $totalHarga ? 'lunas' : 'belum_lunas';
        }

        $member->update([
            'jumlah_bayar'  => $jumlahBayarFinal,
            'kembalian'     => $kembalian,
            'status'        => $status,
            'tanggal_bayar' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'lunas' ? 'Pelunasan berhasil. Member kini berstatus lunas.' : 'Pembayaran berhasil diperbarui.',
            'data'    => $member->fresh(),
        ]);
    }

    /**
     * POST /api/member/{id}/perpanjang
     * Perpanjang masa aktif langganan iuran member (+1 bulan)
     */
    public function perpanjang(Request $request, string $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
            ], 404);
        }

        // 1. Validasi: tagihan sebelumnya harus sudah lunas
        if ($member->status !== 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Member belum dapat diperpanjang karena tagihan sebelumnya belum selesai / lunas.',
            ], 422);
        }

        // 2. Validasi: harus sudah masuk ke bulan habisnya (atau sudah kadaluarsa)
        if ($member->tanggal_expired) {
            $now = now();
            $expiredDate = Carbon::parse($member->tanggal_expired);
            $currentYearMonth = ($now->year * 12) + $now->month;
            $expYearMonth = ($expiredDate->year * 12) + $expiredDate->month;

            if ($currentYearMonth < $expYearMonth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member belum dapat diperpanjang karena belum masuk ke bulan habis masa aktif.',
                ], 422);
            }
        }

        $durasiBulan = (int) $request->input('durasi_bulan', 1);
        $durasiBulan = $durasiBulan < 1 ? 1 : $durasiBulan;

        $tarifInput = $request->input('total_harga') ?? $request->input('tarif');
        $totalBiaya = $tarifInput !== null ? (float) $tarifInput : ($durasiBulan * 150000);

        $bayarInput = $request->input('jumlah_bayar');
        $jumlahBayar = $bayarInput !== null ? (float) $bayarInput : $totalBiaya;

        if ($jumlahBayar < $totalBiaya) {
            return response()->json([
                'success' => false,
                'message' => 'Uang pembayaran tunai kurang dari tarif langganan!',
            ], 422);
        }

        $kembalian = $jumlahBayar > $totalBiaya ? $jumlahBayar - $totalBiaya : 0;

        $currentExpired = $member->tanggal_expired ? Carbon::parse($member->tanggal_expired) : now();
        $baseDate = $currentExpired->isPast() ? now() : $currentExpired;
        $newExpired = $baseDate->copy()->addMonths($durasiBulan);

        $member->update([
            'total_harga'     => $totalBiaya,
            'jumlah_bayar'    => $jumlahBayar,
            'kembalian'       => $kembalian,
            'status'          => 'lunas',
            'tanggal_mulai'   => $currentExpired->isPast() ? now()->format('Y-m-d') : $member->tanggal_mulai,
            'tanggal_expired' => $newExpired->format('Y-m-d'),
            'tanggal_bayar'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Masa aktif member berhasil diperpanjang {$durasiBulan} bulan.",
            'data'    => array_merge($member->fresh()->toArray(), [
                'uang_diterima' => $jumlahBayar,
                'kembalian'     => $kembalian,
            ]),
        ]);
    }

    /**
     * DELETE /api/members/{id}
     */
    public function destroy(string $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
            ], 404);
        }

        if ($member->status === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Member yang sudah lunas tidak dapat dihapus',
            ], 422);
        }

        // Guard: cek apakah member sedang dalam sesi parkir aktif
        $hasActiveParking = $member->parkings()
            ->whereNull('jam_keluar')
            ->exists();

        if ($hasActiveParking) {
            return response()->json([
                'success' => false,
                'message' => 'Member sedang dalam sesi parkir aktif',
            ], 409);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data member berhasil dihapus',
        ]);
    }

    /**
     * POST /api/member/check
     * Validasi QR member dari scanner — PUBLIC endpoint
     */
    public function check(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $payload = json_decode($request->token, true);

        if (!$payload || ($payload['type'] ?? null) !== 'member' || empty($payload['kode'])) {
            return response()->json([
                'success' => false,
                'message' => 'QR code tidak valid',
            ], 400);
        }

        $member = Member::where('kode_member', $payload['kode'])->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $member->id,
                'kode_member'     => $member->kode_member,
                'nama_member'     => $member->nama_member,
                'status'          => $member->status,
                'tanggal_expired' => $member->tanggal_expired,
                'total_harga'     => $member->total_harga,
            ],
        ]);
    }

    // =========================================================
    // KELUAR - Validasi kartu member saat scan di gerbang user
    // POST /api/member/keluar
    // Body: { token: "kode_member atau token QR" }
    // =========================================================

    public function keluar(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = trim($request->input('token'));

        // =========================================================
        // CARI MEMBER BERDASARKAN kode_member ATAU token
        // =========================================================

        $member = Member::where('kode_member', $token)
            ->orWhere('token', $token)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu member tidak ditemukan.',
            ], 404);
        }

        // =========================================================
        // CEK STATUS: harus lunas dan belum expired
        // =========================================================

        if ($member->status !== 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Member belum lunas. Silakan bayar dulu.',
                'data'    => [
                    'kode_member' => $member->kode_member,
                    'nama_member' => $member->nama_member,
                    'status'      => $member->status,
                ],
            ], 403);
        }

        if ($member->tanggal_expired && now()->gt($member->tanggal_expired)) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu member sudah expired.',
                'data'    => [
                    'kode_member'     => $member->kode_member,
                    'nama_member'     => $member->nama_member,
                    'tanggal_expired' => $member->tanggal_expired,
                ],
            ], 403);
        }

        // Catat sesi parkir member masuk (nomor plat baru dicatat saat keluar)
        $activeParking = Parking::where('member_id', $member->id)
            ->whereNull('jam_keluar')
            ->first();

        if (!$activeParking) {
            $activeParking = Parking::create([
                'member_id'     => $member->id,
                'kategori'      => 'mobil',
                'no_plat'       => null,
                'jam_masuk'     => now(),
                'jam_keluar'    => null,
                'total_tagihan' => 0,
            ]);
        }

        // Akses masuk berhasil, pintu terbuka
        return response()->json([
            'success' => true,
            'message' => 'Kartu member valid. Pintu terbuka.',
            'data'    => [
                'kode_member'     => $member->kode_member,
                'nama_member'     => $member->nama_member,
                'nama_perusahaan' => $member->nama_perusahaan,
                'plat_nomor'      => null, // Belum dicatat saat masuk
                'status'          => $member->status,
                'tanggal_expired' => $member->tanggal_expired,
                'waktu_masuk'     => $activeParking->jam_masuk,
            ],
        ]);
    }
}