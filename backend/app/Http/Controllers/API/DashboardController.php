<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\member;
use App\Models\TiketParkir;
use App\Models\Parking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Return aggregate statistics for dashboard.
     */
    public function stats(Request $request)
    {
        $today = Carbon::today();
        $selectedYear = (int) $request->input('tahun', $today->year);

        // Total member terdaftar
        $totalMember = member::count();

        // Kendaraan masuk hari ini (tiket non-member + sesi parkir member)
        $tiketMasuk = TiketParkir::whereDate('waktu_masuk', $today)->count();
        $memberMasuk = Parking::whereDate('jam_masuk', $today)->count();
        $kendaraanMasuk = $tiketMasuk + $memberMasuk;

        // Sedang parkir (tiket status masuk + sesi member yang belum keluar)
        $sedangParkir = TiketParkir::where('status', 'masuk')->count() 
            + Parking::whereNull('jam_keluar')->count();

        // Pendapatan: akumulasi pembayaran member + non-member keluar hari ini
        $pendapatanMember = member::sum(DB::raw('COALESCE(total_harga, jumlah_bayar, 0)'));
        
        // Pendapatan non-member hari ini (tiket keluar hari ini, Rp 3000/jam)
        $tiketKeluarHariIni = TiketParkir::where('status', 'keluar')
            ->whereDate('waktu_keluar', $today)
            ->get();
        
        $pendapatanNonMember = 0;
        foreach ($tiketKeluarHariIni as $tiket) {
            $waktuMasuk = Carbon::parse($tiket->waktu_masuk ?? $tiket->created_at);
            $waktuKeluar = Carbon::parse($tiket->waktu_keluar);
            $jam = ceil($waktuMasuk->diffInMinutes($waktuKeluar) / 60);
            $jam = $jam < 1 ? 1 : $jam;
            $pendapatanNonMember += $jam * 3000;
        }

        $totalPendapatan = $pendapatanMember + $pendapatanNonMember;

        // -------------------------------------------------------------
        // Perhitungan Grafik Bulanan Riil (Tahun Berjalan / Pilihan)
        // -------------------------------------------------------------
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // 1. Total Transaksi Tiket Kendaraan per Bulan
        $ticketMonthlyCounts = TiketParkir::selectRaw('MONTH(waktu_masuk) as bulan, COUNT(*) as total')
            ->whereYear('waktu_masuk', $selectedYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // 2. Pendapatan Tiket Keluar per Bulan
        $ticketsOut = TiketParkir::where('status', 'keluar')
            ->whereYear('waktu_keluar', $selectedYear)
            ->get();

        $ticketMonthlyRevenue = [];
        foreach ($ticketsOut as $t) {
            $m = (int) Carbon::parse($t->waktu_keluar)->format('n');
            $wMasuk = Carbon::parse($t->waktu_masuk ?? $t->created_at);
            $wKeluar = Carbon::parse($t->waktu_keluar);
            $jam = ceil($wMasuk->diffInMinutes($wKeluar) / 60);
            $jam = $jam < 1 ? 1 : $jam;
            $tarif = $jam * 3000;
            $ticketMonthlyRevenue[$m] = ($ticketMonthlyRevenue[$m] ?? 0) + $tarif;
        }

        // 3. Pendapatan Member per Bulan (jumlah tagihan/iuran riil)
        $memberMonthlyRevenue = member::selectRaw('MONTH(created_at) as bulan, SUM(COALESCE(total_harga, jumlah_bayar, 0)) as total')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $monthlyTransactions = [];
        $monthlyRevenue = [];

        for ($m = 1; $m <= 12; $m++) {
            $transVal = (int) ($ticketMonthlyCounts[$m] ?? 0);
            $revVal = (float) (($ticketMonthlyRevenue[$m] ?? 0) + ($memberMonthlyRevenue[$m] ?? 0));
            $isCurrent = ($selectedYear === (int) $today->year && $m === (int) $today->month);

            $monthlyTransactions[] = [
                'label' => $monthNames[$m],
                'value' => $transVal,
                'highlight' => $isCurrent
            ];

            $monthlyRevenue[] = [
                'label' => $monthNames[$m],
                'value' => $revVal,
                'highlight' => $isCurrent
            ];
        }

        // Aktivitas terbaru (gabungan tiket masuk + keluar hari ini)
        $aktivitas = [];

        $tiketMasukTerbaru = TiketParkir::whereDate('waktu_masuk', $today)
            ->orderByDesc('waktu_masuk')
            ->limit(10)
            ->get();

        foreach ($tiketMasukTerbaru as $t) {
            $aktivitas[] = [
                'tipe' => 'kendaraan_masuk',
                'label' => 'Kendaraan masuk',
                'detail' => 'Kendaraan non-member',
                'kode' => $t->kode_tiket,
                'waktu' => $t->waktu_masuk,
            ];
        }

        $tiketKeluarTerbaru = TiketParkir::where('status', 'keluar')
            ->whereDate('waktu_keluar', $today)
            ->orderByDesc('waktu_keluar')
            ->limit(5)
            ->get();

        foreach ($tiketKeluarTerbaru as $t) {
            $aktivitas[] = [
                'tipe' => 'pembayaran_parkir',
                'label' => 'Pembayaran parkir',
                'detail' => 'Transaksi non-member',
                'kode' => $t->kode_tiket,
                'waktu' => $t->waktu_keluar,
            ];
        }

        // Sort by waktu descending
        usort($aktivitas, function ($a, $b) {
            return strtotime($b['waktu']) - strtotime($a['waktu']);
        });

        // Limit to 10 items
        $aktivitas = array_slice($aktivitas, 0, 10);

        return response()->json([
            'success' => true,
            'data' => [
                'totalMember' => $totalMember,
                'kendaraanMasuk' => $kendaraanMasuk,
                'pendapatan' => $totalPendapatan,
                'sedangParkir' => $sedangParkir,
                'monthlyTransactions' => $monthlyTransactions,
                'monthlyRevenue' => $monthlyRevenue,
                'aktivitas' => $aktivitas,
            ],
        ]);
    }

    /**
     * GET /api/parkir
     * Return list of parking events (both non-member and member) for transactions & reports.
     */
    public function parkirList(Request $request)
    {
        $status = $request->input('status');
        $tanggal = $request->input('tanggal');
        $tipe = $request->input('tipe');

        $items = collect();

        // 1. Data Parkir Non-Member (TiketParkir)
        if ($tipe !== 'member') {
            $tiketQuery = TiketParkir::query();

            if ($status === 'selesai') {
                $tiketQuery->where('status', 'keluar');
            } elseif ($status === 'parkir') {
                $tiketQuery->where('status', 'masuk');
            }

            if ($tanggal) {
                $tiketQuery->whereDate('waktu_masuk', $tanggal);
            }

            $tiketItems = $tiketQuery->orderByDesc('created_at')->limit(100)->get()->map(function ($t) {
                $isExited = ($t->status === 'keluar');
                $biaya = 0;
                $durasi = '-';

                if ($isExited && $t->waktu_masuk && $t->waktu_keluar) {
                    $menit = Carbon::parse($t->waktu_masuk)->diffInMinutes(Carbon::parse($t->waktu_keluar));
                    $jam = ceil($menit / 60);
                    $jam = $jam < 1 ? 1 : $jam;
                    $biaya = $jam * 3000;
                    $durasi = $jam . ' Jam';
                } elseif ($t->status === 'masuk') {
                    $durasi = 'Aktif Parkir';
                    $biaya = 3000;
                }

                return [
                    'id'              => 'TKT-' . $t->id,
                    'raw_id'          => $t->id,
                    'tipe'            => 'non_member',
                    'tipe_label'      => 'Non-Member',
                    'kode_tiket'      => $t->kode_tiket,
                    'nama'            => 'Pengunjung Reguler',
                    'nama_perusahaan' => '-',
                    // Plat nomor HANYA tercatat saat kendaraan sudah keluar
                    'plat_nomor'      => $isExited ? ($t->plat_nomor ?: '-') : '-',
                    'jenis_kendaraan' => 'mobil',
                    'waktu_masuk'     => $t->waktu_masuk ? Carbon::parse($t->waktu_masuk)->format('Y-m-d H:i:s') : '-',
                    'waktu_keluar'    => ($isExited && $t->waktu_keluar) ? Carbon::parse($t->waktu_keluar)->format('Y-m-d H:i:s') : '-',
                    'durasi'          => $durasi,
                    'biaya'           => $biaya,
                    'total_biaya'     => $biaya,
                    'status'          => $isExited ? 'selesai' : 'parkir',
                    'created_at'      => $t->waktu_masuk ?? $t->created_at,
                ];
            });

            $items = $items->concat($tiketItems);
        }

        // 2. Data Parkir Member (Parking)
        if ($tipe !== 'non_member') {
            $memberParkingQuery = Parking::with('member');

            if ($status === 'selesai') {
                $memberParkingQuery->whereNotNull('jam_keluar');
            } elseif ($status === 'parkir') {
                $memberParkingQuery->whereNull('jam_keluar');
            }

            if ($tanggal) {
                $memberParkingQuery->whereDate('jam_masuk', $tanggal);
            }

            $memberItems = $memberParkingQuery->orderByDesc('jam_masuk')->limit(100)->get()->map(function ($p) {
                $isExited = !is_null($p->jam_keluar);
                $durasi = 'Aktif Parkir';

                if ($isExited && $p->jam_masuk && $p->jam_keluar) {
                    $menit = Carbon::parse($p->jam_masuk)->diffInMinutes(Carbon::parse($p->jam_keluar));
                    $jam = ceil($menit / 60);
                    $jam = $jam < 1 ? 1 : $jam;
                    $durasi = $jam . ' Jam';
                }

                return [
                    'id'              => 'MBR-PRK-' . $p->id,
                    'raw_id'          => $p->id,
                    'tipe'            => 'member',
                    'tipe_label'      => 'Member',
                    'kode_tiket'      => $p->member?->kode_member ?: ('MBR-' . $p->member_id),
                    'nama'            => $p->member?->nama_member ?: 'Member',
                    'nama_perusahaan' => $p->member?->nama_perusahaan ?: '-',
                    // Plat nomor HANYA tercatat saat kendaraan sudah keluar
                    'plat_nomor'      => $isExited ? ($p->no_plat ?: '-') : '-',
                    'jenis_kendaraan' => $p->kategori ?: 'mobil',
                    'waktu_masuk'     => $p->jam_masuk ? Carbon::parse($p->jam_masuk)->format('Y-m-d H:i:s') : '-',
                    'waktu_keluar'    => ($isExited && $p->jam_keluar) ? Carbon::parse($p->jam_keluar)->format('Y-m-d H:i:s') : '-',
                    'durasi'          => $durasi,
                    'biaya'           => 0,
                    'total_biaya'     => 0,
                    'status'          => $isExited ? 'selesai' : 'parkir',
                    'created_at'      => $p->jam_masuk ?? $p->created_at,
                ];
            });

            $items = $items->concat($memberItems);
        }

        // Urutkan seluruh aktivitas parkir dari yang paling baru
        $sorted = $items->sortByDesc(function ($item) {
            return strtotime((string) ($item['created_at'] ?? now()));
        })->values()->take(200);

        return response()->json([
            'success' => true,
            'data'    => $sorted,
        ]);
    }
}
