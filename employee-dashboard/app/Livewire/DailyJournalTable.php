<?php

namespace App\Livewire;

use App\Models\DailyJournal;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\User2;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DailyJournalTable extends Component
{
    use WithFileUploads;

    // Anchor date for weekly navigation (Y-m-d)
    public string $currentDate;

    // View mode: 'my' (To Do Saya), 'team' (To Do Tim untuk Kadiv), 'all' (Monitoring untuk HRD)
    public string $viewMode = 'my';

    // Filters for Kadiv & HRD
    public ?string $filterDivisiId = null;
    public ?string $filterKaryawanId = null;

    // Modal state
    public bool $isModalOpen = false;
    public string $modalDate = '';
    public string $modalDateFormatted = '';
    public bool $isViewingOther = false; // true if viewing someone else's journal (read-only for status if not boss)

    // Form inputs
    public ?int $editingJournalId = null;
    public ?string $targetKaryawanId = null; // Karyawan yang ditugaskan
    public string $aktivitas = '';
    public string $prioritas = 'tinggi'; // rendah | sedang | tinggi
    public string $status = 'pending'; // pending (0%) | on_progress (50%) | selesai (90%) | revisi (50%) | disetujui (100%)
    public string $catatan = '';
    public $fileBukti = null;
    public ?string $existingFileBukti = null;
    public ?string $existingFileNamaAsli = null;
    public string $linkBukti = '';

    public ?string $feedbackMessage = null;
    public ?string $feedbackType = 'success'; // success | error

    protected function rules(): array
    {
        return [
            'modalDate' => 'required|date',
            'targetKaryawanId' => 'nullable|string|max:50',
            'aktivitas' => 'required|string|max:1000',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'status' => 'required|in:pending,on_progress,selesai,revisi,disetujui',
            'catatan' => 'nullable|string|max:2000',
            'fileBukti' => 'nullable|file|max:20480',
            'linkBukti' => 'nullable|string|max:2000',
        ];
    }

    protected array $messages = [
        'aktivitas.required' => 'Aktivitas / uraian tugas wajib diisi.',
        'prioritas.required' => 'Pilih prioritas tugas.',
        'status.required' => 'Pilih status pengerjaan.',
        'fileBukti.max' => 'Ukuran file bukti maksimal 20 MB.',
    ];

    public function mount(): void
    {
        $this->currentDate = now()->toDateString();
        $this->modalDate = now()->toDateString();
        $this->modalDateFormatted = Carbon::parse($this->modalDate)->locale('id')->translatedFormat('l, j F Y');

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
        $role = strtolower(auth()->user()?->role?->nama_role ?? 'staff');
        $allowedModes = match ($role) {
            'kadiv' => ['my', 'team', 'rekap_divisi'],
            'hrd', 'direktur', 'direksi' => ['my', 'all', 'rekap_divisi'],
            default => ['my', 'rekap_divisi'],
        };

        if (! in_array($mode, $allowedModes, true)) {
            $this->feedbackMessage = 'Anda tidak memiliki akses ke mode To Do tersebut.';
            $this->feedbackType = 'error';
            return;
        }

        $this->viewMode = $mode;
        $this->resetFeedback();

        if ($mode === 'team' && ! $this->filterKaryawanId) {
            $user = auth()->user();
            $divisiId = $user?->karyawan?->divisi_id_divisi;
            $firstMember = Karyawan::where('divisi_id_divisi', $divisiId)
                ->whereHas('user')
                ->where('id_karyawan', '!=', $user?->karyawan_id_karyawan)
                ->first();
            if ($firstMember) {
                $this->filterKaryawanId = $firstMember->id_karyawan;
            }
        }
    }

    public function updatedFilterDivisiId(): void
    {
        if (! in_array(strtolower(auth()->user()?->role?->nama_role ?? ''), ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            $this->filterDivisiId = null;
        }

        $this->filterKaryawanId = null;
    }

    public function updatedFilterKaryawanId(): void
    {
        $user = auth()->user();

        if ($user && $this->filterKaryawanId && ! $this->canViewKaryawan($user, $this->filterKaryawanId)) {
            $this->filterKaryawanId = null;
            $this->feedbackMessage = 'Anda tidak memiliki akses untuk melihat To Do karyawan tersebut.';
            $this->feedbackType = 'error';
        }
    }

    public function openCreateModal(?string $date = null, ?string $targetId = null): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->modalDate = $date ?: ($this->currentDate ?: now()->toDateString());
        $this->modalDateFormatted = Carbon::parse($this->modalDate)->locale('id')->translatedFormat('l, j F Y');
        $this->resetForm();
        $this->resetFeedback();

        $requestedTarget = $targetId ?: ($this->viewMode !== 'my' ? $this->filterKaryawanId : null);
        $this->targetKaryawanId = $requestedTarget ?: $user->karyawan_id_karyawan;

        if ($this->targetKaryawanId && ! $this->canAssignToKaryawan($user, $this->targetKaryawanId)) {
            $this->targetKaryawanId = null;
            $this->feedbackMessage = 'Anda tidak memiliki akses untuk menugaskan To Do kepada karyawan tersebut.';
            $this->feedbackType = 'error';
            return;
        }

        $this->isViewingOther = false;
        $this->isModalOpen = true;
    }

    public function openDayModal(string $date): void
    {
        $this->openCreateModal($date);
    }

    public function closeDayModal(): void
    {
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingJournalId = null;
        $this->targetKaryawanId = null;
        $this->aktivitas = '';
        $this->prioritas = 'tinggi';
        $this->status = 'pending';
        $this->catatan = '';
        $this->fileBukti = null;
        $this->existingFileBukti = null;
        $this->existingFileNamaAsli = null;
        $this->linkBukti = '';
        $this->resetValidation();
    }

    public function resetFeedback(): void
    {
        $this->feedbackMessage = null;
    }

    public function removeExistingFile(): void
    {
        $this->existingFileBukti = null;
        $this->existingFileNamaAsli = null;
    }

    public function saveJournal(): void
    {
        $this->validate();

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $role = strtolower($user->role?->nama_role ?? 'staff');
        $targetKaryawanId = $this->targetKaryawanId ?: $user->karyawan_id_karyawan;
        $targetKaryawan = $targetKaryawanId
            ? Karyawan::with(['user.role', 'divisi'])->find($targetKaryawanId)
            : null;
        $isSelf = $targetKaryawanId === $user->karyawan_id_karyawan;

        if ($targetKaryawanId && ! $targetKaryawan) {
            $this->feedbackMessage = 'Karyawan penerima To Do tidak ditemukan.';
            $this->feedbackType = 'error';
            return;
        }

        if (! $this->canAssignToKaryawan($user, $targetKaryawanId)) {
            $this->feedbackMessage = $role === 'staff'
                ? 'Staff hanya dapat membuat To Do untuk diri sendiri.'
                : 'Anda tidak memiliki akses untuk menugaskan To Do kepada karyawan tersebut.';
            $this->feedbackType = 'error';
            return;
        }

        $targetUserId = $isSelf
            ? $user->id_user
            : $targetKaryawan?->user?->id_user;

        if (! $targetUserId) {
            $this->feedbackMessage = 'Karyawan penerima belum memiliki akun untuk menerima To Do.';
            $this->feedbackType = 'error';
            return;
        }

        $filePath = $this->existingFileBukti;
        $fileName = $this->existingFileNamaAsli;

        if ($this->fileBukti) {
            $filePath = $this->fileBukti->store('daily-journals', 'public');
            $fileName = $this->fileBukti->getClientOriginalName();
        }

        $link = trim($this->linkBukti);
        if (! empty($link) && ! preg_match('~^(?:f|ht)tps?://~i', $link)) {
            $link = 'https://' . $link;
        }

        if ($this->editingJournalId) {
            $journal = DailyJournal::find($this->editingJournalId);

            if (! $journal) {
                $this->feedbackMessage = 'Catatan to do tidak ditemukan.';
                $this->feedbackType = 'error';
                return;
            }

            if (! $this->canManageJournal($user, $journal)) {
                $this->feedbackMessage = 'Anda tidak memiliki hak akses untuk mengedit to do ini.';
                $this->feedbackType = 'error';
                return;
            }

            if ($journal->file_bukti && $journal->file_bukti !== $filePath) {
                Storage::disk('public')->delete($journal->file_bukti);
            }

            $journal->update([
                'tanggal' => $this->modalDate,
                'aktivitas' => trim($this->aktivitas),
                'prioritas' => $this->prioritas,
                'status' => $this->status,
                'catatan' => $this->catatan ? trim($this->catatan) : null,
                'file_bukti' => $filePath,
                'file_nama_asli' => $fileName,
                'link_bukti' => $link ?: null,
            ]);

            $this->feedbackMessage = 'To Do berhasil diperbarui.';
            $this->feedbackType = 'success';
        } else {
            DailyJournal::create([
                'user_id_user' => $targetUserId,
                'karyawan_id_karyawan' => $targetKaryawanId,
                'assigned_by_user_id' => $user->id_user,
                'tanggal' => $this->modalDate,
                'aktivitas' => trim($this->aktivitas),
                'prioritas' => $this->prioritas,
                'status' => $this->status,
                'catatan' => $this->catatan ? trim($this->catatan) : null,
                'file_bukti' => $filePath,
                'file_nama_asli' => $fileName,
                'link_bukti' => $link ?: null,
            ]);

            $targetName = ($targetKaryawanId !== $user->karyawan_id_karyawan && $targetKaryawan)
                ? " untuk {$targetKaryawan->nama}"
                : '';

            $this->feedbackMessage = "To Do berhasil ditambahkan{$targetName}.";
            $this->feedbackType = 'success';
        }

        $this->closeModal();
    }

    public function editJournal(int $id): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $journal = DailyJournal::with(['assignedBy.karyawan', 'assignedBy.role', 'user.karyawan', 'karyawan'])->find($id);

        if (! $journal) {
            return;
        }

        if (! $this->canManageJournal($user, $journal)) {
            $this->feedbackMessage = 'Anda tidak memiliki hak akses untuk mengedit to do ini.';
            $this->feedbackType = 'error';
            return;
        }

        $this->editingJournalId = $journal->id;
        $this->targetKaryawanId = $journal->karyawan_id_karyawan;
        $this->modalDate = Carbon::parse($journal->tanggal)->toDateString();
        $this->modalDateFormatted = Carbon::parse($journal->tanggal)->locale('id')->translatedFormat('l, j F Y');
        $this->aktivitas = $journal->aktivitas;
        $this->prioritas = $journal->prioritas;
        $this->status = $journal->status;
        $this->catatan = (string) ($journal->catatan ?? '');
        $this->existingFileBukti = $journal->file_bukti;
        $this->existingFileNamaAsli = $journal->file_nama_asli;
        $this->linkBukti = (string) ($journal->link_bukti ?? '');
        $this->fileBukti = null;
        $this->isViewingOther = false;
        $this->resetFeedback();
        $this->isModalOpen = true;
    }

    public function deleteJournal(int $id): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $journal = DailyJournal::find($id);

        if (! $journal) {
            return;
        }

        if (! $this->canDeleteJournal($user, $journal)) {
            $this->feedbackMessage = 'To Do ini ditugaskan oleh atasan dan hanya dapat dihapus oleh pemberi tugas.';
            $this->feedbackType = 'error';
            return;
        }

        if ($journal->file_bukti) {
            Storage::disk('public')->delete($journal->file_bukti);
        }
        $journal->delete();
        $this->feedbackMessage = 'To Do berhasil dihapus.';
        $this->feedbackType = 'success';

        if ($this->editingJournalId === $id) {
            $this->resetForm();
        }
    }

    public function updateStatus(int $id, string $newStatus): void
    {
        $allowed = ['pending', 'on_progress', 'selesai', 'revisi', 'disetujui'];
        if (! in_array($newStatus, $allowed, true)) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $role = strtolower($user->role?->nama_role ?? 'staff');
        $journal = DailyJournal::with(['user.karyawan', 'karyawan'])->find($id);

        if (! $journal) {
            return;
        }

        $isOwn = ($journal->user_id_user === $user->id_user || $journal->karyawan_id_karyawan === $user->karyawan_id_karyawan);
        $userDivisiId = $user->karyawan?->divisi_id_divisi;
        $targetDivisiId = $journal->user?->karyawan?->divisi_id_divisi ?? $journal->karyawan?->divisi_id_divisi;

        $isKadivOfDivision = ($role === 'kadiv' && $userDivisiId && $targetDivisiId === $userDivisiId);
        $isHrdOrDirektur = in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true);
        $isAssigner = ($journal->assigned_by_user_id === $user->id_user);

        // Hak akses perubahan status:
        if ($isOwn) {
            // Staff pada to-do miliknya sendiri:
            // Hanya bisa mengubah ke Pending (0%), In Progress (50%), atau Completed (90%).
            // Status Revisi dan Complete 100% ditentukan oleh Kadiv / Atasan.
            if ($role === 'staff') {
                if (in_array($newStatus, ['revisi', 'disetujui'], true)) {
                    $this->feedbackMessage = 'Status revisi dan complete 100% hanya dapat ditentukan oleh Kadiv atau Pimpinan.';
                    $this->feedbackType = 'error';
                    return;
                }
            }
        } else {
            // Melihat to-do orang lain (anggota tim / bawahan):
            // Hanya Kadiv divisi terkait, HRD, Direktur, atau pemberi tugas yang dapat mengubah status.
            if (! $isKadivOfDivision && ! $isHrdOrDirektur && ! $isAssigner) {
                $this->feedbackMessage = 'Anda tidak memiliki hak akses untuk mengubah status to do ini.';
                $this->feedbackType = 'error';
                return;
            }
        }

        $journal->status = $newStatus;
        $journal->save();

        $label = match ($newStatus) {
            'disetujui' => 'Complete 100% (100%)',
            'selesai' => 'Completed (90%)',
            'revisi' => 'Revisi (50%)',
            'on_progress' => 'In Progress (50%)',
            default => 'Pending (0%)',
        };

        $this->feedbackMessage = "Status to do berhasil diubah ke {$label}.";
        $this->feedbackType = 'success';
    }

    public function selectStaffForReview(string $karyawanId): void
    {
        $role = strtolower(auth()->user()?->role?->nama_role ?? 'staff');
        if (! in_array($role, ['kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            $this->feedbackMessage = 'Anda tidak memiliki akses untuk mereview to do karyawan lain.';
            $this->feedbackType = 'error';
            return;
        }

        $this->viewMode = $role === 'kadiv' ? 'team' : 'all';
        $this->filterKaryawanId = $karyawanId;
        $this->resetFeedback();
    }

    public function updatePrioritas(int $id, string $newPrioritas): void
    {
        if (! in_array($newPrioritas, ['rendah', 'sedang', 'tinggi'], true)) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $role = strtolower($user->role?->nama_role ?? 'staff');
        $journal = DailyJournal::with('user.karyawan')->find($id);

        if (! $journal) {
            return;
        }

        $isOwn = ($journal->user_id_user === $user->id_user);
        $userDivisiId = $user->karyawan?->divisi_id_divisi;
        $targetDivisiId = $journal->user?->karyawan?->divisi_id_divisi;
        $isKadivOfDivision = ($role === 'kadiv' && $userDivisiId && $targetDivisiId === $userDivisiId);
        $isHrd = ($role === 'hrd');

        if (! $isOwn && ! $isKadivOfDivision && ! $isHrd) {
            return;
        }

        $journal->prioritas = $newPrioritas;
        $journal->save();
        $label = match ($newPrioritas) {
            'tinggi' => 'High',
            'sedang' => 'Medium',
            'rendah' => 'Low',
        };
        $this->feedbackMessage = 'Prioritas berhasil diubah ke ' . $label . '.';
        $this->feedbackType = 'success';
    }

    private function canAssignTodos(User2 $user): bool
    {
        return in_array(strtolower($user->role?->nama_role ?? ''), ['kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true);
    }

    private function canAssignToKaryawan(User2 $user, ?string $karyawanId): bool
    {
        if (! $karyawanId) {
            return $user->karyawan_id_karyawan === null
                && in_array(strtolower($user->role?->nama_role ?? ''), ['staff', 'kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true);
        }

        if ($karyawanId === $user->karyawan_id_karyawan) {
            return in_array(strtolower($user->role?->nama_role ?? ''), ['staff', 'kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true);
        }

        $role = strtolower($user->role?->nama_role ?? '');
        if (! in_array($role, ['kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            return false;
        }

        return $this->assignableEmployees($user)->contains('id_karyawan', $karyawanId);
    }

    private function canViewKaryawan(User2 $user, string $karyawanId): bool
    {
        $role = strtolower($user->role?->nama_role ?? '');

        if (in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            return Karyawan::whereKey($karyawanId)->whereHas('user')->exists();
        }

        if ($role === 'kadiv') {
            return $this->assignableEmployees($user)->contains('id_karyawan', $karyawanId);
        }

        return false;
    }

    private function assignableEmployees(User2 $user): Collection
    {
        $role = strtolower($user->role?->nama_role ?? '');
        $query = Karyawan::query()
            ->whereHas('user')
            ->with(['user.role', 'divisi'])
            ->orderBy('nama');

        if ($role === 'kadiv') {
            $divisionId = $user->karyawan?->divisi_id_divisi;
            if (! $divisionId) {
                return collect();
            }

            $query->where('divisi_id_divisi', $divisionId);
        } elseif (! in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            return collect();
        }

        return $query->get();
    }

    private function canManageJournal(User2 $user, DailyJournal $journal): bool
    {
        $role = strtolower($user->role?->nama_role ?? '');
        $isRecipient = $journal->user_id_user === $user->id_user
            || $journal->karyawan_id_karyawan === $user->karyawan_id_karyawan;
        $isAssigner = $journal->assigned_by_user_id === $user->id_user;

        if ($isRecipient || $isAssigner || in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            return true;
        }

        if ($role !== 'kadiv') {
            return false;
        }

        $journal->loadMissing(['user.karyawan', 'karyawan']);
        $targetDivisionId = $journal->karyawan?->divisi_id_divisi
            ?? $journal->user?->karyawan?->divisi_id_divisi;

        return $user->karyawan?->divisi_id_divisi !== null
            && $user->karyawan->divisi_id_divisi === $targetDivisionId;
    }

    private function canDeleteJournal(User2 $user, DailyJournal $journal): bool
    {
        $isSelfCreated = $journal->user_id_user === $user->id_user
            && ($journal->assigned_by_user_id === null || $journal->assigned_by_user_id === $user->id_user);

        if ($isSelfCreated || $journal->assigned_by_user_id === $user->id_user) {
            return true;
        }

        $role = strtolower($user->role?->nama_role ?? '');
        if (in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            return true;
        }

        if ($role !== 'kadiv') {
            return false;
        }

        $journal->loadMissing(['user.karyawan', 'karyawan']);
        $targetDivisionId = $journal->karyawan?->divisi_id_divisi
            ?? $journal->user?->karyawan?->divisi_id_divisi;

        return $user->karyawan?->divisi_id_divisi !== null
            && $user->karyawan->divisi_id_divisi === $targetDivisionId;
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

        $query = DailyJournal::query()
            ->with(['assignedBy.karyawan', 'assignedBy.role'])
            ->whereDate('tanggal', $this->modalDate);

        if ($this->viewMode === 'my') {
            $user = auth()->user();
            $query->where('user_id_user', $user->id_user);
        } elseif ($this->filterKaryawanId && $this->canViewKaryawan(auth()->user(), $this->filterKaryawanId)) {
            $query->where('karyawan_id_karyawan', $this->filterKaryawanId);
        } else {
            return collect();
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
            ->with(['assignedBy.karyawan', 'assignedBy.role'])
            ->whereBetween('tanggal', [$startOfWeekStr, $endOfWeekStr]);

        $activeTargetKaryawan = null;

        if ($this->viewMode === 'my') {
            if ($user) {
                $query->where('user_id_user', $user->id_user);
                $activeTargetKaryawan = $user->karyawan;
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($this->filterKaryawanId && $user && $this->canViewKaryawan($user, $this->filterKaryawanId)) {
                $query->where('karyawan_id_karyawan', $this->filterKaryawanId);
                $activeTargetKaryawan = Karyawan::find($this->filterKaryawanId);
        } else {
            $query->whereRaw('1 = 0');
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
        // Khusus untuk assign todo: pilihan karyawan yang bisa ditugaskan
        $assignableKaryawan = collect();

        if ($role === 'kadiv') {
            $divisiId = $user?->karyawan?->divisi_id_divisi;
            $employeeOptions = $assignableKaryawan = $this->assignableEmployees($user);
        } elseif (in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            $divisions = Divisi::query()->ordered()->get();
            $empQuery = Karyawan::query()->orderBy('nama');
            if ($this->filterDivisiId) {
                $empQuery->where('divisi_id_divisi', $this->filterDivisiId);
            }
            $employeeOptions = $empQuery->whereHas('user')->with(['user.role'])->get();
            $assignableKaryawan = $this->assignableEmployees($user);
        }

        $weeklyJournals = (clone $query)->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();

        $activePercentage = 0;
        if ($weeklyJournals->isNotEmpty()) {
            $activePercentage = round($weeklyJournals->sum(fn ($j) => DailyJournal::statusToPercentage($j->status)) / $weeklyJournals->count(), 1);
        }

        // Rekapitulasi Progres Divisi (Kumpulkan dari seluruh staff & kadiv per minggu)
        $currentDivisiId = null;
        if ($role === 'kadiv' || $role === 'staff') {
            $currentDivisiId = $user?->karyawan?->divisi_id_divisi;
        } elseif (in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true)) {
            $currentDivisiId = $this->filterDivisiId ?: ($divisions->first()?->id_divisi ?? null);
        }

        $activeDivisi = $currentDivisiId ? Divisi::find($currentDivisiId) : null;

        $allDivisiEmployees = $currentDivisiId
            ? Karyawan::with(['user.role'])
                ->where('divisi_id_divisi', $currentDivisiId)
                ->orderBy('nama')
                ->get()
            : collect();

        $divisiJournals = $currentDivisiId
            ? DailyJournal::whereBetween('tanggal', [$startOfWeekStr, $endOfWeekStr])
                ->whereIn('karyawan_id_karyawan', $allDivisiEmployees->pluck('id_karyawan'))
                ->get()
            : collect();

        $staffProgressList = collect();
        $totalDivisiPercentageSum = 0;
        $totalDivisiTasks = 0;

        foreach ($allDivisiEmployees as $emp) {
            $empJournals = $divisiJournals->where('karyawan_id_karyawan', $emp->id_karyawan);
            $empTaskCount = $empJournals->count();

            $sumPct = $empJournals->sum(fn ($j) => DailyJournal::statusToPercentage($j->status));
            $empPct = $empTaskCount > 0 ? round($sumPct / $empTaskCount, 1) : 0;

            $isKadivMember = strtolower($emp->user?->role?->nama_role ?? '') === 'kadiv';

            $staffProgressList->push([
                'karyawan' => $emp,
                'isKadiv' => $isKadivMember,
                'roleLabel' => $isKadivMember ? 'Kepala Divisi' : 'Staff',
                'totalTasks' => $empTaskCount,
                'pct' => $empPct,
                'count100' => $empJournals->filter(fn ($j) => in_array($j->status, ['disetujui', 'approved', 'complete_100', 'fix']))->count(),
                'count90' => $empJournals->filter(fn ($j) => in_array($j->status, ['selesai', 'complete', 'completed']))->count(),
                'count50Progress' => $empJournals->where('status', 'on_progress')->count(),
                'count50Revisi' => $empJournals->where('status', 'revisi')->count(),
                'count0' => $empJournals->filter(fn ($j) => ! in_array($j->status, ['disetujui', 'approved', 'complete_100', 'fix', 'selesai', 'complete', 'completed', 'on_progress', 'revisi']))->count(),
            ]);

            $totalDivisiPercentageSum += $sumPct;
            $totalDivisiTasks += $empTaskCount;
        }

        $divisiWeeklyPercentage = $totalDivisiTasks > 0
            ? round($totalDivisiPercentageSum / $totalDivisiTasks, 1)
            : 0;

        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $dateObj = $startOfWeek->copy()->addDays($i);
            $daysOfWeek[] = [
                'date' => $dateObj->toDateString(),
                'label' => $dateObj->locale('id')->translatedFormat('l, j F Y'),
                'dayName' => $dateObj->locale('id')->translatedFormat('l'),
                'short' => $dateObj->locale('id')->translatedFormat('j M'),
                'isToday' => $dateObj->isToday(),
            ];
        }

        return view('livewire.daily-journal-table', [
            'calendarDays' => $calendarDays,
            'weeklyJournals' => $weeklyJournals,
            'daysOfWeek' => $daysOfWeek,
            'weekTitle' => $weekTitle,
            'role' => $role,
            'divisions' => $divisions,
            'employeeOptions' => $employeeOptions,
            'assignableKaryawan' => $assignableKaryawan,
            'canAssignTodos' => $user && $this->canAssignTodos($user),
            'activeTargetKaryawan' => $activeTargetKaryawan,
            'modalJournals' => $this->modalJournals,
            'activeDivisi' => $activeDivisi,
            'staffProgressList' => $staffProgressList,
            'divisiWeeklyPercentage' => $divisiWeeklyPercentage,
            'totalDivisiTasks' => $totalDivisiTasks,
            'stats' => [
                'totalTugas' => $totalTugasMingguIni,
                'totalSelesai' => $totalSelesaiMingguIni,
                'totalOnProgress' => $totalOnProgressMingguIni,
                'hariTerisi' => $hariTerisiCount,
                'totalHari' => 7,
                'activePercentage' => $activePercentage,
                'divisiWeeklyPercentage' => $divisiWeeklyPercentage,
                'totalDivisiTasks' => $totalDivisiTasks,
            ],
        ]);
    }
}
