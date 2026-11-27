<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder karyawan terbaru yang cukup diisi `nama` dan `role`.
 * Field lain (`username`, `password`, `email`, `divisi`, `jabatan`, `job_level`, `atasan_id`)
 * otomatis diisi atau bisa di-override bila diperlukan.
 *
 * Cara jalankan:
 *   php artisan db:seed --class=KaryawanTerbaruSeeder
 */
class KaryawanTerbaruSeeder extends Seeder
{
    /**
     * Daftar karyawan. Minimal cukup isi `'nama'` dan `'role'`.
     * Opsional: `'username'`, `'password'`, `'divisi'` (kode_divisi), `'jabatan'`, `'jenis_kelamin'`.
     */
    private array $employees = [
        [
            'nama' => 'Meida',
            'role' => 'superadmin',
            'divisi' => 'DIRUT',
            'jabatan' => 'Direktur Utama',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Jeffry',
            'role' => 'superadmin',
            'divisi' => 'DIRUT',
            'jabatan' => 'Direktur Operasional',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Setianingsih',
            'role' => 'staff',
            'divisi' => 'DIRUT',
            'jabatan' => 'Personal Assistant',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Yemima',
            'role' => 'kadiv',
            'divisi' => 'OPS',
            'jabatan' => 'Kadiv Operasional',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Via',
            'role' => 'hrd',
            'divisi' => 'OPS',
            'jabatan' => 'HRD & GA',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Kevin',
            'role' => 'staff',
            'divisi' => 'OPS',
            'jabatan' => 'Legal',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Agnes',
            'role' => 'hrd',
            'divisi' => 'OPS',
            'jabatan' => 'Administrator Operasional',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Jeffry 2',
            'username' => 'jeffry2',
            'role' => 'kadiv',
            'divisi' => 'FIN',
            'jabatan' => 'Kadiv Finance',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Bu Sari Handayani',
            'username' => 'sarifin',
            'role' => 'kadiv',
            'divisi' => 'FIN',
            'jabatan' => 'Kepala Divisi Finance',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Test Admin',
            'username' => 'testadmin',
            'role' => 'superadmin',
            'divisi' => 'MGT',
            'jabatan' => 'Superadmin',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Siti',
            'role' => 'kadiv',
            'divisi' => 'KJ',
            'jabatan' => 'Admin KJ',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Grace',
            'role' => 'kadiv',
            'divisi' => 'AE',
            'jabatan' => 'Admin AE',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Yovita',
            'role' => 'staff',
            'divisi' => 'PJ',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Annisa',
            'role' => 'staff',
            'divisi' => 'PJ',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Rohma',
            'role' => 'kadiv',
            'divisi' => 'PB',
            'jabatan' => 'Kadiv Buku',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Cindy',
            'role' => 'staff',
            'divisi' => 'PB',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Argha',
            'role' => 'staff',
            'divisi' => 'PB',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Vega',
            'role' => 'kadiv',
            'divisi' => 'MARKOM',
            'jabatan' => 'Kadiv Markom',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Eko',
            'role' => 'staff',
            'divisi' => 'MARKOM',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Wardi',
            'role' => 'kadiv',
            'divisi' => 'IT',
            'jenis_kelamin' => 'Laki-laki',
        ],
    ];

