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



//route" untuk halaman daftar divisi dan daftar karyawan, dan daftar pesan, yang akan 
//menampilkan tabel data divisi, karyawan, dan pesan
Route::get('/hrd/daftarDivisi', function () {
    return view('hrd.daftarDivisi');
})->name('daftarDivisi');

Route::get('/hrd/daftarKaryawan', function () {
    return view('hrd.daftarKaryawan');
})->name('daftarKaryawan');

Route::get('/hrd/daftarPesan', function () {
    return view('hrd.daftarPesan');
})->name('daftarPesan');



// 1. Route untuk proses form login
Route::post('/login-proses', function (Request $request) {
    // Data dummy
    $userDummy = [
        'nama' => 'Samuel Sigalingging',
        'inisial' => 'S',
        'email' => 'staff@silindo.co.id',
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

Route::post('/profile', function (Request $request) {
    $validated = $request->validate([
        'nama' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'telepon' => ['required', 'string', 'max:30'],
        'alamat' => ['required', 'string', 'max:1000'],
    ]);

    $pegawai = session('user_session', []);
    session(['user_session' => array_merge($pegawai, $validated)]);

    return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
})->name('profile.update');