<?php

use App\Actions\MarkConversationAsRead;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\Direktur\DirekturDashboardController;
use App\Http\Controllers\Finance\AlokasiController;
use App\Http\Controllers\Finance\JurnalUmumController;
use App\Http\Controllers\Finance\KeuanganController;
use App\Http\Controllers\Finance\LabaRugiController;
use App\Http\Controllers\Finance\PemasukanController;
use App\Http\Controllers\Finance\PengeluaranController;
use App\Http\Controllers\Finance\PermintaanDanaController;
use App\Http\Controllers\Hrd\HrdDashboardController;
use App\Http\Controllers\Hrd\HrdDivisiController;
use App\Http\Controllers\Hrd\HrdFileController;
use App\Http\Controllers\Hrd\HrdJabatanController;
use App\Http\Controllers\Hrd\HrdKaryawanController;
use App\Http\Controllers\Hrd\HrdPeranController;
use App\Http\Controllers\Hrd\HrdPesanController;
use App\Http\Controllers\Hrd\HrdStaffController;
use App\Http\Controllers\Kadiv\KadivMessageController;
use App\Http\Controllers\KadivController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\SecureFileController;
// ─── Finance Module Controllers ───────────────────────────────────────────────
use App\Http\Controllers\Staff\CalendarNoteController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\KalenderController;
use App\Http\Controllers\Staff\NotifikasiController;
use App\Http\Controllers\Staff\PesanController;
use App\Http\Controllers\Staff\SuratIzinController;
use App\Http\Controllers\StrukturOrganisasiController;
use App\Http\Controllers\SuratIzinPdfController;
use App\Http\Middleware\EnsureContentCalendarAccess;
use App\Http\Middleware\EnsureStaffRole;
use App\Models\BookingRequest;
use App\Models\Carousel;
use App\Models\Divisi;
use App\Models\SuratIzin;
use App\Services\BirthdayNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Fallback for legacy profile photos stored on the private disk. The public
// disk symlink serves these URLs first; only missing files reach this route.
Route::get('/storage/foto-profil/{filename}', [ProfilePhotoController::class, 'show'])
    ->middleware('auth')
    ->where('filename', '[A-Za-z0-9_.-]+');

// Sensitive uploads (finance proofs, messages, tasks, leave letters, journals,
// tower documents) live on the private disk. /files/<path> re-checks access to
// the owning record before streaming, so files are never publicly reachable.
Route::get('/files/{path}', [SecureFileController::class, 'show'])
    ->middleware(['auth', 'throttle:umum'])
    ->where('path', '(?:keuangan|pesan-lampiran|lampiran_pesan|tugas_pendukung|submissions|revisi|surat_izin|daily-journals|tower_files)/.+')
    ->name('files.secure');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Wajib ganti password saat login pertama / setelah atur ulang password.
Route::middleware('auth')->group(function () {
    Route::get('/ganti-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ganti-password', [PasswordChangeController::class, 'store'])
        ->middleware('throttle:submit-form')
        ->name('password.change.store');
});

// Detail dan Like Konten Carousel (Dapat diakses seluruh role pengguna yang login)
Route::middleware(['auth', 'throttle:umum'])->group(function () {
    Route::get('/carousel/{id}', [CarouselController::class, 'show'])->name('carousel.show');
    Route::get('/detailKonten/{id}', [CarouselController::class, 'show'])->name('carousel.detailKonten');
    Route::post('/carousel/{id}/like', [CarouselController::class, 'toggleLike'])->name('carousel.like');

    // Struktur Organisasi CRUD & Tree API
    Route::get('/struktur-organisasi/data', [StrukturOrganisasiController::class, 'getTreeData'])->name('strukturOrganisasi.tree');
    Route::post('/struktur-organisasi/divisi-layout', [StrukturOrganisasiController::class, 'updateDivisiLayout'])->name('strukturOrganisasi.divisiLayout');
    Route::post('/struktur-organisasi/karyawan/{id}', [StrukturOrganisasiController::class, 'updateKaryawan'])->name('strukturOrganisasi.updateKaryawan');
    Route::put('/struktur-organisasi/karyawan/{id}', [StrukturOrganisasiController::class, 'updateKaryawan'])->name('strukturOrganisasi.updateKaryawan.put');
    Route::get('/struktur-organisasi/{id}', [StrukturOrganisasiController::class, 'show'])->name('strukturOrganisasi.show');
    Route::post('/struktur-organisasi', [StrukturOrganisasiController::class, 'store'])->name('strukturOrganisasi.store');
    Route::post('/struktur-organisasi/{id}', [StrukturOrganisasiController::class, 'update'])->name('strukturOrganisasi.update');
    Route::put('/struktur-organisasi/{id}', [StrukturOrganisasiController::class, 'update'])->name('strukturOrganisasi.update.put');
    Route::delete('/struktur-organisasi/{id}', [StrukturOrganisasiController::class, 'destroy'])->name('strukturOrganisasi.destroy');
    Route::post('/struktur-organisasi/{id}/reorder', [StrukturOrganisasiController::class, 'reorder'])->name('strukturOrganisasi.reorder');
});

