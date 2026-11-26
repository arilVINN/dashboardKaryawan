<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TEMPLATE seeder untuk menambah karyawan + akun login secara manual.
 *
 * Cara pakai:
 *   1. Ubah 5 data contoh di array $rows (tambahkan/hapus baris sesuai perlu).
 *   2. Jalankan: php artisan db:seed --class=EmployeeTemplateSeeder
 *
 * ID karyawan (KR001, KR002, ...) dan ID user (US001, ...) dibuat OTOMATIS,
 * mengikuti format yang dipakai HRD di HrdStaffController::store.
 * Untuk menjaga idempotent, akun dikenali berdasarkan 'username'.
 *
 * Seeder ini sengaja TIDAK didaftarkan di DatabaseSeeder.php.
 *
 * CATATAN:
 * - 'username': WAJIB huruf/angka saja. Login menolak titik/spasi.
 * - 'role'  : staff | kadiv | hrd | direktur (huruf besar/kecil bebas).
 * - 'divisi': KODE divisi (kode_divisi): DIRUT, MGT, HR, FIN, IT, KJ, PB, PJ, AE, KD.
 * - 'jabatan': memengaruhi izin. 'Kepala Divisi' = kadiv; 'Markom' = staff
 *              dengan akses CRUD kalender konten divisi Publikasi Buku.
 */
class EmployeeTemplateSeeder extends Seeder
{
    /**
     * ─────────────────────────────────────────────────────────────────────
     * 5 DATA CONTOH — ubah namanya, username, divisi, dan jabatannya.
     *
     * - Hapus satu blok [...] untuk melewati baris tersebut.
     * - Field wajib: nama, username, role, divisi, jabatan.
     * - Field opsional: password (default pass123), jenis_kelamin,
     *   email, no_telepon, tanggal_masuk.
     * ─────────────────────────────────────────────────────────────────────
     */
    private array $rows = [
        [
            'nama' => 'Yovita Aryani, S.TP., M.P.',
            'username' => 'yovita',
            'password' => 'pass001',
            'role' => 'staff',
            'divisi' => 'PJ',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Agnes Galih Chris Roseita',
            'username' => 'agnes',
            'password' => 'pass002',
            'role' => 'hrd',
            'divisi' => 'HR',
            'jabatan' => 'HRD Manager',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Vega Rossy Danang Pratama',
            'username' => 'vega',
            'password' => 'pass003',
            'role' => 'staff',
            'divisi' => 'MARKOM',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Annisa Sholikhatul Fatih',
            'username' => 'annisa',
            'password' => 'pass004',
            'role' => 'staff',
            'divisi' => 'PJ',
            'jabatan' => 'Manager',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'VINI ARUMSARI, S.P., M.',
            'username' => 'vini',
            'password' => 'pass005',
            'role' => 'kadiv',
            'divisi' => 'PJ',
            'jabatan' => 'Kepala Divisi',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Cindy Reta Qorinsa',
            'username' => 'cindy',
            'password' => 'pass006',
            'role' => 'staff',
            'divisi' => 'PB',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Argha Yudha Prathama',
            'username' => 'argha',
            'password' => 'pass007',
            'role' => 'staff',
            'divisi' => 'PB',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Mukhamad Miftakhul',
            'username' => 'mukhamad',
            'password' => 'pass008',
            'role' => 'staff',
            'divisi' => 'MARKOM',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Durotur Rohma',
            'username' => 'durotur',
            'password' => 'pass009',
            'role' => 'kadiv',
            'divisi' => 'PB',
            'jabatan' => 'Kepala Divisi',
            'jenis_kelamin' => 'Perempuan',
        ],
        [
            'nama' => 'Test Admin',
            'username' => 'testadmin',
            'password' => 'pass010',
            'role' => 'superadmin',
            'divisi' => 'MGT',
            'jabatan' => 'Superadmin',
            'jenis_kelamin' => 'Laki-laki',
        ],
        [
            'nama' => 'Bu Sari Handayani',
            'username' => 'sarifin',
            'password' => 'pass123',
            'role' => 'kadiv',
            'divisi' => 'FIN',
            'jabatan' => 'Kepala Divisi Finance',
            'jenis_kelamin' => 'Perempuan',
        ],
    ];

