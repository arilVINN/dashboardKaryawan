<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\LampiranKaryawan;
use App\Models\Role;
use App\Models\User2;
use App\Support\AccountSecurity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * All employee/user writes go through here so the create + account pair is
 * always atomic and audited. Sensitive values (passwords, rekening numbers)
 * are never written to the audit log.
 */
final class EmployeeWriteService
{
    public function create(array $payload, User2 $actor, bool $strict = true): array
    {
        $validator = EmployeeValidation::make($payload, true, null, $strict);
        $validated = $validator->validate();

        $plainPassword = (string) $validated['password'];
        $storedPaths = [];
        $deletedPaths = [];

        try {
            $result = DB::transaction(function () use ($validated, $payload, $actor, $strict, &$storedPaths, &$deletedPaths, $plainPassword): array {
                $divisi = Divisi::findOrFail($validated['divisi_id_divisi']);

                if (! $divisi->canAcceptMember()) {
                    throw ValidationException::withMessages([
                        'divisi_id_divisi' => [$this->divisionCapacityMessage($divisi)],
                    ]);
                }

                $jabatan = Jabatan::findOrFail($validated['jabatan_id']);
                $roleId = $this->defaultRoleId($jabatan, $divisi, $validated['role_id_role'] ?? null);
                $this->assertRoleAllowed($roleId);

                $idKaryawan = $this->nextGeneratedId(Karyawan::class, 'id_karyawan', 'KR');
                $idUser = $this->nextGeneratedId(User2::class, 'id_user', 'US');

                $tanggalMasuk = $validated['tanggal_masuk'] ?? now()->toDateString();
                $tanggalAkhir = $validated['tanggal_akhir_kontrak'] ?? null;

                $karyawan = Karyawan::create([
                    'id_karyawan' => $idKaryawan,
                    'nama' => $validated['nama'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                    'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                    'tanggal_masuk' => $tanggalMasuk,
                    'tanggal_akhir_kontrak' => $tanggalAkhir,
                    'no_rekening' => $validated['no_rekening'] ?? null,
                    'no_telepon' => $validated['no_telepon'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                    'jabatan' => $jabatan->nama,
                    'jabatan_id' => $jabatan->id,
                    'status_aktif' => $validated['status_aktif'] ?? 'Aktif',
                    'aktif' => ($validated['status_aktif'] ?? 'Aktif') === 'Aktif',
                    'divisi_id_divisi' => $divisi->id_divisi,
                    'pendidikan' => $validated['pendidikan'] ?? null,
                    'job_level' => $validated['job_level'] ?? null,
                    'status_karyawan' => $tanggalAkhir ? 'Kontrak' : 'Tetap',
                ]);

                $fotoPath = $this->storeFoto($payload, $storedPaths);
                if ($fotoPath) {
                    $karyawan->update(['foto' => $fotoPath]);
                }

                $this->syncLampiran($karyawan, $payload, $deletedPaths, $storedPaths);

                $user = User2::create([
                    'id_user' => $idUser,
                    'username' => trim((string) $validated['username']),
                    'password' => Hash::make($plainPassword),
                    'must_change_password' => (bool) ($validated['must_change_password'] ?? false),
                    'session_version' => (string) Str::uuid(),
                    'role_id_role' => $roleId,
                    'karyawan_id_karyawan' => $karyawan->id_karyawan,
                ]);

                AuditLog::catat(
                    $actor->id_user,
                    'tambah_karyawan',
                    'karyawan',
                    $karyawan->id_karyawan,
                    ['bidang' => array_keys($this->employeeFields($payload))],
                );

                return [
                    'karyawan' => $karyawan->fresh(['jabatanRef', 'divisi']),
                    'user' => ['id_user' => $user->id_user, 'username' => $user->username, 'role' => $roleId],
                    'plain_password' => $plainPassword,
                ];
            });

            return $result;
        } catch (Throwable $e) {
            $this->deletePaths($storedPaths);
            throw $e;
        }
    }

    public function update(Karyawan $karyawan, array $payload, User2 $actor, bool $strict = true): Karyawan
    {
        $validator = EmployeeValidation::make($payload, false, $karyawan->id_karyawan, $strict);
        $validated = $validator->validate();

        if ($karyawan->tanggal_keluar !== null && ($validated['status_aktif'] ?? null) === 'Aktif') {
            throw ValidationException::withMessages([
                'status_aktif' => ['Karyawan yang sudah ditandai keluar tidak dapat diaktifkan kembali.'],
            ]);
        }

        $storedPaths = [];
        $deletedPaths = [];

        try {
            $updated = DB::transaction(function () use ($karyawan, $validated, $payload, $actor, &$storedPaths, &$deletedPaths): Karyawan {
                $targetDivisionId = $validated['divisi_id_divisi'] ?? $karyawan->divisi_id_divisi;
                $divisi = Divisi::findOrFail($targetDivisionId);

                if ($targetDivisionId !== $karyawan->divisi_id_divisi && $divisi->isGrouping()) {
                    throw ValidationException::withMessages([
                        'divisi_id_divisi' => [$this->divisionCapacityMessage($divisi)],
                    ]);
                }

                $changes = $this->employeeFields($payload);
                $changedKeys = array_keys(array_filter(
                    $changes,
                    fn ($value, $key) => $this->normaliseForCompare($value) !== $this->normaliseForCompare($karyawan->getAttribute($key)),
                    ARRAY_FILTER_USE_BOTH,
                ));

                $jabatanId = $validated['jabatan_id'] ?? $karyawan->jabatan_id;
                $jabatan = Jabatan::find($jabatanId);
                if ($jabatan) {
                    $changes['jabatan'] = $jabatan->nama;
                    $changes['jabatan_id'] = $jabatan->id;
                    if (! in_array('jabatan', $changedKeys, true) && $jabatan->nama !== $karyawan->jabatan) {
                        $changedKeys[] = 'jabatan';
                    }
                }

                if (array_key_exists('tanggal_akhir_kontrak', $validated)) {
                    $changes['status_karyawan'] = $validated['tanggal_akhir_kontrak'] ? 'Kontrak' : 'Tetap';
                }

                if (array_key_exists('status_aktif', $validated) && $karyawan->tanggal_keluar === null) {
                    $changes['aktif'] = $validated['status_aktif'] === 'Aktif';
                }

                // Keep historical status_karyawan when the form did not touch
                // the contract date (legacy API compatibility).
                if (! array_key_exists('tanggal_akhir_kontrak', $validated)) {
                    unset($changes['tanggal_akhir_kontrak']);
                }

                if ($changes) {
                    $karyawan->update($changes);

                    // Deactivating an employee must also cut off every API
                    // token, not just the web session.
                    if (($changes['status_aktif'] ?? null) === 'Tidak Aktif' && $karyawan->user) {
                        AccountSecurity::invalidate($karyawan->user);
                    }
                }

                $oldFoto = $karyawan->foto;
                $fotoPath = $this->storeFoto($payload, $storedPaths);
                if ($fotoPath) {
                    $karyawan->update(['foto' => $fotoPath]);
                    if ($oldFoto) {
                        $deletedPaths[] = $oldFoto;
                    }
                    $changedKeys[] = 'foto';
                } elseif (! empty($payload['hapus_foto'])) {
                    $karyawan->update(['foto' => null]);
                    if ($oldFoto) {
                        $deletedPaths[] = $oldFoto;
                    }
                    $changedKeys[] = 'foto';
                }

                $this->syncLampiran($karyawan, $payload, $deletedPaths, $storedPaths);

                $user = $karyawan->user;
                if ($user) {
                    $accountChanges = [];
                    if (isset($validated['username'])) {
                        $accountChanges['username'] = trim((string) $validated['username']);
                        $changedKeys[] = 'username';
                    }
                    // An explicit role_id_role from legacy clients is honoured
                    // unless a jabatan in the same request implies a role.
                    $roleId = isset($validated['role_id_role'])
                        ? (string) $validated['role_id_role']
                        : ($jabatan ? $this->defaultRoleId($jabatan, $divisi, null) : null);

                    if ($roleId) {
                        $this->assertRoleAllowed($roleId);
                        if ($roleId !== $user->role_id_role) {
                            if ($error = HrdProtectionService::validateRoleChange($actor, $karyawan, $roleId)) {
                                throw ValidationException::withMessages(['role_id_role' => [$error]]);
                            }
                            $accountChanges['role_id_role'] = $roleId;
                            $changedKeys[] = 'role';
                        }
                    }
                    $password = $validated['password'] ?? null;
                    if ($password) {
                        $accountChanges['password'] = Hash::make($password);
                        $accountChanges['must_change_password'] = (bool) ($validated['must_change_password'] ?? false);
                        $accountChanges['session_version'] = (string) Str::uuid();
                        $changedKeys[] = 'password';
                    }
                    if ($accountChanges) {
                        $user->update($accountChanges);
                    }
                } elseif (isset($validated['username']) || isset($validated['password'])) {
                    throw ValidationException::withMessages([
                        'username' => ['Akun login karyawan tidak ditemukan.'],
                    ]);
                }

                AuditLog::catat(
                    $actor->id_user,
                    'edit_karyawan',
                    'karyawan',
                    $karyawan->id_karyawan,
                    ['bidang' => array_values(array_unique($changedKeys))],
                );

                return $karyawan->fresh(['jabatanRef', 'divisi', 'user.role']);
            });

            $this->deletePaths($deletedPaths);

            return $updated;
        } catch (Throwable $e) {
            $this->deletePaths($storedPaths);
            throw $e;
        }
    }

    /**
     * Generate a strong temporary password, set the forced-change flag and
     * invalidate the target's existing sessions. Returns the plain password
     * so it can be shown once to the administrator.
     */
    public function resetPassword(Karyawan $karyawan, User2 $actor): string
    {
        $user = $karyawan->user;
        if (! $user) {
            throw ValidationException::withMessages([
                'username' => ['Karyawan ini belum memiliki akun login.'],
            ]);
        }

        $plain = $this->generateStrongPassword();

        DB::transaction(function () use ($user, $plain, $karyawan, $actor): void {
            $user->update([
                'password' => Hash::make($plain),
                'must_change_password' => true,
            ]);

            // Rotate the session version and revoke all Sanctum tokens so a
            // previously issued bearer token cannot keep using the old password.
            AccountSecurity::invalidate($user);

            AuditLog::catat(
                $actor->id_user,
                'reset_password_karyawan',
                'karyawan',
                $karyawan->id_karyawan,
                ['aksi' => 'atur_ulang_password'],
            );
        });

        return $plain;
    }

    public function changeOwnPassword(User2 $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);

        AccountSecurity::invalidate($user);
    }

    /**
     * Legacy API clients still post a plain `jabatan` string. Resolve it to a
     * division-scoped row so both paths share the same data model.
     */
    public function resolveLegacyJabatan(string $divisionId, string $nama): Jabatan
    {
        $nama = Jabatan::normaliseNama($nama);

        if ($nama === '') {
            throw ValidationException::withMessages(['jabatan' => ['Jabatan wajib dipilih.']]);
        }

        if (mb_strlen($nama) > 80) {
            $nama = mb_substr($nama, 0, 80);
        }

        return Jabatan::firstOrCreate(
            ['division_id' => $divisionId, 'nama' => $nama],
            ['aktif' => true],
        );
    }

    public function generateStrongPassword(int $length = 16): string
    {
        $password = Str::password(max(12, $length));

        // Str::password already mixes case, digits and symbols. The loop only
        // guards against a future implementation change.
        while (mb_strlen($password) < 12 || ! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password) || ! preg_match('/\d/', $password)) {
            $password = Str::password(max(12, $length));
        }

        return $password;
    }

