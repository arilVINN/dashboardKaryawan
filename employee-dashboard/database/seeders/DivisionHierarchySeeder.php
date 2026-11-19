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
            [
                'id_divisi' => 'DV011',
                'kode_divisi' => 'MARKOM',
                'nama_divisi' => 'MARKOM',
                'tipe' => Divisi::TIPE_DIVISI,
                'level' => 3,
                'kapasitas' => null,
                'urutan' => 11,
                'parent_id' => 'DV005',
            ],
        ];

        foreach ($divisions as $division) {
            Divisi::updateOrCreate(
                ['id_divisi' => $division['id_divisi']],
                $division + ['status_aktif' => 'Aktif']
            );
        }

        $this->command?->info('Berhasil seed struktur divisi.');
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
