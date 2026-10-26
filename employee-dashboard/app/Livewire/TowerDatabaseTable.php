<?php

namespace App\Livewire;

use App\Models\TowerDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TowerDatabaseTable extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $jenis = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $dir = 'desc';

    // Modal Form States
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $formNama = '';
    public string $formKeterangan = '';
    public string $formJenis = 'file'; // 'file', 'nomor', 'link'
    public $formFile = null;
    public string $formNilaiKontak = '';
    public string $formEmailKontak = '';
    public string $formUrlLink = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJenis(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jenis']);
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

    public function canManage(): bool
    {
        $role = strtolower(auth()->user()?->role?->nama_role ?? '');
        return in_array($role, ['hrd', 'kadiv', 'admin'], true);
    }

    public function bukaModalTambah(): void
    {
        if (!$this->canManage()) {
            return;
        }

        $this->resetModalForm();
        $this->showModal = true;
    }

    public function bukaModalEdit(int $id): void
    {
        if (!$this->canManage()) {
            return;
        }

        $item = TowerDatabase::findOrFail($id);
        $this->editingId = $item->id;
        $this->formNama = $item->nama;
        $this->formKeterangan = $item->keterangan ?? '';
        $this->formJenis = $item->jenis;
        $this->formNilaiKontak = $item->nilai_kontak ?? '';
        $this->formEmailKontak = $item->email_kontak ?? '';
        $this->formUrlLink = $item->url_link ?? '';
        $this->formFile = null;

        $this->showModal = true;
    }

    public function tutupModal(): void
    {
        $this->showModal = false;
        $this->resetModalForm();
    }

    private function resetModalForm(): void
    {
        $this->reset([
            'editingId',
            'formNama',
            'formKeterangan',
            'formJenis',
            'formFile',
            'formNilaiKontak',
            'formEmailKontak',
            'formUrlLink',
        ]);
        $this->formJenis = 'file';
        $this->resetErrorBag();
    }

    public function simpan(): void
    {
        if (!$this->canManage()) {
            return;
        }

        $rules = [
            'formNama'       => 'required|string|max:255',
            'formKeterangan' => 'nullable|string',
            'formJenis'      => 'required|in:file,nomor,link',
        ];

        if ($this->formJenis === 'file') {
            if (!$this->editingId) {
                $rules['formFile'] = 'required|file|max:51200'; // max 50 MB
            } else {
                $rules['formFile'] = 'nullable|file|max:51200';
            }
        } elseif ($this->formJenis === 'nomor') {
            $rules['formNilaiKontak'] = 'required|string|max:100';
            $rules['formEmailKontak'] = 'nullable|email|max:100';
        } elseif ($this->formJenis === 'link') {
            $rules['formUrlLink'] = 'required|url|max:500';
        }

        $this->validate($rules, [
            'formNama.required'        => 'Nama item wajib diisi.',
            'formFile.required'        => 'Silakan pilih file yang akan diunggah.',
            'formFile.max'             => 'Ukuran file maksimal 50 MB.',
            'formNilaiKontak.required' => 'Nomor telepon / kontak wajib diisi.',
            'formEmailKontak.email'    => 'Format email tidak valid.',
            'formUrlLink.required'     => 'URL tautan wajib diisi.',
            'formUrlLink.url'          => 'Format URL tidak valid (harus diawali http:// atau https://).',
        ]);

        $karyawanId = auth()->user()?->karyawan_id_karyawan;

        if ($this->editingId) {
            $item = TowerDatabase::findOrFail($this->editingId);
            $item->nama = $this->formNama;
            $item->keterangan = $this->formKeterangan ?: null;
            $item->jenis = $this->formJenis;

            if ($this->formJenis === 'file') {
                if ($this->formFile) {
                    if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                        Storage::disk('public')->delete($item->file_path);
                    }
                    $storedPath = $this->formFile->store('tower_files', 'public');
                    $item->file_path = $storedPath;
                    $item->file_name = $this->formFile->getClientOriginalName();
                    $item->file_ext  = strtolower($this->formFile->getClientOriginalExtension());
                    $bytes = $this->formFile->getSize();
                    $item->file_size = $this->formatBytes($bytes);
                }
                $item->nilai_kontak = null;
                $item->email_kontak = null;
                $item->url_link = null;
            } elseif ($this->formJenis === 'nomor') {
                $item->nilai_kontak = $this->formNilaiKontak;
                $item->email_kontak = $this->formEmailKontak ?: null;
                $item->url_link = null;
            } elseif ($this->formJenis === 'link') {
                $item->url_link = $this->formUrlLink;
                $item->nilai_kontak = null;
                $item->email_kontak = null;
            }

            $item->save();
            session()->flash('tower_success', 'Data berhasil diperbarui.');
        } else {
            $data = [
                'nama'                    => $this->formNama,
                'keterangan'              => $this->formKeterangan ?: null,
                'jenis'                   => $this->formJenis,
                'dibuat_oleh_id_karyawan' => $karyawanId,
            ];

            if ($this->formJenis === 'file' && $this->formFile) {
                $storedPath = $this->formFile->store('tower_files', 'public');
                $data['file_path'] = $storedPath;
                $data['file_name'] = $this->formFile->getClientOriginalName();
                $data['file_ext']  = strtolower($this->formFile->getClientOriginalExtension());
                $bytes = $this->formFile->getSize();
                $data['file_size'] = $this->formatBytes($bytes);
            } elseif ($this->formJenis === 'nomor') {
                $data['nilai_kontak'] = $this->formNilaiKontak;
                $data['email_kontak'] = $this->formEmailKontak ?: null;
            } elseif ($this->formJenis === 'link') {
                $data['url_link'] = $this->formUrlLink;
            }

            TowerDatabase::create($data);
            session()->flash('tower_success', 'Data baru berhasil ditambahkan.');
        }

        $this->tutupModal();
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        if (!$this->canManage()) {
            return;
        }

        $item = TowerDatabase::findOrFail($id);
        if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
            Storage::disk('public')->delete($item->file_path);
        }
        $item->delete();

        session()->flash('tower_success', 'Data berhasil dihapus.');
    }

    public function downloadFile(int $id)
    {
        $item = TowerDatabase::findOrFail($id);
        if (!$item->file_path || !Storage::disk('public')->exists($item->file_path)) {
            session()->flash('tower_error', 'File fisik tidak ditemukan pada server.');
            return null;
        }

        return Storage::disk('public')->download($item->file_path, $item->file_name ?: 'dokumen');
    }

    private function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function render()
    {
        $query = TowerDatabase::query();

        if ($this->search !== '') {
            $s = '%' . mb_strtolower(trim($this->search)) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(nama) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(keterangan) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(nilai_kontak) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(email_kontak) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(url_link) LIKE ?', [$s]);
            });
        }

        if ($this->jenis !== '') {
            $query->where('jenis', $this->jenis);
        }

        $sortCol = in_array($this->sort, ['nama', 'jenis', 'created_at'], true) ? $this->sort : 'created_at';
        $sortDir = strtolower($this->dir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $sortDir);

        $items = $query->paginate(10);

        // Counter untuk badge filter jenis
        $counts = [
            'total' => TowerDatabase::count(),
            'file'  => TowerDatabase::where('jenis', 'file')->count(),
            'nomor' => TowerDatabase::where('jenis', 'nomor')->count(),
            'link'  => TowerDatabase::where('jenis', 'link')->count(),
        ];

        return view('livewire.tower-database-table', [
            'items'     => $items,
            'counts'    => $counts,
            'canManage' => $this->canManage(),
            'sort'      => $this->sort,
            'dir'       => $this->dir,
            'showModal' => $this->showModal,
        ]);
    }
}
