<?php

use App\Http\Controllers\API\MemberController;
use App\Http\Controllers\API\GateController;
use App\Http\Controllers\API\TiketController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PetugasController;
use App\Http\Controllers\API\DashboardController;

// ============================================================
// AUTH ROUTES (PUBLIC)
// ============================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// AUTH ROUTES (PROTECTED)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// ============================================================
// DASHBOARD STATS
// ============================================================
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/parkir', [DashboardController::class, 'parkirList']);

// ============================================================
// MEMBER ROUTES
// ============================================================
Route::prefix('member')->group(function () {
    Route::get('/', [MemberController::class, 'index']);
    Route::post('/', [MemberController::class, 'store']);
    Route::get('/{id}', [MemberController::class, 'show']);
    Route::put('/{id}', [MemberController::class, 'update']);
    Route::put('/{id}/pembayaran', [MemberController::class, 'updatePembayaran']);
    Route::post('/{id}/perpanjang', [MemberController::class, 'perpanjang']);
    Route::delete('/{id}', [MemberController::class, 'destroy']);
    Route::post('/check', [MemberController::class, 'check']);
    Route::post('/keluar', [MemberController::class, 'keluar']);
});

// ============================================================
// GATE ROUTES
// ============================================================
Route::prefix('gate')->group(function () {
    Route::post('/keluar', [GateController::class, 'scanKeluar']);
    Route::post('/payment', [GateController::class, 'processPayment']);
});

// Alias endpoints
Route::post('/scan', [GateController::class, 'scanKeluar']);
Route::post('/payment', [GateController::class, 'processPayment']);
Route::post('/member/masuk', [MemberController::class, 'keluar']);

// Endpoint untuk mesin cetak tiket pengunjung (User)
Route::post('/tiket', [TiketController::class, 'create']);

// ============================================================
// PETUGAS MANAGEMENT (SUPER ADMIN)
// ============================================================
Route::prefix('petugas')->group(function () {
    Route::get('/', [PetugasController::class, 'index']);
    Route::post('/', [PetugasController::class, 'store']);
    Route::get('/{id}', [PetugasController::class, 'show']);
    Route::put('/{id}', [PetugasController::class, 'update']);
    Route::delete('/{id}', [PetugasController::class, 'destroy']);
});