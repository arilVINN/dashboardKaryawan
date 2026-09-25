<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Staff\TugasController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:staff')->prefix('staff')->group(function () {
        Route::get('/tugas', [TugasController::class, 'index']);
        Route::get('/tugas/{id}', [TugasController::class, 'show']);
        Route::post('/tugas/{id}/submit', [TugasController::class, 'submit']);
    });
});
