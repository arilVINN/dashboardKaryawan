<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\User2;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorInstance;

/**
 * Validation rules for employee writes. Used by both the Livewire form and
 * the legacy JSON endpoints so the two paths cannot drift apart.
 */
final class EmployeeValidation
{
    /**
     * @param  bool  $strict  The form marks email/tanggal masuk/status as
     *                        required; legacy API clients keep the historical
     *                        nullable rules.
     */
    public static function rules(bool $create, ?string $karyawanId, bool $strict = true): array
    {
        $usernameUnique = Rule::unique('users2', 'username');
        if (! $create && $karyawanId) {
            $userId = User2::where('karyawan_id_karyawan', $karyawanId)->value('id_user');
            $usernameUnique->ignore($userId, 'id_user');
        }

        $emailUnique = Rule::unique('karyawans', 'email');
        if (! $create && $karyawanId) {
            $emailUnique->ignore($karyawanId, 'id_karyawan');
        }

        return [
            'nama' => array_merge(self::required($create), ['string', 'max:100']),
            'jenis_kelamin' => array_merge(self::required($create), [Rule::in(config('employee.jenis_kelamin'))]),
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'tanggal_masuk' => array_merge(
                $create && $strict ? ['required'] : self::nullable($create),
                ['date'],
            ),
            'tanggal_akhir_kontrak' => array_merge(self::nullable($create), ['date']),
            'no_rekening' => array_merge(self::nullable($create), ['string', 'max:50']),
            'no_telepon' => array_merge(self::nullable($create), ['string', 'max:20']),
            'email' => array_merge(
                $create && $strict ? ['required'] : self::nullable($create),
                ['email', 'max:100', $emailUnique],
            ),
            'alamat' => array_merge(self::nullable($create), ['string']),
            'divisi_id_divisi' => array_merge(self::required($create), ['string', 'max:20', 'exists:divisis,id_divisi']),
            'jabatan_id' => array_merge(self::required($create), ['integer', 'exists:jabatan,id']),
            'pendidikan' => array_merge(self::nullable($create), [Rule::in(Karyawan::pendidikanOptions())]),
            'job_level' => array_merge(self::nullable($create), [Rule::in(Karyawan::jobLevelOptions())]),
            'jobdesk' => array_merge(self::nullable($create), ['string', 'max:50']),
            'status_aktif' => array_merge(
                $strict ? self::required($create) : self::nullable($create),
                [Rule::in(Karyawan::STATUS_OPTIONS)],
            ),
            'role_id_role' => array_merge(self::nullable($create), ['string', 'exists:roles,id_role']),
            'username' => array_merge(self::required($create), ['string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', $usernameUnique]),
            'password' => $create
                ? ['required', 'string', 'min:8']
                : ['sometimes', 'nullable', 'string', 'min:8'],
            'must_change_password' => ['sometimes', 'boolean'],
            'foto' => ['nullable', 'file', 'mimetypes:'.implode(',', config('employee.photo.types')), 'max:'.config('employee.photo.max_kb')],
            'lampiran' => array_merge(self::nullable($create), ['array']),
            'lampiran.*' => [
                'nullable',
                'file',
                'mimetypes:'.implode(',', config('employee.document.types')),
                'max:'.config('employee.document.max_kb'),
            ],
            'hapus_foto' => ['sometimes', 'boolean'],
            'hapus_lampiran' => ['sometimes', 'array'],
            'hapus_lampiran.*' => ['sometimes', 'boolean'],
        ];
    }

    public static function make(array $data, bool $create, ?string $karyawanId, bool $strict = true): ValidatorInstance
    {
        $validator = Validator::make(
            $data,
            self::rules($create, $karyawanId, $strict),
            self::messages($strict),
            self::attributes(),
        );

        $validator->after(function (ValidatorInstance $validator) use ($data, $create, $karyawanId): void {
            self::checkInvariants($validator, $data, $create, $karyawanId);
        });

        return $validator;
    }

    private static function required(bool $create): array
    {
        return $create ? ['required'] : ['sometimes', 'required'];
    }

    private static function nullable(bool $create): array
    {
        return $create ? ['nullable'] : ['sometimes', 'nullable'];
    }

