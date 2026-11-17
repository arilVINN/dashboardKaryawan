<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the dummy company structure used by the dashboard:
 *
 * Direktur Utama (1 person)
 * └─ Manajemen (grouping level, no direct members)
 *    ├─ HR (1 person)
 *    ├─ Finance & Accounting (1 person)
 *    ├─ Kelas Kerjasama (kepala divisi + staff)
 *    ├─ Publikasi Buku (kepala divisi + staff)
 *    ├─ Publikasi Jurnal (kepala divisi + staff)
 *    ├─ Academic Event (kepala divisi + staff)
 *    ├─ Kreatif Digital (kepala divisi + staff)
 *    └─ IT (kepala divisi + staff)
 */
class DivisionHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'staff' => $this->roleId('staff', 'RL001'),
            'kadiv' => $this->roleId('kadiv', 'RL002'),
            'hrd' => $this->roleId('hrd', 'RL003'),
        ];

        $divisions = [
            [
                'id_divisi' => 'DV004',
                'kode_divisi' => Divisi::KODE_DIREKTUR,
                'nama_divisi' => 'Direktur Utama',
                'tipe' => Divisi::TIPE_DIREKTUR,
                'level' => 1,
                'kapasitas' => 1,
                'urutan' => 1,
                'parent_id' => null,
            ],
            [
                'id_divisi' => 'DV005',
                'kode_divisi' => Divisi::KODE_MANAJEMEN,
                'nama_divisi' => 'Manajemen',
                'tipe' => Divisi::TIPE_MANAJEMEN,
                'level' => 2,
                'kapasitas' => 0,
                'urutan' => 2,
                'parent_id' => 'DV004',
            ],
            [
                'id_divisi' => 'DV002',
                'kode_divisi' => Divisi::KODE_HR,
                'nama_divisi' => 'HR',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => 1,
                'urutan' => 3,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV003',
                'kode_divisi' => Divisi::KODE_FINANCE,
                'nama_divisi' => 'Finance & Accounting',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => 1,
                'urutan' => 4,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV006',
                'kode_divisi' => 'KJ',
                'nama_divisi' => 'Kelas Kerjasama',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 5,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV007',
                'kode_divisi' => 'PB',
                'nama_divisi' => 'Publikasi Buku',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 6,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV008',
                'kode_divisi' => 'PJ',
                'nama_divisi' => 'Publikasi Jurnal',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 7,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV009',
                'kode_divisi' => 'AE',
                'nama_divisi' => 'Academic Event',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 8,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV010',
                'kode_divisi' => 'KD',
                'nama_divisi' => 'Kreatif Digital',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 9,
                'parent_id' => 'DV005',
            ],
            [
                'id_divisi' => 'DV001',
                'kode_divisi' => Divisi::KODE_IT,
                'nama_divisi' => 'IT',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 10,
                'parent_id' => 'DV005',
            ],
        ];

        foreach ($divisions as $division) {
            Divisi::updateOrCreate(
                ['id_divisi' => $division['id_divisi']],
                $division + ['status_aktif' => 'Aktif']
            );
        }

        $employees = [
            [
                'id_karyawan' => 'KR001',
                'nama' => 'Budi',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1995-05-15',
                'tanggal_masuk' => '2023-01-10',
                'email' => 'staff@silindo.co.id',
                'no_telepon' => '081234567890',
                'alamat' => 'Salatiga, Jawa Tengah',
                'pendidikan' => 'S1 Komunikasi',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV001',
                'atasan_id' => 'KR003',
            ],
            [
                'id_karyawan' => 'KR002',
                'nama' => 'Reva',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1998-08-20',
                'tanggal_masuk' => '2024-02-01',
                'email' => 'reva@silindo.co.id',
                'no_telepon' => '08987654',
                'alamat' => 'Semarang, Jawa Tengah',
                'pendidikan' => 'S1 Desain Komunikasi Visual',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV001',
                'atasan_id' => 'KR003',
            ],
            [
                'id_karyawan' => 'KR003',
                'nama' => 'Pak Tono',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1980-01-01',
                'tanggal_masuk' => '2015-01-01',
                'email' => 'tono@silindo.co.id',
                'no_telepon' => '08111222333',
                'alamat' => 'Yogyakarta',
                'pendidikan' => 'S1 Teknik Informatika',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV001',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR004',
                'nama' => 'Bu Rina',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1985-04-12',
                'tanggal_masuk' => '2018-06-01',
                'email' => 'rina@silindo.co.id',
                'no_telepon' => '08122334455',
                'alamat' => 'Bandung',
                'pendidikan' => 'S2 Manajemen SDM',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV006',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR005',
                'nama' => 'Andi',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1997-03-18',
                'tanggal_masuk' => '2022-08-15',
                'email' => 'andi@silindo.co.id',
                'no_telepon' => '08133445566',
                'alamat' => 'Magelang',
                'pendidikan' => 'S1 Psikologi',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV007',
                'atasan_id' => 'KR011',
            ],
            [
                'id_karyawan' => 'KR006',
                'nama' => 'Maya',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1999-11-07',
                'tanggal_masuk' => '2023-05-10',
                'email' => 'maya@silindo.co.id',
                'no_telepon' => '08144556677',
                'alamat' => 'Surakarta',
                'pendidikan' => 'S1 Hukum',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV008',
                'atasan_id' => 'KR012',
            ],
            [
                'id_karyawan' => 'KR007',
                'nama' => 'Pak Darmawan',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1978-06-20',
                'tanggal_masuk' => '2012-01-15',
                'email' => 'darmawan@silindo.co.id',
                'no_telepon' => '08155667788',
                'alamat' => 'Solo',
                'pendidikan' => 'S2 Manajemen Sumber Daya Manusia',
                'jabatan' => Karyawan::JABATAN_HRD_MANAGER,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV002',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR008',
                'nama' => 'Bu Sari Handayani',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1988-03-25',
                'tanggal_masuk' => '2019-04-01',
                'email' => 'sari.finance@silindo.co.id',
                'no_telepon' => '081233445566',
                'alamat' => 'Salatiga, Jawa Tengah',
                'pendidikan' => 'S1 Akuntansi',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV003',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR009',
                'nama' => 'Hendra Wijaya',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1975-02-14',
                'tanggal_masuk' => '2010-01-04',
                'email' => 'direktur@silindo.co.id',
                'no_telepon' => '081100112233',
                'alamat' => 'Salatiga, Jawa Tengah',
                'pendidikan' => 'S2 Manajemen',
                'jabatan' => Karyawan::JABATAN_DIREKTUR,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV004',
                'atasan_id' => null,
            ],
            [
                'id_karyawan' => 'KR010',
                'nama' => 'Nadia Puspita',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1999-06-21',
                'tanggal_masuk' => '2024-03-01',
                'email' => 'nadia@silindo.co.id',
                'no_telepon' => '081177889900',
                'alamat' => 'Salatiga, Jawa Tengah',
                'pendidikan' => 'S1 Pendidikan',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV006',
                'atasan_id' => 'KR004',
            ],
            [
                'id_karyawan' => 'KR011',
                'nama' => 'Rudi Hartono',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1986-09-12',
                'tanggal_masuk' => '2017-07-10',
                'email' => 'rudi@silindo.co.id',
                'no_telepon' => '081188990011',
                'alamat' => 'Yogyakarta',
                'pendidikan' => 'S1 Sastra',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV007',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR012',
                'nama' => 'Sinta Maharani',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1987-12-03',
                'tanggal_masuk' => '2016-02-15',
                'email' => 'sinta@silindo.co.id',
                'no_telepon' => '081199001122',
                'alamat' => 'Semarang, Jawa Tengah',
                'pendidikan' => 'S2 Ilmu Komunikasi',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV008',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR013',
                'nama' => 'Fajar Nugroho',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1985-05-19',
                'tanggal_masuk' => '2015-08-01',
                'email' => 'fajar@silindo.co.id',
                'no_telepon' => '081211223344',
                'alamat' => 'Surakarta',
                'pendidikan' => 'S1 Manajemen',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV009',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR014',
                'nama' => 'Dewi Anggraini',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '2000-01-30',
                'tanggal_masuk' => '2025-02-10',
                'email' => 'dewi@silindo.co.id',
                'no_telepon' => '081222334455',
                'alamat' => 'Salatiga, Jawa Tengah',
                'pendidikan' => 'S1 Hubungan Internasional',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV009',
                'atasan_id' => 'KR013',
            ],
            [
                'id_karyawan' => 'KR015',
                'nama' => 'Yoga Pratama',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1990-07-08',
                'tanggal_masuk' => '2019-11-04',
                'email' => 'yoga@silindo.co.id',
                'no_telepon' => '081233445566',
                'alamat' => 'Semarang, Jawa Tengah',
                'pendidikan' => 'S1 Desain Komunikasi Visual',
                'jabatan' => Karyawan::JABATAN_KEPALA,
                'job_level' => 'Manager',
                'divisi_id_divisi' => 'DV010',
                'atasan_id' => 'KR009',
            ],
            [
                'id_karyawan' => 'KR016',
                'nama' => 'Laras Ayuningtyas',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1998-10-25',
                'tanggal_masuk' => '2023-09-18',
                'email' => 'laras@silindo.co.id',
                'no_telepon' => '081244556677',
                'alamat' => 'Magelang',
                'pendidikan' => 'S1 Ilmu Komunikasi',
                'jabatan' => Karyawan::JABATAN_STAFF,
                'job_level' => 'Staff',
                'divisi_id_divisi' => 'DV010',
                'atasan_id' => 'KR015',
            ],
        ];

        // Pass 1: upsert every row without its supervisor, because a
        // supervisor may itself be created later in this same seeder.
        foreach ($employees as $employee) {
            $atasanId = $employee['atasan_id'] ?? null;
            unset($employee['atasan_id']);

            Karyawan::updateOrCreate(
                ['id_karyawan' => $employee['id_karyawan']],
                $employee + [
                    'atasan_id' => null,
                    'tanggal_akhir_kontrak' => null,
                    'status_aktif' => 'Aktif',
                    'tanggal_keluar' => null,
                    'status_karyawan' => 'tetap',
                    'aktif' => true,
                ]
            );
        }

        // Pass 2: wire the reporting lines once every employee exists.
        foreach ($employees as $employee) {
            Karyawan::whereKey($employee['id_karyawan'])->update([
                'atasan_id' => $employee['atasan_id'] ?? null,
            ]);
        }

        $accounts = [
            ['id_user' => 'US001', 'username' => 'budist', 'karyawan_id_karyawan' => 'KR001', 'role' => 'staff'],
            ['id_user' => 'US002', 'username' => 'revast', 'karyawan_id_karyawan' => 'KR002', 'role' => 'staff'],
            ['id_user' => 'US003', 'username' => 'tonokd', 'karyawan_id_karyawan' => 'KR003', 'role' => 'kadiv'],
            ['id_user' => 'US004', 'username' => 'rinakadiv', 'karyawan_id_karyawan' => 'KR004', 'role' => 'kadiv'],
            ['id_user' => 'US005', 'username' => 'andist', 'karyawan_id_karyawan' => 'KR005', 'role' => 'staff'],
            ['id_user' => 'US006', 'username' => 'mayast', 'karyawan_id_karyawan' => 'KR006', 'role' => 'staff'],
            ['id_user' => 'US007', 'username' => 'darmawanhrd', 'karyawan_id_karyawan' => 'KR007', 'role' => 'hrd'],
            ['id_user' => 'US008', 'username' => 'sarifin', 'karyawan_id_karyawan' => 'KR008', 'role' => 'kadiv'],
            ['id_user' => 'US009', 'username' => 'nadia', 'karyawan_id_karyawan' => 'KR010', 'role' => 'staff'],
            ['id_user' => 'US010', 'username' => 'rudih', 'karyawan_id_karyawan' => 'KR011', 'role' => 'kadiv'],
            ['id_user' => 'US011', 'username' => 'sinta', 'karyawan_id_karyawan' => 'KR012', 'role' => 'kadiv'],
            ['id_user' => 'US012', 'username' => 'fajar', 'karyawan_id_karyawan' => 'KR013', 'role' => 'kadiv'],
            ['id_user' => 'US013', 'username' => 'dewi', 'karyawan_id_karyawan' => 'KR014', 'role' => 'staff'],
            ['id_user' => 'US014', 'username' => 'yoga', 'karyawan_id_karyawan' => 'KR015', 'role' => 'kadiv'],
            ['id_user' => 'US015', 'username' => 'laras', 'karyawan_id_karyawan' => 'KR016', 'role' => 'staff'],
        ];

        foreach ($accounts as $account) {
            User2::firstOrCreate(
                ['id_user' => $account['id_user']],
                [
                    'username' => $account['username'],
                    'password' => Hash::make('pass123'),
                    'role_id_role' => $roles[$account['role']],
                    'karyawan_id_karyawan' => $account['karyawan_id_karyawan'],
                ]
            );
        }

        $this->command?->info('Berhasil seed struktur divisi: Direktur Utama, Manajemen, HR, Finance & Accounting, dan 6 divisi operasional.');
    }

    private function roleId(string $name, string $fallbackId): string
    {
        $role = Role::whereRaw('LOWER(nama_role) = ?', [strtolower($name)])->first();

        if ($role) {
            return $role->id_role;
        }

        return Role::create([
            'id_role' => $fallbackId,
            'nama_role' => $name,
        ])->id_role;
    }
}