    public function run(): void
    {
        $this->ensureRoles();
        $this->ensureOperasionalDivision();

        $nextKaryawan = $this->nextSequence(Karyawan::query()->pluck('id_karyawan'), 'KR');
        $nextUser = $this->nextSequence(User2::query()->pluck('id_user'), 'US');

        $usedUsernames = [];
        $resolved = [];
        $passSeq = 1;

        // Pass 1: Buat / update Karyawan & User2
        foreach ($this->employees as $index => $row) {
            $nama = trim((string) ($row['nama'] ?? ''));
            $roleInput = strtolower(trim((string) ($row['role'] ?? 'staff')));

            if ($nama === '') {
                $this->command?->warn('Baris #' . ($index + 1) . ' dilewati: nama kosong.');
                continue;
            }

            $roleName = $this->resolveRoleName($roleInput);
            $role = Role::whereRaw('LOWER(nama_role) = ?', [$roleName])->first();
            if (! $role) {
                $this->command?->warn("  [{$nama}] dilewati: role '{$roleName}' tidak ditemukan.");
                continue;
            }

            $divisiKode = strtoupper(trim((string) ($row['divisi'] ?? $this->defaultDivisiForRole($roleName))));
            $divisi = Divisi::where('kode_divisi', $divisiKode)->first();
            if (! $divisi) {
                $this->command?->warn("  [{$nama}] dilewati: divisi '{$divisiKode}' tidak ditemukan.");
                continue;
            }

            $username = $this->resolveUsername($row['username'] ?? null, $nama, $usedUsernames);
            $usedUsernames[] = $username;

            $plainPassword = $row['password'] ?? ('pass' . str_pad((string) $passSeq, 3, '0', STR_PAD_LEFT));
            $passSeq++;

            $jabatan = trim((string) ($row['jabatan'] ?? $this->defaultJabatanForRole($roleName)));
            $jobLevel = $this->resolveJobLevel($roleName, $jabatan);

            $existingUser = User2::where('username', $username)->first();
            $idUser = $existingUser?->id_user ?? 'US' . str_pad((string) $nextUser++, 3, '0', STR_PAD_LEFT);
            $idKaryawan = $existingUser?->karyawan_id_karyawan
                ?? 'KR' . str_pad((string) $nextKaryawan++, 3, '0', STR_PAD_LEFT);

            Karyawan::updateOrCreate(
                ['id_karyawan' => $idKaryawan],
                [
                    'nama' => $nama,
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? 'Laki-laki',
                    'tanggal_masuk' => $row['tanggal_masuk'] ?? now()->subMonths(6)->toDateString(),
                    'status_aktif' => 'Aktif',
                    'email' => $row['email'] ?? ($username . '@silindo.co.id'),
                    'no_telepon' => $row['no_telepon'] ?? null,
                    'jabatan' => $jabatan,
                    'job_level' => $jobLevel,
                    'divisi_id_divisi' => $divisi->id_divisi,
                    'status_karyawan' => 'tetap',
                    'aktif' => true,
                ]
            );

            User2::updateOrCreate(
                ['id_user' => $idUser],
                [
                    'username' => $username,
                    'password' => Hash::make($plainPassword),
                    'role_id_role' => $role->id_role,
                    'karyawan_id_karyawan' => $idKaryawan,
                ]
            );

            $resolved[] = [
                'id_karyawan' => $idKaryawan,
                'id_user' => $idUser,
                'nama' => $nama,
                'username' => $username,
                'password' => $plainPassword,
                'role' => $roleName,
                'divisi_id' => $divisi->id_divisi,
                'divisi_kode' => $divisiKode,
                'jabatan' => $jabatan,
                'status' => $existingUser ? 'updated' : 'created',
            ];
        }

        // Pass 2: Hubungkan atasan_id otomatis
        $this->linkSuperiors($resolved);

        $this->command?->info('KaryawanTerbaruSeeder selesai (' . count($resolved) . ' akun):');
        foreach ($resolved as $item) {
            $this->command?->line(sprintf(
                '  [%s] %s | %s | pass: %s | role: %s | divisi: %s | %s (%s)',
                $item['status'],
                $item['id_karyawan'],
                $item['username'],
                $item['password'],
                $item['role'],
                $item['divisi_kode'],
                $item['jabatan'],
                $item['nama']
            ));
        }
    }

    private function ensureRoles(): void
    {
        $roles = [
            'RL001' => 'staff',
            'RL002' => 'kadiv',
            'RL003' => 'hrd',
            'RL004' => 'direktur',
            'RL005' => 'superadmin',
        ];

        foreach ($roles as $id => $name) {
            Role::firstOrCreate(['id_role' => $id], ['nama_role' => $name]);
        }
    }

    private function ensureOperasionalDivision(): void
    {
        if (Divisi::where('kode_divisi', 'OPS')->exists()) {
            return;
        }

        $nextDv = $this->nextSequence(Divisi::query()->pluck('id_divisi'), 'DV');
        $dirut = Divisi::where('kode_divisi', 'DIRUT')->first();

        Divisi::create([
            'id_divisi' => 'DV' . str_pad((string) $nextDv, 3, '0', STR_PAD_LEFT),
            'parent_id' => $dirut?->id_divisi,
            'kode_divisi' => 'OPS',
            'nama_divisi' => 'Operasional',
            'tipe' => Divisi::TIPE_DIVISI,
            'level' => 2,
            'urutan' => 2,
            'posisi_bagan' => Divisi::POSISI_OPERASIONAL,
            'status_aktif' => 'Aktif',
        ]);
    }

