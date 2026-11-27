<?php

namespace App\Livewire\Kadiv;

use App\Events\PesanDikirim;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\User2;
use App\Presenters\MessagePresenter;
use App\Queries\KadivMessageQuery;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public string $tipe = '';

    #[Url]
    public string $arah = '';

    public string $divisiId = '';

    public string $recipientUserId = '';

    public string $judul = '';

    public string $isi = '';

    public bool $showCompose = false;

    public bool $listOpen = false;

    public bool $confirmDiscard = false;

    public bool $sending = false;

    public string $recipientSearch = '';

    /** @var array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}|null */
    public ?array $recipient = null;

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->kadiv();
    }

    private function kadiv(): User2
    {
        $user = auth()->user();

        if (
            ! $user instanceof User2
            || (! $user instanceof User2 || ! $user->hasRole('kadiv') && ! $user->isDirektur())
            || ! $user->karyawan
        ) {
            abort(403);
        }

        return $user;
    }

    public function updatedDivisiId(): void
    {
        $this->recipientUserId = '';
        $this->recipient = null;
        $this->recipientSearch = '';
        $this->listOpen = true;
        $this->resetValidation('recipientUserId');
    }

    #[On('open-compose-message')]
    public function openCompose(): void
    {
        $this->reset(['divisiId', 'recipientUserId', 'recipient', 'recipientSearch', 'judul', 'isi', 'errorMessage']);
        $this->resetValidation();
        $this->listOpen = false;
        $this->confirmDiscard = false;
        $this->showCompose = true;
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
        $this->listOpen = false;
        $this->confirmDiscard = false;
        $this->dispatch('compose-message-closed');
    }

    #[On('compose-close-list')]
    public function closeList(): void
    {
        $this->listOpen = false;
    }

    public function openList(): void
    {
        if (! $this->recipientUserId) {
            $this->listOpen = true;
        }
    }

    public function updatedRecipientSearch(): void
    {
        $this->listOpen = true;
    }

    public function requestClose(): void
    {
        if ($this->divisiId !== '' || $this->recipientUserId || trim($this->judul) !== ''
            || trim($this->isi) !== '' || trim($this->recipientSearch) !== '') {
            $this->confirmDiscard = true;

            return;
        }
        $this->closeCompose();
    }

    public function keepDraft(): void
    {
        $this->confirmDiscard = false;
        $this->dispatch('compose-focus-cancel');
    }

    public function discardDraft(): void
    {
        $this->confirmDiscard = false;
        $this->closeCompose();
    }

    public function selectRecipient(string $id): void
    {
        $karyawan = $this->eligibleKaryawan()
            ->whereHas('user', fn ($query) => $query->where('users2.id_user', $id))
            ->first();

        if (! $karyawan?->user) {
            $this->addError('recipientUserId', 'Penerima tidak valid.');

            return;
        }

        $this->recipientUserId = $karyawan->user->id_user;
        $this->recipient = $this->present($karyawan);
        $this->recipientSearch = '';
        $this->listOpen = false;
        $this->errorMessage = '';
        $this->resetErrorBag('recipientUserId');
    }

    public function clearRecipient(): void
    {
        $this->recipientUserId = '';
        $this->recipient = null;
        $this->recipientSearch = '';
        $this->listOpen = false;
    }

    /**
     * @return array<int, array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}>
     */
    public function getRecipientResultsProperty(): array
    {
        $term = mb_strtolower(trim($this->recipientSearch));
        $query = $this->eligibleKaryawan();

        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->whereRaw('LOWER(nama) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%'.$term.'%'])
                    ->orWhereHas('divisi', fn ($q) => $q->whereRaw('LOWER(nama_divisi) LIKE ?', ['%'.$term.'%']));
            })->orderByRaw('LOWER(nama) = ? DESC', [$term]);
        }

        return $query->orderBy('nama')->limit(20)->get()
            ->map(fn (Karyawan $karyawan) => $this->present($karyawan))->all();
    }

    private function eligibleKaryawan()
    {
        $sender = $this->kadiv();
        $query = Karyawan::query()
            ->with(['user.role', 'divisi'])
            ->whereHas('user', fn ($q) => $q->where('users2.id_user', '!=', $sender->id_user))
            ->where('status_aktif', 'Aktif')
            ->where(function ($q): void {
                $q->where('aktif', true)->orWhereNull('aktif');
            });

        if ($this->divisiId !== '') {
            $query->where('divisi_id_divisi', $this->divisiId);
        }

        return $query;
    }

    /**
     * @return array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}
     */
    private function present(Karyawan $karyawan): array
    {
        $nama = (string) $karyawan->nama;

        return [
            'id' => (string) $karyawan->user?->id_user,
            'nama' => $nama,
            'jabatan' => $karyawan->jabatan ?: ($karyawan->user?->role?->nama_role ?? 'Staff'),
            'divisi' => $karyawan->divisi?->nama_divisi ?: '-',
            'foto' => $karyawan->foto ? route('hrd.karyawan.foto', $karyawan->id_karyawan) : null,
            'initials' => mb_strtoupper(mb_substr($nama, 0, 1)),
        ];
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
        $this->sort = 'tanggal';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function sendMessage(): void
    {
        if ($this->sending) {
            return;
        }

        $kadiv = $this->kadiv();

        $this->validate([
            'divisiId' => 'nullable|string|exists:divisis,id_divisi',
            'recipientUserId' => 'required|string|exists:users2,id_user',
            'judul' => 'nullable|string|max:200',
            'isi' => 'required|string|max:5000',
        ]);

        $recipient = User2::with('karyawan')->find($this->recipientUserId);

        if (! $recipient || $recipient->id_user === $kadiv->id_user) {
            $this->addError('recipientUserId', 'Penerima tidak valid.');

            return;
        }

        $rateKey = 'kadiv-pesan:'.$kadiv->id_user;
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->errorMessage = 'Terlalu banyak pesan dikirim. Tunggu sebentar sebelum mencoba lagi.';

            return;
        }

        $this->sending = true;

        try {
            RateLimiter::hit($rateKey, 60);

            $judulPesan = ! empty(trim($this->judul))
                ? strip_tags(trim($this->judul))
                : (Str::limit($this->isi, 200, '') ?: 'Pesan baru');

            $message = Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => $judulPesan,
                'deskripsi' => trim($this->isi),
                'tipe' => 'pesan',
                'tanggal_pesan' => now()->toDateString(),
                'pengirim_id_user' => $kadiv->id_user,
                'penerima_id_user' => $recipient->id_user,
            ]);

            PesanDikirim::dispatch($message);

            $this->reset(['divisiId', 'recipientUserId', 'recipient', 'recipientSearch', 'judul', 'isi', 'errorMessage']);
            $this->listOpen = false;
            $this->dispatch('app-toast', message: 'Pesan terkirim', type: 'success');
            $this->closeCompose();
            $this->resetPage();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Gagal mengirim pesan. Periksa kembali lalu coba lagi.';
        } finally {
            $this->sending = false;
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
        $kadiv = $this->kadiv();

        $messages = KadivMessageQuery::forUser($kadiv)
            ->apply([
                'sort' => $this->sort,
                'dir' => $this->dir,
                'tipe' => $this->tipe,
                'arah' => $this->arah,
                'q' => $this->search,
            ])
            ->paginate(10)
            ->through(fn (Pesan $message) => MessagePresenter::for($message, $kadiv));

        $divisis = Divisi::query()->ordered()->get();

        $recipientsQuery = User2::with(['karyawan.divisi', 'role'])
            ->where('id_user', '!=', $kadiv->id_user);

        if ($this->divisiId !== '') {
            $recipientsQuery->whereHas('karyawan', fn ($q) => $q->where('divisi_id_divisi', $this->divisiId));
        }

        $recipients = $recipientsQuery->get()
            ->sortBy(fn (User2 $user) => mb_strtolower($user->karyawan?->nama ?? $user->username))
            ->values();

        return view('livewire.kadiv.pesan-table', [
            'messages' => $messages,
            'recipients' => $recipients,
            'divisis' => $divisis,
        ]);
    }
}
