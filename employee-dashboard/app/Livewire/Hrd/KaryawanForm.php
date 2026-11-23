<?php

namespace App\Livewire\Hrd;

use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\User2;
use App\Services\EmployeeValidation;
use App\Services\EmployeeWriteService;
use App\Support\EmployeeAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

/**
 * One shared form for "Tambah karyawan" and "Edit karyawan".
 */
class KaryawanForm extends Component
{
    use WithFileUploads;

    public bool $show = false;
    public string $mode = 'create';
    public ?string $karyawanId = null;
    public ?string $divisiTerkunci = null;
    public ?string $judulTambah = null;

    // Data karyawan
    public string $nama = '';
    public string $jenisKelamin = 'Laki-laki';
    public ?string $tanggalLahir = null;
    public string $noTelepon = '';
    public string $email = '';
    public string $alamat = '';
    public string $noRekening = '';
    public $foto = null;
    public bool $hapusFoto = false;
    public ?string $fotoLama = null;

    // Kepegawaian
    public string $divisiId = '';
    public ?int $jabatanId = null;
    public string $jabatanNama = '';
    public ?string $tanggalMasuk = null;
    public ?string $tanggalAkhirKontrak = null;
    public string $pendidikan = '';
    public string $jobLevel = '';
    public string $statusAktif = 'Aktif';
    public string $jabatanResetNote = '';

    // Akun login
    public bool $hasAccount = false;
    public string $username = '';
    public string $password = '';
    public bool $showPassword = false;
    public bool $mustChangePassword = true;
    public string $usernameStatus = '';
    public ?string $generatedPassword = null;

    // Lampiran
    public bool $lampiranOpen = false;
    public array $lampiran = [];
    public array $hapusLampiran = [];
    public array $lampiranLama = [];

    // Dialog jabatan baru
    public bool $showJabatanDialog = false;
    public string $jabatanBaruNama = '';
    public ?int $jabatanDuplikatId = null;

    // Dialog atur ulang password
    public bool $showResetDialog = false;

    public function mount(?string $divisiTerkunci = null, ?string $judulTambah = null): void
    {
        $this->divisiTerkunci = $divisiTerkunci;
        $this->judulTambah = $judulTambah;
    }

    #[On('buka-karyawan-form')]
    public function buka(string $mode = 'create', ?string $id = null): void
    {
        $this->authorizeWrite();

        $this->resetErrorBag();
        $this->resetForm();
        $this->mode = $mode === 'edit' && $id ? 'edit' : 'create';

        if ($this->divisiTerkunci) {
            $this->divisiId = $this->divisiTerkunci;
        }

        if ($this->mode === 'edit') {
            $karyawan = Karyawan::with(['jabatanRef', 'divisi', 'user', 'lampiran'])->findOrFail($id);
            $this->karyawanId = $karyawan->id_karyawan;
            $this->nama = (string) $karyawan->nama;
            $this->jenisKelamin = (string) $karyawan->jenis_kelamin;
            $this->tanggalLahir = $karyawan->tanggal_lahir?->toDateString();
            $this->noTelepon = (string) ($karyawan->no_telepon ?? '');
            $this->email = (string) ($karyawan->email ?? '');
            $this->alamat = (string) ($karyawan->alamat ?? '');
            $this->noRekening = (string) ($karyawan->no_rekening ?? '');
            $this->fotoLama = $karyawan->foto;
            $this->divisiId = (string) $karyawan->divisi_id_divisi;
            $this->jabatanId = $karyawan->jabatan_id;
            $this->jabatanNama = (string) ($karyawan->jabatan ?? '');
            $this->tanggalMasuk = ($karyawan->tanggal_masuk ?? $karyawan->tanggal_rekrut)?->toDateString();
            $this->tanggalAkhirKontrak = $karyawan->tanggal_akhir_kontrak?->toDateString();
            $this->pendidikan = (string) ($karyawan->pendidikan ?? '');
            $this->jobLevel = (string) ($karyawan->job_level ?? '');
            $this->statusAktif = (string) ($karyawan->status_aktif ?? 'Aktif');
            $this->hasAccount = $karyawan->user !== null;
            $this->username = (string) ($karyawan->user?->username ?? '');
            $this->mustChangePassword = (bool) ($karyawan->user?->must_change_password ?? false);
            $this->lampiranLama = $karyawan->lampiran
                ->mapWithKeys(fn ($item) => [$item->jenis_lampiran => [
                    'id' => $item->id_lampiran,
                    'nama' => $item->nama_file,
                    'ukuran' => $this->formatUkuran($item),
                ]])
                ->all();
            $this->checkUsername();
        }

        $this->show = true;
        $this->dispatch('karyawan-modal-opened', mode: $this->mode);
    }

