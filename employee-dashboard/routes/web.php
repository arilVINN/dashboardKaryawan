<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Controllers\Staff\PesanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('staff.dashboard');
})->name('dashboard');

Route::get('/tugas', function () {
    return view('staff.tugas');
})->name('tugas');

Route::get('/pesan', function () {
    return view('staff.pesan');
})->name('pesan');

Route::get('/pesan/detail/{id?}', function () {
    return view('staff.detailPesan');
})->name('pesan.detail');

Route::get('/tugas/detail/{id?}', function () {
    return view('staff.detailTugas');
})->name('tugas.detail');

Route::get('/profile', function () {
    return view('staff.detailProfile');
})->name('profile');

Route::get('/login', function () {
    return view('login');
})->name('login');


Route::prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/pesan', [PesanController::class, 'index']);
    Route::post('/pesan/send', [PesanController::class, 'send']);
    Route::get('/pesan/{id_tugas}', [PesanController::class, 'show']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
});
