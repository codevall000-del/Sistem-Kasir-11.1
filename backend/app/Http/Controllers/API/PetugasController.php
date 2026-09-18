<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    /**
     * GET /api/petugas
     * Return semua akun dengan role petugas.
     */
    public function index()
    {
        $petugas = User::where('role', 'petugas')
            ->select('id', 'name', 'email', 'no_hp', 'role', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $petugas,
        ]);
    }

    /**
     * POST /api/petugas
     * Buat akun petugas baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'no_hp'                 => 'nullable|string|max:25|unique:users,no_hp',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        $petugas = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'no_hp'    => $request->no_hp ? WhatsAppService::formatPhoneNumber($request->no_hp) : null,
            'password' => $request->password, // auto-hashed via cast
            'role'     => 'petugas',           // hardcoded, not from input
        ]);

        return response()->json([
            'success' => true,
            'data'    => $petugas->only('id', 'name', 'email', 'no_hp', 'role', 'created_at'),
        ], 201);
    }

    /**
     * GET /api/petugas/{id}
     * Return detail satu petugas.
     */
    public function show(string $id)
    {
        $petugas = User::where('id', $id)
            ->where('role', 'petugas')
            ->select('id', 'name', 'email', 'no_hp', 'role', 'created_at')
            ->first();

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Petugas tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $petugas,
        ]);
    }

    /**
     * PUT/PATCH /api/petugas/{id}
     * Update data petugas (partial update).
     */
    public function update(Request $request, string $id)
    {
        $petugas = User::where('id', $id)
            ->where('role', 'petugas')
            ->first();

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Petugas tidak ditemukan',
            ], 404);
        }

        $rules = [];

        if ($request->has('name')) {
            $rules['name'] = 'string|max:255';
        }

        if ($request->has('email')) {
            $rules['email'] = 'email|unique:users,email,' . $petugas->id;
        }

        if ($request->has('no_hp')) {
            $rules['no_hp'] = 'nullable|string|max:25|unique:users,no_hp,' . $petugas->id;
        }

        if ($request->has('password')) {
            $rules['password']              = 'min:8';
            $rules['password_confirmation'] = 'required|same:password';
        }

        if (!empty($rules)) {
            $request->validate($rules);
        }

        $data = $request->only(array_keys($rules));

        // Format phone number if present
        if (array_key_exists('no_hp', $data)) {
            $data['no_hp'] = !empty($data['no_hp']) ? WhatsAppService::formatPhoneNumber($data['no_hp']) : null;
        }

        // Remove password_confirmation from data to save
        unset($data['password_confirmation']);

        $petugas->update($data);

        return response()->json([
            'success' => true,
            'data'    => $petugas->fresh()->only('id', 'name', 'email', 'no_hp', 'role', 'created_at'),
        ]);
    }

    /**
     * DELETE /api/petugas/{id}
     * Hapus akun petugas.
     */
    public function destroy(Request $request, string $id)
    {
        $petugas = User::where('id', $id)
            ->where('role', 'petugas')
            ->first();

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Petugas tidak ditemukan',
            ], 404);
        }

        // Guard: tidak boleh hapus diri sendiri
        if ($request->user() && (int) $request->user()->id === (int) $id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri',
            ], 403);
        }

        // Revoke all tokens before delete
        $petugas->tokens()->delete();

        $petugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun petugas berhasil dihapus',
        ]);
    }
}
