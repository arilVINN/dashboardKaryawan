<?php

namespace Database\Seeders;

use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PesanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menambahkan pesan entah dari Kadiv atau HRD untuk para staff,
     * baik berupa pesan langsung/surat edaran maupun pesan diskusi terkait tugas.
     */
    public function run(): void
    {
        $kadivUsers = User2::whereHas('role', function ($q) {
            $q->where('nama_role', 'kadiv');
        })->with('karyawan.divisi')->get();

        $hrdUsers = User2::whereHas('role', function ($q) {
            $q->where('nama_role', 'hrd');
        })->with('karyawan')->get();

        $staffUsers = User2::whereHas('role', function ($q) {
            $q->where('nama_role', 'staff');
        })->with('karyawan.divisi')->get();

        if ($staffUsers->isEmpty()) {
            $this->command->warn('Tidak ada user staff yang ditemukan untuk menerima pesan.');
            return;
        }

        $allSenders = $kadivUsers->concat($hrdUsers);
        if ($allSenders->isEmpty()) {
            $this->command->warn('Tidak ada user Kadiv atau HRD yang ditemukan sebagai pengirim pesan.');
            return;
        }

        $generatePesanId = function (): string {
            do {
                $id = 'PSN' . strtoupper(Str::random(10));
            } while (Pesan::where('id_pesan', $id)->exists());
            return $id;
        };

        $totalPesan = 0;

        // =========================================================================
        // 1. Pesan & Surat Langsung dari HRD (Pak Darmawan) ke Staff
        // =========================================================================
        $primaryHrd = $hrdUsers->first() ?? $allSenders->first();

        $hrdAnnouncements = [
            [
                'judul' => 'Surat Edaran: Evaluasi Kinerja dan Penilaian KPI Semester',
                'deskripsi' => 'Kepada seluruh rekan staf, mohon untuk segera melengkapi formulir evaluasi mandiri (self-assessment) dan merekap pencapaian tugas kerja sebelum akhir bulan ini. Form penilaian dapat diakses melalui portal internal HR.',
                'tipe' => 'surat',
                'link' => 'https://hr.silindo.co.id/evaluasi-kpi',
            ],
            [
                'judul' => 'Sosialisasi Penyesuaian Jam Kerja Fleksibel (Flexi-Time)',
                'deskripsi' => 'Mulai awal bulan depan, perusahaan memberlakukan kebijakan flexi-time untuk jam kedatangan antara pukul 07.30 - 08.30 WIB dengan kewajiban pemenuhan 8 jam kerja per hari. Presensi kehadiran tetap wajib dilakukan via portal.',
                'tipe' => 'surat',
                'link' => null,
            ],
            [
                'judul' => 'Pembaruan Data Kepesertaan Asuransi & BPJS Kesehatan',
                'deskripsi' => 'Bagi karyawan yang memiliki pembaruan susunan keluarga (penambahan anak/pernikahan), harap menyerahkan salinan Kartu Keluarga (KK) terbaru ke divisi HRD paling lambat hari Jumat ini untuk pembaruan polis asuransi.',
                'tipe' => 'pesan',
                'link' => null,
            ],
        ];

        foreach ($staffUsers as $sIndex => $staffUser) {
            $announcement = $hrdAnnouncements[$sIndex % count($hrdAnnouncements)];
            $createdAt = now()->subDays(rand(1, 4))->subHours(rand(1, 8));

            Pesan::create([
                'id_pesan' => $generatePesanId(),
                'judul_pesan' => $announcement['judul'],
                'deskripsi' => $announcement['deskripsi'],
                'tipe' => $announcement['tipe'],
                'tanggal_pesan' => $createdAt->toDateString(),
                'tugas_id_tugas' => null,
                'tugas_karyawan_id_karyawan' => null,
                'pengirim_id_user' => $primaryHrd->id_user,
                'penerima_id_user' => $staffUser->id_user,
                'balasan_dari_id_pesan' => null,
                'link_lampiran' => $announcement['link'],
                'file_lampiran' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $totalPesan++;
        }

        // =========================================================================
        // 2. Pesan Langsung dari Kadiv ke Staff per Divisi
        // =========================================================================
        $kadivIT = $kadivUsers->first(function ($u) {
            $div = strtolower($u->karyawan?->divisi?->nama_divisi ?? '');
            return str_contains($div, 'it') || str_contains($div, 'tech');
        }) ?? $kadivUsers->first();

        $kadivHR = $kadivUsers->first(function ($u) {
            $div = strtolower($u->karyawan?->divisi?->nama_divisi ?? '');
            return str_contains($div, 'hr') || str_contains($div, 'human');
        }) ?? $kadivUsers->last();

        $kadivMessages = [
            'it' => [
                'judul' => 'Koordinasi Penjadwalan Rilis & Demo Sistem',
                'deskripsi' => 'Halo, tolong siapkan ringkasan modul apa saja yang sudah selesai di-deploy ke server staging. Kita agendakan demo ke user pada hari Kamis pukul 13.30 WIB.',
            ],
            'hr' => [
                'judul' => 'Review Kesiapan Acara Onboarding Karyawan Baru',
                'deskripsi' => 'Tolong pastikan ruangan pelatihan, materi presentasi handbook, dan perlengkapan seragam untuk kandidat baru minggu depan sudah siap semua ya.',
            ],
        ];

        foreach ($staffUsers as $staffUser) {
            $staffDiv = strtolower($staffUser->karyawan?->divisi?->nama_divisi ?? '');
            $isIT = str_contains($staffDiv, 'it') || str_contains($staffDiv, 'tech');

            $sender = $isIT ? ($kadivIT ?? $primaryHrd) : ($kadivHR ?? $primaryHrd);
            $msgTemplate = $isIT ? $kadivMessages['it'] : $kadivMessages['hr'];

            $createdAt = now()->subDays(rand(1, 3))->subHours(rand(2, 6));

            Pesan::create([
                'id_pesan' => $generatePesanId(),
                'judul_pesan' => $msgTemplate['judul'],
                'deskripsi' => $msgTemplate['deskripsi'],
                'tipe' => 'pesan',
                'tanggal_pesan' => $createdAt->toDateString(),
                'tugas_id_tugas' => null,
                'tugas_karyawan_id_karyawan' => null,
                'pengirim_id_user' => $sender->id_user,
                'penerima_id_user' => $staffUser->id_user,
                'balasan_dari_id_pesan' => null,
                'link_lampiran' => null,
                'file_lampiran' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $totalPesan++;
        }

        // =========================================================================
        // 3. Pesan Diskusi Terkait Tugas (Task Thread dengan Balasan)
        // =========================================================================
        foreach ($staffUsers as $staffUser) {
            $karyawanId = $staffUser->karyawan_id_karyawan;
            if (! $karyawanId) {
                continue;
            }

            // Ambil 1 tugas milik staff ini untuk dibuatkan thread diskusi
            $tugas = Tugas::where('karyawan_id_karyawan', $karyawanId)->latest('created_at')->first();
            if (! $tugas) {
                continue;
            }

            $staffDiv = strtolower($staffUser->karyawan?->divisi?->nama_divisi ?? '');
            $isIT = str_contains($staffDiv, 'it') || str_contains($staffDiv, 'tech');
            $sender = $isIT ? ($kadivIT ?? $primaryHrd) : ($kadivHR ?? $primaryHrd);

            $parentCreatedAt = now()->subDays(2)->subHours(5);
            $parentPesanId = $generatePesanId();

            // Pesan dari Kadiv / HRD mengenai tugas
            $parentPesan = Pesan::create([
                'id_pesan' => $parentPesanId,
                'judul_pesan' => 'Catatan Review: ' . $tugas->judul_tugas,
                'deskripsi' => 'Tolong pastikan detail checklist dan dokumen pendukung sudah lengkap di sistem sebelum mengajukan acc ya.',
                'tipe' => 'pesan',
                'tanggal_pesan' => $parentCreatedAt->toDateString(),
                'tugas_id_tugas' => $tugas->id_tugas,
                'tugas_karyawan_id_karyawan' => $karyawanId,
                'pengirim_id_user' => $sender->id_user,
                'penerima_id_user' => $staffUser->id_user,
                'balasan_dari_id_pesan' => null,
                'link_lampiran' => null,
                'file_lampiran' => null,
                'created_at' => $parentCreatedAt,
                'updated_at' => $parentCreatedAt,
            ]);
            $totalPesan++;

            // Balasan dari Staff
            $replyCreatedAt = now()->subDays(1)->subHours(2);
            Pesan::create([
                'id_pesan' => $generatePesanId(),
                'judul_pesan' => 'Re: Catatan Review: ' . $tugas->judul_tugas,
                'deskripsi' => 'Baik Pak/Bu, catatan sudah saya baca dan berkas pendukung sedang saya lengkapi hari ini. Terima kasih masukannya.',
                'tipe' => 'pesan',
                'tanggal_pesan' => $replyCreatedAt->toDateString(),
                'tugas_id_tugas' => $tugas->id_tugas,
                'tugas_karyawan_id_karyawan' => $karyawanId,
                'pengirim_id_user' => $staffUser->id_user,
                'penerima_id_user' => $sender->id_user,
                'balasan_dari_id_pesan' => $parentPesan->id_pesan,
                'link_lampiran' => null,
                'file_lampiran' => null,
                'created_at' => $replyCreatedAt,
                'updated_at' => $replyCreatedAt,
            ]);
            $totalPesan++;
        }

        $this->command->info("Selesai! Total {$totalPesan} pesan dari Kadiv/HRD ke Staff berhasil ditambahkan.");
    }
}
