<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Direktur\DirekturDashboardController;
use App\Http\Controllers\Hrd\HrdDashboardController;
use App\Http\Controllers\Hrd\HrdDivisiController;
use App\Http\Controllers\Hrd\HrdPesanController;
use App\Http\Controllers\Hrd\HrdStaffController;
use App\Http\Controllers\Kadiv\KadivDashboardController;
use App\Http\Controllers\Kadiv\KadivMessageController;
use App\Http\Controllers\Kadiv\KadivStaffController;
use App\Http\Controllers\Kadiv\KadivTaskController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\KalenderController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Controllers\Staff\PesanController;
use App\Http\Controllers\Staff\ProfileController;
use App\Http\Controllers\Staff\StaffTugasController;
use Illuminate\Support\Facades\Route;

// Public – login (no token required, throttled against brute-force)
Route::middleware('throttle:login')->post('/login', [AuthController::class, 'login']);

// Protected – token auth + throttling for every authenticated endpoint
Route::middleware([
    'gateway.throttle:60,1',   // 60 req/min per user/IP
    'auth:sanctum',            // Sanctum guard
    'akun.aman.api',           // token status / forced-password checks
])->group(function () {
    // Logout (needs token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Direktur / Direksi notification center
    Route::middleware('role:direktur,direksi')->prefix('direktur')->group(function (): void {
        Route::get('/notifikasi', [DirekturDashboardController::class, 'notifikasi']);
    });

    // Kadiv dashboard & messaging endpoints
    Route::middleware('role:kadiv')->prefix('kadiv')->group(function (): void {
        Route::get('/dashboard', [KadivDashboardController::class, 'index']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:submit-form');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:submit-form');
        Route::post('/profile/foto', [ProfileController::class, 'updateFoto'])->middleware('throttle:upload-foto');
        Route::get('/pesan', [KadivMessageController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/pesan/{id_pesan}', [KadivMessageController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [KadivMessageController::class, 'balas'])->middleware('throttle:submit-form');
        Route::post('/pesan', [KadivMessageController::class, 'store'])->middleware('throttle:submit-form');
    });

    // Staff‑only routes (role middleware registered as "role")
    Route::middleware('role:staff')->prefix('staff')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/tugas', [StaffTugasController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/tugas/{id}', [StaffTugasController::class, 'show']);

        // Director-level roles are read-only: they cannot mutate task state.
        Route::middleware('deny.superadmin')->group(function (): void {
            Route::patch('/tugas/{id}/toggle-checklist', [StaffTugasController::class, 'toggleChecklist'])->middleware('throttle:submit-form');
            Route::post('/tugas/{id}/submit', [StaffTugasController::class, 'submit'])->middleware('throttle:submit-form');
        });

        Route::get('/pesan', [PesanController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/pesan/{id_pesan}', [PesanController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [PesanController::class, 'balas'])->middleware('throttle:submit-form');
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:submit-form');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:submit-form');
        Route::post('/profile/foto', [ProfileController::class, 'updateFoto'])->middleware('throttle:upload-foto');
        Route::get('/kalender-events', [KalenderController::class, 'events']);
    });

    // Kadiv‑only routes
    Route::middleware('role:kadiv')->prefix('kadiv')->group(function () {
        // Manajemen Staff
        Route::get('/staff', [KadivStaffController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/staff/{id}', [KadivStaffController::class, 'show']);

        // Manajemen Tugas (read / monitoring)
        Route::get('/tugas', [KadivTaskController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/tugas/{id}', [KadivTaskController::class, 'show']);
        Route::get('/kalender-events', [KalenderController::class, 'eventsKadiv']);

        // Director-level roles are read-only: they cannot mutate tugas.
        Route::middleware('deny.superadmin')->group(function (): void {
            Route::post('/tugas', [KadivTaskController::class, 'store']);
            Route::post('/tugas/{id}', [KadivTaskController::class, 'update']); // Some clients use POST for PUT when uploading files
            Route::put('/tugas/{id}', [KadivTaskController::class, 'update']);
            Route::delete('/tugas/{id}', [KadivTaskController::class, 'destroy']);
            Route::post('/tugas/{id}/review', [KadivTaskController::class, 'review']);
        });
    });

    // HRD‑only routes
    Route::middleware('role:hrd')->prefix('hrd')->group(function () {
        Route::get('/dashboard', [HrdDashboardController::class, 'index']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:submit-form');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:submit-form');
        Route::post('/profile/foto', [ProfileController::class, 'updateFoto'])->middleware('throttle:upload-foto');

        Route::get('/pesan', [HrdPesanController::class, 'index'])->middleware('throttle:pencarian');
        Route::post('/pesan', [HrdPesanController::class, 'store'])->middleware('throttle:submit-form');
        Route::get('/pesan/{id_pesan}', [HrdPesanController::class, 'show']);
        Route::post('/pesan/{id_pesan}/balas', [HrdPesanController::class, 'balas'])->middleware('throttle:submit-form');

        // Manajemen Divisi (read / monitoring)
        Route::get('/divisi', [HrdDivisiController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/divisi/{id}', [HrdDivisiController::class, 'show']);

        // Kalender
        Route::get('/kalender-events', [KalenderController::class, 'eventsHrd']);

        // Manajemen Staff (read / monitoring)
        Route::get('/staff', [HrdStaffController::class, 'index'])->middleware('throttle:pencarian');
        Route::get('/staff/{id}', [HrdStaffController::class, 'show']);

        // Director-level roles are read-only: they cannot mutate master data.
        Route::middleware('deny.superadmin')->group(function (): void {
            Route::post('/divisi', [HrdDivisiController::class, 'store'])->middleware('throttle:submit-form');
            Route::put('/divisi/{id}', [HrdDivisiController::class, 'update'])->middleware('throttle:submit-form');
            Route::patch('/divisi/{id}', [HrdDivisiController::class, 'update'])->middleware('throttle:submit-form');
            Route::delete('/divisi/{id}', [HrdDivisiController::class, 'destroy'])->middleware('throttle:submit-form');

            Route::post('/staff', [HrdStaffController::class, 'store'])->middleware('throttle:submit-form');
            Route::put('/staff/{id}', [HrdStaffController::class, 'update'])->middleware('throttle:submit-form');
            Route::patch('/staff/{id}', [HrdStaffController::class, 'update'])->middleware('throttle:submit-form');
            Route::delete('/staff/{id}', [HrdStaffController::class, 'destroy'])->middleware('throttle:submit-form');
        });
    });

});