    public function tutup(): void
    {
        $this->show = false;
        $this->showJabatanDialog = false;
        $this->showResetDialog = false;
        $this->resetErrorBag();
        $this->dispatch('karyawan-modal-closed');
    }

    public function updatedDivisiId(): void
    {
        if ($this->jabatanId !== null) {
            $this->jabatanId = null;
            $this->jabatanNama = '';
            $this->jabatanResetNote = 'Jabatan direset karena divisi berubah.';
        }

        $this->jabatanDuplikatId = null;
        $this->jabatanBaruNama = '';
        $this->validateOnly('divisiId');
    }

    /**
     * Livewire only allows calls to methods defined on the component itself;
     * the inherited validateOnly() cannot be called from wire:blur directly.
     */
    public function validateField(string $field): void
    {
        if (! in_array($field, ['tanggalLahir', 'tanggalMasuk', 'tanggalAkhirKontrak'], true)) {
            return;
        }

        $this->validateOnly($field);
    }

    public function updatedUsername(): void
    {
        $this->checkUsername();
    }

    public function updatedNama(): void
    {
        if ($this->mode === 'create' && $this->username === '') {
            $this->username = app(EmployeeWriteService::class)->suggestUsername($this->nama);
            $this->checkUsername();
        }
    }

    public function generatePassword(): void
    {
        $this->password = app(EmployeeWriteService::class)->generateStrongPassword();
        $this->showPassword = true;
        $this->generatedPassword = $this->password;
        $this->validateOnly('password');
    }

    public function buatJabatanDialog(): void
    {
        $this->authorizeJabatan();
        $this->jabatanBaruNama = '';
        $this->jabatanDuplikatId = null;
        $this->showJabatanDialog = true;
        $this->resetErrorBag('jabatanBaruNama');
    }

    public function tutupJabatanDialog(): void
    {
        $this->showJabatanDialog = false;
    }

