<?php

namespace App\Livewire\Staff;

use App\Actions\SendStaffMessage;
use App\Models\Divisi;
use App\Models\User2;
use Livewire\Attributes\On;
use Livewire\Component;

class ComposeMessage extends Component
{
    public bool $showModal = false;

    public string $divisiId = '';

    public string $recipientUserId = '';

    public string $judul = '';

    public string $isi = '';

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

    public function updatedDivisiId(): void
    {
        $this->recipientUserId = '';
        $this->resetValidation('recipientUserId');
    }

    #[On('open-compose-message')]
    public function openModal(): void
    {
        $this->reset(['divisiId', 'recipientUserId', 'judul', 'isi']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function send(SendStaffMessage $send): void
    {
        $this->validate();

        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'staff') {
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

        $send->handle($user, $recipient, $this->judul, $this->isi);

        $this->showModal = false;
        $this->reset(['divisiId', 'recipientUserId', 'judul', 'isi']);
        session()->flash('success', 'Pesan berhasil dikirim.');
        $this->redirectRoute('pesan');
    }

    public function render()
    {
        $divisis = Divisi::query()
            ->orderBy('nama_divisi')
            ->get();

        $recipients = collect();

        if ($this->divisiId !== '') {
            $recipients = User2::with(['karyawan', 'role'])
                ->where('id_user', '!=', auth()->id())
                ->whereHas('karyawan', fn ($query) => $query->where('divisi_id_divisi', $this->divisiId))
                ->get()
                ->sortBy(fn (User2 $user) => mb_strtolower($user->karyawan?->nama ?? $user->username))
                ->values();
        }

        return view('livewire.staff.compose-message', [
            'divisis' => $divisis,
            'recipients' => $recipients,
        ]);
    }
}
