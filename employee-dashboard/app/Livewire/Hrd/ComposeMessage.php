<?php

namespace App\Livewire\Hrd;

use App\Events\PesanDikirim;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ComposeMessage extends Component
{
    public bool $showModal = false;

    public bool $listOpen = false;

    public bool $confirmDiscard = false;

    public bool $sending = false;

    public string $search = '';

    public ?string $recipientUserId = null;

    /** @var array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}|null */
    public ?array $recipient = null;

    public string $isi = '';

    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'recipientUserId' => 'required|string|exists:users2,id_user',
            'isi' => 'required|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'recipientUserId.required' => 'Pilih penerima terlebih dahulu.',
            'isi.required' => 'Isi pesan wajib diisi.',
            'isi.max' => 'Isi pesan maksimal 2000 karakter.',
        ];
    }

    #[On('open-compose-message')]
    public function open(): void
    {
        $this->showModal = true;
        $this->errorMessage = '';
        $this->resetValidation();
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

    public function updatedSearch(): void
    {
        $this->listOpen = true;
    }

    public function requestClose(): void
    {
        if ($this->recipientUserId || trim($this->isi) !== '' || trim($this->search) !== '') {
            $this->confirmDiscard = true;

            return;
        }

        $this->close();
    }

    public function keepDraft(): void
    {
        $this->confirmDiscard = false;
        $this->dispatch('compose-focus-cancel');
    }

    public function discardDraft(): void
    {
        $this->confirmDiscard = false;
        $this->close();
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->listOpen = false;
        $this->confirmDiscard = false;
        $this->errorMessage = '';
        $this->search = '';
        $this->recipientUserId = null;
        $this->recipient = null;
        $this->isi = '';
        $this->resetValidation();
        $this->dispatch('compose-message-closed');
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
        $this->search = '';
        $this->listOpen = false;
        $this->errorMessage = '';
        $this->resetErrorBag('recipientUserId');
    }

    public function clearRecipient(): void
    {
        $this->recipientUserId = null;
        $this->recipient = null;
        $this->search = '';
        $this->listOpen = false;
    }

    /**
     * @return array<int, array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}>
     */
    public function getResultsProperty(): array
    {
        $term = mb_strtolower(trim($this->search));
        $query = $this->eligibleKaryawan();

        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->whereRaw('LOWER(nama) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%'.$term.'%'])
                    ->orWhereHas('jabatanRef', fn ($q) => $q->whereRaw('LOWER(nama) LIKE ?', ['%'.$term.'%']))
                    ->orWhereHas('divisi', fn ($q) => $q->whereRaw('LOWER(nama_divisi) LIKE ?', ['%'.$term.'%']));
            })->orderByRaw('LOWER(nama) = ? DESC', [$term]);
        }

        return $query->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(fn (Karyawan $karyawan) => $this->present($karyawan))
            ->all();
    }

    /**
     * @return array<int, array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}>
     */
    public function getRecentProperty(): array
    {
        $sender = auth()->user();

        if (! $sender instanceof User2) {
            return [];
        }

        $ids = Pesan::query()
            ->where('pengirim_id_user', $sender->id_user)
            ->where('tipe', 'pesan')
            ->whereNotNull('penerima_id_user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('penerima_id_user')
            ->unique()
            ->take(3);

        if ($ids->isEmpty()) {
            return [];
        }

        return $this->eligibleKaryawan()
            ->whereHas('user', fn ($query) => $query->whereIn('users2.id_user', $ids))
            ->get()
            ->sortBy(fn (Karyawan $karyawan) => $ids->search($karyawan->user->id_user))
            ->map(fn (Karyawan $karyawan) => $this->present($karyawan))
            ->values()
            ->all();
    }

    public function send(): void
    {
        if ($this->sending) {
            return;
        }

        $this->validate();

        $sender = auth()->user();

        if (! $sender instanceof User2) {
            abort(403);
        }

        $recipient = User2::with(['role', 'karyawan.divisi', 'karyawan.jabatanRef'])
            ->findOrFail($this->recipientUserId);

        Gate::forUser($sender)->authorize('create', [Pesan::class, $recipient]);

        $key = 'hrd-pesan:'.$sender->id_user;

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->errorMessage = 'Terlalu banyak pesan dikirim. Tunggu sebentar sebelum mencoba lagi.';

            return;
        }

        $this->sending = true;

        try {
            RateLimiter::hit($key, 60);

            $isi = trim($this->isi);
            // The app derives the title from the message, so there is no title
            // field in this modal. Keep it within the documented 150 chars.
            $judul = Str::limit($isi, 150, '') ?: 'Pesan baru';

            $pesan = Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => strip_tags($judul),
                'deskripsi' => $isi,
                'tipe' => 'pesan',
                'tanggal_pesan' => now()->toDateString(),
                'pengirim_id_user' => $sender->id_user,
                'penerima_id_user' => $recipient->id_user,
            ]);

            // Dispatches the bell notification to the recipient.
            PesanDikirim::dispatch($pesan);

            $this->dispatch('app-toast', message: 'Pesan terkirim', type: 'success');
            $this->dispatch('pesan-dikirim');
            $this->close();
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'Gagal mengirim pesan. Periksa kembali lalu coba lagi.';
        } finally {
            $this->sending = false;
        }
    }

    public function render()
    {
        return view('livewire.hrd.compose-message');
    }

    private function eligibleKaryawan()
    {
        $sender = auth()->user();

        return Karyawan::query()
            ->with(['user.role', 'jabatanRef', 'divisi'])
            ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) IN (?, ?)', ['staff', 'kadiv']))
            ->whereHas('user', fn ($query) => $query->where('users2.id_user', '!=', $sender instanceof User2 ? $sender->id_user : ''))
            ->where('status_aktif', 'Aktif')
            ->where(function ($query): void {
                $query->where('aktif', true)->orWhereNull('aktif');
            });
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
            'jabatan' => $karyawan->jabatan ?: ($karyawan->jabatanRef?->nama ?: 'Staff'),
            'divisi' => $karyawan->divisi?->nama_divisi ?: '-',
            'foto' => $karyawan->foto ? route('hrd.karyawan.foto', $karyawan->id_karyawan) : null,
            'initials' => mb_strtoupper(mb_substr($nama, 0, 1)),
        ];
    }

    private function generateMessageId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid('', true), -8));
        } while (Pesan::where('id_pesan', $id)->exists());

        return $id;
    }
}