    public function simpanJabatanBaru(): void
    {
        $this->authorizeJabatan();

        $this->validateOnly('jabatanBaruNama', [
            'jabatanBaruNama' => ['required', 'string', 'max:80'],
        ], [
            'jabatanBaruNama.required' => 'Nama jabatan wajib diisi.',
            'jabatanBaruNama.max' => 'Nama jabatan maksimal 80 karakter.',
        ]);

        if ($this->divisiId === '') {
            $this->addError('jabatanBaruNama', 'Pilih divisi terlebih dahulu.');

            return;
        }

        $nama = Jabatan::normaliseNama($this->jabatanBaruNama);

        $existing = Jabatan::where('division_id', $this->divisiId)
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])
            ->first();

        if ($existing) {
            $this->jabatanDuplikatId = $existing->id;
            $this->addError('jabatanBaruNama', 'Jabatan ini sudah ada di divisi ini');

            return;
        }

        $jabatan = Jabatan::create([
            'division_id' => $this->divisiId,
            'nama' => $nama,
            'aktif' => true,
        ]);

        AuditLog::catat(auth()->user()?->id_user, 'tambah_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $this->divisiId,
            'nama' => $nama,
        ]);

        $this->jabatanId = $jabatan->id;
        $this->jabatanNama = $jabatan->nama;
        $this->jabatanResetNote = '';
        $this->showJabatanDialog = false;
        $this->jabatanDuplikatId = null;
        $this->resetErrorBag('jabatanBaruNama');
    }

    public function pilihJabatanDuplikat(): void
    {
        if (! $this->jabatanDuplikatId) {
            return;
        }

        $jabatan = Jabatan::findOrFail($this->jabatanDuplikatId);
        $this->jabatanId = $jabatan->id;
        $this->jabatanNama = $jabatan->nama;
        $this->showJabatanDialog = false;
        $this->jabatanDuplikatId = null;
        $this->resetErrorBag('jabatanBaruNama');
    }

    public function konfirmasiResetPassword(): void
    {
        $this->authorizeWrite();
        $this->showResetDialog = true;
        $this->generatedPassword = null;
        $this->dispatch('karyawan-reset-dialog-opened');
    }

    public function resetPassword(): void
    {
        $this->authorizeWrite();
        $karyawan = Karyawan::with('user')->findOrFail($this->karyawanId);

        $this->generatedPassword = app(EmployeeWriteService::class)->resetPassword($karyawan, auth()->user());
        $this->showResetDialog = false;
        $this->mustChangePassword = true;
        $this->password = '';
        $this->dispatch('karyawan-password-diatur');
    }

    public function simpan(): void
    {
        $this->authorizeWrite();
        $this->resetErrorBag();

        $payload = $this->payload();
        $service = app(EmployeeWriteService::class);

        try {
            if ($this->mode === 'edit' && $this->karyawanId) {
                $service->update(Karyawan::findOrFail($this->karyawanId), $payload, auth()->user(), true);
                $pesan = 'Perubahan disimpan';
            } else {
                $result = $service->create($payload, auth()->user(), true);
                $this->generatedPassword = $result['plain_password'];
                $pesan = 'Karyawan ditambahkan';
            }
        } catch (ValidationException $e) {
            $this->setErrorBag($this->mapErrors($e->errors()));
            $this->dispatch('karyawan-validation-error');

            return;
        } catch (Throwable $e) {
            report($e);
            $this->addError('nama', 'Data belum bisa disimpan. Silakan coba lagi.');
            $this->dispatch('karyawan-validation-error');

            return;
        }

        $this->show = false;
        $this->showResetDialog = false;
        $this->resetForm();
        $this->dispatch('toast', message: $pesan, type: 'success');
        $this->dispatch('karyawan-disimpan');
        $this->dispatch('karyawan-modal-closed');
    }

    /**
     * Rules used by Livewire's blur validation. The authoritative rules live
     * in EmployeeValidation; only the property names differ (camelCase).
     */
    public function rules(): array
    {
        $rules = EmployeeValidation::rules($this->mode === 'create', $this->karyawanId, true);
        $map = [
            'jabatan_id' => 'jabatanId',
            'divisi_id_divisi' => 'divisiId',
            'jenis_kelamin' => 'jenisKelamin',
            'tanggal_lahir' => 'tanggalLahir',
            'tanggal_masuk' => 'tanggalMasuk',
            'tanggal_akhir_kontrak' => 'tanggalAkhirKontrak',
            'no_telepon' => 'noTelepon',
            'no_rekening' => 'noRekening',
            'job_level' => 'jobLevel',
            'status_aktif' => 'statusAktif',
            'must_change_password' => 'mustChangePassword',
        ];

        $mapped = [];
        foreach ($rules as $field => $fieldRules) {
            $mapped[$map[$field] ?? $field] = $fieldRules;
        }

        // Only keep rules whose property actually exists on this component.
        return collect($mapped)
            ->filter(function ($rules, $key) {
                $base = explode('.', $key)[0];

                return property_exists($this, $base);
            })
            ->all();
    }

    public function messages(): array
    {
        return EmployeeValidation::messages(true);
    }

    #[Computed]
    public function daftarDivisi()
    {
        return Divisi::query()->ordered()->get();
    }

    #[Computed]
    public function jabatanOptions()
    {
        if ($this->divisiId === '') {
            return collect();
        }

        return Jabatan::query()
            ->where('division_id', $this->divisiId)
            ->where(function ($query) {
                $query->where('aktif', true);
                if ($this->mode === 'edit' && $this->jabatanId) {
                    $query->orWhere('id', $this->jabatanId);
                }
            })
            ->orderBy('nama')
            ->get()
            ->map(fn (Jabatan $j) => ['id' => $j->id, 'label' => $j->label(), 'aktif' => $j->aktif]);
    }

    #[Computed]
    public function canManageJabatan(): bool
    {
        return EmployeeAccess::canManage(auth()->user());
    }

    #[Computed]
    public function canChangeUsername(): bool
    {
        return $this->mode === 'create' || EmployeeAccess::canManage(auth()->user());
    }

    #[Computed]
    public function selectedDivisiNama(): string
    {
        return (string) (Divisi::find($this->divisiId)?->nama_divisi ?? '');
    }

    #[Computed]
    public function dokumenJenis(): array
    {
        return config('employee.document.jenis');
    }

    public function render()
    {
        return view('livewire.hrd.karyawan-form');
    }

    private function payload(): array
    {
        $payload = [
            'nama' => trim($this->nama),
            'jenis_kelamin' => $this->jenisKelamin,
            'tanggal_lahir' => $this->tanggalLahir ?: null,
            'tanggal_masuk' => $this->tanggalMasuk ?: null,
            'tanggal_akhir_kontrak' => $this->tanggalAkhirKontrak ?: null,
            'no_rekening' => trim($this->noRekening),
            'no_telepon' => trim($this->noTelepon),
            'email' => trim($this->email) ?: null,
            'alamat' => trim($this->alamat),
            'divisi_id_divisi' => $this->divisiId,
            'jabatan_id' => $this->jabatanId,
            'jabatan' => $this->jabatanNama,
            'pendidikan' => $this->pendidikan ?: null,
            'job_level' => $this->jobLevel ?: null,
            'status_aktif' => $this->statusAktif,
            'foto' => $this->foto,
            'hapus_foto' => $this->hapusFoto,
            'lampiran' => array_filter($this->lampiran),
            'hapus_lampiran' => $this->hapusLampiran,
        ];

        if ($this->mode === 'create') {
            $payload['username'] = trim($this->username);
            $payload['password'] = $this->password;
            $payload['must_change_password'] = $this->mustChangePassword;
        } else {
            // Only send the account fields the UI actually allows changing.
            if ($this->canChangeUsername && trim($this->username) !== '') {
                $payload['username'] = trim($this->username);
            }
            if ($this->password !== '') {
                $payload['password'] = $this->password;
                $payload['must_change_password'] = $this->mustChangePassword;
            }
        }

        return $payload;
    }

    private function checkUsername(): void
    {
        if (trim($this->username) === '') {
            $this->usernameStatus = '';

            return;
        }

        $tersedia = app(EmployeeWriteService::class)
            ->usernameTersedia($this->username, $this->karyawanId);
        $this->usernameStatus = $tersedia ? 'tersedia' : 'dipakai';
    }

    private function mapErrors(array $errors): array
    {
        $map = [
            'jabatan_id' => 'jabatanId',
            'divisi_id_divisi' => 'divisiId',
            'jenis_kelamin' => 'jenisKelamin',
            'tanggal_lahir' => 'tanggalLahir',
            'tanggal_masuk' => 'tanggalMasuk',
            'tanggal_akhir_kontrak' => 'tanggalAkhirKontrak',
            'no_telepon' => 'noTelepon',
            'no_rekening' => 'noRekening',
            'job_level' => 'jobLevel',
            'status_aktif' => 'statusAktif',
            'must_change_password' => 'mustChangePassword',
        ];

        $mapped = [];
        foreach ($errors as $field => $messages) {
            $mapped[$map[$field] ?? $field] = $messages;
        }

        return $mapped;
    }

    private function resetForm(): void
    {
        $this->karyawanId = null;
        $this->nama = '';
        $this->jenisKelamin = 'Laki-laki';
        $this->tanggalLahir = null;
        $this->noTelepon = '';
        $this->email = '';
        $this->alamat = '';
        $this->noRekening = '';
        $this->foto = null;
        $this->hapusFoto = false;
        $this->fotoLama = null;
        $this->divisiId = $this->divisiTerkunci ?? '';
        $this->jabatanId = null;
        $this->jabatanNama = '';
        $this->tanggalMasuk = null;
        $this->tanggalAkhirKontrak = null;
        $this->pendidikan = '';
        $this->jobLevel = '';
        $this->statusAktif = 'Aktif';
        $this->jabatanResetNote = '';
        $this->hasAccount = false;
        $this->username = '';
        $this->password = '';
        $this->showPassword = false;
        $this->mustChangePassword = true;
        $this->usernameStatus = '';
        $this->generatedPassword = null;
        $this->lampiran = [];
        $this->hapusLampiran = [];
        $this->lampiranLama = [];
        $this->lampiranOpen = false;
        $this->showJabatanDialog = false;
        $this->jabatanBaruNama = '';
        $this->jabatanDuplikatId = null;
        $this->showResetDialog = false;
    }

    private function formatUkuran($lampiran): string
    {
        $path = $lampiran->path_file;
        foreach (['private', 'public'] as $disk) {
            try {
                $size = \Illuminate\Support\Facades\Storage::disk($disk)->size($path);
                if ($size !== false) {
                    return round($size / 1024).' KB';
                }
            } catch (Throwable) {
            }
        }

        return '';
    }

    private function authorizeWrite(): void
    {
        if (! EmployeeAccess::canManage(auth()->user())) {
            abort(403);
        }
    }

    private function authorizeJabatan(): void
    {
        $this->authorizeWrite();
        Gate::authorize('create', Jabatan::class);
    }
}
