<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Middleware\EnsureStaffRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::get('/', function () {
    return view('staff.dashboard');
})->name('dashboard');

Route::get('/login', function () {
    return view('login');
})->name('login');

//staff route
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



Route::prefix('staff')->middleware(EnsureStaffRole::class)->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
});

//HRD
Route::get('/hrd/dashboard', function () {
    return view('hrd.dashboard');
})->name('dashboard');

Route::get('/hrd/manajemenDivisi', function () {
    return view('hrd.manajemenDivisi');
})->name('manajemen');

Route::get('/hrd/pesan', function () {
    return view('hrd.pesan');
})->name('pesan');

Route::get('/hrd/detailPesan/{id}', function () {
    return view('hrd.detailPesan');
})->name('detailPesan');

Route::get('/hrd/detailDivisi/{id}', function () {
    return view('hrd.detailDivisi');
})->name('detailDivisi');

Route::get('/hrd/detailDivisi', function () {
    return view('hrd.detailDivisi');
})->name('detailDivisi');

Route::get('/hrd/detailKaryawan', function () {
    return view('hrd.detailKaryawan');
})->name('detailKaryawan');

// 1. Route untuk proses form login
Route::post('/login-proses', function (Request $request) {
    // Data dummy
    $userDummy = [
        'nama' => 'Samuel Sigalingging',
        'inisial' => 'S',
        'telepon' => '081234567890',
        'tingkatan' => 'Staff',
        'divisi' => 'Content Writer',
        'tanggal_masuk' => '12 Januari 2025',
        'alamat' => 'Salatiga, Jawa Tengah',
        'tanggal_dibuat' => '10 Januari 2025',
    ];

    session(['user_session' => $userDummy]);

    return redirect('/');
})->name('login.proses');


// 2. Route untuk halaman profil
Route::get('/profile', function () {
    // Cek apakah ada session login, jika tidak, tendang balik ke login
    if (!session()->has('user_session')) {
        return redirect('/login');
    }

    // Ambil data dari session dan lempar ke view detailProfile
    $pegawai = session('user_session');

    return view('staff.detailProfile', ['pegawai' => $pegawai]);
});
