<?php

namespace Database\Seeders;

use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menambahkan 3 tugas untuk setiap staff.
     */
    public function run(): void
    {
        $staffUsers = User2::whereHas('role', function ($q) {
            $q->where('nama_role', 'staff');
        })->with(['karyawan.divisi'])->get();

        if ($staffUsers->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role staff ditemukan.');
            return;
        }

        $itTaskTemplates = [
            [
                'judul' => 'Optimasi Performa Query Dashboard',
                'deskripsi' => 'Lakukan profiling pada query PostgreSQL dan tambahkan indexing pada tabel tugas & notifikasi.',
                'status' => 'berjalan',
                'progress' => '50',
                'days' => 1, // Besok (memicu pengingat tugas)
            ],
            [
                'judul' => 'Slicing Tampilan Responsif Modul Profil',
                'deskripsi' => 'Pastikan layout dan komponen kartu profil rapi di resolusi mobile breakpoint < 768px.',
                'status' => 'baru',
                'progress' => '0',
                'days' => 3,
            ],
            [
                'judul' => 'Implementasi Integrasi REST API Kalender',
                'deskripsi' => 'Hubungkan endpoint CRUD catatan kalender dengan UI modal interaktif staff.',
                'status' => 'menunggu di-acc',
                'progress' => '100',
                'days' => 5,
            ],
            [
                'judul' => 'Refactoring Komponen Livewire Tower Database',
                'deskripsi' => 'Sederhanakan logika validasi file upload dan filter pencarian pada tabel pusat data.',
                'status' => 'berjalan',
                'progress' => '65',
                'days' => 1, // Besok
            ],
            [
                'judul' => 'Pemeriksaan Kerentanan Keamanan Sesi Auth',
                'deskripsi' => 'Audit proteksi CSRF, sanitasi input pesan masuk, dan token expiry pada sistem.',
                'status' => 'baru',
                'progress' => '10',
                'days' => 4,
            ],
            [
                'judul' => 'Penyusunan Dokumentasi Endpoint API Internal',
                'deskripsi' => 'Tuliskan spesifikasi request dan response API tugas dan notifikasi di Swagger/Postman.',
                'status' => 'menunggu di-acc',
                'progress' => '90',
                'days' => 7,
            ],
        ];

        $hrTaskTemplates = [
            [
                'judul' => 'Rekapitulasi Absensi & Lembur Karyawan Bulanan',
                'deskripsi' => 'Verifikasi seluruh data presensi bulanan dan sesuaikan dengan form surat izin yang masuk.',
                'status' => 'berjalan',
                'progress' => '60',
                'days' => 1, // Besok (memicu pengingat tugas)
            ],
            [
                'judul' => 'Penyusunan Materi Orientasi Karyawan Baru Batch 4',
                'deskripsi' => 'Siapkan slide presentasi company profile, fasilitas kerja, dan handbook karyawan.',
                'status' => 'baru',
                'progress' => '0',
                'days' => 3,
            ],
            [
                'judul' => 'Verifikasi Berkas Pengajuan Cuti dan Izin',
                'deskripsi' => 'Lakukan validasi bukti surat dokter dan form permohonan izin dari divisi operasional.',
                'status' => 'menunggu di-acc',
                'progress' => '100',
                'days' => 6,
            ],
            [
                'judul' => 'Update Database Kontak Darurat Pegawai',
                'deskripsi' => 'Sinkronisasi nomor kontak keluarga inti karyawan aktif ke database arsip HRD.',
                'status' => 'berjalan',
                'progress' => '45',
                'days' => 1, // Besok
            ],
            [
                'judul' => 'Koordinasi Pelaksanaan Medical Check-Up Tahunan',
                'deskripsi' => 'Kirim jadwal giliran MCU ke seluruh divisi dan konfirmasi ke pihak klinik rujukan.',
                'status' => 'baru',
                'progress' => '0',
                'days' => 5,
            ],
            [
                'judul' => 'Evaluasi Kuesioner Kepuasan Lingkungan Kerja',
                'deskripsi' => 'Tabulasi hasil feedback survey internal kuartal ketiga untuk bahan rapat manajemen.',
                'status' => 'menunggu di-acc',
                'progress' => '95',
                'days' => 4,
            ],
        ];

        $totalAdded = 0;

        foreach ($staffUsers as $index => $staffUser) {
            $karyawan = $staffUser->karyawan;
            if (! $karyawan) {
                continue;
            }

            $divisiName = strtolower($karyawan->divisi?->nama_divisi ?? '');
            $isIT = str_contains($divisiName, 'tech') || str_contains($divisiName, 'it') || str_contains($divisiName, 'info');

            $pool = $isIT ? $itTaskTemplates : $hrTaskTemplates;

            // Ambil 3 template unik untuk staff ini berdasarkan index atau rotasi
            $offset = ($index * 3) % count($pool);
            $selectedTemplates = [];
            for ($i = 0; $i < 3; $i++) {
                $selectedTemplates[] = $pool[($offset + $i) % count($pool)];
            }

            foreach ($selectedTemplates as $tIndex => $tmpl) {
                do {
                    $idTugas = 'TGS-' . strtoupper(Str::random(8));
                } while (Tugas::where('id_tugas', $idTugas)->exists());

                // Set deadline: jika days = 1, buat besok pukul 17:00
                $deadline = now()->addDays($tmpl['days'])->setHour(17)->setMinute(0)->setSecond(0);

                // Buat tanpa event agar tidak memicu dispatch email listener saat seeding
                Tugas::withoutEvents(function () use ($idTugas, $karyawan, $tmpl, $deadline) {
                    Tugas::create([
                        'id_tugas' => $idTugas,
                        'karyawan_id_karyawan' => $karyawan->id_karyawan,
                        'judul_tugas' => $tmpl['judul'],
                        'deskripsi' => $tmpl['deskripsi'],
                        'deadline' => $deadline,
                        'progress' => $tmpl['progress'],
                        'status' => $tmpl['status'],
                        'checklist_selesai' => false,
                        'tanggal_dibuat' => now()->subDays(rand(0, 2))->toDateString(),
                        'tanggal_update' => now()->toDateString(),
                    ]);
                });

                $totalAdded++;
            }

            $this->command->info("Menambahkan 3 tugas untuk staff: {$karyawan->nama} ({$staffUser->username})");
        }

        $this->command->info("Selesai! Total {$totalAdded} tugas baru berhasil ditambahkan.");
    }
}
