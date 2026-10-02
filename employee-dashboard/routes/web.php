<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Middleware\EnsureStaffRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// =========================
// STAFF
// =========================

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


Route::prefix('staff')->middleware(EnsureStaffRole::class)->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);

});


// =========================
// HRD
// =========================

Route::get('/hrd/dashboard', function () {
    return view('hrd.dashboard');
})->name('hrd.dashboard');

Route::get('/hrd/manajemenDivisi', function () {
    return view('hrd.manajemenDivisi');
})->name('manajemen');

Route::get('/hrd/pesan', function () {
    return view('hrd.pesan');
})->name('hrd.pesan');

Route::get('/hrd/detailPesan/{id}', function () {
    return view('hrd.detailPesan');
})->name('detailPesan');

Route::get('/hrd/detailDivisi/{id}', function () {
    return view('hrd.detailDivisi');
})->name('detailDivisi');


// =========================
// KADIV
// =========================

Route::get('/kadiv/dashboard', function () {
    return view('kadiv.dashboard');
})->name('kadiv.dashboard');

Route::get('/kadiv/manajemenStaff', function () {
    return view('kadiv.manajemenStaff');
})->name('manajemenStaff');

Route::get('/kadiv/detailTugas', function () {
    return view('kadiv.detailTugas');
})->name('kadiv.detailTugas');

Route::get('/kadiv/detailPesan', function () {
    return view('kadiv.detailPesan');
})->name('kadiv.detailPesan');

// LOGIN

Route::get('/login', function () {
    return view('login');
})->name('login');


Route::post('/login-proses', function (Request $request) {

    $username = $request->input('username');
    $password = $request->input('password');

    // LOGIN KADIV

    if ($username === 'kadiv' && $password === '123456') {

        $userDummy = [
            'nama' => 'Nama Kadiv',
            'inisial' => 'K',
            'email' => 'kadiv@silindo.co.id',
            'telepon' => '081234567890',
            'tingkatan' => 'Kadiv',
            'divisi' => 'Content Writer',
            'tanggal_masuk' => '12 Januari 2025',
            'alamat' => 'Salatiga, Jawa Tengah',
            'tanggal_dibuat' => '10 Januari 2025',
        ];

        session(['user_session' => $userDummy]);

        return redirect('/kadiv/dashboard');
    }

    // LOGIN STAFF
    if ($username === 'staff' && $password === '123456') {

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
    }


    // LOGIN GAGAL

    return back()->with('error', 'Username atau password salah.');

})->name('login.proses');



// PROFILE
Route::get('/profile', function () {

    if (!session()->has('user_session')) {
        return redirect('/login');
    }

    $pegawai = session('user_session');


    // Kadiv → profile Kadiv
    if (($pegawai['tingkatan'] ?? '') === 'Kadiv') {
        return view('kadiv.detailProfile', [
            'pegawai' => $pegawai
        ]);
    }
    // Staff → profile Staff
    return view('staff.detailProfile', [
        'pegawai' => $pegawai
    ]);

})->name('profile');

Route::get('/management-staf1', function () {
    return view('management-staf1');
});

Route::get('/manajemen-add-pesan', function () {
    return view('manajemen-add-pesan');
});

Route::get('/manajemen-kadiv-pesan', function () {
    return view('manajemen-kadiv-pesan');
});

Route::get('/manajemen-lihat-pesan', function () {
    return view('manajemen-lihat-pesan');
});

Route::get('/manajemen-pesan', function () {
    return view('manajemen-pesan');
});

Route::get('/manajemen-staff-tugas', function () {
    return view('manajemen-staff-tugas');
});

Route::get('/manajemen-staff', function () {
    return view('manajemen-staff');
});

Route::get('/manajemen-tugas1', function () {
    return view('manajemen-tugas1');
});

Route::get('/tambah-pesan', function () {
    return view('tambah-pesan');
});

Route::get('/tugas-revisi', function () {
    return view('tugas-revisi');
});