<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login user dan buat Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'    => true,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Logout user dengan menghapus token saat ini.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Ambil data user yang sedang login.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Cek akun email dan kirim kode OTP ke nomor WhatsApp terdaftar.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email tidak terdaftar di sistem',
            ], 404);
        }

        if (empty($user->no_hp)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini belum memiliki nomor WhatsApp terdaftar. Silakan hubungi Super Admin untuk menambahkan nomor kontak akun Anda.',
            ], 422);
        }

        $formattedPhone = \App\Services\WhatsAppService::formatPhoneNumber($user->no_hp);

        // Mask nomor telepon untuk privasi (contoh: 0856****3414)
        $rawPhone = $user->no_hp;
        $len = strlen($rawPhone);
        if ($len > 8) {
            $maskedPhone = substr($rawPhone, 0, 4) . '****' . substr($rawPhone, -4);
        } else {
            $maskedPhone = substr($rawPhone, 0, 2) . '****' . substr($rawPhone, -2);
        }

        // Generate kode acak 6 digit
        $otp = (string) random_int(100000, 999999);

        // Hapus atau batalkan OTP aktif sebelumnya
        \App\Models\PasswordResetOtp::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Simpan OTP baru berlaku 5 menit
        \App\Models\PasswordResetOtp::create([
            'user_id'    => $user->id,
            'no_hp'      => $formattedPhone,
            'otp'        => $otp,
            'expired_at' => now()->addMinutes(5),
            'is_used'    => false,
        ]);

        // Kirim via WhatsApp Service Fonnte
        $sendResult = \App\Services\WhatsAppService::sendOtp($formattedPhone, $otp);

        return response()->json([
            'success' => true,
            'message' => "Kode verifikasi telah dikirim ke nomor WhatsApp Anda ({$maskedPhone})",
            'data'    => [
                'email'        => $user->email,
                'masked_phone' => $maskedPhone,
                'mode'         => $sendResult['mode'] ?? 'gateway',
                'otp'          => ($sendResult['mode'] ?? '') === 'simulation' ? $otp : null,
                'warning'      => $sendResult['warning'] ?? null,
            ],
        ]);
    }

    /**
     * Verifikasi kode OTP yang diinput oleh pengguna.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $user = \App\Models\User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
            ], 404);
        }

        $resetOtp = \App\Models\PasswordResetOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (!$resetOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa',
            ], 422);
        }

        $resetToken = \Illuminate\Support\Str::random(60);
        $resetOtp->update(['token' => $resetToken]);

        return response()->json([
            'success'     => true,
            'message'     => 'Kode verifikasi valid',
            'reset_token' => $resetToken,
        ]);
    }

    /**
     * Reset password baru setelah kode OTP diverifikasi.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'reset_token'           => 'required|string',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = \App\Models\User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
            ], 404);
        }

        $resetOtp = \App\Models\PasswordResetOtp::where('user_id', $user->id)
            ->where('token', $request->reset_token)
            ->where('is_used', false)
            ->first();

        if (!$resetOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi tidak valid atau telah berakhir',
            ], 422);
        }

        $user->update([
            'password' => $request->password,
        ]);

        // Hanguskan sesi OTP
        $resetOtp->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui. Silakan login kembali.',
        ]);
    }
}
