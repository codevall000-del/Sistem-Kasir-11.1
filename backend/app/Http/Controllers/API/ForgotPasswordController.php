<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar',
            ], 404);
        }

        // Generate raw token and its hash
        $rawToken = Str::random(60);
        $hashedToken = hash('sha256', $rawToken);

        // Delete old tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => $hashedToken,
            'created_at' => now(),
        ]);

        // Build reset URL
        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000')
            . '/reset-password?token=' . $rawToken
            . '&email=' . urlencode($request->email);

        // Send email
        Mail::to($request->email)->send(new ResetPasswordMail($resetUrl));

        return response()->json([
            'success' => true,
            'message' => 'Link reset password telah dikirim ke email Anda',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        // Find the token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid',
            ], 400);
        }

        // Verify token hash
        if (hash('sha256', $request->token) !== $record->token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid',
            ], 400);
        }

        // Check expiry (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluarsa',
            ], 400);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $user->update(['password' => $request->password]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all Sanctum tokens for security
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset',
        ]);
    }
}
