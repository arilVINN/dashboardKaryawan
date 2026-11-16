<?php

namespace App\Livewire;

use App\Models\DailyJournal;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class DailyJournalTable extends Component
{
    // Anchor date for weekly navigation (Y-m-d)
    public string $currentDate;

    // View mode: 'my' (Jurnal Saya), 'team' (Jurnal Divisi untuk Kadiv), 'all' (Monitoring untuk HRD)
    public string $viewMode = 'my';

    // Filters for Kadiv & HRD
    public ?string $filterDivisiId = null;
    public ?string $filterKaryawanId = null;

    // Modal state
    public bool $isModalOpen = false;
    public string $modalDate = '';
    public string $modalDateFormatted = '';
    public bool $isViewingOther = false; // true if viewing someone else's journal (read-only)

    // Form inputs
    public ?int $editingJournalId = null;
    public string $aktivitas = '';
    public string $prioritas = 'sedang'; // rendah | sedang | tinggi
    public string $status = 'on_progress'; // on_progress | selesai
    public string $catatan = '';

    public ?string $feedbackMessage = null;
    public ?string $feedbackType = 'success'; // success | error

    protected array $rules = [
        'aktivitas' => 'required|string|max:1000',
        'prioritas' => 'required|in:rendah,sedang,tinggi',
        'status' => 'required|in:on_progress,selesai',
        'catatan' => 'nullable|string|max:2000',
    ];

    protected array $messages = [
        'aktivitas.required' => 'Aktivitas / uraian tugas wajib diisi.',
        'prioritas.required' => 'Pilih prioritas tugas.',
        'status.required' => 'Pilih status pengerjaan.',
    ];

    public function mount(): void
    {
        $this->currentDate = now()->toDateString();

        $user = auth()->user();
        $role = strtolower($user?->role?->nama_role ?? 'staff');

        if ($role === 'kadiv') {
            $this->filterDivisiId = $user?->karyawan?->divisi_id_divisi;
        }
    }

    public function prevWeek(): void
    {
        $this->currentDate = Carbon::parse($this->currentDate)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->currentDate = Carbon::parse($this->currentDate)->addWeek()->toDateString();
    }

    public function goToToday(): void
    {
        $this->currentDate = now()->toDateString();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetFeedback();

        if ($mode === 'team' && ! $this->filterKaryawanId) {
            $user = auth()->user();
            $divisiId = $user?->karyawan?->divisi_id_divisi;
            $firstMember = Karyawan::where('divisi_id_divisi', $divisiId)
                ->where('id_karyawan', '!=', $user?->karyawan_id_karyawan)
                ->first();
            if ($firstMember) {
                $this->filterKaryawanId = $firstMember->id_karyawan;
            }
        }
    }

    public function updatedFilterDivisiId(): void
    {
        $this->filterKaryawanId = null;
    }

    public function openDayModal(string $date): void
    {
        $this->modalDate = $date;
        $this->modalDateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, j F Y');
        $this->resetForm();
        $this->resetFeedback();

        // Determine if read-only: viewing someone else's journal
        $this->isViewingOther = ($this->viewMode !== 'my');

        $this->isModalOpen = true;
    }

    public function closeDayModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingJournalId = null;
        $this->aktivitas = '';
        $this->prioritas = 'sedang';
        $this->status = 'on_progress';
        $this->catatan = '';
        $this->resetValidation();
    }

    public function resetFeedback(): void
    {
        $this->feedbackMessage = null;
    }

    public function saveJournal(): void
    {
        // Only own journals can be added or edited
        if ($this->viewMode !== 'my') {
            $this->feedbackMessage = 'Anda hanya dapat mengelola jurnal pribadi Anda.';
            $this->feedbackType = 'error';
            return;
        }

        $this->validate();

        $user = auth()->user();

        if ($this->editingJournalId) {
            $journal = DailyJournal::where('id', $this->editingJournalId)
                ->where('user_id_user', $user->id_user)
                ->first();

            if (! $journal) {
                $this->feedbackMessage = 'Catatan jurnal tidak ditemukan atau bukan milik Anda.';
                $this->feedbackType = 'error';
                return;
            }

            $journal->update([
                'aktivitas' => $this->aktivitas,
                'prioritas' => $this->prioritas,
                'status' => $this->status,
                'catatan' => $this->catatan ? trim($this->catatan) : null,
            ]);

            $this->feedbackMessage = 'Jurnal berhasil diperbarui.';
            $this->feedbackType = 'success';
        } else {
            DailyJournal::create([
                'user_id_user' => $user->id_user,
                'karyawan_id_karyawan' => $user->karyawan_id_karyawan,
                'tanggal' => $this->modalDate,
                'aktivitas' => $this->aktivitas,
                'prioritas' => $this->prioritas,
                'status' => $this->status,
                'catatan' => $this->catatan ? trim($this->catatan) : null,
            ]);

            $this->feedbackMessage = 'Aktivitas berhasil ditambahkan ke jurnal.';
            $this->feedbackType = 'success';
        }

        $this->resetForm();
    }

    public function editJournal(int $id): void
    {
        $user = auth()->user();
        $journal = DailyJournal::where('id', $id)
            ->where('user_id_user', $user->id_user)
            ->first();

        if ($journal) {
            $this->editingJournalId = $journal->id;
            $this->aktivitas = $journal->aktivitas;
            $this->prioritas = $journal->prioritas;
            $this->status = $journal->status;
            $this->catatan = (string) ($journal->catatan ?? '');
            $this->resetFeedback();
        }
    }

    public function deleteJournal(int $id): void
    {
        $user = auth()->user();
        $journal = DailyJournal::where('id', $id)
            ->where('user_id_user', $user->id_user)
            ->first();

        if ($journal) {
            $journal->delete();
            $this->feedbackMessage = 'Aktivitas berhasil dihapus.';
            $this->feedbackType = 'success';

            if ($this->editingJournalId === $id) {
                $this->resetForm();
            }
        }
    }

    public function quickToggleStatus(int $id): void
    {
        $user = auth()->user();
        $journal = DailyJournal::where('id', $id)
            ->where('user_id_user', $user->id_user)
            ->first();

        if ($journal) {
            $journal->status = ($journal->status === 'selesai') ? 'on_progress' : 'selesai';
            $journal->save();
        }
    }

    public function getModalJournalsProperty(): Collection
    {
        if (! $this->modalDate) {
            return collect();
        }

        $query = DailyJournal::query()->whereDate('tanggal', $this->modalDate);

        if ($this->viewMode === 'my') {
            $user = auth()->user();
            $query->where('user_id_user', $user->id_user);
        } else {
            if ($this->filterKaryawanId) {
                $query->where('karyawan_id_karyawan', $this->filterKaryawanId);
            } else {
                return collect();
            }
        }

        return $query->latest('id')->get();
    }

    public function render()
    {
        $user = auth()->user();
        $role = strtolower($user?->role?->nama_role ?? 'staff');

        // Week calculation: Monday to Sunday
        $startOfWeek = Carbon::parse($this->currentDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::parse($this->currentDate)->endOfWeek(Carbon::SUNDAY);

        $startOfWeekStr = $startOfWeek->toDateString();
        $endOfWeekStr = $endOfWeek->toDateString();

        // Formatted title for the week (e.g. "10 – 16 November 2026")
        if ($startOfWeek->year !== $endOfWeek->year) {
            $weekTitle = $startOfWeek->locale('id')->translatedFormat('j M Y') . ' – ' . $endOfWeek->locale('id')->translatedFormat('j M Y');
        } elseif ($startOfWeek->month !== $endOfWeek->month) {
            $weekTitle = $startOfWeek->locale('id')->translatedFormat('j M') . ' – ' . $endOfWeek->locale('id')->translatedFormat('j M Y');
        } else {
            $weekTitle = $startOfWeek->locale('id')->translatedFormat('j') . ' – ' . $endOfWeek->locale('id')->translatedFormat('j F Y');
        }

        // Target user/karyawan for journal query
        $query = DailyJournal::query()
            ->whereBetween('tanggal', [$startOfWeekStr, $endOfWeekStr]);

        $activeTargetKaryawan = null;

        if ($this->viewMode === 'my') {
            $query->where('user_id_user', $user->id_user);
            $activeTargetKaryawan = $user->karyawan;
        } else {
            if ($this->filterKaryawanId) {
                $query->where('karyawan_id_karyawan', $this->filterKaryawanId);
                $activeTargetKaryawan = Karyawan::find($this->filterKaryawanId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $allJournals = $query->latest('id')->get()->groupBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        // Holidays for current week
        $holidays = Holiday::query()
            ->whereBetween('date', [$startOfWeekStr, $endOfWeekStr])
            ->get()
            ->keyBy(fn ($h) => Carbon::parse($h->date)->toDateString());

        // Construct 7 days (Monday to Sunday)
        $calendarDays = [];
        $totalTugasMingguIni = 0;
        $totalSelesaiMingguIni = 0;
        $totalOnProgressMingguIni = 0;
        $hariTerisiCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $dateObj = $startOfWeek->copy()->addDays($i);
            $dateString = $dateObj->toDateString();
            $dayJournals = $allJournals->get($dateString, collect());

            $isToday = $dateObj->isToday();
            $isWeekend = $dateObj->isWeekend();
            $holiday = $holidays->get($dateString);

            $count = $dayJournals->count();
            if ($count > 0) {
                $hariTerisiCount++;
            }

            $selesaiCount = $dayJournals->where('status', 'selesai')->count();
            $onProgressCount = $dayJournals->where('status', 'on_progress')->count();

            $totalTugasMingguIni += $count;
            $totalSelesaiMingguIni += $selesaiCount;
            $totalOnProgressMingguIni += $onProgressCount;

            // Compute overall day status
            $overallStatus = 'empty';
            if ($count > 0) {
                if ($onProgressCount > 0) {
                    $overallStatus = 'on_progress';
                } else {
                    $overallStatus = 'selesai';
                }
            }

            $hasHighPriority = $dayJournals->where('prioritas', 'tinggi')->isNotEmpty();
            $hasMediumPriority = $dayJournals->where('prioritas', 'sedang')->isNotEmpty();
            $hasLowPriority = $dayJournals->where('prioritas', 'rendah')->isNotEmpty();

            $calendarDays[] = [
                'date' => $dateString,
                'dayNumber' => $dateObj->format('j'),
                'dayName' => $dateObj->locale('id')->translatedFormat('l'),
                'dateFormatted' => $dateObj->locale('id')->translatedFormat('l, j M Y'),
                'shortFormatted' => $dateObj->locale('id')->translatedFormat('j M Y'),
                'isToday' => $isToday,
                'isWeekend' => $isWeekend,
                'holiday' => $holiday ? $holiday->name : null,
                'journals' => $dayJournals,
                'count' => $count,
                'selesaiCount' => $selesaiCount,
                'onProgressCount' => $onProgressCount,
                'overallStatus' => $overallStatus,
                'hasHighPriority' => $hasHighPriority,
                'hasMediumPriority' => $hasMediumPriority,
                'hasLowPriority' => $hasLowPriority,
            ];
        }

        // Division & Employee lists for dropdowns
        $divisions = collect();
        $employeeOptions = collect();

        if ($role === 'kadiv') {
            $divisiId = $user?->karyawan?->divisi_id_divisi;
            $employeeOptions = Karyawan::where('divisi_id_divisi', $divisiId)
                ->orderBy('nama')
                ->get();
        } elseif ($role === 'hrd') {
            $divisions = Divisi::orderBy('nama_divisi')->get();
            $empQuery = Karyawan::query()->orderBy('nama');
            if ($this->filterDivisiId) {
                $empQuery->where('divisi_id_divisi', $this->filterDivisiId);
            }
            $employeeOptions = $empQuery->get();
        }

        return view('livewire.daily-journal-table', [
            'calendarDays' => $calendarDays,
            'weekTitle' => $weekTitle,
            'role' => $role,
            'divisions' => $divisions,
            'employeeOptions' => $employeeOptions,
            'activeTargetKaryawan' => $activeTargetKaryawan,
            'modalJournals' => $this->modalJournals,
            'stats' => [
                'totalTugas' => $totalTugasMingguIni,
                'totalSelesai' => $totalSelesaiMingguIni,
                'totalOnProgress' => $totalOnProgressMingguIni,
                'hariTerisi' => $hariTerisiCount,
                'totalHari' => 7,
            ],
        ]);
    }
}
