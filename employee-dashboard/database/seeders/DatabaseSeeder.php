<?php

namespace database\Seeders;

use Illuminate\database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User2;
use App\Models\Tugas;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ID maksimal 5 karakter!
        
        $roleStaff = Role::create([
            'id_role' => 'RL001',
            'nama_role' => 'staff'
        ]);

        $divisiIT = Divisi::create([
            'id_divisi' => 'DV001',
            'kode_divisi' => 'IT01',
            'nama_divisi' => 'Information Technology',
            'status_aktif' => 'Aktif',
        ]);

        $karyawan = Karyawan::create([
            'id_karyawan' => 'KR001',
            'nama' => 'Budi',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-05-15',
            'tanggal_rekrut' => '2023-01-10',
            'no_telepon' => '08123456',
            'email' => 'budi@mail.com',
            'jabatan' => 'Backend',
            'divisi_id_divisi' => $divisiIT->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US001',
            'username' => 'budist',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleStaff->id_role,
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);

        Tugas::create([
            'id_tugas' => 'TG001',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
            'judul_tugas' => 'Bikin API',
            'deskripsi' => 'Buat backend.',
            'deadline' => now()->addDays(3),
            'progress' => '0',
            'status' => 'pending',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        
        $this->command->info('Berhasil Seed Database dengan panjang karakter aman (<= 5)!');
    }
}