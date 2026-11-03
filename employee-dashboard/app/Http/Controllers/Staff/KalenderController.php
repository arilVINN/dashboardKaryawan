<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\SuratIzin;
use App\Models\Tugas;
use App\Queries\KadivTugasQuery;
use App\Queries\StaffTugasQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KalenderController extends Controller
{
    /**
     * Map status tugas ke warna dan badge class yang konsisten dengan aplikasi.
     */
    public static function getStatusStyle(string $status): array
    {
        $normalized = strtolower(trim($status));
        if ($normalized === 'telat') {
            $normalized = 'terlambat';
        } elseif (in_array($normalized, ['menunggu acc', 'menunggu_acc', 'menunggu di acc'], true)) {
            $normalized = 'menunggu di-acc';
        } elseif (in_array($normalized, ['sudah acc', 'sudah_acc', 'sudah di acc'], true)) {
            $normalized = 'sudah di-acc';
        }

        return match ($normalized) {
            'baru' => [
                'backgroundColor' => '#C0E7FF',
                'borderColor'     => '#3B82F6',
                'textColor'       => '#1D4ED8',
                'badgeClass'      => 'bg-[#C0E7FF] text-blue-700 border border-blue-200',
                'label'           => 'Baru',
                'dotColor'        => '#3B82F6',
            ],
            'berjalan' => [
                'backgroundColor' => '#D1FAE5',
                'borderColor'     => '#10B981',
                'textColor'       => '#047857',
                'badgeClass'      => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                'label'           => 'Berjalan',
                'dotColor'        => '#10B981',
            ],
            'menunggu di-acc' => [
                'backgroundColor' => '#FEF3C7',
                'borderColor'     => '#F59E0B',
                'textColor'       => '#B45309',
                'badgeClass'      => 'bg-amber-100 text-amber-700 border border-amber-200',
                'label'           => 'Menunggu di-acc',
                'dotColor'        => '#F59E0B',
            ],
            'sudah di-acc' => [
                'backgroundColor' => '#00ae7d',
                'borderColor'     => '#065F46',
                'textColor'       => '#FFFFFF',
                'badgeClass'      => 'bg-emerald-700 text-white border border-emerald-800',
                'label'           => 'Sudah di-acc',
                'dotColor'        => '#065F46',
            ],
            'terlambat' => [
                'backgroundColor' => '#FEE2E2',
                'borderColor'     => '#EF4444',
                'textColor'       => '#B91C1C',
                'badgeClass'      => 'bg-red-100 text-red-700 border border-red-200',
                'label'           => 'Terlambat',
                'dotColor'        => '#EF4444',
            ],
            default => [
                'backgroundColor' => '#F1F5F9',
                'borderColor'     => '#94A3B8',
                'textColor'       => '#475569',
                'badgeClass'      => 'bg-slate-100 text-slate-700 border border-slate-200',
                'label'           => ucfirst($status),
                'dotColor'        => '#94A3B8',
            ],
        };
    }

    /**
     * Tampilkan halaman kalender untuk staff.
     */
    public function index(Request $request)
    {
        return view('staff.kalender');
    }

    /**
     * Tampilkan halaman kalender untuk kadiv.
     */
    public function indexKadiv(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        $divisiId = $user?->karyawan?->divisi_id_divisi;

        $staffList = $divisiId
            ? \App\Models\Karyawan::where('divisi_id_divisi', $divisiId)
                ->orderBy('nama', 'asc')
                ->get()
            : collect();

        return view('kadiv.kalender', compact('staffList'));
    }

    /**
     * Endpoint API untuk data event kalender staff.
     */
    public function events(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $filters = $request->only(['status', 'q']);
        $query = StaffTugasQuery::forStaff($user)->apply($filters);

        $start = $request->input('start');
        $end = $request->input('end');

        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->get();

        $events = [];

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);

            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;
            if (!$startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (!$isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'           => 'tugas',
                    'id_tugas'       => $tugas->id_tugas,
                    'status'         => $effectiveStatus,
                    'status_label'   => $style['label'],
                    'badge_class'    => $style['badgeClass'],
                    'deskripsi'      => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label' => $tugas->deadline
                        ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm')
                        : 'Tidak ditentukan',
                    'tanggal_dibuat' => $tugas->tanggal_dibuat
                        ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y')
                        : '-',
                    'detail_url'     => url('/tugas/detail/' . $tugas->id_tugas),
                ],
            ];
        }

        // Tambahkan event surat izin (jika ada)
        if ($user->karyawan_id_karyawan) {
            $suratList = SuratIzin::where('karyawan_id_karyawan', $user->karyawan_id_karyawan)->get();
            foreach ($suratList as $izin) {
                $events[] = [
                    'id'              => 'IZIN-' . $izin->id_surat,
                    'title'           => 'Izin: ' . $izin->judul_surat,
                    'start'           => Carbon::parse($izin->tanggal_izin)->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => '#EDE9FE',
                    'borderColor'     => '#8B5CF6',
                    'textColor'       => '#6D28D9',
                    'extendedProps'   => [
                        'type'           => 'izin',
                        'id_surat'       => $izin->id_surat,
                        'status'         => $izin->status,
                        'status_label'   => 'Izin (' . $izin->status . ')',
                        'badge_class'    => 'bg-purple-100 text-purple-700 border border-purple-200',
                        'deskripsi'      => $izin->keterangan ?? '-',
                        'deadline_label' => Carbon::parse($izin->tanggal_izin)->locale('id')->isoFormat('dddd, D MMMM Y'),
                        'detail_url'     => null,
                    ],
                ];
            }
        }

        return response()->json([
            'message' => 'Data event kalender staff',
            'data'    => $events,
        ]);
    }

    public function data(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $filters = $request->only(['status', 'q']);
        $query = StaffTugasQuery::forStaff($user)->apply($filters);
        $start = $request->input('start');
        $end = $request->input('end');

        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->get();

        $kadivList = \App\Models\Karyawan::whereHas('user.role', fn ($q) => $q->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala divisi%'])
            ->get()
            ->keyBy('divisi_id_divisi');

        $events = [];

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);
            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;

            if (! $startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (! $isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }

            $karyawan = $tugas->karyawan;
            $namaStaff = $karyawan?->nama ?? 'Staff';
            $divisiNama = $karyawan?->divisi?->nama_divisi ?? 'Tanpa Divisi';
            $kadiv = $karyawan?->divisi_id_divisi ? ($kadivList[$karyawan->divisi_id_divisi] ?? null) : null;
            $pengirimNama = $kadiv ? $kadiv->nama : ('Kadiv ' . $divisiNama);
            $pengirimJabatan = $kadiv ? ($kadiv->jabatan ?: 'Kepala Divisi') : 'Kepala Divisi';

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'             => 'tugas',
                    'id_tugas'         => $tugas->id_tugas,
                    'judul_murni'      => $tugas->judul_tugas,
                    'divisi_nama'      => $divisiNama,
                    'pengirim_nama'    => $pengirimNama,
                    'pengirim_jabatan' => $pengirimJabatan,
                    'staff_nama'       => $namaStaff,
                    'staff_jabatan'    => $karyawan?->jabatan ?? 'Staff',
                    'staff_initial'    => strtoupper(substr($namaStaff, 0, 1)),
                    'status'           => $effectiveStatus,
                    'status_label'     => $style['label'],
                    'badge_class'      => $style['badgeClass'],
                    'dot_color'        => $style['dotColor'],
                    'deskripsi'        => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label'   => $tugas->deadline
                        ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm')
                        : 'Tidak ditentukan',
                    'tanggal_dibuat'   => $tugas->tanggal_dibuat
                        ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y')
                        : '-',
                    'has_submission'   => $tugas->latestSubmitTugas !== null,
                    'submission_date'  => $tugas->latestSubmitTugas?->created_at
                        ? Carbon::parse($tugas->latestSubmitTugas->created_at)->locale('id')->isoFormat('D MMMM Y, HH:mm')
                        : null,
                    'detail_url'       => url('/tugas/detail/' . $tugas->id_tugas),
                ],
            ];
        }

        if ($user->karyawan_id_karyawan) {
            $staffKaryawan = $user->karyawan;
            $staffNama = $staffKaryawan?->nama ?? 'Staff';
            $staffDivisi = $staffKaryawan?->divisi?->nama_divisi ?? 'Tanpa Divisi';
            $staffInitial = strtoupper(substr($staffNama, 0, 1));

            $suratList = SuratIzin::where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
                ->when($start, fn ($query) => $query->whereDate('tanggal_izin', '>=', Carbon::parse($start)->toDateString()))
                ->when($end, fn ($query) => $query->whereDate('tanggal_izin', '<=', Carbon::parse($end)->toDateString()))
                ->get();

            foreach ($suratList as $izin) {
                $events[] = [
                    'id'              => 'IZIN-' . $izin->id_surat,
                    'title'           => 'Izin: ' . $izin->judul_surat,
                    'start'           => Carbon::parse($izin->tanggal_izin)->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => '#EDE9FE',
                    'borderColor'     => '#8B5CF6',
                    'textColor'       => '#6D28D9',
                    'extendedProps'   => [
                        'type'             => 'izin',
                        'id_surat'         => $izin->id_surat,
                        'judul_murni'      => $izin->judul_surat,
                        'divisi_nama'      => $staffDivisi,
                        'pengirim_nama'    => 'Pengajuan',
                        'pengirim_jabatan' => '',
                        'staff_nama'       => $staffNama,
                        'staff_jabatan'    => $staffKaryawan?->jabatan ?? 'Staff',
                        'staff_initial'    => $staffInitial,
                        'status'           => $izin->status,
                        'status_label'     => 'Izin (' . $izin->status . ')',
                        'badge_class'      => 'bg-purple-100 text-purple-700 border border-purple-200',
                        'dot_color'        => '#8B5CF6',
                        'deskripsi'        => $izin->keterangan ?? '-',
                        'deadline_label'   => Carbon::parse($izin->tanggal_izin)->locale('id')->isoFormat('dddd, D MMMM Y'),
                        'has_submission'   => false,
                        'submission_date'  => null,
                        'detail_url'       => null,
                    ],
                ];
            }
        }

        $notes = \App\Models\CalendarNote::where('user_id_user', $user->id_user)
            ->when($start, fn ($query) => $query->whereDate('tanggal', '>=', Carbon::parse($start)->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('tanggal', '<=', Carbon::parse($end)->toDateString()))
            ->orderBy('tanggal')
            ->get();

        foreach ($notes as $note) {
            $warna = \App\Models\CalendarNote::WARNA_OPTIONS[$note->warna] ?? \App\Models\CalendarNote::WARNA_OPTIONS['slate'];
            $events[] = [
                'id'              => 'NOTE-' . $note->id,
                'title'           => $note->judul,
                'start'           => $note->tanggal->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $warna['bg'],
                'borderColor'     => $warna['border'],
                'textColor'       => $warna['text'],
                'extendedProps'   => [
                    'type'          => 'note',
                    'note_id'       => $note->id,
                    'judul'         => $note->judul,
                    'detail'        => $note->detail,
                    'warna'         => $note->warna,
                    'dot_color'     => $warna['dot'],
                    'tanggal_label' => $note->tanggal->locale('id')->isoFormat('dddd, D MMMM Y'),
                ],
            ];
        }

        return response()->json(['data' => $events]);
    }

    /**
     * Endpoint API untuk data event kalender kadiv (semua tugas divisi).
     */
    public function eventsKadiv(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (!$user || !$user->karyawan) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $divisiId = $user->karyawan->divisi_id_divisi;
        $filters = $request->only(['status', 'staff', 'q']);
        $query = KadivTugasQuery::forDivision($divisiId)->apply($filters);

        $start = $request->input('start');
        $end = $request->input('end');

        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->get();

        $events = [];

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);

            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;
            if (!$startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (!$isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }
            $namaStaff = $tugas->karyawan?->nama ?? 'Staff';

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => '[' . $namaStaff . '] ' . $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'            => 'tugas',
                    'id_tugas'        => $tugas->id_tugas,
                    'judul_murni'     => $tugas->judul_tugas,
                    'staff_id'        => $tugas->karyawan_id_karyawan,
                    'staff_nama'      => $namaStaff,
                    'staff_jabatan'   => $tugas->karyawan?->jabatan ?? 'Staff',
                    'staff_initial'   => strtoupper(substr($namaStaff, 0, 1)),
                    'status'          => $effectiveStatus,
                    'status_label'    => $style['label'],
                    'badge_class'     => $style['badgeClass'],
                    'dot_color'       => $style['dotColor'],
                    'deskripsi'       => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label'  => $tugas->deadline
                        ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm')
                        : 'Tidak ditentukan',
                    'tanggal_dibuat'  => $tugas->tanggal_dibuat
                        ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y')
                        : '-',
                    'has_submission'  => $tugas->latestSubmitTugas !== null,
                    'submission_date' => $tugas->latestSubmitTugas?->created_at
                        ? Carbon::parse($tugas->latestSubmitTugas->created_at)->locale('id')->isoFormat('D MMMM Y, HH:mm')
                        : null,
                    'detail_url'      => url('/kadiv/detailTugas/' . $tugas->id_tugas),
                ],
            ];
        }

        return response()->json([
            'message' => 'Data event kalender divisi',
            'data'    => $events,
        ]);
    }

    public function dataKadiv(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (! $user || ! $user->karyawan) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $divisiId = $user->karyawan->divisi_id_divisi;
        $filters = $request->only(['status', 'staff', 'q']);
        $query = KadivTugasQuery::forDivision($divisiId)->apply($filters);

        $start = $request->input('start');
        $end = $request->input('end');
        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->get();
        $events = [];
        $kadivList = \App\Models\Karyawan::whereHas('user.role', fn ($q) => $q->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala divisi%'])
            ->get()
            ->keyBy('divisi_id_divisi');

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);
            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;
            if (! $startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (! $isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }

            $namaStaff = $tugas->karyawan?->nama ?? 'Staff';
            $divisiNama = $tugas->karyawan?->divisi?->nama_divisi ?? 'Tanpa Divisi';
            $kadiv = $tugas->karyawan?->divisi_id_divisi ? ($kadivList[$tugas->karyawan->divisi_id_divisi] ?? null) : null;
            $pengirimNama = $kadiv ? $kadiv->nama : ('Kadiv ' . $divisiNama);
            $pengirimJabatan = $kadiv ? ($kadiv->jabatan ?: 'Kepala Divisi') : 'Kepala Divisi';

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => '[' . $namaStaff . '] ' . $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'            => 'tugas',
                    'id_tugas'        => $tugas->id_tugas,
                    'judul_murni'     => $tugas->judul_tugas,
                    'divisi_nama'     => $divisiNama,
                    'pengirim_nama'   => $pengirimNama,
                    'pengirim_jabatan' => $pengirimJabatan,
                    'staff_id'        => $tugas->karyawan_id_karyawan,
                    'staff_nama'      => $namaStaff,
                    'staff_jabatan'   => $tugas->karyawan?->jabatan ?? 'Staff',
                    'staff_initial'   => strtoupper(substr($namaStaff, 0, 1)),
                    'status'          => $effectiveStatus,
                    'status_label'    => $style['label'],
                    'badge_class'     => $style['badgeClass'],
                    'dot_color'       => $style['dotColor'],
                    'deskripsi'       => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label'  => $tugas->deadline ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm') : 'Tidak ditentukan',
                    'tanggal_dibuat'  => $tugas->tanggal_dibuat ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y') : '-',
                    'has_submission'  => $tugas->latestSubmitTugas !== null,
                    'submission_date' => $tugas->latestSubmitTugas?->created_at ? Carbon::parse($tugas->latestSubmitTugas->created_at)->locale('id')->isoFormat('D MMMM Y, HH:mm') : null,
                    'detail_url'      => url('/kadiv/detailTugas/' . $tugas->id_tugas),
                ],
            ];
        }

        $notes = \App\Models\CalendarNote::where('user_id_user', $user->id_user)
            ->when($start, fn ($query) => $query->whereDate('tanggal', '>=', Carbon::parse($start)->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('tanggal', '<=', Carbon::parse($end)->toDateString()))
            ->orderBy('tanggal')
            ->get();

        foreach ($notes as $note) {
            $warna = \App\Models\CalendarNote::WARNA_OPTIONS[$note->warna] ?? \App\Models\CalendarNote::WARNA_OPTIONS['slate'];
            $events[] = [
                'id'              => 'NOTE-' . $note->id,
                'title'           => $note->judul,
                'start'           => $note->tanggal->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $warna['bg'],
                'borderColor'     => $warna['border'],
                'textColor'       => $warna['text'],
                'extendedProps'   => [
                    'type'          => 'note',
                    'note_id'       => $note->id,
                    'judul'         => $note->judul,
                    'detail'        => $note->detail,
                    'warna'         => $note->warna,
                    'dot_color'     => $warna['dot'],
                    'tanggal_label' => $note->tanggal->locale('id')->isoFormat('dddd, D MMMM Y'),
                ],
            ];
        }

        return response()->json(['data' => $events]);
    }

    /**
     * Tampilkan halaman kalender untuk HRD (seluruh divisi & perusahaan).
     */
    public function indexHrd(Request $request)
    {
        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();
        $staffList = Karyawan::with('divisi')
            ->orderBy('nama', 'asc')
            ->get();

        return view('hrd.kalender', compact('divisiList', 'staffList'));
    }

    /**
     * Endpoint API untuk data event kalender HRD (seluruh divisi, tahu dari siapa ke siapa).
     */
    public function eventsHrd(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $divisiFilter = $request->input('divisi');
        $staffFilter  = $request->input('staff');
        $statusFilter = $request->input('status');

        // Map Kadiv per divisi untuk lookup "Pemberi Tugas (Dari)"
        $kadivList = Karyawan::whereHas('user.role', fn($q) => $q->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala divisi%'])
            ->get()
            ->keyBy('divisi_id_divisi');

        $query = Tugas::with(['karyawan.divisi', 'submitTugas', 'latestSubmitTugas']);
        $start = $request->input('start');
        $end = $request->input('end');

        if ($divisiFilter) {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi_id_divisi', $divisiFilter));
        }

        if ($staffFilter) {
            $query->where('karyawan_id_karyawan', $staffFilter);
        }

        if ($statusFilter && in_array($statusFilter, Tugas::ALL_STATUSES, true)) {
            $query->statusEfektif($statusFilter);
        }

        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->orderBy('tanggal_dibuat', 'desc')->get();

        $events = [];

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);

            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;
            if (!$startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (!$isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }
            $karyawan = $tugas->karyawan;
            $penerimaNama = $karyawan?->nama ?? 'Staff';
            $penerimaJabatan = $karyawan?->jabatan ?? 'Staff';
            $penerimaId = $tugas->karyawan_id_karyawan;
            $divisiId = $karyawan?->divisi_id_divisi;
            $divisiNama = $karyawan?->divisi?->nama_divisi ?? 'Tanpa Divisi';

            $kadiv = $divisiId ? ($kadivList[$divisiId] ?? null) : null;
            $pengirimNama = $kadiv ? $kadiv->nama : ('Kadiv ' . $divisiNama);
            $pengirimJabatan = $kadiv ? $kadiv->jabatan : 'Kepala Divisi';
            $pengirimId = $kadiv ? $kadiv->id_karyawan : null;

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => '[' . $divisiNama . '] ' . $pengirimNama . ' ➔ ' . $penerimaNama . ': ' . $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'             => 'tugas',
                    'id_tugas'         => $tugas->id_tugas,
                    'judul_murni'      => $tugas->judul_tugas,
                    'divisi_id'        => $divisiId,
                    'divisi_nama'      => $divisiNama,
                    'pengirim_id'      => $pengirimId,
                    'pengirim_nama'    => $pengirimNama,
                    'pengirim_jabatan' => $pengirimJabatan,
                    'staff_id'         => $penerimaId,
                    'staff_nama'       => $penerimaNama,
                    'staff_jabatan'    => $penerimaJabatan,
                    'staff_initial'    => strtoupper(substr($penerimaNama, 0, 1)),
                    'status'           => $effectiveStatus,
                    'status_label'     => $style['label'],
                    'badge_class'      => $style['badgeClass'],
                    'dot_color'        => $style['dotColor'],
                    'deskripsi'        => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label'   => $tugas->deadline
                        ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm')
                        : 'Tidak ditentukan',
                    'tanggal_dibuat'   => $tugas->tanggal_dibuat
                        ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y')
                        : '-',
                    'has_submission'   => $tugas->latestSubmitTugas !== null,
                    'submission_date'  => $tugas->latestSubmitTugas?->created_at
                        ? Carbon::parse($tugas->latestSubmitTugas->created_at)->locale('id')->isoFormat('D MMMM Y, HH:mm')
                        : null,
                    'detail_url'       => null,
                ],
            ];
        }

        return response()->json([
            'message' => 'Data event kalender HRD',
            'data'    => $events,
        ]);
    }

    public function dataHrd(Request $request)
    {
        $user = $request->user() ?: auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $divisiFilter = $request->input('divisi');
        $staffFilter  = $request->input('staff');
        $statusFilter = $request->input('status');
        $start = $request->input('start');
        $end = $request->input('end');

        $kadivList = \App\Models\Karyawan::whereHas('user.role', fn($q) => $q->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala divisi%'])
            ->get()
            ->keyBy('divisi_id_divisi');

        $query = Tugas::with(['karyawan.divisi', 'submitTugas', 'latestSubmitTugas']);

        if ($divisiFilter) {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi_id_divisi', $divisiFilter));
        }

        if ($staffFilter) {
            $query->where('karyawan_id_karyawan', $staffFilter);
        }

        if ($statusFilter && in_array($statusFilter, Tugas::ALL_STATUSES, true)) {
            $query->statusEfektif($statusFilter);
        }

        if ($start) {
            $startDate = Carbon::parse($start);
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '>=', $startDate->toDateString());
                })->orWhere(function ($inner) use ($startDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '>=', $startDate->toDateString());
                });
            });
        }

        if ($end) {
            $endDate = Carbon::parse($end);
            $query->where(function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', $endDate->toDateString());
                })->orWhere(function ($inner) use ($endDate) {
                    $inner->whereNull('deadline')
                        ->whereDate('tanggal_dibuat', '<=', $endDate->toDateString());
                });
            });
        }

        $tugasList = $query->orderBy('tanggal_dibuat', 'desc')->get();
        $events = [];

        foreach ($tugasList as $tugas) {
            $effectiveStatus = $tugas->status_efektif;
            $style = self::getStatusStyle($effectiveStatus);

            $startDate = $tugas->deadline ?: $tugas->tanggal_dibuat ?: $tugas->created_at;
            if (! $startDate) {
                continue;
            }

            $carbonDate = Carbon::parse($startDate);
            $isAllDay = empty($tugas->deadline) || $carbonDate->format('H:i:s') === '00:00:00';
            $endDate = $isAllDay ? $carbonDate : $carbonDate->copy()->addMinutes(45);
            if (! $isAllDay && $endDate->day !== $carbonDate->day) {
                $endDate = $carbonDate->copy()->endOfDay();
            }
            $karyawan = $tugas->karyawan;
            $penerimaNama = $karyawan?->nama ?? 'Staff';
            $penerimaJabatan = $karyawan?->jabatan ?? 'Staff';
            $penerimaId = $tugas->karyawan_id_karyawan;
            $divisiId = $karyawan?->divisi_id_divisi;
            $divisiNama = $karyawan?->divisi?->nama_divisi ?? 'Tanpa Divisi';

            $kadiv = $divisiId ? ($kadivList[$divisiId] ?? null) : null;
            $pengirimNama = $kadiv ? $kadiv->nama : ('Kadiv ' . $divisiNama);
            $pengirimJabatan = $kadiv ? $kadiv->jabatan : 'Kepala Divisi';
            $pengirimId = $kadiv ? $kadiv->id_karyawan : null;

            $events[] = [
                'id'              => $tugas->id_tugas,
                'title'           => '[' . $divisiNama . '] ' . $pengirimNama . ' ➔ ' . $penerimaNama . ': ' . $tugas->judul_tugas,
                'start'           => $carbonDate->toIso8601String(),
                'end'             => $endDate->toIso8601String(),
                'allDay'          => $isAllDay,
                'backgroundColor' => $style['backgroundColor'],
                'borderColor'     => $style['borderColor'],
                'textColor'       => $style['textColor'],
                'extendedProps'   => [
                    'type'             => 'tugas',
                    'id_tugas'         => $tugas->id_tugas,
                    'judul_murni'      => $tugas->judul_tugas,
                    'divisi_id'        => $divisiId,
                    'divisi_nama'      => $divisiNama,
                    'pengirim_id'      => $pengirimId,
                    'pengirim_nama'    => $pengirimNama,
                    'pengirim_jabatan' => $pengirimJabatan,
                    'staff_id'         => $penerimaId,
                    'staff_nama'       => $penerimaNama,
                    'staff_jabatan'    => $penerimaJabatan,
                    'staff_initial'    => strtoupper(substr($penerimaNama, 0, 1)),
                    'status'           => $effectiveStatus,
                    'status_label'     => $style['label'],
                    'badge_class'      => $style['badgeClass'],
                    'dot_color'        => $style['dotColor'],
                    'deskripsi'        => $tugas->deskripsi ?? 'Tidak ada deskripsi',
                    'deadline_label'   => $tugas->deadline ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm') : 'Tidak ditentukan',
                    'tanggal_dibuat'   => $tugas->tanggal_dibuat ? Carbon::parse($tugas->tanggal_dibuat)->locale('id')->isoFormat('D MMMM Y') : '-',
                    'has_submission'   => $tugas->latestSubmitTugas !== null,
                    'submission_date'  => $tugas->latestSubmitTugas?->created_at ? Carbon::parse($tugas->latestSubmitTugas->created_at)->locale('id')->isoFormat('D MMMM Y, HH:mm') : null,
                    'detail_url'       => null,
                ],
            ];
        }

        $notes = \App\Models\CalendarNote::where('user_id_user', $user->id_user)
            ->when($start, fn ($query) => $query->whereDate('tanggal', '>=', Carbon::parse($start)->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('tanggal', '<=', Carbon::parse($end)->toDateString()))
            ->orderBy('tanggal')
            ->get();

        foreach ($notes as $note) {
            $warna = \App\Models\CalendarNote::WARNA_OPTIONS[$note->warna] ?? \App\Models\CalendarNote::WARNA_OPTIONS['slate'];
            $events[] = [
                'id'              => 'NOTE-' . $note->id,
                'title'           => $note->judul,
                'start'           => $note->tanggal->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $warna['bg'],
                'borderColor'     => $warna['border'],
                'textColor'       => $warna['text'],
                'extendedProps'   => [
                    'type'          => 'note',
                    'note_id'       => $note->id,
                    'judul'         => $note->judul,
                    'detail'        => $note->detail,
                    'warna'         => $note->warna,
                    'dot_color'     => $warna['dot'],
                    'tanggal_label' => $note->tanggal->locale('id')->isoFormat('dddd, D MMMM Y'),
                ],
            ];
        }

        return response()->json(['data' => $events]);
    }
}