    private static function checkInvariants(ValidatorInstance $validator, array $data, bool $create, ?string $karyawanId): void
    {
        $masuk = $data['tanggal_masuk'] ?? null;
        $akhir = $data['tanggal_akhir_kontrak'] ?? null;
        if ($masuk && $akhir && $akhir < $masuk) {
            $validator->errors()->add('tanggal_akhir_kontrak', 'Tanggal akhir kontrak tidak boleh sebelum tanggal masuk.');
        }

        // Unique checks are case-insensitive and ignore surrounding spaces.
        $username = isset($data['username']) ? strtolower(trim((string) $data['username'])) : null;
        if ($username !== null && $username !== '') {
            $query = User2::query()->whereRaw('LOWER(TRIM(username)) = ?', [$username]);
            if (! $create && $karyawanId) {
                $userId = User2::where('karyawan_id_karyawan', $karyawanId)->value('id_user');
                if ($userId) {
                    $query->where('id_user', '!=', $userId);
                }
            }
            if ($query->exists()) {
                $validator->errors()->add('username', 'Username sudah digunakan. Silakan pilih username lain.');
            }
        }

        $email = isset($data['email']) && $data['email'] !== null ? strtolower(trim((string) $data['email'])) : null;
        if ($email !== null && $email !== '') {
            $query = Karyawan::query()->whereRaw('LOWER(TRIM(email)) = ?', [$email]);
            if (! $create && $karyawanId) {
                $query->where('id_karyawan', '!=', $karyawanId);
            }
            if ($query->exists()) {
                $validator->errors()->add('email', 'Email sudah digunakan karyawan lain. Gunakan email lain atau kosongkan.');
            }
        }

        $divisi = $data['divisi_id_divisi'] ?? null;
        $jabatanId = $data['jabatan_id'] ?? null;
        if (! $divisi || ! $jabatanId) {
            return;
        }

        $jabatan = Jabatan::find($jabatanId);
        if (! $jabatan) {
            return;
        }

        if ($jabatan->division_id !== $divisi) {
            $validator->errors()->add('jabatan_id', 'Jabatan ini tidak termasuk divisi yang dipilih.');

            return;
        }

        $currentJabatanId = $create ? null : Karyawan::whereKey($karyawanId)->value('jabatan_id');
        if (! $jabatan->aktif && (int) $jabatanId !== (int) $currentJabatanId) {
            $validator->errors()->add('jabatan_id', 'Jabatan ini sudah nonaktif dan tidak dapat dipilih.');
        }
    }

    public static function messages(bool $strict = true): array
    {
        return [
            'nama.required' => $strict ? 'Nama lengkap wajib diisi.' : 'Nama karyawan wajib diisi.',
            'nama.max' => 'Nama karyawan maksimal 100 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'tanggal_akhir_kontrak.date' => 'Tanggal akhir kontrak tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah digunakan karyawan lain. Gunakan email lain atau kosongkan.',
            'divisi_id_divisi.required' => 'Divisi wajib dipilih.',
            'divisi_id_divisi.exists' => 'Divisi tidak ditemukan.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan.',
            'status_aktif.required' => 'Status wajib dipilih.',
            'pendidikan.in' => 'Pendidikan harus salah satu dari pilihan yang tersedia.',
            'job_level.in' => 'Job level harus salah satu dari pilihan yang tersedia.',
            'jobdesk.max' => 'Keterangan jobdesk maksimal 50 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.regex' => 'Username hanya boleh huruf, angka, titik, garis bawah, dan tanda hubung.',
            'username.unique' => 'Username sudah digunakan. Silakan pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'foto.mimetypes' => 'Foto profil harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto profil maksimal 2 MB.',
            'lampiran.*.mimetypes' => 'Format dokumen harus PDF, JPG, atau PNG.',
            'lampiran.*.max' => 'Ukuran dokumen maksimal 5 MB per file.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'nama' => 'nama lengkap',
            'jenis_kelamin' => 'jenis kelamin',
            'tanggal_lahir' => 'tanggal lahir',
            'tanggal_masuk' => 'tanggal masuk',
            'tanggal_akhir_kontrak' => 'tanggal akhir kontrak',
            'no_rekening' => 'no. rekening',
            'no_telepon' => 'nomor telepon',
            'divisi_id_divisi' => 'divisi',
            'jabatan_id' => 'jabatan',
            'pendidikan' => 'pendidikan terakhir',
            'job_level' => 'job level',
            'jobdesk' => 'keterangan jobdesk',
            'status_aktif' => 'status',
            'foto' => 'foto profil',
        ];
    }
}
