<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User2;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $roleKadiv = Role::firstOrCreate(
            ['id_role' => 'RL002'],
            ['nama_role' => 'kadiv']
        );

        $divisiFinance = Divisi::firstOrCreate(
            ['id_divisi' => 'DV003'],
            [
                'kode_divisi'  => 'FIN',
                'nama_divisi'  => 'Finance & Accounting',
                'status_aktif' => 'Aktif',
            ]
        );

        $karyawanFinance = Karyawan::firstOrCreate(
            ['id_karyawan' => 'KR008'],
            [
                'nama'                  => 'Bu Sari Handayani',
                'jenis_kelamin'         => 'Perempuan',
                'tanggal_lahir'         => '1988-03-25',
                'tanggal_masuk'         => '2019-04-01',
                'tanggal_akhir_kontrak' => now()->addYears(2)->toDateString(),
                'no_rekening'           => '8234567890',
                'no_telepon'            => '081233445566',
                'email'                 => 'sari.finance@silindo.co.id',
                'alamat'                => 'Salatiga, Jawa Tengah',
                'jabatan'               => 'Kepala Divisi Finance',
                'status_aktif'          => 'Aktif',
                'foto'                  => null,
                'divisi_id_divisi'      => $divisiFinance->id_divisi,
                'pendidikan'            => 'S1 Akuntansi',
                'job_level'             => 'Manager',
                'status_karyawan'       => 'tetap',
                'aktif'                 => true,
            ]
        );

        User2::firstOrCreate(
            ['id_user' => 'US008'],
            [
                'username'             => 'sarifin',
                'password'             => Hash::make('pass123'),
                'role_id_role'         => $roleKadiv->id_role,
                'karyawan_id_karyawan' => $karyawanFinance->id_karyawan,
            ]
        );

        $this->command?->info('Berhasil Seed Divisi Finance & Kadiv Finance (sarifin / pass123)');
    }
}
