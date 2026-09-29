<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Staff\StaffTugasController;

// Public – login (no token required)
Route::post('/login', [AuthController::class, 'login']);

// Protected – centralized auth, throttling, global response format
Route::middleware([
        'gateway.throttle:60,1',   // 60 req/min per user/IP
        'auth:sanctum',            // Sanctum guard
        'gateway.format'           // JSON formatter
    ])->group(function () {
    // Logout (needs token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Staff‑only routes (role middleware registered as "role")
    Route::middleware('role:staff')->prefix('staff')->group(function () {
        Route::get('/tugas', [StaffTugasController::class, 'index']);
        Route::get('/tugas/{id}', [StaffTugasController::class, 'show']);
        Route::post('/tugas/{id}/submit', [StaffTugasController::class, 'submit']);
    });

    // Kadiv‑only routes
    Route::middleware('role:kadiv')->prefix('kadiv')->group(function () {
        // Manajemen Staff
        Route::get('/staff', [\App\Http\Controllers\Kadiv\KadivStaffController::class, 'index']);
        Route::get('/staff/{id}', [\App\Http\Controllers\Kadiv\KadivStaffController::class, 'show']);

        // Manajemen Tugas
        Route::get('/tugas', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'index']);
        Route::post('/tugas', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'store']);
        Route::post('/tugas/{id}', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'update']); // Some clients use POST for PUT when uploading files, but we'll stick to PUT
        Route::put('/tugas/{id}', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'update']);
        Route::delete('/tugas/{id}', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'destroy']);
        Route::post('/tugas/{id}/review', [\App\Http\Controllers\Kadiv\KadivTaskController::class, 'review']);
    });

    // HRD‑only routes
    Route::middleware('role:hrd')->prefix('hrd')->group(function () {
        // Manajemen Divisi
        Route::get('/divisi', [\App\Http\Controllers\Hrd\HrdDivisiController::class, 'index']);
        Route::post('/divisi', [\App\Http\Controllers\Hrd\HrdDivisiController::class, 'store']);
        Route::get('/divisi/{id}', [\App\Http\Controllers\Hrd\HrdDivisiController::class, 'show']);

        // Manajemen Staff
        Route::get('/staff', [\App\Http\Controllers\Hrd\HrdStaffController::class, 'index']);
        Route::post('/staff', [\App\Http\Controllers\Hrd\HrdStaffController::class, 'store']);
        Route::get('/staff/{id}', [\App\Http\Controllers\Hrd\HrdStaffController::class, 'show']);
    });
});
