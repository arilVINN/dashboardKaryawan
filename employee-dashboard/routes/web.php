<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Middleware\EnsureStaffRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\KadivController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Hrd\HrdKaryawanController;
use App\Http\Controllers\Hrd\HrdDivisiController;
use App\Http\Controllers\Hrd\HrdPesanController;
use App\Http\Controllers\Hrd\HrdStaffController;
use App\Http\Controllers\Hrd\HrdDashboardController;
use App\Models\Divisi;


Route::get('/', function () {
    return view('staff.dashboard');
})->name('dashboard');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('gateway.throttle:5,1')
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
// Rute Daftar Pesan
Route::get('/kadiv/pesan', function () { // Path diubah dari /kadiv/detailPesan menjadi /kadiv/pesan
    return view('kadiv.pesan'); // Disesuaikan ke pesan.blade.php
})->name('kadiv.pesan');

Route::get('/kadiv/detailPesan/{id}', function ($id) {
    return view('kadiv.detailPesan', compact('id'));
})->name('kadiv.detailPesan');



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
    return view('staff.detailProfile');
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



//route hrd 
Route::middleware(['auth', 'role:hrd'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdDashboardController::class, 'index'])->name('hrd.dashboard');

    Route::get('/manajemenDivisi', function () {
        return view('hrd.manajemenDivisi', [
            'divisis' => Divisi::all(),
        ]);
    })->name('hrd.manajemenDivisi');

    Route::get('/pesan', [HrdPesanController::class, 'page'])->name('hrd.pesan');

    Route::get('/detailPesan/{id_pesan}', [HrdPesanController::class, 'detailPage'])->name('hrd.detailPesan');
    Route::post('/detailPesan/{id_pesan}/balas', [HrdPesanController::class, 'balas'])->name('hrd.detailPesan.balas');

    Route::get('/detailDivisi/{id}', [HrdDivisiController::class, 'detailPage'])->name('hrd.detailDivisi');

    Route::get('/detailKaryawan', function () {
        return view('hrd.detailKaryawan');
    })->name('hrd.detailKaryawan');

    Route::get('/daftarKaryawan', [HrdKaryawanController::class, 'karyawanPage'])->name('hrd.daftarKaryawan');
    Route::get('/daftarDivisi', [HrdDivisiController::class, 'listPage'])->name('hrd.daftarDivisi');

    Route::get('/daftarPesan', function () {
        return redirect()->route('hrd.pesan');
    })->name('hrd.daftarPesan');

    Route::get('/divisi-page', function () {
        return view('hrd.divisi', [
            'isCompact' => false,
            'cellPadding' => 'px-4 py-3'
        ]);
    })->name('hrd.divisiPage');

    // 2. Route Action/API Divisi (Dipanggil via Fetch JavaScript)
    Route::get('/divisi', [HrdDivisiController::class, 'index']);
    Route::post('/divisi', [HrdDivisiController::class, 'store']);
    Route::get('/divisi/{id}', [HrdDivisiController::class, 'show']);
    Route::put('/divisi/{id}', [HrdDivisiController::class, 'update']);
    Route::delete('/divisi/{id}', [HrdDivisiController::class, 'destroy']);

    Route::post('/staff', [HrdStaffController::class, 'store']);
    Route::put('/staff/{id}', [HrdStaffController::class, 'update']);
    Route::delete('/staff/{id}', [HrdStaffController::class, 'destroy']);
});