// ─── Direktur / Direksi (executive monitoring) ───────────────────────────────
Route::middleware(['auth', 'role:direktur,direksi', 'throttle:umum'])->prefix('direktur')->group(function () {
    Route::get('/dashboard', [DirekturDashboardController::class, 'index'])->name('direktur.dashboard');
    Route::get('/notifikasi', [DirekturDashboardController::class, 'notifikasi'])->name('direktur.notifikasi');
});

// Shared content calendar (HR and Marketing Communication only)
Route::middleware(['auth', 'throttle:umum', EnsureContentCalendarAccess::class])->group(function () {
    Route::get('/kalender-konten', function () {
        return view('kalender-konten');
    })->name('kalender-konten');
});

// staff route (session auth + staff role)
Route::middleware(['auth', 'role:staff', 'throttle:umum'])->group(function () {
    Route::get('/', function (BirthdayNotificationService $birthdayNotifications) {
        $user = auth()->user();

        // Superadmin stays on the executive dashboard instead of the staff home.
        if ($user?->isSuperAdmin()) {
            return redirect()->route('direktur.dashboard');
        }

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
        $birthdayEmployees = $birthdayNotifications->forUser($user);

        return view('staff.dashboard', compact('carousels', 'birthdayEmployees'));
    })->name('dashboard');

    Route::get('/tugas', function () {
        return view('staff.tugas');
    })->name('tugas');

    Route::get('/pesan', function () {
        $user = auth()->user();
        $suratIzinStatus = SuratIzin::query()
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
            ->latest()
            ->get();
        $bookingStatus = BookingRequest::query()
            ->where('pemohon_id_user', $user->id_user)
            ->latest()
            ->get();

        return view('staff.pesan', compact('suratIzinStatus', 'bookingStatus'));
    })->name('pesan');

    Route::get('/pesan/detail/{id?}', function ($id = null) {
        if (! $id) {
            abort(404);
        }

        $user = auth()->user();
        $response = app(PesanController::class)->show(request(), $id);

        if ($response->getStatusCode() !== 200) {
            abort(404);
        }

        $data = json_decode($response->getContent(), true)['data'] ?? null;
        MarkConversationAsRead::handle($user, $id);
        $token = $user->createToken('web_token')->plainTextToken;

        return view('staff.detailPesan', ['id' => $id, 'token' => $token, 'data' => $data]);
    })->name('pesan.detail');

    Route::get('/tugas/detail/{id?}', function () {
        return view('staff.detailTugas');
    })->name('tugas.detail');

    Route::get('/profile', function () {
        return view('staff.detailProfile');
    })->name('profile');

    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender');
    Route::get('/kalender/events', [KalenderController::class, 'events'])->name('kalender.events');
    Route::get('/kalender/data', [KalenderController::class, 'data'])->name('kalender.data');

    // Kalender Note (staff)
    Route::get('/kalender/notes', [CalendarNoteController::class, 'index'])->name('kalender.notes.index');
    Route::post('/kalender/notes', [CalendarNoteController::class, 'store'])->name('kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [CalendarNoteController::class, 'destroy'])->name('kalender.notes.destroy')->middleware('throttle:submit-form');

    // Jurnal Harian (Staff)
    Route::get('/jurnal', function () {
        return view('staff.jurnal');
    })->name('jurnal');

    // Pusat Data & Arsip
    Route::get('/pusat-data', function () {
        return view('staff.pusatData');
    })->name('staff.pusatData');

    // Struktur Organisasi (staff)
    Route::get('/strukturOrganisasi', [StrukturOrganisasiController::class, 'index'])->name('staff.strukturOrganisasi');
});

Route::prefix('staff')->middleware([EnsureStaffRole::class, 'throttle:umum'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
});

// ================= KADIV (auth + role) ================= //
Route::middleware(['auth', 'role:kadiv', 'throttle:umum'])->prefix('kadiv')->group(function () {
    // Dashboard
    Route::get('/dashboard', function (BirthdayNotificationService $birthdayNotifications) {
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
        $birthdayEmployees = $birthdayNotifications->forUser($user);

        return view('kadiv.dashboard', compact('carousels', 'birthdayEmployees'));
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

    Route::get('/detailTugas/{id}', function ($id) {
        return view('kadiv.detailTugas', ['id' => $id]);
    })->name('kadiv.detailTugas.show');

    Route::get('/tugas/{id}', function ($id) {
        return view('kadiv.detailTugas', compact('id'));
    });

    Route::post('/tugas', [KadivController::class, 'store'])
        ->middleware(['throttle:submit-form', 'deny.superadmin']) // + throttle
        ->name('kadiv.tugas.store');

    Route::post('/tugas/{id}/revisi', function (Request $request, $id) {
        $request->validate([
            'isi_revisi' => ['required', 'string', 'max:2000'],
            'tenggat' => ['nullable', 'string'],
        ]);

        $semuaRevisi = session('revisi_tugas', []);
        $semuaRevisi[] = [
            'id_tugas' => $id,
            'isi' => $request->input('isi_revisi'),
            'tenggat' => $request->input('tenggat', ''),
            'waktu' => now()->format('d M Y, H.i'),
        ];
        session(['revisi_tugas' => $semuaRevisi]);

        return redirect()
            ->route('kadiv.detailTugas.show', $id)
            ->with('success', 'Revisi berhasil dikirim.');
    })->middleware(['throttle:submit-form', 'deny.superadmin'])->name('kadiv.revisiTugas'); // + throttle

    // Pesan
    Route::get('/pesan', function () {
        $user = auth()->user();
        $suratIzinStatus = SuratIzin::query()
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
            ->latest()
            ->get();
        $bookingStatus = BookingRequest::query()
            ->where('pemohon_id_user', $user->id_user)
            ->latest()
            ->get();

        return view('kadiv.pesan', compact('suratIzinStatus', 'bookingStatus'));
    })->name('kadiv.pesan');

    Route::get('/detailPesan/{id}', function ($id) {
        $user = auth()->user();
        $token = $user ? $user->createToken('web_token')->plainTextToken : null;
        $response = app(KadivMessageController::class)->show(request(), $id);

        if ($response->getStatusCode() !== 200) {
            abort(404);
        }

        $data = json_decode($response->getContent(), true)['data'] ?? null;
        MarkConversationAsRead::handle($user, $id);

        return view('kadiv.detailPesan', ['id' => $id, 'token' => $token, 'data' => $data]);
    })->name('kadiv.detailPesan');

    // Kalender
    Route::get('/kalender', [KalenderController::class, 'indexKadiv'])->name('kadiv.kalender');
    Route::get('/kalender/events', [KalenderController::class, 'eventsKadiv'])->name('kadiv.kalender.events');
    Route::get('/kalender/data', [KalenderController::class, 'dataKadiv'])->name('kadiv.kalender.data');

    // Kalender Note (kadiv)
    Route::get('/kalender/notes', [CalendarNoteController::class, 'index'])->name('kadiv.kalender.notes.index');
    Route::post('/kalender/notes', [CalendarNoteController::class, 'store'])->name('kadiv.kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [CalendarNoteController::class, 'destroy'])->name('kadiv.kalender.notes.destroy')->middleware('throttle:submit-form');

    // Jurnal Harian (Kadiv)
    Route::get('/jurnal', function () {
        return view('kadiv.jurnal');
    })->name('kadiv.jurnal');

    // Pusat Data & Arsip
    Route::get('/pusat-data', function () {
        return view('kadiv.pusatData');
    })->name('kadiv.pusatData');

    // Struktur Organisasi (kadiv)
    Route::get('/strukturOrganisasi', [StrukturOrganisasiController::class, 'index'])->name('kadiv.strukturOrganisasi');

    // Carousel Konten Kegiatan / Informasi
    Route::post('/carousel', [CarouselController::class, 'store'])
        ->middleware(['throttle:submit-form', 'deny.superadmin'])
        ->name('carousel.store');
    Route::delete('/carousel/{id}', [CarouselController::class, 'destroy'])
        ->middleware(['throttle:submit-form', 'deny.superadmin'])
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

// route hrd
Route::middleware(['auth', 'role:hrd', 'throttle:umum'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [HrdDashboardController::class, 'index'])->name('hrd.dashboard');
    Route::get('/strukturOrganisasi', [StrukturOrganisasiController::class, 'index'])->name('hrd.strukturOrganisasi');

    // Kalender
    Route::get('/kalender', [KalenderController::class, 'indexHrd'])->name('hrd.kalender');
    Route::get('/kalender/events', [KalenderController::class, 'eventsHrd'])->name('hrd.kalender.events');

    Route::get('/kalender/data', [KalenderController::class, 'dataHrd'])->name('hrd.kalender.data');

    // Kalender Note (hrd)
    Route::get('/kalender/notes', [CalendarNoteController::class, 'index'])->name('hrd.kalender.notes.index');
    Route::post('/kalender/notes', [CalendarNoteController::class, 'store'])->name('hrd.kalender.notes.store')->middleware('throttle:submit-form');
    Route::delete('/kalender/notes/{id}', [CalendarNoteController::class, 'destroy'])->name('hrd.kalender.notes.destroy')->middleware('throttle:submit-form');

    // Jurnal Harian (HRD)
    Route::get('/jurnal', function () {
        return view('hrd.jurnal');
    })->name('hrd.jurnal');

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
    Route::put('/detailKaryawan/{id}/peran', [HrdPeranController::class, 'update'])
        ->middleware('throttle:submit-form')
        ->name('hrd.karyawan.peran');
    Route::patch('/detailKaryawan/{id}/keluar', [HrdKaryawanController::class, 'tandaiKeluar'])
        ->middleware(['throttle:submit-form', 'deny.superadmin'])
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
            'cellPadding' => 'px-4 py-3',
        ]);
    })->name('hrd.divisiPage');

    // 2. Route Action/API Divisi (Dipanggil via Fetch JavaScript)
    Route::get('/divisi', [HrdDivisiController::class, 'index']);
    Route::post('/divisi', [HrdDivisiController::class, 'store'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle
    Route::get('/divisi/{id}', [HrdDivisiController::class, 'show']);
    Route::put('/divisi/{id}', [HrdDivisiController::class, 'update'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle
    Route::delete('/divisi/{id}', [HrdDivisiController::class, 'destroy'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle

    Route::post('/staff', [HrdStaffController::class, 'store'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle
    Route::get('/staff', [HrdStaffController::class, 'index']); // Dipakai JS modal pesan HRD (session auth)
    Route::match(['put', 'post'], '/staff/{id}', [HrdStaffController::class, 'update'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle
    Route::post('/staff/{id}/foto', [HrdStaffController::class, 'updateFoto'])->middleware(['throttle:submit-form', 'deny.superadmin']);
    Route::delete('/staff/{id}', [HrdStaffController::class, 'destroy'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle
    Route::delete('/karyawan/{id}', [HrdStaffController::class, 'destroy'])->middleware(['throttle:submit-form', 'deny.superadmin']); // + throttle

    // Private storage: every file goes through an authorization check.
    Route::get('/lampiran/{lampiran}/download', [HrdFileController::class, 'lampiran'])
        ->name('hrd.lampiran.download');
    Route::get('/karyawan/{karyawan}/foto', [HrdFileController::class, 'foto'])
        ->name('hrd.karyawan.foto');

    // Pesan via web session (dipakai JS modal pesan HRD)
    Route::post('/pesan', [HrdPesanController::class, 'store'])->middleware('throttle:submit-form'); // + throttle

    // hr analitik karyawan
    Route::get('/analitikKaryawan', [HrdDashboardController::class, 'analitikKaryawan'])
        ->middleware('throttle:pencarian')
        ->name('hrd.analitikKaryawan');
    // turnover karyawan hr
    Route::get('/turnoverKaryawan', [HrdDashboardController::class, 'turnoverKaryawan'])
        ->middleware('throttle:pencarian')
        ->name('hrd.turnoverKaryawan');

    // Carousel Konten Kegiatan / Informasi (HRD)
    Route::post('/carousel', [CarouselController::class, 'store'])
        ->middleware(['throttle:submit-form', 'deny.superadmin'])
        ->name('hrd.carousel.store');
    Route::delete('/carousel/{id}', [CarouselController::class, 'destroy'])
        ->middleware(['throttle:submit-form', 'deny.superadmin'])
        ->name('hrd.carousel.destroy');
});

Route::post('/booking', [BookingController::class, 'store'])
    ->middleware(['auth', 'role:staff,kadiv', 'throttle:submit-form'])
    ->name('booking.store');

Route::middleware(['auth', 'role:hrd,direksi', 'throttle:umum'])->prefix('hrd')->group(function () {
    Route::get('/suratBooking', [BookingController::class, 'index'])->name('hrd.suratBooking');
    Route::post('/suratBooking/{booking}/acc', [BookingController::class, 'approve'])
        ->middleware('throttle:submit-form')
        ->name('hrd.suratBooking.acc');
    Route::post('/suratBooking/{booking}/tolak', [BookingController::class, 'reject'])
        ->middleware('throttle:submit-form')
        ->name('hrd.suratBooking.tolak');

    Route::get('/suratIzin', [HrdDashboardController::class, 'suratIzinPage']);
    Route::post('/suratIzin/{suratIzin}/acc', [HrdDashboardController::class, 'approveSuratIzin'])
        ->middleware('throttle:submit-form');
    Route::post('/suratIzin/{suratIzin}/tolak', [HrdDashboardController::class, 'rejectSuratIzin'])
        ->middleware('throttle:submit-form');
});

// Surat Izin (Staff & Kadiv)
Route::post('/surat-izin', [SuratIzinController::class, 'store'])
    ->middleware(['auth', 'role:staff,kadiv', 'throttle:submit-form'])
    ->name('staff.suratIzin.store');

// Cetak / unduh formulir perizinan sebagai PDF
Route::get('/surat-izin/{suratIzin}/pdf', [SuratIzinPdfController::class, 'show'])
    ->middleware(['auth', 'throttle:umum'])
    ->name('suratIzin.pdf');

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
        'direktur', 'direksi', 'superadmin' => 'direktur.dashboard',
        'hrd' => 'hrd.dashboard',
        'kadiv' => 'kadiv.dashboard',
        'staff' => 'dashboard',
        default => 'login',
    };

    return redirect()->route($dashboardRoute)
        ->with('error', 'Halaman tidak ditemukan. Anda telah diarahkan ke dashboard Anda.');
});

// ═════════════════════════════════════════════════════════════════════════════
// MODUL KEUANGAN (FINANCE)
// ═════════════════════════════════════════════════════════════════════════════

// ── Khusus Kadiv Finance (harus kode_divisi = 'FIN') ─────────────────────────
Route::middleware(['auth', 'role:kadiv', 'kadiv.finance', 'throttle:umum'])
    ->prefix('kadiv/keuangan')
    ->group(function () {

        // Dashboard Keuangan
        Route::get('/', [KeuanganController::class, 'index'])->name('kadiv.keuangan');
        Route::get('/rekap-divisi', [KeuanganController::class, 'rekapDivisi'])->name('kadiv.keuangan.rekapDivisi');
        Route::get('/rekap-divisi/export', [KeuanganController::class, 'export'])->name('kadiv.keuangan.exportRekapDivisi');

        // Arus Kas (Gabungan Kas Masuk + Kas Keluar)
        Route::get('/arus-kas', [KeuanganController::class, 'arusKas'])->name('kadiv.keuangan.arusKas');

        // Manajemen Dana (Gabungan Permintaan Dana + Verifikasi Laporan L/R)
        Route::get('/manajemen-dana', [KeuanganController::class, 'manajemenDana'])->name('kadiv.keuangan.manajemenDana');

        // Pemasukan Kas
        Route::get('/pemasukan', [PemasukanController::class, 'index'])->name('kadiv.keuangan.pemasukan');
        Route::post('/pemasukan', [PemasukanController::class, 'store'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.pemasukan.store');

        // Alokasi Pagu Anggaran
        Route::get('/alokasi', [AlokasiController::class, 'index'])->name('kadiv.keuangan.alokasi');
        Route::post('/alokasi', [AlokasiController::class, 'store'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.alokasi.store');
        Route::put('/alokasi/{id}', [AlokasiController::class, 'update'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.alokasi.update');

        // Permintaan Dana — Lihat & Approve/Reject (Finance)
        Route::get('/permintaan', [PermintaanDanaController::class, 'index'])->name('kadiv.keuangan.permintaan');
        Route::get('/permintaan/{id}', [PermintaanDanaController::class, 'show'])->name('kadiv.keuangan.permintaan.show');
        Route::post('/permintaan/{id}/approve', [PermintaanDanaController::class, 'approve'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.permintaan.approve');
        Route::post('/permintaan/{id}/reject', [PermintaanDanaController::class, 'reject'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.permintaan.reject');

        // Pengeluaran / Pencairan
        Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('kadiv.keuangan.pengeluaran');
        Route::post('/pengeluaran', [PengeluaranController::class, 'store'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.pengeluaran.store');
        Route::post('/pengeluaran/cairkan/{idKpd}', [PengeluaranController::class, 'cairkan'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.cairkan');

        // Verifikasi Laporan Laba/Rugi (Finance)
        Route::get('/laporan', [LabaRugiController::class, 'daftarLaporan'])->name('kadiv.keuangan.laporan');
        Route::post('/laporan/{id}/verifikasi', [LabaRugiController::class, 'verifikasi'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.keuangan.laporan.verifikasi');

        // Jurnal Umum — entri dibuat otomatis dari transaksi keuangan
        Route::get('/jurnal-umum', [JurnalUmumController::class, 'index'])
            ->name('kadiv.keuangan.jurnalUmum');
        Route::get('/jurnal-umum/export', [JurnalUmumController::class, 'export'])
            ->name('kadiv.keuangan.exportJurnalUmum');
    });

// ── Semua Kadiv: Pengajuan Dana & Laporan Laba/Rugi ──────────────────────────
// Jabatan management (HRD / superadmin only). Director-level roles are
// read-only monitors, so `deny.superadmin` blocks direktur/direksi writes.
Route::middleware(['auth', 'role:hrd', 'throttle:submit-form', 'deny.superadmin'])
    ->prefix('hrd')
    ->group(function () {
        Route::get('/jabatan', [HrdJabatanController::class, 'index'])
            ->middleware('throttle:pencarian')
            ->name('hrd.jabatan.index');
        Route::post('/jabatan', [HrdJabatanController::class, 'store'])->name('hrd.jabatan.store');
        Route::put('/jabatan/{jabatan}', [HrdJabatanController::class, 'update'])->name('hrd.jabatan.update');
        Route::patch('/jabatan/{jabatan}/nonaktifkan', [HrdJabatanController::class, 'nonaktifkan'])
            ->name('hrd.jabatan.nonaktifkan');
    });

Route::middleware(['auth', 'role:kadiv', 'throttle:umum'])
    ->prefix('kadiv')
    ->group(function () {
        // Pengajuan Dana
        Route::get('/pengajuan-dana', [PermintaanDanaController::class, 'create'])->name('kadiv.pengajuanDana');
        Route::post('/pengajuan-dana', [PermintaanDanaController::class, 'store'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.pengajuanDana.store');
        Route::get('/pengajuan-dana/riwayat', [PermintaanDanaController::class, 'riwayat'])->name('kadiv.pengajuanDana.riwayat');

        // Laporan Laba/Rugi per Kadiv
        Route::get('/laporan-laba-rugi', [LabaRugiController::class, 'index'])->name('kadiv.laporanLabaRugi');
        Route::post('/laporan-laba-rugi', [LabaRugiController::class, 'store'])
            ->middleware(['throttle:submit-form', 'deny.superadmin'])
            ->name('kadiv.laporanLabaRugi.store');
    });
