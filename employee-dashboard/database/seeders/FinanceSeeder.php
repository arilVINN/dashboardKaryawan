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
            ['id_karyawan' => 'KR011'],
            [
                'nama'                  => 'Bu Sari Handayani',
                'jenis_kelamin'         => 'Perempuan',
                'tanggal_lahir'         => '1988-03-25',
                'tanggal_masuk'         => '2019-04-01',
                'tanggal_akhir_kontrak' => now()->addYears(2)->toDateString(),
                'email'                 => 'sari.finance@silindo.co.id',
                'jabatan'               => 'Kepala Divisi Finance',
                'status_aktif'          => 'Aktif',
                'divisi_id_divisi'      => $divisiFinance->id_divisi,
                'job_level'             => 'Manager',
                'status_karyawan'       => 'tetap',
                'aktif'                 => true,
            ]
        );

        User2::firstOrCreate(
            ['username' => 'sarifin'],
            [
                'id_user'              => 'US011',
                'password'             => Hash::make('pass123'),
                'role_id_role'         => $roleKadiv->id_role,
                'karyawan_id_karyawan' => $karyawanFinance->id_karyawan,
            ]
        );

        $this->command?->info('Berhasil Seed Divisi Finance & Kadiv Finance (sarifin / pass123)');
    }
}
