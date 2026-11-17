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
 *   1. Isi array $rows di bawah (contoh sudah disiapkan, masih di-comment).
 *   2. Jalankan: php artisan db:seed --class=EmployeeTemplateSeeder
 *
 * Aman dijalankan ulang: data dengan id yang sama akan di-update, bukan dobel.
 * Seeder ini sengaja TIDAK didaftarkan di DatabaseSeeder.php.
 *
 * CATATAN PENTING:
 * - 'kode'  : token unik pendek (huruf/angka). Dipakai untuk id_karyawan /
 *             id_user otomatis. Maksimal 20 karakter.
 * - 'role'  : nama role, bebas huruf besar/kecil: staff | kadiv | hrd | direktur
 * - 'divisi': KODE divisi (kode_divisi), mis. IT01, HR01, FIN01.
 * - 'username': WAJIB hanya huruf dan angka (alpha_num). Login menolak titik,
 *             spasi, dan tanda baca lainnya.
 * - 'jabatan': memengaruhi izin. 'Kepala Divisi ...' / 'Kadiv ...' membuat
 *             user dianggap kadiv; 'Kadiv Finance' + divisi FIN = Kadiv Finance.
 */
class EmployeeTemplateSeeder extends Seeder
{
    /**
     * ─────────────────────────────────────────────────────────────────────
     * TULIS DATA ANDA DI SINI
     * ─────────────────────────────────────────────────────────────────────
     */
    private array $rows = [
        // [
        //     'kode'      => 'STFNEW',          // wajib, unik, <= 20 char
        //     'nama'      => 'Nama Karyawan',
        //     'username'  => 'stfnew',          // huruf/angka SAJA
        //     'password'  => 'pass123',         // opsional, default: pass123
        //     'role'      => 'staff',           // staff | kadiv | hrd | direktur
        //     'divisi'    => 'IT01',            // kode_divisi
        //     'jabatan'   => 'Staff',
        //     'jenis_kelamin' => 'Laki-laki',   // opsional
        //     'email'     => 'stfnew@example.test', // opsional
        //     'no_telepon'=> '081234567890',   // opsional
        //     'tanggal_masuk' => '2026-01-01',  // opsional
        // ],
        //
        // Contoh Kadiv Finance:
        // [
        //     'kode' => 'FINNEW', 'nama' => 'Kadiv Finance Baru',
        //     'username' => 'finnew', 'role' => 'kadiv',
        //     'divisi' => 'FIN01', 'jabatan' => 'Kadiv Finance',
        // ],
    ];

    public function run(): void
    {
        if ($this->rows === []) {
            $this->command->warn('EmployeeTemplateSeeder: array $rows masih kosong. Tidak ada data yang dibuat.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->rows as $index => $row) {
            $label = $row['kode'] ?? ('baris #'.($index + 1));

            $kode = strtoupper((string) ($row['kode'] ?? ''));
            $username = strtolower((string) ($row['username'] ?? ''));
            $roleName = strtolower((string) ($row['role'] ?? ''));
            $divisiKode = strtoupper((string) ($row['divisi'] ?? ''));

            if ($kode === '' || strlen($kode) > 20) {
                $this->command->warn("  [{$label}] dilewati: 'kode' wajib dan maksimal 20 karakter.");
                $skipped++;
                continue;
            }

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

            $idKaryawan = 'KRY-'.$kode;
            $idUser = 'USR-'.$kode;

            Karyawan::updateOrCreate(
                ['id_karyawan' => $idKaryawan],
                [
                    'nama'              => $row['nama'] ?? $kode,
                    'jenis_kelamin'     => $row['jenis_kelamin'] ?? 'Laki-laki',
                    'jabatan'           => $row['jabatan'] ?? 'Staff',
                    'divisi_id_divisi'  => $divisi->id_divisi,
                    'email'             => $row['email'] ?? null,
                    'no_telepon'        => $row['no_telepon'] ?? null,
                    'tanggal_masuk'     => $row['tanggal_masuk'] ?? null,
                    'status_aktif'      => 'Aktif',
                    'aktif'             => true,
                ]
            );

            User2::updateOrCreate(
                ['id_user' => $idUser],
                [
                    'username'              => $username,
                    'password'              => Hash::make($row['password'] ?? 'pass123'),
                    'role_id_role'          => $role->id_role,
                    'karyawan_id_karyawan'  => $idKaryawan,
                ]
            );

            $created++;
            $this->command->line("  [{$label}] OK -> {$username} / ".($row['password'] ?? 'pass123'));
        }

        $this->command->info("EmployeeTemplateSeeder selesai: {$created} dibuat/diubah, {$skipped} dilewati.");
        $this->command->line('Cek kode divisi dengan: php artisan tinker --execute="App\\Models\\Divisi::all([\'kode_divisi\',\'nama_divisi\']);"');
    }
}
