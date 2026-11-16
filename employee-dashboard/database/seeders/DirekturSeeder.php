<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User2;

class DirekturSeeder extends Seeder
{
    public function run(): void
    {
        $roleDirektur = Role::firstOrCreate(
            ['nama_role' => 'direktur'],
            ['id_role' => 'RL004']
        );

        $divisiDireksi = Divisi::firstOrCreate(
            ['kode_divisi' => 'DIR'],
            [
                'id_divisi' => 'DV000',
                'nama_divisi' => 'Direksi & Pimpinan',
                'status_aktif' => 'Aktif',
            ]
        );

        $karyawanDirektur = Karyawan::firstOrCreate(
            ['email' => 'direktur@silindo.co.id'],
            [
                'id_karyawan' => 'KR000',
                'nama' => 'Hendra Prasetya',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1975-01-01',
                'tanggal_masuk' => '2010-01-01',
                'tanggal_akhir_kontrak' => now()->addYears(10)->toDateString(),
                'no_rekening' => '9999999999',
                'no_telepon' => '081199998888',
                'alamat' => 'Salatiga, Jawa Tengah',
                'jabatan' => 'Direktur Utama',
                'status_aktif' => 'Aktif',
                'foto' => null,
                'divisi_id_divisi' => $divisiDireksi->id_divisi,
                'pendidikan' => 'S2 Manajemen Bisnis',
                'job_level' => 'Direksi',
                'status_karyawan' => 'tetap',
                'aktif' => true,
                'atasan_id' => null,
            ]
        );

        Karyawan::where('id_karyawan', 'KR007')
            ->whereNull('atasan_id')
            ->update(['atasan_id' => $karyawanDirektur->id_karyawan]);

        User2::firstOrCreate(
            ['username' => 'direktur'],
            [
                'id_user' => 'US000',
                'password' => Hash::make('pass123'),
                'role_id_role' => $roleDirektur->id_role,
                'karyawan_id_karyawan' => $karyawanDirektur->id_karyawan,
            ]
        );

        $this->command?->info('Berhasil Seed Role Direktur & Akun (direktur / pass123)');
    }
}
