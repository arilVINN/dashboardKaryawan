<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirekturSeeder extends Seeder
{
    public function run(): void
    {
        $roleDirektur = Role::firstOrCreate(
            ['nama_role' => 'direktur'],
            ['id_role' => 'RL004']
        );

        // DivisionHierarchySeeder already creates the Direktur Utama employee
        // and the "Direktur Utama" division node. Reuse them when present so
        // this seeder is safe on an already-seeded database.
        $karyawanDirektur = Karyawan::query()
            ->where('email', 'direktur@silindo.co.id')
            ->orWhere('jabatan', Karyawan::JABATAN_DIREKTUR)
            ->first();

        if (! $karyawanDirektur) {
            $divisiDireksi = Divisi::firstOrCreate(
                ['kode_divisi' => Divisi::KODE_DIREKTUR],
                [
                    'id_divisi' => 'DV004',
                    'nama_divisi' => 'Direktur Utama',
                    'tipe' => Divisi::TIPE_DIREKTUR,
                    'level' => 1,
                    'kapasitas' => 1,
                    'urutan' => 1,
                    'parent_id' => null,
                    'status_aktif' => 'Aktif',
                ]
            );

            $karyawanDirektur = Karyawan::create([
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
                'divisi_id_divisi' => $divisiDireksi->id_divisi,
                'atasan_id' => null,
                'status_aktif' => 'Aktif',
                'status_karyawan' => 'tetap',
                'aktif' => true,
            ]);
        }

        User2::firstOrCreate(
            ['username' => 'direktur'],
            [
                'id_user' => 'US000',
                'password' => Hash::make('pass123'),
                'role_id_role' => $roleDirektur->id_role,
                'karyawan_id_karyawan' => $karyawanDirektur->id_karyawan,
            ]
        );

        $this->command?->info('Berhasil seed role Direktur & akun (direktur / pass123).');
    }
}
