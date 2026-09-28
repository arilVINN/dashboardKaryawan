<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Controllers\Staff\PesanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/pesan', [PesanController::class, 'index']);
    Route::post('/pesan/send', [PesanController::class, 'send']);
    Route::get('/pesan/{id_tugas}', [PesanController::class, 'show']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
});
