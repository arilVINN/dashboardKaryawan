<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Middleware\EnsureStaffRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\KadivController;


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




// ================= KADIV ================= //

// Rute Dashboard
Route::get('/kadiv/dashboard', function () {
    return view('kadiv.dashboard');
})->name('kadiv.dashboard');

// Rute Manajemen Staff
Route::get('/kadiv/manajemenStaff', function () {
    return view('kadiv.manajemenStaff');
})->name('kadiv.manajemenStaff'); 

// (Tambahan) Rute Detail Manajemen Staff sesuai file baru di gambar
Route::get('/kadiv/manajemenStaff/{id}', function ($id) {
    return view('kadiv.detailManajemenStaff', ['id' => $id]); 
})->name('kadiv.manajemenStaff.show');

// Rute Profil
Route::get('/kadiv/profile', function () {
    return view('kadiv.profile'); // Disesuaikan ke profile.blade.php
})->name('kadiv.profile');



// ================= TUGAS ================= //

// Rute Daftar Tugas
Route::get('/kadiv/tugas', function () {
    return view('kadiv.tugas'); // Disesuaikan ke tugas.blade.php
})->name('kadiv.tugas');

// Rute Detail Tugas
Route::get('/kadiv/detailTugas/{id}', function ($id) {
    return view('kadiv.detailTugas', ['id' => $id]);
})->name('kadiv.detailTugas.show');

Route::get('/kadiv/tugas/{id}', function ($id) {
    return view('kadiv.detailTugas', compact('id'));
});

 // sesuaikan dengan controller kamu
Route::post('/kadiv/tugas', [KadivController::class, 'store'])->name('kadiv.tugas.store');

// Rute Revisi Tugas
Route::post('/kadiv/tugas/{id}/revisi', function (Request $request, $id) {
    $request->validate([
        'isi_revisi' => ['required', 'string', 'max:2000'],
        'tenggat'    => ['nullable', 'string'],
    ]);

    $semuaRevisi = session('revisi_tugas', []);
    $semuaRevisi[] = [
        'id_tugas' => $id,
        'isi'      => $request->input('isi_revisi'),
        'tenggat'  => $request->input('tenggat', ''),
        'waktu'    => now()->format('d M Y, H.i'),
    ];
    session(['revisi_tugas' => $semuaRevisi]);

    return redirect()
        ->route('kadiv.detailTugas.show', $id)
        ->with('success', 'Revisi berhasil dikirim.');
})->name('kadiv.revisiTugas');



// ================= PESAN ================= //
Route::post('/kadiv/detailPesan/{id}/balas', [KadivController::class, 'balasPesan'])->name('kadiv.balasPesan');

// Rute Daftar Pesan
Route::get('/kadiv/pesan', function () { // Path diubah dari /kadiv/detailPesan menjadi /kadiv/pesan
    return view('kadiv.pesan'); // Disesuaikan ke pesan.blade.php
})->name('kadiv.pesan');

// Rute Detail Pesan
Route::get('/kadiv/detailPesan/{id}', function ($id) {
    return view('kadiv.lihatPesan', ['id' => $id]); // Sesuai dengan file lihatPesan.blade.php
})->name('kadiv.detailPesan.show');

Route::get('/kadiv/detailPesan/{id}', function ($id) {
    return view('kadiv.detailPesan', compact('id'));
})->name('kadiv.detailPesan');

Route::get('/kadiv/detailPesan/{id}', [KadivController::class, 'detailPesan'])->name('kadiv.detailPesan');

// Rute Balas Pesan
Route::post('/kadiv/pesan/{id}/balas', function (Request $request, $id) {
    $request->validate([
        'pesan' => ['required', 'string', 'max:1000'],
    ]);

    $semuaBalasan = session('balasan_pesan', []);
    $semuaBalasan[] = [
        'id_pesan'    => $id,
        'isi'         => $request->input('pesan'),
        'pengirim'    => 'Kadiv',
        'waktu'       => now()->format('d M Y, H.i'),
    ];
    session(['balasan_pesan' => $semuaBalasan]);

    return redirect()
        ->route('kadiv.detailPesan.show', $id)
        ->with('success', 'Balasan berhasil dikirim.');
})->name('kadiv.balasPesan');



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