    private function linkSuperiors(array $resolved): void
    {
        $dirUtama = collect($resolved)->first(
            fn (array $item) => str_contains(strtolower($item['jabatan']), 'direktur utama')
        );
        $dirOps = collect($resolved)->first(
            fn (array $item) => str_contains(strtolower($item['jabatan']), 'direktur operasional')
        );

        $kadivByDivisi = [];
        foreach ($resolved as $item) {
            if ($item['role'] === 'kadiv' && ! isset($kadivByDivisi[$item['divisi_id']])) {
                $kadivByDivisi[$item['divisi_id']] = $item['id_karyawan'];
            }
        }

        foreach ($resolved as $item) {
            $jabatanLower = strtolower($item['jabatan']);
            $atasanId = null;

            if (str_contains($jabatanLower, 'direktur utama') || $item['divisi_kode'] === 'MGT') {
                $atasanId = null;
            } elseif (str_contains($jabatanLower, 'direktur operasional') || $item['divisi_kode'] === 'DIRUT') {
                $atasanId = $dirUtama['id_karyawan'] ?? null;
            } elseif ($item['role'] === 'kadiv') {
                $atasanId = $item['divisi_kode'] === 'OPS'
                    ? ($dirOps['id_karyawan'] ?? $dirUtama['id_karyawan'] ?? null)
                    : ($dirUtama['id_karyawan'] ?? null);
            } else {
                $atasanId = $kadivByDivisi[$item['divisi_id']]
                    ?? $dirOps['id_karyawan']
                    ?? $dirUtama['id_karyawan']
                    ?? null;
            }

            if ($atasanId !== null && $atasanId !== $item['id_karyawan']) {
                Karyawan::whereKey($item['id_karyawan'])->update(['atasan_id' => $atasanId]);
            }
        }
    }

    private function resolveRoleName(string $roleInput): string
    {
        return match (true) {
            str_contains($roleInput, 'superadmin') => 'superadmin',
            str_contains($roleInput, 'direktur') => 'direktur',
            str_contains($roleInput, 'hrd') => 'hrd',
            str_contains($roleInput, 'kadiv') => 'kadiv',
            default => 'staff',
        };
    }

    private function defaultDivisiForRole(string $roleName): string
    {
        return match ($roleName) {
            'direktur' => 'DIRUT',
            'superadmin' => 'MGT',
            'hrd' => 'HR',
            default => 'OPS',
        };
    }

    private function defaultJabatanForRole(string $roleName): string
    {
        return match ($roleName) {
            'direktur' => 'Direktur',
            'superadmin' => 'Superadmin',
            'hrd' => 'HRD',
            'kadiv' => 'Kepala Divisi',
            default => 'Staff',
        };
    }

    private function resolveJobLevel(string $roleName, string $jabatan): string
    {
        $jabatanLower = strtolower($jabatan);

        return match (true) {
            str_contains($jabatanLower, 'direktur') || $roleName === 'direktur' => 'Direktur',
            $roleName === 'kadiv' || str_contains($jabatanLower, 'kadiv') || str_contains($jabatanLower, 'kepala divisi') => 'Manager',
            $roleName === 'hrd' => 'HRD',
            $roleName === 'superadmin' => 'Superadmin',
            default => 'Staff',
        };
    }

    private function resolveUsername(?string $explicitUsername, string $nama, array $usedUsernames): string
    {
        if ($explicitUsername !== null && trim($explicitUsername) !== '') {
            $clean = strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $explicitUsername));
            if ($clean !== '') {
                return $clean;
            }
        }

        $firstWord = explode(' ', trim($nama))[0] ?? $nama;
        $base = strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $firstWord));
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $suffix = 2;
        while (in_array($candidate, $usedUsernames, true)) {
            $candidate = $base . $suffix++;
        }

        return $candidate;
    }

    private function nextSequence($ids, string $prefix): int
    {
        $max = 0;
        foreach ($ids as $id) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) $id, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max + 1;
    }
}
