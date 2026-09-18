<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Standardize Indonesian phone number to international format (628xxx).
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, dots, and plus signs
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Convert leading 0 to 62
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Send OTP message to phone number via WhatsApp Gateway or simulation log.
     */
    public static function sendOtp(string $targetPhone, string $otp): array
    {
        $formattedPhone = self::formatPhoneNumber($targetPhone);
        $message = "Halo! Kode verifikasi (OTP) untuk reset kata sandi akun E-Parking Anda adalah: *{$otp}*.\n\nKode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun demi keamanan akun Anda.";

        $apiToken    = env('WA_API_TOKEN', 'gQ56ucP2PakiaYjYhUSc');
        $gatewayUrl  = env('WA_GATEWAY_URL', 'https://api.fonnte.com/send');

        // Development fallback: jika token gateway belum dikonfigurasi
        if (empty($apiToken)) {
            Log::info("[WhatsApp Simulation] OTP sent to {$formattedPhone}: {$otp}");
            return [
                'success' => true,
                'mode'    => 'simulation',
                'target'  => $formattedPhone,
                'otp'     => $otp,
                'message' => 'Kode OTP berhasil dibuat (mode simulasi aktif)',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post($gatewayUrl, [
                'target'      => $formattedPhone,
                'message'     => $message,
                'countryCode' => '62',
            ]);

            $resData = $response->json();

            if ($response->successful() && !empty($resData['status'])) {
                Log::info("[WhatsApp Gateway] OTP sent to {$formattedPhone} via Fonnte");
                return [
                    'success' => true,
                    'mode'    => 'gateway',
                    'target'  => $formattedPhone,
                    'message' => 'Kode OTP berhasil dikirim via WhatsApp Fonnte',
                ];
            }

            // Jika device Fonnte disconnect atau error response
            $reason = $resData['reason'] ?? $resData['message'] ?? $response->body();
            Log::warning("[WhatsApp Gateway Warning] Fonnte reported: {$reason}. Falling back to simulation mode for dev.");

            return [
                'success' => true,
                'mode'    => 'simulation',
                'target'  => $formattedPhone,
                'otp'     => $otp,
                'warning' => 'Device WhatsApp Fonnte belum connect (' . $reason . '). Kode OTP ditampilkan untuk pengujian.',
                'message' => 'Kode OTP berhasil dibuat (Mode simulasi karena device Fonnte disconnect)',
            ];
        } catch (\Throwable $e) {
            Log::error("[WhatsApp Gateway Exception] " . $e->getMessage());
            return [
                'success' => true,
                'mode'    => 'simulation',
                'target'  => $formattedPhone,
                'otp'     => $otp,
                'warning' => 'Gagal menghubungi gateway Fonnte (' . $e->getMessage() . '). Kode OTP ditampilkan untuk pengujian.',
                'message' => 'Kode OTP berhasil dibuat (Mode simulasi aktif)',
            ];
        }
    }
}
