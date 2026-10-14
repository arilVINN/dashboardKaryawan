<?php

namespace App\Livewire\Staff;

use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\User2;
use App\Queries\StaffPesanQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $arah = '';

    #[Url]
    public string $dari = '';

    #[Url]
    public string $sampai = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public int $perPage = 10;

    public bool $showCompose = false;

    public string $recipientUserId = '';

    public string $isi = '';

    public function mount(): void
    {
        $this->staff();
    }

    private function staff(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'staff'
            || ! $user->karyawan_id_karyawan) {
            abort(403);
        }

        return $user;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedArah(): void
    {
        $this->resetPage();
    }

    public function updatedDari(): void
    {
        $this->resetPage();
    }

    public function updatedSampai(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->arah = '';
        $this->dari = '';
        $this->sampai = '';
        $this->sort = '';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function openCompose(): void
    {
        $this->reset(['recipientUserId', 'isi']);
        $this->resetValidation();
        $this->showCompose = true;
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
    }

    public function sendMessage(): void
    {
        $staff = $this->staff();
        $divisionId = $staff->karyawan?->divisi_id_divisi;

        $this->validate([
            'recipientUserId' => 'required|string|exists:users2,id_user',
            'isi' => 'required|string|max:2000',
        ]);

        $recipient = User2::with(['role', 'karyawan'])->find($this->recipientUserId);
        $recipientRole = strtolower($recipient->role?->nama_role ?? '');
        $sameDivisionKadiv = $recipientRole === 'kadiv'
            && $recipient->karyawan?->divisi_id_divisi === $divisionId;

        if (! $sameDivisionKadiv && $recipientRole !== 'hrd') {
            $this->addError('recipientUserId', 'Penerima harus Kadiv di divisi Anda atau HRD.');
            return;
        }

        try {
            $message = Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => Str::limit($this->isi, 200, '') ?: 'Pesan baru',
                'deskripsi' => $this->isi,
                'tipe' => 'pesan',
                'tanggal_pesan' => now()->toDateString(),
                'pengirim_id_user' => $staff->id_user,
                'penerima_id_user' => $recipient->id_user,
            ]);

            PesanDikirim::dispatch($message);

            $this->reset(['recipientUserId', 'isi']);
            session()->flash('pesan_success', 'Pesan berhasil dikirim.');
            $this->showCompose = false;
            $this->resetPage();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('sendMessage error: ' . $e->getMessage());
            $this->addError('isi', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    /**
     * Mark the row's message as read (incoming only, first time only) and
     * open its detail view. Outgoing messages are viewable but never marked.
     */
    public function baca(string $jenis, string $linkId): void
    {
        $staff = $this->staff();

        if ($jenis === 'tugas') {
            // Task threads belong to the staff, so the latest message is
            // always viewable by them; only incoming ones can be marked read.
            // Ownership is checked through the task itself so legacy rows
            // with an empty tugas_karyawan_id_karyawan still open.
            $pesan = Pesan::query()
                ->where('tugas_id_tugas', $linkId)
                ->whereHas('tugas', fn ($query) => $query->where('karyawan_id_karyawan', $staff->karyawan_id_karyawan))
                ->orderByDesc('created_at')
                ->first();
        } else {
            $pesan = Pesan::query()
                ->whereKey($linkId)
                ->where(function ($query) use ($staff): void {
                    $query->where('penerima_id_user', $staff->id_user)
                        ->orWhere('pengirim_id_user', $staff->id_user);
                })
                ->first();
        }

        if ($pesan && $pesan->penerima_id_user === $staff->id_user && $pesan->dibaca_at === null) {
            $pesan->update(['dibaca_at' => now()]);
        }

        if ($pesan) {
            $this->redirect(url('/pesan/detail/'.$pesan->id_pesan));
        }
    }

    private function generateMessageId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid('', true), -12));
            $id = str_replace('.', '', $id);
        } while (Pesan::whereKey($id)->exists());

        return $id;
    }

    public function render()
    {
        $staff = $this->staff();

        $items = StaffPesanQuery::forStaff($staff)->unified([
            'sort' => $this->sort,
            'dir' => $this->dir,
            'arah' => $this->arah,
            'q' => $this->search,
            'dari' => $this->dari,
            'sampai' => $this->sampai,
        ]);

        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $page = (int) $this->getPage();

        $rows = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $ringkasan = StaffPesanQuery::forStaff($staff)->ringkasan();

        return view('livewire.staff.pesan-table', [
            'rows' => $rows,
            'belumDibaca' => $ringkasan['belum_dibaca'],
            'totalPesan' => $ringkasan['total'],
            'recipients' => $this->recipients($staff),
        ]);
    }

    /**
     * Staff can write to the Kadiv of their division or to HRD.
     */
    private function recipients(User2 $staff)
    {
        $divisionId = $staff->karyawan?->divisi_id_divisi;

        return User2::with(['karyawan', 'role'])
            ->where(function ($query) use ($divisionId): void {
                $query->whereHas('role', fn ($role) => $role->whereRaw('LOWER(nama_role) = ?', ['hrd']))
                    ->orWhere(function ($query) use ($divisionId): void {
                        $query->whereHas('role', fn ($role) => $role->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
                            ->whereHas('karyawan', fn ($karyawan) => $karyawan->where('divisi_id_divisi', $divisionId));
                    });
            })
            ->get();
    }
}