    /*
     * Contoh baris KADIV — salin ke dalam $rows bila perlu:
     * [
     *     'nama' => 'Kadiv Publikasi Jurnal',
     *     'username' => 'kadivpj',
     *     'password' => 'pass123',
     *     'role' => 'kadiv',
     *     'divisi' => 'PJ',
     *     'jabatan' => 'Kepala Divisi',
     * ],
     */

    public function run(): void
    {
        if ($this->rows === []) {
            $this->command->warn('EmployeeTemplateSeeder: array $rows masih kosong. Tidak ada data yang dibuat.');

            return;
        }

        $nextKaryawan = $this->nextSequence(Karyawan::query()->pluck('id_karyawan'), 'KR');
        $nextUser = $this->nextSequence(User2::query()->pluck('id_user'), 'US');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->rows as $index => $row) {
            $username = strtolower((string) ($row['username'] ?? ''));
            $roleName = strtolower((string) ($row['role'] ?? ''));
            $divisiKode = strtoupper((string) ($row['divisi'] ?? ''));
            $label = $row['nama'] ?? $username ?: ('baris #'.($index + 1));

            if ($username === '' || ! preg_match('/^[a-z0-9]+$/', $username)) {
                $this->command->warn("  [{$label}] dilewati: username harus huruf/angka saja (tanpa titik/spasi).");
                $skipped++;

                continue;
            }

            $role = Role::whereRaw('LOWER(nama_role) = ?', [$roleName])->first();
            if (! $role) {
                $this->command->warn("  [{$label}] dilewati: role '{$roleName}' tidak ditemukan.");
                $skipped++;

                continue;
            }

            $divisi = Divisi::where('kode_divisi', $divisiKode)->first();
            if (! $divisi) {
                $this->command->warn("  [{$label}] dilewati: divisi dengan kode '{$divisiKode}' tidak ditemukan.");
                $skipped++;

                continue;
            }

            // Idempotent berdasarkan username: akun yang sudah ada di-update.
            $existing = User2::where('username', $username)->first();

            $idUser = $existing?->id_user ?? 'US'.str_pad($nextUser++, 3, '0', STR_PAD_LEFT);
            $idKaryawan = $existing?->karyawan_id_karyawan
                ?? 'KR'.str_pad($nextKaryawan++, 3, '0', STR_PAD_LEFT);

            Karyawan::updateOrCreate(
                ['id_karyawan' => $idKaryawan],
                [
                    'nama' => $row['nama'] ?? $username,
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? 'Laki-laki',
                    'jabatan' => $row['jabatan'] ?? 'Staff',
                    'divisi_id_divisi' => $divisi->id_divisi,
                    'email' => $row['email'] ?? null,
                    'no_telepon' => $row['no_telepon'] ?? null,
                    'tanggal_masuk' => $row['tanggal_masuk'] ?? null,
                    'status_aktif' => 'Aktif',
                    'aktif' => true,
                ]
            );

            User2::updateOrCreate(
                ['id_user' => $idUser],
                [
                    'username' => $username,
                    'password' => Hash::make($row['password'] ?? 'pass123'),
                    'role_id_role' => $role->id_role,
                    'karyawan_id_karyawan' => $idKaryawan,
                ]
            );

            $existing ? $updated++ : $created++;
            $this->command->line("  [{$label}] ".($existing ? 'diubah' : 'dibuat')." -> {$idKaryawan} / {$idUser} / {$username} / ".($row['password'] ?? 'pass123'));
        }

        $this->command->info("EmployeeTemplateSeeder selesai: {$created} dibuat, {$updated} diubah, {$skipped} dilewati.");
        $this->command->line('Cek kode divisi dengan: php artisan tinker --execute="App\\Models\\Divisi::all([\'kode_divisi\',\'nama_divisi\']);"');
    }

    /**
     * Cari nomor urut berikutnya hanya dari ID dengan format <prefix><angka>,
     * supaya ID legacy/format lain tidak merusak urutan (sama seperti SubmitTugas::nextId).
     */
    private function nextSequence($ids, string $prefix): int
    {
        $max = 0;

        foreach ($ids as $id) {
            if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $id, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max + 1;
    }
}
