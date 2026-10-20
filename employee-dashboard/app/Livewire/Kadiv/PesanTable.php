<?php

namespace App\Livewire\Kadiv;

use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\SuratIzin;
use App\Models\User2;
use App\Presenters\MessagePresenter;
use App\Queries\KadivMessageQuery;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;
    use WithFileUploads;

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

    public string $recipientUserId = '';
    public string $isi = '';

    // Properti untuk form Kirim Surat Izin
    public string $judulSurat = '';
    public string $tanggalIzin = '';
    public $fileSurat = null;
    public string $keteranganSurat = '';

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

    public function kirimSuratIzin(): void
    {
        $kadiv = $this->kadiv();

        $this->validate([
            'judulSurat' => 'required|string|max:100',
            'tanggalIzin' => 'required|date',
            'fileSurat' => 'nullable|file|max:20480',
            'keteranganSurat' => 'nullable|string|max:1000',
        ]);

        try {
            SuratIzin::create([
                'id_surat' => $this->generateSuratId(),
                'karyawan_id_karyawan' => $kadiv->karyawan->id_karyawan,
                'judul_surat' => $this->judulSurat,
                'tanggal_izin' => $this->tanggalIzin,
                'keterangan' => $this->keteranganSurat,
                'file' => $this->fileSurat ? $this->fileSurat->store('surat_izin', 'public') : null,
            ]);

            $this->reset(['judulSurat', 'tanggalIzin', 'fileSurat', 'keteranganSurat']);
            session()->flash('pesan_success', 'Surat izin berhasil dikirim.');
            $this->dispatch('tutup-modal-surat');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('kirimSuratIzin error: ' . $e->getMessage());
            $this->addError('judulSurat', 'Gagal mengirim surat izin: ' . $e->getMessage());
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

    private function generateSuratId(): string
    {
        do {
            $id = 'SRT' . strtoupper(substr(uniqid('', true), -10));
            $id = str_replace('.', '', $id);
        } while (SuratIzin::whereKey($id)->exists());

        return $id;
    }

    public function render()
    {
        $kadiv = $this->kadiv();
        $divisionId = $kadiv->karyawan->divisi_id_divisi;

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

        $recipients = User2::with(['karyawan', 'role'])
            ->where(function ($q) use ($divisionId): void {
                $q->whereHas('role', fn ($r) => $r->whereRaw('LOWER(nama_role) = ?', ['hrd']))
                    ->orWhere(function ($q2) use ($divisionId): void {
                        $q2->whereHas('role', fn ($r) => $r->whereRaw('LOWER(nama_role) = ?', ['staff']))
                            ->whereHas('karyawan', fn ($k) => $k->where('divisi_id_divisi', $divisionId));
                    });
            })
            ->get();

        return view('livewire.kadiv.pesan-table', ['messages' => $messages, 'recipients' => $recipients]);
    }
}