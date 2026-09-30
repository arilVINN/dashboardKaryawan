<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
            'status' => 'baru',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        
        // --- ADD REVA ---
        $karyawanReva = Karyawan::create([
            'id_karyawan' => 'KR002',
            'nama' => 'Reva',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1998-08-20',
            'tanggal_rekrut' => '2024-02-01',
            'no_telepon' => '08987654',
            'email' => 'reva@mail.com',
            'jabatan' => 'Frontend',
            'divisi_id_divisi' => $divisiIT->id_divisi, // Same division as Budi for testing
        ]);

        User2::create([
            'id_user' => 'US002',
            'username' => 'revast',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleStaff->id_role,
            'karyawan_id_karyawan' => $karyawanReva->id_karyawan,
        ]);

        Tugas::create([
            'id_tugas' => 'TG002',
            'karyawan_id_karyawan' => $karyawanReva->id_karyawan,
            'judul_tugas' => 'Desain UI Dashboard',
            'deskripsi' => 'Buat tampilan dashboard yang responsive.',
            'deadline' => now()->addDays(5),
            'progress' => '0',
            'status' => 'baru',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        // -----------------
        // --- ADD KADIV (PAK TONO) ---
        $roleKadiv = Role::create([
            'id_role' => 'RL002',
            'nama_role' => 'kadiv'
        ]);

        $karyawanKadiv = Karyawan::create([
            'id_karyawan' => 'KR003',
            'nama' => 'Pak Tono',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1980-01-01',
            'tanggal_rekrut' => '2015-01-01',
            'no_telepon' => '08111222333',
            'email' => 'tono@mail.com',
            'jabatan' => 'Kepala Divisi IT',
            'divisi_id_divisi' => $divisiIT->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US003',
            'username' => 'tonokd',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleKadiv->id_role,
            'karyawan_id_karyawan' => $karyawanKadiv->id_karyawan,
        ]);
        // -----------------

        $divisiHR = Divisi::create([
            'id_divisi' => 'DV002',
            'kode_divisi' => 'HR01',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'Aktif',
        ]);

        $karyawanKadivHR = Karyawan::create([
            'id_karyawan' => 'KR004',
            'nama' => 'Bu Rina',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1985-04-12',
            'tanggal_rekrut' => '2018-06-01',
            'no_telepon' => '08122334455',
            'email' => 'rina@mail.com',
            'jabatan' => 'Kepala Divisi HR',
            'divisi_id_divisi' => $divisiHR->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US004',
            'username' => 'rinakadiv',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleKadiv->id_role,
            'karyawan_id_karyawan' => $karyawanKadivHR->id_karyawan,
        ]);

        $karyawanAndi = Karyawan::create([
            'id_karyawan' => 'KR005',
            'nama' => 'Andi',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1997-03-18',
            'tanggal_rekrut' => '2022-08-15',
            'no_telepon' => '08133445566',
            'email' => 'andi@mail.com',
            'jabatan' => 'Staff HR',
            'divisi_id_divisi' => $divisiHR->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US005',
            'username' => 'andist',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleStaff->id_role,
            'karyawan_id_karyawan' => $karyawanAndi->id_karyawan,
        ]);

        Tugas::create([
            'id_tugas' => 'TG003',
            'karyawan_id_karyawan' => $karyawanAndi->id_karyawan,
            'judul_tugas' => 'Perbarui Data Karyawan',
            'deskripsi' => 'Periksa dan perbarui data karyawan divisi HR.',
            'deadline' => now()->addDays(4),
            'progress' => '0',
            'status' => 'baru',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        $karyawanMaya = Karyawan::create([
            'id_karyawan' => 'KR006',
            'nama' => 'Maya',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1999-11-07',
            'tanggal_rekrut' => '2023-05-10',
            'no_telepon' => '08144556677',
            'email' => 'maya@mail.com',
            'jabatan' => 'Staff HR',
            'divisi_id_divisi' => $divisiHR->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US006',
            'username' => 'mayast',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleStaff->id_role,
            'karyawan_id_karyawan' => $karyawanMaya->id_karyawan,
        ]);

        Tugas::create([
            'id_tugas' => 'TG004',
            'karyawan_id_karyawan' => $karyawanMaya->id_karyawan,
            'judul_tugas' => 'Siapkan Orientasi Karyawan',
            'deskripsi' => 'Siapkan materi orientasi untuk karyawan baru.',
            'deadline' => now()->addDays(6),
            'progress' => '0',
            'status' => 'baru',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        // --- ADD HRD ---
        $roleHrd = Role::create([
            'id_role' => 'RL003',
            'nama_role' => 'hrd'
        ]);

        $karyawanHrd = Karyawan::create([
            'id_karyawan' => 'KR007',
            'nama' => 'Pak Darmawan',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1978-06-20',
            'tanggal_rekrut' => '2012-01-15',
            'no_telepon' => '08155667788',
            'email' => 'darmawan@mail.com',
            'jabatan' => 'HRD Manager',
            'divisi_id_divisi' => $divisiHR->id_divisi,
        ]);

        User2::create([
            'id_user' => 'US007',
            'username' => 'darmawanhrd',
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleHrd->id_role,
            'karyawan_id_karyawan' => $karyawanHrd->id_karyawan,
        ]);
        // -----------------
        
        $this->command->info('Berhasil Seed Database: 3 Role (staff, kadiv, hrd), 2 Divisi, 7 Karyawan!');
    }
}