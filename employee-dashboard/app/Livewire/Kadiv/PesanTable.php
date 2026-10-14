<?php

namespace App\Livewire\Kadiv;

use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\User2;
use App\Presenters\MessagePresenter;
use App\Queries\KadivMessageQuery;
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
    public string $tipe = '';

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

    public string $recipientUserId = '';
    public string $isi = '';

    public function mount(): void
    {
        $this->kadiv();
    }

    private function kadiv(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'kadiv'
            || ! $user->karyawan?->divisi_id_divisi) {
            abort(403);
        }

        return $user;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipe(): void
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
        $this->tipe = '';
        $this->arah = '';
        $this->dari = '';
        $this->sampai = '';
        $this->sort = 'tanggal';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function sendMessage(): void
    {
        $kadiv = $this->kadiv();
        $divisionId = $kadiv->karyawan->divisi_id_divisi;

        $this->validate([
            'recipientUserId' => 'required|string|exists:users2,id_user',
            'isi' => 'required|string|max:2000',
        ]);

        $recipient = User2::with(['role', 'karyawan'])->find($this->recipientUserId);
        $recipientRole = strtolower($recipient->role?->nama_role ?? '');
        $sameDivisionStaff = $recipientRole === 'staff'
            && $recipient->karyawan?->divisi_id_divisi === $divisionId;

        if (! $sameDivisionStaff && $recipientRole !== 'hrd') {
            $this->addError('recipientUserId', 'Penerima harus Staff di divisi Anda atau HRD.');
            return;
        }

        try {
            $message = Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => Str::limit($this->isi, 200, '') ?: 'Pesan baru',
                'deskripsi' => $this->isi,
                'tipe' => 'pesan',
                'tanggal_pesan' => now()->toDateString(),
                'pengirim_id_user' => $kadiv->id_user,
                'penerima_id_user' => $recipient->id_user,
            ]);

            PesanDikirim::dispatch($message);

            $this->reset(['recipientUserId', 'isi']);
            session()->flash('pesan_success', 'Pesan berhasil dikirim.');
            $this->dispatch('tutup-modal-pesan');
            $this->resetPage();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('sendMessage error: ' . $e->getMessage());
            $this->addError('isi', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    private function generateMessageId(): string
    {
        do {
            $id = 'PSN' . strtoupper(substr(uniqid('', true), -12));
            $id = str_replace('.', '', $id);
        } while (Pesan::whereKey($id)->exists());

        return $id;
    }

    /**
     * Mark the message as read (incoming only, first time only) and open
     * its detail view. Outgoing messages are viewable but never marked.
     */
    public function baca(string $id): void
    {
        $kadiv = $this->kadiv();

        $pesan = Pesan::query()
            ->whereKey($id)
            ->where(function ($query) use ($kadiv): void {
                $query->where('pengirim_id_user', $kadiv->id_user)
                    ->orWhere('penerima_id_user', $kadiv->id_user);
            })
            ->first();

        if ($pesan && $pesan->penerima_id_user === $kadiv->id_user && $pesan->dibaca_at === null) {
            $pesan->update(['dibaca_at' => now()]);
        }

        if ($pesan) {
            $this->redirect(url('/kadiv/detailPesan/'.$pesan->id_pesan));
        }
    }

    public function render()
    {
        $kadiv = $this->kadiv();
        $divisionId = $kadiv->karyawan->divisi_id_divisi;

        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        $messages = KadivMessageQuery::forUser($kadiv)
            ->apply([
                'sort' => $this->sort,
                'dir' => $this->dir,
                'tipe' => $this->tipe,
                'arah' => $this->arah,
                'q' => $this->search,
                'dari' => $this->dari,
                'sampai' => $this->sampai,
            ])
            ->paginate($perPage)
            ->through(fn (Pesan $message) => MessagePresenter::for($message, $kadiv));

        // Read states live outside the presenter (shared with the API);
        // merge them here so rows and the badge know what is unread.
        $ids = collect($messages->items())->pluck('id_pesan')->all();
        $read = Pesan::whereIn('id_pesan', $ids)->whereNotNull('dibaca_at')->pluck('id_pesan')->all();
        $messages->getCollection()->transform(function (array $row) use ($read): array {
            $row['unread'] = ($row['arah'] ?? 'masuk') === 'masuk' && ! in_array($row['id_pesan'], $read, true);

            return $row;
        });

        $belumDibaca = KadivMessageQuery::forUser($kadiv)
            ->apply(['arah' => 'masuk'])
            ->whereNull('dibaca_at')
            ->count();

        $recipients = User2::with(['karyawan', 'role'])
            ->where(function ($q) use ($divisionId): void {
                $q->whereHas('role', fn ($r) => $r->whereRaw('LOWER(nama_role) = ?', ['hrd']))
                    ->orWhere(function ($q2) use ($divisionId): void {
                        $q2->whereHas('role', fn ($r) => $r->whereRaw('LOWER(nama_role) = ?', ['staff']))
                            ->whereHas('karyawan', fn ($k) => $k->where('divisi_id_divisi', $divisionId));
                    });
            })
            ->get();

        return view('livewire.kadiv.pesan-table', [
            'messages' => $messages,
            'belumDibaca' => $belumDibaca,
            'recipients' => $recipients,
        ]);
    }
}