    public function usernameTersedia(string $username, ?string $ignoreKaryawanId = null): bool
    {
        $username = strtolower(trim($username));
        if ($username === '') {
            return false;
        }

        $query = User2::query()->whereRaw('LOWER(TRIM(username)) = ?', [$username]);
        if ($ignoreKaryawanId) {
            $userId = User2::where('karyawan_id_karyawan', $ignoreKaryawanId)->value('id_user');
            if ($userId) {
                $query->where('id_user', '!=', $userId);
            }
        }

        return ! $query->exists();
    }

    public function suggestUsername(string $nama): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '.', strtolower(trim($nama))) ?? '';
        $slug = trim(preg_replace('/\.+/', '.', $slug) ?? '', '.');
        $slug = $slug !== '' ? $slug : 'karyawan';

        $base = $slug;
        $counter = 2;
        while (! $this->usernameTersedia($slug)) {
            $slug = $base.'.'.$counter;
            $counter++;
        }

        return $slug;
    }

    // ─── Internal helpers ─────────────────────────────────────────────────────

    private function employeeFields(array $payload): array
    {
        $keys = [
            'nama', 'jenis_kelamin', 'tanggal_lahir', 'tanggal_masuk',
            'tanggal_akhir_kontrak', 'no_rekening', 'no_telepon', 'email',
            'alamat', 'divisi_id_divisi', 'pendidikan', 'job_level', 'status_aktif',
        ];

        $fields = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                $fields[$key] = $payload[$key];
            }
        }

        return $fields;
    }

    private function normaliseForCompare(mixed $value): string
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->toDateString();
        }
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function defaultRoleId(Jabatan $jabatan, Divisi $divisi, ?string $requestedRoleId): string
    {
        $roleName = $this->roleNameForJabatan($jabatan->nama);

        if (! $roleName && strtoupper((string) $divisi->kode_divisi) === Divisi::KODE_HR) {
            $roleName = 'hrd';
        }
        $roleName ??= 'staff';

        $roleId = Role::whereRaw('LOWER(nama_role) = ?', [$roleName])->value('id_role');
        if (! $roleId && $requestedRoleId) {
            $roleId = $requestedRoleId;
        }
        if (! $roleId) {
            $roleId = Role::whereRaw('LOWER(nama_role) = ?', ['staff'])->value('id_role');
        }

        if (! $roleId) {
            throw ValidationException::withMessages([
                'jabatan_id' => ['Role '.$roleName.' belum tersedia. Tambahkan role tersebut terlebih dahulu.'],
            ]);
        }

        return (string) $roleId;
    }

    private function roleNameForJabatan(string $jabatan): ?string
    {
        $normalized = mb_strtolower(trim($jabatan));

        if (preg_match('/^(?:kepala\s+divisi|direktur\s+utama)(?:\s|$)/i', $normalized) === 1) {
            return 'kadiv';
        }
        if ($normalized === 'hrd manager' || str_contains($normalized, 'hrd')) {
            return 'hrd';
        }
        if (in_array($normalized, ['staff', 'wakil kepala divisi', 'markom', 'marcom', 'layouter'], true)
            || preg_match('/^(?:markom|marcom)(\s|$)/i', $normalized) === 1) {
            return 'staff';
        }

        return null;
    }

    private function assertRoleAllowed(string $roleId): void
    {
        $nama = strtolower((string) Role::where('id_role', $roleId)->value('nama_role'));
        if ($nama === 'superadmin') {
            throw ValidationException::withMessages([
                'role_id_role' => ['Akun superadmin hanya dapat dibuat melalui perintah artisan.'],
            ]);
        }
    }

    private function storeFoto(array $payload, array &$storedPaths): ?string
    {
        $file = $payload['foto'] ?? null;
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        $path = $file->store('foto-profil', 'private');
        if (! $path) {
            throw new \RuntimeException('Foto profil gagal disimpan.');
        }
        $storedPaths[] = $path;

        return $path;
    }

    private function syncLampiran(Karyawan $karyawan, array $payload, array &$deletedPaths, array &$storedPaths): void
    {
        $jenisList = array_keys(config('employee.document.jenis'));
        $existing = $karyawan->lampiran()->get()->keyBy('jenis_lampiran');
        $hapus = $payload['hapus_lampiran'] ?? [];

        foreach ($jenisList as $jenis) {
            $file = $payload['lampiran'][$jenis] ?? null;
            $shouldDelete = ! empty($hapus[$jenis]);

            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store('lampiran_karyawan', 'private');
                if (! $path) {
                    throw new \RuntimeException('Lampiran gagal disimpan.');
                }
                $storedPaths[] = $path;

                $old = $existing->get($jenis);
                if ($old) {
                    $deletedPaths[] = $old->path_file;
                    $old->delete();
                }

                LampiranKaryawan::create([
                    'karyawan_id_karyawan' => $karyawan->id_karyawan,
                    'jenis_lampiran' => $jenis,
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                ]);

                continue;
            }

            if ($shouldDelete && ($old = $existing->get($jenis))) {
                $deletedPaths[] = $old->path_file;
                $old->delete();
            }
        }
    }

    private function deletePaths(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            try {
                Storage::disk('private')->delete($path);
            } catch (Throwable) {
                // Storage cleanup is best-effort; the database is already safe.
            }
        }
    }

    private function nextGeneratedId(string $modelClass, string $column, string $prefix, int $pad = 3): string
    {
        $max = 0;
        $modelClass::query()
            ->where($column, 'like', $prefix.'%')
            ->pluck($column)
            ->each(function ($id) use ($prefix, &$max): void {
                if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $id, $matches) === 1) {
                    $max = max($max, (int) $matches[1]);
                }
            });

        do {
            $max++;
            $candidate = $prefix.str_pad((string) $max, $pad, '0', STR_PAD_LEFT);
        } while ($modelClass::query()->where($column, $candidate)->exists());

        return $candidate;
    }

    private function divisionCapacityMessage(Divisi $divisi): string
    {
        if ($divisi->isGrouping()) {
            return 'Manajemen adalah level grup dan tidak memiliki anggota langsung.';
        }
        if ($divisi->isSinglePerson()) {
            return 'Unit '.$divisi->nama_divisi.' hanya dapat diisi oleh satu orang.';
        }

        return 'Kapasitas unit '.$divisi->nama_divisi.' sudah penuh.';
    }
}
