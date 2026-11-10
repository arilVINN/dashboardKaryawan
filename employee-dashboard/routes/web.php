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
use App\Http\Controllers\Staff\SuratIzinController;
use App\Models\Carousel;
use App\Models\Divisi;


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('gateway.throttle:5,1') // sudah ada, tidak diubah
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Shared public content calendar (all authenticated roles)
Route::middleware(['auth', 'throttle:umum'])->group(function () {
    Route::get('/kalender-konten', function () {
        return view('kalender-konten');
    })->name('kalender-konten');
});

// staff route (session auth + staff role)
Route::middleware(['auth', 'role:staff', 'throttle:umum'])->group(function () {
    Route::get('/', function () {
        $divisionId = auth()->user()?->karyawan?->divisi_id_divisi;
        $carousels = Carousel::query()
            ->with(['user.karyawan', 'divisi'])
            ->where(function ($query) use ($divisionId) {
                $query->whereRaw('LOWER(category) != ?', ['quotes']);

                if ($divisionId) {
                    $query->orWhere(function ($quotes) use ($divisionId) {
                        $quotes->whereRaw('LOWER(category) = ?', ['quotes'])
                            ->where('divisi_id_divisi', $divisionId);
                    });
                }

                $query->orWhere(function ($hrdQuotes) {
                    $hrdQuotes->whereRaw('LOWER(category) = ?', ['quotes'])
                        ->whereHas('user.role', function ($roleQuery) {
                            $roleQuery->whereRaw('LOWER(nama_role) = ?', ['hrd']);
                        });
                });
            })
            ->latest()
            ->get();

        return view('staff.dashboard', compact('carousels'));
    })->name('dashboard');

    Route::get('/tugas', function () {
        return view('staff.tugas');
    })->name('tugas');

    Route::get('/pesan', function () {
        return view('staff.pesan');
    })->name('pesan');

    Route::get('/pesan/detail/{id?}', function ($id = null) {
        $user = auth()->user();
        $token = $user ? $user->createToken('web_token')->plainTextToken : null;
        return view('staff.detailPesan', ['id' => $id, 'token' => $token]);
    })->name('pesan.detail');

    Route::get('/tugas/detail/{id?}', function () {
        return view('staff.detailTugas');
    })->name('tugas.detail');

    Route::get('/project/detail/{id}', function (string $id) {
        return view('staff.detailProject', ['id' => $id]);
    })->name('project.detail');

    Route::get('/profile', function () {
        return view('staff.detailProfile');
    })->name('profile');

    Route::get('/kalender', [\App\Http\Controllers\Staff\KalenderController::class, 'index'])->name('kalender');
    Route::get('/kalender/events', [\App\Http\Controllers\Staff\KalenderController::class, 'events'])->name('kalender.events');
    Route::get('/kalender/data', [\App\Http\Controllers\Staff\KalenderController::class, 'data'])->name('kalender.data');

    // Kalender Note (staff)
    Route::get('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'index'])->name('kalender.notes.index');
    Route::post('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'store'])->name('kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'destroy'])->name('kalender.notes.destroy')->middleware('throttle:submit-form');

    // Pusat Data & Arsip
    Route::get('/pusat-data', function () {
        return view('staff.pusatData');
    })->name('staff.pusatData');
});

Route::prefix('staff')->middleware([EnsureStaffRole::class, 'throttle:umum'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
});


