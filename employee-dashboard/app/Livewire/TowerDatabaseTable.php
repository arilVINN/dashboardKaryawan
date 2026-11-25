<?php

namespace App\Livewire;

use App\Models\Divisi;
use App\Models\TowerDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TowerDatabaseTable extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $jenis = '';

    #[Url(as: 'akses')]
    public string $filterAkses = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $dir = 'desc';

    public int $perPage = 5;

    // Modal Form States
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $formNama = '';
    public string $formKeterangan = '';
    public string $formJenis = 'file'; // 'file', 'nomor', 'link'
    public string $formAkses = 'general'; // 'general' atau 'divisi'
    public ?string $formDivisiId = null;
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

    public function updatedFilterAkses(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [5, 10, 25, 50], true)) {
            $this->perPage = 5;
        }

        $this->resetPage();
    }

    public function updatedFormJenis(string $value): void
    {
        if ($value !== 'file') {
            $this->clearTemporaryFile();
        }
    }

    private function clearTemporaryFile(): void
    {
        if ($this->formFile instanceof TemporaryUploadedFile) {
            try {
                $this->formFile->delete();
            } catch (\Throwable) {
                // Ignore stale Livewire temp uploads.
            }
        }

        $this->formFile = null;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jenis', 'filterAkses']);
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

    public function currentUserRole(): string
    {
        return strtolower(auth()->user()?->role?->nama_role ?? '');
    }

    public function isExecutiveRole(): bool
    {
        return in_array($this->currentUserRole(), ['hrd', 'admin', 'direktur', 'direksi', 'superadmin'], true);
    }

    public function currentUserDivisiId(): ?string
    {
        return auth()->user()?->karyawan?->divisi_id_divisi;
    }

    public function canManage(): bool
    {
        // Direktur/Direksi are read-only monitors: they can read every item
        // (see canAccessItem) but may not create/update/delete them.
        return in_array($this->currentUserRole(), ['hrd', 'kadiv', 'admin', 'superadmin'], true);
    }

    public function canAccessItem(TowerDatabase $item): bool
    {
        if ($this->isExecutiveRole()) {
            return true;
        }

        if ($item->isGeneral()) {
            return true;
        }

        $userDivisiId = $this->currentUserDivisiId();
        return $userDivisiId && $item->divisi_id_divisi === $userDivisiId;
    }

    public function canManageItem(TowerDatabase $item): bool
    {
        if (!$this->canManage()) {
            return false;
        }

        if ($this->isExecutiveRole()) {
            return true;
        }

        if ($item->isGeneral()) {
            return true;
        }

        $userDivisiId = $this->currentUserDivisiId();
        return $userDivisiId && $item->divisi_id_divisi === $userDivisiId;
    }

    public function bukaModalTambah(): void
    {
        if (!$this->canManage()) {
            return;
        }

        $this->resetModalForm();
        $this->formAkses = 'general';
        $this->formDivisiId = $this->currentUserDivisiId();
        $this->showModal = true;
    }

    public function bukaModalEdit(int $id): void
    {
        if (!$this->canManage()) {
            return;
        }

        $item = TowerDatabase::findOrFail($id);
        if (!$this->canManageItem($item)) {
            session()->flash('tower_error', 'Anda tidak memiliki hak untuk mengedit data ini.');
            return;
        }

        $this->editingId = $item->id;
        $this->formNama = $item->nama;
        $this->formKeterangan = $item->keterangan ?? '';
        $this->formJenis = $item->jenis;
        $this->formNilaiKontak = $item->nilai_kontak ?? '';
        $this->formEmailKontak = $item->email_kontak ?? '';
        $this->formUrlLink = $item->url_link ?? '';
        $this->formFile = null;

        if ($item->divisi_id_divisi) {
            $this->formAkses = 'divisi';
            $this->formDivisiId = $item->divisi_id_divisi;
        } else {
            $this->formAkses = 'general';
            $this->formDivisiId = $this->currentUserDivisiId();
        }

        $this->showModal = true;
    }

    public function tutupModal(): void
    {
        $this->clearTemporaryFile();
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
            'formAkses',
            'formDivisiId',
            'formFile',
            'formNilaiKontak',
            'formEmailKontak',
            'formUrlLink',
        ]);
        $this->formJenis = 'file';
        $this->formAkses = 'general';
        $this->formFile = null;
        $this->resetErrorBag();
    }

    public function simpan(): void
    {
        if (!$this->canManage()) {
            return;
        }

        try {
            if ($this->formJenis === 'file' && $this->formFile instanceof TemporaryUploadedFile) {
                $path = $this->formFile->getRealPath();
                if (! is_string($path) || ! is_file($path)) {
                    $this->clearTemporaryFile();
                    $this->addError('formFile', 'File upload tidak valid atau sudah kedaluwarsa. Silakan pilih ulang file.');
                    return;
                }
            }

            $rules = [
                'formNama'       => 'required|string|max:255',
                'formKeterangan' => 'nullable|string',
                'formJenis'      => 'required|in:file,nomor,link',
                'formAkses'      => 'required|in:general,divisi',
                'formDivisiId'   => 'nullable|required_if:formAkses,divisi|exists:divisis,id_divisi',
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

            // Jika role non-eksekutif (seperti Kadiv), divisi otomatis terkunci ke divisi miliknya
            if (! $this->isExecutiveRole() && $this->formAkses === 'divisi') {
                $this->formDivisiId = $this->currentUserDivisiId();
            }

            $this->validate($rules, [
                'formNama.required'        => 'Nama item wajib diisi.',
                'formFile.required'        => 'Silakan pilih file yang akan diunggah.',
                'formFile.max'             => 'Ukuran file maksimal 50 MB.',
                'formNilaiKontak.required' => 'Nomor telepon / kontak wajib diisi.',
                'formEmailKontak.email'    => 'Format email tidak valid.',
                'formUrlLink.required'     => 'URL tautan wajib diisi.',
                'formUrlLink.url'          => 'Format URL tidak valid (harus diawali http:// atau https://).',
                'formDivisiId.required_if' => 'Silakan pilih divisi untuk arsip khusus divisi.',
                'formDivisiId.exists'      => 'Divisi yang dipilih tidak valid.',
            ]);

            $karyawanId = auth()->user()?->karyawan_id_karyawan;

            // Tentukan divisi target:
            $targetDivisiId = null;
            if ($this->formAkses === 'divisi') {
                if ($this->isExecutiveRole()) {
                    $targetDivisiId = $this->formDivisiId;
                } else {
                    $targetDivisiId = $this->currentUserDivisiId();
                }
            }

            if ($this->editingId) {
                $item = TowerDatabase::findOrFail($this->editingId);
                if (!$this->canManageItem($item)) {
                    session()->flash('tower_error', 'Anda tidak memiliki hak untuk mengedit data ini.');
                    return;
                }

                $item->nama = $this->formNama;
                $item->keterangan = $this->formKeterangan ?: null;
                $item->jenis = $this->formJenis;
                $item->divisi_id_divisi = $targetDivisiId;

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
                    'divisi_id_divisi'        => $targetDivisiId,
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
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            $this->clearTemporaryFile();
            $this->addError('formFile', 'File tidak valid atau gagal diproses. Pastikan file yang dipilih sesuai format dan ukuran yang diizinkan.');
            session()->flash('tower_error', 'Data gagal dikirim. Periksa kembali file yang Anda pilih.');
        }
    }

    public function hapus(int $id): void
    {
        if (!$this->canManage()) {
            return;
        }

        $item = TowerDatabase::findOrFail($id);
        if (! $this->canManageItem($item)) {
            session()->flash('tower_error', 'Anda tidak memiliki hak untuk menghapus data ini.');
            return;
        }

        if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
            Storage::disk('public')->delete($item->file_path);
        }
        $item->delete();

        session()->flash('tower_success', 'Data berhasil dihapus.');
    }

    public function downloadFile(int $id)
    {
        $item = TowerDatabase::findOrFail($id);
        if (! $this->canAccessItem($item)) {
            session()->flash('tower_error', 'Anda tidak memiliki izin akses untuk mengunduh dokumen divisi ini.');
            return null;
        }

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
        $user = auth()->user();
        $userDivisiId = $this->currentUserDivisiId();
        $isExecutive = $this->isExecutiveRole();

        $baseScope = TowerDatabase::with(['divisi', 'dibuatOleh']);

        // Filter visibilitas berdasarkan hak akses:
        // Staff & Kadiv hanya boleh melihat data General (divisi_id_divisi is null) ATAU divisi mereka sendiri
        if (! $isExecutive) {
            $baseScope->where(function ($q) use ($userDivisiId) {
                $q->whereNull('divisi_id_divisi');
                if ($userDivisiId) {
                    $q->orWhere('divisi_id_divisi', $userDivisiId);
                }
            });
        }

        $query = clone $baseScope;

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

        // Filter akses / divisi
        if ($this->filterAkses === 'general') {
            $query->whereNull('divisi_id_divisi');
        } elseif ($this->filterAkses === 'my-division') {
            if ($userDivisiId) {
                $query->where('divisi_id_divisi', $userDivisiId);
            } else {
                $query->whereRaw('1=0');
            }
        } elseif ($this->filterAkses !== '') {
            $query->where('divisi_id_divisi', $this->filterAkses);
        }

        $sortCol = in_array($this->sort, ['nama', 'jenis', 'created_at'], true) ? $this->sort : 'created_at';
        $sortDir = strtolower($this->dir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $sortDir);

        $items = $query->paginate($this->perPage);

        // Counter untuk badge filter jenis & akses
        $counts = [
            'total'   => (clone $baseScope)->count(),
            'file'    => (clone $baseScope)->where('jenis', 'file')->count(),
            'nomor'   => (clone $baseScope)->where('jenis', 'nomor')->count(),
            'link'    => (clone $baseScope)->where('jenis', 'link')->count(),
            'general' => (clone $baseScope)->whereNull('divisi_id_divisi')->count(),
            'divisi'  => (clone $baseScope)->whereNotNull('divisi_id_divisi')->count(),
        ];

        $daftarDivisi = Divisi::where('status_aktif', 'Aktif')->orderBy('nama_divisi')->get();
        $userDivisi = $user?->karyawan?->divisi;

        return view('livewire.tower-database-table', [
            'items'        => $items,
            'counts'       => $counts,
            'canManage'    => $this->canManage(),
            'isExecutive'  => $isExecutive,
            'userDivisi'   => $userDivisi,
            'daftarDivisi' => $daftarDivisi,
            'sort'         => $this->sort,
            'dir'          => $this->dir,
            'showModal'    => $this->showModal,
        ]);
    }
}
