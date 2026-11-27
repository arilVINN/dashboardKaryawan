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

        // 2. Struktur Divisi Lengkap & Akun Karyawan Terbaru
        $this->call([
            DivisionHierarchySeeder::class,
            KaryawanTerbaruSeeder::class,
        ]);

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