// ================= KADIV (auth + role) ================= //
Route::middleware(['auth', 'role:kadiv', 'throttle:umum'])->prefix('kadiv')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $divisiId = $user?->karyawan?->divisi_id_divisi;

        $carousels = Carousel::query()
            ->with(['user.karyawan', 'divisi'])
            ->where(function ($query) use ($divisiId) {
                if ($divisiId) {
                    $query->where('divisi_id_divisi', $divisiId);
                }

                $query->orWhereHas('user.role', function ($roleQuery) {
                    $roleQuery->whereRaw('LOWER(nama_role) = ?', ['hrd']);
                });
            })
            ->latest()
            ->get();

        return view('kadiv.dashboard', compact('carousels'));
    })->name('kadiv.dashboard');

    // Manajemen Staff
    Route::get('/manajemenStaff', function () {
        return view('kadiv.manajemenStaff');
    })->name('kadiv.manajemenStaff');

    Route::get('/manajemenStaff/{id}', function ($id) {
        return view('kadiv.detailManajemenStaff', ['id' => $id]);
    })->name('kadiv.manajemenStaff.show');

    // Profil
    Route::get('/profile', function () {
        return view('kadiv.profile');
    })->name('kadiv.profile');

    // Tugas
    Route::get('/tugas', function () {
        return view('kadiv.tugas');
    })->name('kadiv.tugas');

    Route::get('/project/{id}', function ($id) {
        return view('kadiv.detailProject', ['id' => $id]);
    })->name('kadiv.project.show');

    Route::get('/detailTugas/{id}', function ($id) {
        return view('kadiv.detailTugas', ['id' => $id]);
    })->name('kadiv.detailTugas.show');

    Route::get('/tugas/{id}', function ($id) {
        return view('kadiv.detailTugas', compact('id'));
    });

    Route::post('/tugas', [KadivController::class, 'store'])
        ->middleware('throttle:submit-form') // + throttle
        ->name('kadiv.tugas.store');

    Route::post('/tugas/{id}/revisi', function (Request $request, $id) {
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
    })->middleware('throttle:submit-form')->name('kadiv.revisiTugas'); // + throttle

    // Pesan
    Route::get('/pesan', function () {
        return view('kadiv.pesan');
    })->name('kadiv.pesan');

    Route::get('/detailPesan/{id}', function ($id) {
        $user = auth()->user();
        $token = $user ? $user->createToken('web_token')->plainTextToken : null;
        return view('kadiv.detailPesan', ['id' => $id, 'token' => $token]);
    })->name('kadiv.detailPesan');

    // Kalender
    Route::get('/kalender', [\App\Http\Controllers\Staff\KalenderController::class, 'indexKadiv'])->name('kadiv.kalender');
    Route::get('/kalender/events', [\App\Http\Controllers\Staff\KalenderController::class, 'eventsKadiv'])->name('kadiv.kalender.events');
    Route::get('/kalender/data', [\App\Http\Controllers\Staff\KalenderController::class, 'dataKadiv'])->name('kadiv.kalender.data');

    // Kalender Note (kadiv)
    Route::get('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'index'])->name('kadiv.kalender.notes.index');
    Route::post('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'store'])->name('kadiv.kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'destroy'])->name('kadiv.kalender.notes.destroy')->middleware('throttle:submit-form');

    // Pusat Data & Arsip
    Route::get('/pusat-data', function () {
        return view('kadiv.pusatData');
    })->name('kadiv.pusatData');

    // Carousel Konten Kegiatan / Informasi
    Route::post('/carousel', [\App\Http\Controllers\CarouselController::class, 'store'])
        ->middleware('throttle:submit-form')
        ->name('carousel.store');
    Route::delete('/carousel/{id}', [\App\Http\Controllers\CarouselController::class, 'destroy'])
        ->middleware('throttle:submit-form')
        ->name('carousel.destroy');
});


// 1. Route untuk proses form login
Route::post('/login-proses', function (Request $request) {
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
})->middleware('throttle:submit-form')->name('login.proses'); // + throttle


// 2. Route untuk menyimpan profil
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
})->middleware('throttle:submit-form')->name('profile.update'); // + throttle


