<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiketParkir as Tiket;
use App\Services\PlatNomorValidator;
use Illuminate\Support\Str;

class TiketController extends Controller
{
    // public function index()
    // {
    //     $tiket = Tiket::all()->makeHidden(['token', 'qr_code']);
    //     return response()->json(['success' => true, 'data' => $tiket]);
    // }

    public function create(Request $request)
    {
        $last = Tiket::orderByDesc('id')->first();
        if ($last && preg_match('/^A(\d+)$/', $last->kode_tiket, $matches)) {
            $nomor = ((int) $matches[1]) + 1;
        } else {
            $nomor = 1;
        }

        do {
            $kode = 'A' . str_pad((string) $nomor, 4, "0", STR_PAD_LEFT);
            $nomor++;
        } while (Tiket::where('kode_tiket', $kode)->exists());

        // Saat kendaraan masuk (ambil tiket), nomor plat tidak diinput (plat diinput petugas saat keluar)
        $tiket = Tiket::create([
            'kode_tiket'  => $kode,
            'qr_code'     => \Illuminate\Support\Str::random(60),
            'plat_nomor'  => null,
            'status'      => 'masuk',
            'waktu_masuk' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $tiket], 201);
    }

    // public function show(string $id)
    // {
    //     $tiket = Tiket::find($id);
    //     if (! $tiket) {
    //         return response()->json(['success' => false, 'message' => 'Not found'], 404);
    //     }
    //     $tiket->makeHidden(['token', 'qr_code']);
    //     return response()->json(['success' => true, 'data' => $tiket]);
    // }

    // public function keluar(string $id)
    // {
    //     $tiket = Tiket::find($id);
    //     if (! $tiket) {
    //         return response()->json(['success' => false, 'message' => 'Not found'], 404);
    //     }
    //     $tiket->status = 'keluar';
    //     $tiket->waktu_keluar = now();
    //     $tiket->save();

    //     $tiket->makeHidden(['token', 'qr_code']);
    //     return response()->json(['success' => true, 'data' => $tiket]);
    // }

    // public function destroy(string $id)
    // {
    //     $tiket = Tiket::find($id);
    //     if (! $tiket) {
    //         return response()->json(['success' => false, 'message' => 'Not found'], 404);
    //     }
    //     $tiket->delete();
    //     return response()->json(['success' => true]);
    // }
}