<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roleStaff = Role::firstOrCreate(
            ['id_role' => 'RL001'],
            ['nama_role' => 'staff']
        );

        $roleKadiv = Role::firstOrCreate(
            ['id_role' => 'RL002'],
            ['nama_role' => 'kadiv']
        );

        $roleHrd = Role::firstOrCreate(
            ['id_role' => 'RL003'],
            ['nama_role' => 'hrd']
        );

        $roleDirektur = Role::firstOrCreate(
            ['id_role' => 'RL004'],
            ['nama_role' => 'direktur']
        );

        $roleSuperadmin = Role::firstOrCreate(
            ['id_role' => 'RL005'],
            ['nama_role' => 'superadmin']
        );

        // 2. Struktur Divisi Lengkap (Kosongkan Staff & Kadiv lama, Divisi tetap ada)
        $this->call([
            DivisionHierarchySeeder::class,
            DirekturSeeder::class,
        ]);

        // 3. Akun Karyawan & User Baru
        $accounts = [
            [
                'id_karyawan' => 'KR001',
                'id_user' => 'US001',
                'nama' => 'Yovita Aryani, S.TP., M.P.',
                'username' => 'yovita',
                'password' => 'pass001',
                'role_id' => $roleStaff->id_role,
                'divisi_id' => 'DV008', // Publikasi Jurnal (JURNAL)
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR005', // Vini (Kadiv JURNAL)
            ],
            [
                'id_karyawan' => 'KR002',
                'id_user' => 'US002',
                'nama' => 'Agnes Galih Chris Roseita',
                'username' => 'agnes',
                'password' => 'pass002',
                'role_id' => $roleHrd->id_role, // Jadikan HRD Manager
                'divisi_id' => 'DV002', // HR (HRD)
                'jabatan' => 'HRD Manager',
                'job_level' => 'Manager',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR000', // Direktur
            ],
            [
                'id_karyawan' => 'KR003',
                'id_user' => 'US003',
                'nama' => 'Vega Rossy Danang Pratama',
                'username' => 'vega',
                'password' => 'pass003',
                'role_id' => $roleStaff->id_role,
                'divisi_id' => 'DV011', // MARKOM
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Laki-laki',
                'atasan_id' => 'KR000',
            ],
            [
                'id_karyawan' => 'KR004',
                'id_user' => 'US004',
                'nama' => 'Annisa Sholikhatul Fatih',
                'username' => 'annisa',
                'password' => 'pass004',
                'role_id' => $roleStaff->id_role,
                'divisi_id' => 'DV008', // Publikasi Jurnal (JURNAL)
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR005', // Vini (Kadiv JURNAL)
            ],
            [
                'id_karyawan' => 'KR005',
                'id_user' => 'US005',
                'nama' => 'VINI ARUMSARI, S.P., M.',
                'username' => 'vini',
                'password' => 'pass005',
                'role_id' => $roleKadiv->id_role, // Kepala Divisi
                'divisi_id' => 'DV008', // Publikasi Jurnal (JURNAL)
                'jabatan' => 'Kepala Divisi',
                'job_level' => 'Manager',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR000',
            ],
            [
                'id_karyawan' => 'KR006',
                'id_user' => 'US006',
                'nama' => 'Cindy Reta Qorinsa',
                'username' => 'cindy',
                'password' => 'pass006',
                'role_id' => $roleStaff->id_role,
                'divisi_id' => 'DV007', // Publikasi Buku (BUKU)
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR009', // Durotur (Kadiv BUKU)
            ],
            [
                'id_karyawan' => 'KR007',
                'id_user' => 'US007',
                'nama' => 'Argha Yudha Prathama',
                'username' => 'argha',
                'password' => 'pass007',
                'role_id' => $roleStaff->id_role, // Layouter jadi Staff
                'divisi_id' => 'DV007', // Publikasi Buku (BUKU)
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Laki-laki',
                'atasan_id' => 'KR009', // Durotur (Kadiv BUKU)
            ],
            [
                'id_karyawan' => 'KR008',
                'id_user' => 'US008',
                'nama' => 'Mukhamad Miftakhul',
                'username' => 'mukhamad',
                'password' => 'pass008',
                'role_id' => $roleStaff->id_role,
                'divisi_id' => 'DV011', // MARKOM
                'jabatan' => 'Staff',
                'job_level' => 'Staff',
                'jenis_kelamin' => 'Laki-laki',
                'atasan_id' => 'KR000',
            ],
            [
                'id_karyawan' => 'KR009',
                'id_user' => 'US009',
                'nama' => 'Durotur Rohma',
                'username' => 'durotur',
                'password' => 'pass009',
                'role_id' => $roleKadiv->id_role, // Kepala Divisi
                'divisi_id' => 'DV007', // Publikasi Buku (BUKU)
                'jabatan' => 'Kepala Divisi',
                'job_level' => 'Manager',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR000',
            ],
            [
                'id_karyawan' => 'KR010',
                'id_user' => 'US010',
                'nama' => 'Test Admin',
                'username' => 'testadmin',
                'password' => 'pass010',
                'role_id' => $roleSuperadmin->id_role, // Superadmin
                'divisi_id' => 'DV005', // Manajemen
                'jabatan' => 'Superadmin',
                'job_level' => 'Superadmin',
                'jenis_kelamin' => 'Laki-laki',
                'atasan_id' => null,
            ],
            [
                'id_karyawan' => 'KR011',
                'id_user' => 'US011',
                'nama' => 'Bu Sari Handayani',
                'username' => 'sarifin',
                'password' => 'pass123',
                'role_id' => $roleKadiv->id_role, // Kadiv Finance
                'divisi_id' => 'DV003', // Finance & Accounting
                'jabatan' => 'Kepala Divisi Finance',
                'job_level' => 'Manager',
                'jenis_kelamin' => 'Perempuan',
                'atasan_id' => 'KR000', // Direktur
            ],
        ];

        // Pass 1: Buat karyawan tanpa atasan_id agar tidak melanggar foreign key constraint
        foreach ($accounts as $acc) {
            Karyawan::updateOrCreate(
                ['id_karyawan' => $acc['id_karyawan']],
                [
                    'nama' => $acc['nama'],
                    'jenis_kelamin' => $acc['jenis_kelamin'],
                    'tanggal_lahir' => null, // Ignore tanggal lahir
                    'tanggal_masuk' => now()->subMonths(6)->toDateString(),
                    'status_aktif' => 'Aktif',
                    'email' => $acc['email'] ?? ($acc['username'].'@silindo.co.id'),
                    'jabatan' => $acc['jabatan'],
                    'job_level' => $acc['job_level'],
                    'divisi_id_divisi' => $acc['divisi_id'],
                    'status_karyawan' => 'tetap',
                    'aktif' => true,
                ]
            );

            User2::updateOrCreate(
                ['id_user' => $acc['id_user']],
                [
                    'username' => $acc['username'],
                    'password' => Hash::make($acc['password']),
                    'role_id_role' => $acc['role_id'],
                    'karyawan_id_karyawan' => $acc['id_karyawan'],
                ]
            );
        }

        // Pass 2: Hubungkan atasan_id setelah semua karyawan ada
        foreach ($accounts as $acc) {
            if (! empty($acc['atasan_id'])) {
                Karyawan::whereKey($acc['id_karyawan'])->update([
                    'atasan_id' => $acc['atasan_id'],
                ]);
            }
        }

        $this->command?->info('Berhasil Seed 11 Akun Karyawan & User Utama!');

        // 4. Data Pendukung Lainnya
        $this->call([
            ArsipPusatDataSeeder::class,
            HolidaySeeder::class,
            ContentTypeSeeder::class,
            ContentPillarSeeder::class,
            ContentAccountSeeder::class,
            JurnalUmumSeeder::class,
            StrukturOrganisasiSeeder::class,
            CoaSeeder::class,
        ]);

        // 5. Pesan Broadcast dari testadmin ke semua akun
        $testadmin = User2::where('username', 'testadmin')->first();
        if ($testadmin) {
            $recipients = User2::where('id_user', '!=', $testadmin->id_user)->get();
            $counter = 1;
            foreach ($recipients as $recipient) {
                Pesan::create([
                    'id_pesan' => sprintf('PSN%04d%s', $counter++, strtoupper(Str::random(6))),
                    'judul_pesan' => 'testing',
                    'deskripsi' => 'tolong coba ke page todo harian lalu tulis todo harian dan masukan file atau isi link atau catatan. terimakasih.',
                    'tipe' => 'pesan',
                    'tanggal_pesan' => now()->toDateString(),
                    'tugas_id_tugas' => null,
                    'tugas_karyawan_id_karyawan' => null,
                    'pengirim_id_user' => $testadmin->id_user,
                    'penerima_id_user' => $recipient->id_user,
                    'balasan_dari_id_pesan' => null,
                    'link_lampiran' => null,
                    'file_lampiran' => null,
                ]);
            }
            $this->command?->info("Berhasil mengirim pesan broadcast dari testadmin ke {$recipients->count()} akun!");
        }
    }
}