//route hrd
Route::middleware(['auth', 'role:hrd', 'throttle:umum'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdDashboardController::class, 'index'])->name('hrd.dashboard');

    // Kalender
    Route::get('/kalender', [\App\Http\Controllers\Staff\KalenderController::class, 'indexHrd'])->name('hrd.kalender');
    Route::get('/kalender/events', [\App\Http\Controllers\Staff\KalenderController::class, 'eventsHrd'])->name('hrd.kalender.events');

    Route::get('/kalender/data', [\App\Http\Controllers\Staff\KalenderController::class, 'dataHrd'])->name('hrd.kalender.data');

    // Kalender Note (hrd)
    Route::get('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'index'])->name('hrd.kalender.notes.index');
    Route::post('/kalender/notes', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'store'])->name('hrd.kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [\App\Http\Controllers\Staff\CalendarNoteController::class, 'destroy'])->name('hrd.kalender.notes.destroy')->middleware('throttle:submit-form');

    // Pusat Data & Arsip
    Route::get('/pusat-data', function () {
        return view('hrd.pusatData');
    })->name('hrd.pusatData');

    Route::get('/profile', function () {
        return view('hrd.profile');
    })->name('hrd.profile');

    Route::get('/manajemenDivisi', [HrdDivisiController::class, 'manajemenPage'])->name('hrd.manajemenDivisi');

    Route::get('/pesan', [HrdPesanController::class, 'page'])->name('hrd.pesan');

    Route::get('/detailPesan/{id_pesan}', [HrdPesanController::class, 'detailPage'])->name('hrd.detailPesan');
    Route::post('/detailPesan/{id_pesan}/balas', [HrdPesanController::class, 'balas'])
        ->middleware('throttle:submit-form') // + throttle
        ->name('hrd.detailPesan.balas');

    Route::get('/detailDivisi/{id}', [HrdDivisiController::class, 'detailPage'])->name('hrd.detailDivisi');

    Route::get('/detailKaryawan/{id}', [HrdKaryawanController::class, 'detailPage'])->name('hrd.detailKaryawan');
    Route::patch('/detailKaryawan/{id}/keluar', [HrdKaryawanController::class, 'tandaiKeluar'])
        ->middleware('throttle:submit-form')
        ->name('hrd.karyawan.keluar');

    Route::get('/daftarKaryawan', [HrdKaryawanController::class, 'karyawanPage'])
        ->middleware('throttle:pencarian') // + throttle (ada filter: nama, divisi, jabatan, status)
        ->name('hrd.daftarKaryawan');

    Route::get('/daftarDivisi', [HrdDivisiController::class, 'listPage'])
        ->middleware('throttle:pencarian') // + throttle
        ->name('hrd.daftarDivisi');

    Route::get('/daftarPesan', [HrdPesanController::class, 'daftarPage'])
        ->middleware('throttle:pencarian') // + throttle
        ->name('hrd.daftarPesan');

    Route::get('/divisi-page', function () {
        return view('hrd.divisi', [
            'isCompact' => false,
            'cellPadding' => 'px-4 py-3'
        ]);
    })->name('hrd.divisiPage');

    // 2. Route Action/API Divisi (Dipanggil via Fetch JavaScript)
    Route::get('/divisi', [HrdDivisiController::class, 'index']);
    Route::post('/divisi', [HrdDivisiController::class, 'store'])->middleware('throttle:submit-form'); // + throttle
    Route::get('/divisi/{id}', [HrdDivisiController::class, 'show']);
    Route::put('/divisi/{id}', [HrdDivisiController::class, 'update'])->middleware('throttle:submit-form'); // + throttle
    Route::delete('/divisi/{id}', [HrdDivisiController::class, 'destroy'])->middleware('throttle:submit-form'); // + throttle

    Route::post('/staff', [HrdStaffController::class, 'store'])->middleware('throttle:submit-form'); // + throttle
    Route::get('/staff', [HrdStaffController::class, 'index']); // Dipakai JS modal pesan HRD (session auth)
    Route::match(['put', 'post'], '/staff/{id}', [HrdStaffController::class, 'update'])->middleware('throttle:submit-form'); // + throttle
    Route::post('/staff/{id}/foto', [HrdStaffController::class, 'updateFoto'])->middleware('throttle:submit-form');
    Route::delete('/staff/{id}', [HrdStaffController::class, 'destroy'])->middleware('throttle:submit-form'); // + throttle
    Route::delete('/karyawan/{id}', [HrdStaffController::class, 'destroy'])->middleware('throttle:submit-form'); // + throttle

    Route::get('/lampiran/{id}/download', function ($id) {
        $lampiran = \App\Models\LampiranKaryawan::findOrFail($id);
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($lampiran->path_file)) {
            abort(404, 'File lampiran tidak ditemukan di storage.');
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->download($lampiran->path_file, $lampiran->nama_file);
    })->name('hrd.lampiran.download');

    // Pesan via web session (dipakai JS modal pesan HRD)
    Route::post('/pesan', [HrdPesanController::class, 'store'])->middleware('throttle:submit-form'); // + throttle

    //hr analitik karyawan
    Route::get('/analitikKaryawan', [HrdDashboardController::class, 'analitikKaryawan'])
        ->middleware('throttle:pencarian')
        ->name('hrd.analitikKaryawan');
    //turnover karyawan hr
    Route::get('/turnoverKaryawan', [HrdDashboardController::class, 'turnoverKaryawan'])
        ->middleware('throttle:pencarian')
        ->name('hrd.turnoverKaryawan');

    // Carousel Konten Kegiatan / Informasi (HRD)
    Route::post('/carousel', [\App\Http\Controllers\CarouselController::class, 'store'])
        ->middleware('throttle:submit-form')
        ->name('hrd.carousel.store');
    Route::delete('/carousel/{id}', [\App\Http\Controllers\CarouselController::class, 'destroy'])
        ->middleware('throttle:submit-form')
        ->name('hrd.carousel.destroy');
});


// Surat Izin (Staff)
Route::post('/surat-izin', [SuratIzinController::class, 'store'])
    ->middleware(['auth', 'role:staff', 'throttle:submit-form'])
    ->name('staff.suratIzin.store');

// Surat Izin (HRD)
Route::get('/suratIzin', [HrdDashboardController::class, 'suratIzinPage'])
    ->middleware(['auth', 'role:hrd', 'throttle:pencarian'])
    ->name('hrd.suratIzin');
Route::post('/suratIzin/{suratIzin}/acc', [HrdDashboardController::class, 'approveSuratIzin'])
    ->middleware(['auth', 'role:hrd', 'throttle:submit-form'])
    ->name('hrd.suratIzin.acc');
Route::post('/suratIzin/{suratIzin}/tolak', [HrdDashboardController::class, 'rejectSuratIzin'])
    ->middleware(['auth', 'role:hrd', 'throttle:submit-form'])
    ->name('hrd.suratIzin.tolak');

// Fallback Route (404 Not Found) -> Otomatis diarahkan ke dashboard masing-masing role dengan toast
Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->isXmlHttpRequest() || $request->is('api/*')) {
        return response()->json(['message' => 'Halaman atau endpoint tidak ditemukan.'], 404);
    }

    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login')->with('error', 'Halaman tidak ditemukan. Silakan login terlebih dahulu.');
    }

    $role = strtolower($user->role?->nama_role ?? '');
    $dashboardRoute = match ($role) {
        'hrd'   => 'hrd.dashboard',
        'kadiv' => 'kadiv.dashboard',
        'staff' => 'dashboard',
        default => 'login',
    };

    return redirect()->route($dashboardRoute)
        ->with('error', 'Halaman tidak ditemukan. Anda telah diarahkan ke dashboard Anda.');
});
