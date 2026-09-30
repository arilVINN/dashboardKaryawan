<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Hrd\HrdDivisiController;
use App\Http\Controllers\Hrd\HrdPesanController;
use App\Http\Controllers\Hrd\HrdStaffController;
use App\Http\Controllers\Kadiv\KadivDashboardController;
use App\Http\Controllers\Kadiv\KadivMessageController;
use App\Http\Controllers\Kadiv\KadivStaffController;
use App\Http\Controllers\Kadiv\KadivTaskController;
use App\Http\Controllers\Staff\PesanController;
use App\Http\Controllers\Staff\StaffTugasController;
use Illuminate\Support\Facades\Route;

// Public – login (no token required)
Route::post('/login', [AuthController::class, 'login']);

// Protected – token auth + throttling for every authenticated endpoint
Route::middleware([
    'gateway.throttle:60,1',   // 60 req/min per user/IP
    'auth:sanctum',            // Sanctum guard
])->group(function () {
    // Logout (needs token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Kadiv dashboard & messaging endpoints
    Route::middleware('role:kadiv')->prefix('kadiv')->group(function (): void {
        Route::get('/dashboard', [KadivDashboardController::class, 'index']);
        Route::get('/pesan', [KadivMessageController::class, 'index']);
        Route::get('/pesan/{id_pesan}', [KadivMessageController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [KadivMessageController::class, 'balas']);
        Route::post('/pesan', [KadivMessageController::class, 'store']);
    });

    // Staff‑only routes (role middleware registered as "role")
    Route::middleware('role:staff')->prefix('staff')->group(function () {
        Route::get('/tugas', [StaffTugasController::class, 'index']);
        Route::get('/tugas/{id}', [StaffTugasController::class, 'show']);
        Route::post('/tugas/{id}/submit', [StaffTugasController::class, 'submit']);
        Route::get('/pesan', [PesanController::class, 'index']);
        Route::get('/pesan/{id_pesan}', [PesanController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [PesanController::class, 'balas']);
    });

    // Kadiv‑only routes
    Route::middleware('role:kadiv')->prefix('kadiv')->group(function () {
        // Manajemen Staff
        Route::get('/staff', [KadivStaffController::class, 'index']);
        Route::get('/staff/{id}', [KadivStaffController::class, 'show']);

        // Manajemen Tugas
        Route::get('/tugas', [KadivTaskController::class, 'index']);
        Route::post('/tugas', [KadivTaskController::class, 'store']);
        Route::post('/tugas/{id}', [KadivTaskController::class, 'update']); // Some clients use POST for PUT when uploading files
        Route::put('/tugas/{id}', [KadivTaskController::class, 'update']);
        Route::delete('/tugas/{id}', [KadivTaskController::class, 'destroy']);
        Route::post('/tugas/{id}/review', [KadivTaskController::class, 'review']);
    });

    // HRD‑only routes
    Route::middleware('role:hrd')->prefix('hrd')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Hrd\HrdDashboardController::class, 'index']);
        
        Route::get('/pesan', [HrdPesanController::class, 'index']);
        Route::post('/pesan', [HrdPesanController::class, 'store']);
        Route::get('/pesan/{id_pesan}', [HrdPesanController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [HrdPesanController::class, 'balas']);

        // Manajemen Divisi
        Route::get('/divisi', [HrdDivisiController::class, 'index']);
        Route::post('/divisi', [HrdDivisiController::class, 'store']);
        Route::get('/divisi/{id}', [HrdDivisiController::class, 'show']);

        // Manajemen Staff
        Route::get('/staff', [HrdStaffController::class, 'index']);
        Route::post('/staff', [HrdStaffController::class, 'store']);
        Route::get('/staff/{id}', [HrdStaffController::class, 'show']);
    });
});
