<?php

namespace App\Livewire\Staff;

use App\Actions\SendStaffMessage;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User2;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Component;

class ComposeMessage extends Component
{
    public bool $showModal = false;

    public bool $listOpen = false;

    public bool $confirmDiscard = false;

    public bool $sending = false;

    public string $divisiId = '';

    public string $search = '';

    public string $recipientUserId = '';

    /** @var array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}|null */
    public ?array $recipient = null;

    public string $judul = '';

    public string $isi = '';

    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'divisiId' => ['required', 'string', 'exists:divisis,id_divisi'],
            'recipientUserId' => ['required', 'string', 'exists:users2,id_user'],
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'divisiId.required' => 'Pilih divisi terlebih dahulu.',
            'divisiId.exists' => 'Divisi yang dipilih tidak valid.',
            'recipientUserId.required' => 'Pilih penerima pesan.',
            'recipientUserId.exists' => 'Penerima yang dipilih tidak valid.',
            'judul.required' => 'Topik pesan wajib diisi.',
            'judul.max' => 'Topik pesan maksimal 200 karakter.',
            'isi.required' => 'Isi pesan wajib diisi.',
            'isi.max' => 'Isi pesan maksimal 5000 karakter.',
        ];
    }

    #[On('open-compose-message')]
    public function openModal(): void
    {
        $this->reset(['divisiId', 'search', 'recipientUserId', 'recipient', 'judul', 'isi', 'errorMessage']);
        $this->resetValidation();
        $this->listOpen = false;
        $this->confirmDiscard = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->dispatch('compose-message-closed');
    }

    #[On('compose-close-list')]
    public function closeList(): void
    {
        $this->listOpen = false;
    }

    public function openList(): void
    {
        if ($this->divisiId !== '' && ! $this->recipientUserId) {
            $this->listOpen = true;
        }
    }

    public function updatedSearch(): void
    {
        $this->listOpen = true;
    }

    public function updatedDivisiId(): void
    {
        $this->recipientUserId = '';
        $this->recipient = null;
        $this->search = '';
        $this->listOpen = $this->divisiId !== '';
        $this->resetValidation('recipientUserId');
    }

    public function selectRecipient(string $id): void
    {
        $karyawan = $this->eligibleKaryawan()
            ->whereHas('user', fn ($query) => $query->where('users2.id_user', $id))
            ->first();

        if (! $karyawan?->user) {
            $this->addError('recipientUserId', 'Penerima tidak valid di divisi ini.');

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
        $this->recipientUserId = '';
        $this->recipient = null;
        $this->search = '';
        $this->listOpen = false;
    }

    public function requestClose(): void
    {
        if ($this->divisiId !== '' || $this->recipientUserId || trim($this->judul) !== ''
            || trim($this->isi) !== '' || trim($this->search) !== '') {
            $this->confirmDiscard = true;

            return;
        }

        $this->closeModal();
    }

    public function cancelDiscard(): void
    {
        $this->confirmDiscard = false;
        $this->dispatch('compose-focus-cancel');
    }

    public function confirmDiscard(): void
    {
        $this->confirmDiscard = false;
        $this->closeModal();
    }

    /**
     * @return array<int, array{id: string, nama: string, jabatan: string, divisi: string, foto: ?string, initials: string}>
     */
    public function getResultsProperty(): array
    {
        if ($this->divisiId === '') {
            return [];
        }

        $term = mb_strtolower(trim($this->search));
        $query = $this->eligibleKaryawan();

        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->whereRaw('LOWER(nama) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%'.$term.'%'])
                    ->orWhereHas('divisi', fn ($q) => $q->whereRaw('LOWER(nama_divisi) LIKE ?', ['%'.$term.'%']));
            })->orderByRaw('LOWER(nama) = ? DESC', [$term]);
        }

        return $query->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(fn (Karyawan $karyawan) => $this->present($karyawan))
            ->all();
    }

    public function send(SendStaffMessage $send): void
    {
        if ($this->sending) {
            return;
        }

        $this->validate();

        $user = auth()->user();

        if (! $user instanceof User2
            || (strtolower($user->role?->nama_role ?? '') !== 'staff' && ! $user->isDirektur())) {
            abort(403);
        }

        $recipient = User2::with(['karyawan', 'role'])
            ->whereKey($this->recipientUserId)
            ->whereHas('karyawan', fn ($query) => $query->where('divisi_id_divisi', $this->divisiId))
            ->first();

        if (! $recipient || $recipient->id_user === $user->id_user) {
            $this->addError('recipientUserId', 'Pilih penerima yang valid di divisi ini.');

            return;
        }

        $rateKey = 'staff-pesan:'.$user->id_user;

        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->errorMessage = 'Terlalu banyak pesan dikirim. Tunggu sebentar sebelum mencoba lagi.';

            return;
        }

        $this->sending = true;

        try {
            RateLimiter::hit($rateKey, 60);
            $send->handle($user, $recipient, $this->judul, $this->isi);

            $this->dispatch('app-toast', message: 'Pesan terkirim', type: 'success');
            $this->dispatch('pesan-dikirim');
            $this->closeModal();
            $this->reset(['divisiId', 'search', 'recipientUserId', 'recipient', 'judul', 'isi', 'errorMessage']);
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'Gagal mengirim pesan. Periksa kembali lalu coba lagi.';
        } finally {
            $this->sending = false;
        }
    }

    public function render()
    {
        return view('livewire.staff.compose-message', [
            'divisis' => Divisi::query()->ordered()->get(),
        ]);
    }

    private function eligibleKaryawan()
    {
        $sender = auth()->user();

        return Karyawan::query()
            ->with(['user.role', 'divisi'])
            ->where('divisi_id_divisi', $this->divisiId)
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
            'jabatan' => $karyawan->jabatan ?: ($karyawan->user?->role?->nama_role ?? 'Staff'),
            'divisi' => $karyawan->divisi?->nama_divisi ?: '-',
            'foto' => $karyawan->foto ? route('hrd.karyawan.foto', $karyawan->id_karyawan) : null,
            'initials' => mb_strtoupper(mb_substr($nama, 0, 1)),
        ];
    }
}
