<?php

namespace Database\Seeders;

use App\Models\KLabaRugi;
use App\Models\KeuanganAldiv;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Illuminate\Database\Seeder;

/**
 * Dummy finance data for local development.
 *
 * Covers the current period (November 2026) and the previous one so the
 * period switcher on the finance pages has something to show.
 *
 * Run with:
 *   php artisan db:seed --class=FinanceDummySeeder
 */
class FinanceDummySeeder extends Seeder
{
    private const FINANCE_USER = 'US008';

    public function run(): void
    {
        $this->seedAlokasi();
        $this->seedPemasukan();
        $this->seedPermintaanDana();
        $this->seedPengeluaran();
        $this->seedLabaRugi();

        $this->command?->info('Dummy data finance berhasil dibuat.');
    }

    private function seedAlokasi(): void
    {
        $rows = [
            // Periode berjalan
            ['KAD00001', '2026-11', 25_000_000, 'Pagu operasional dan pengembangan IT', 'DV001'],
            ['KAD00002', '2026-11', 18_000_000, 'Pagu rekrutmen dan pengembangan SDM', 'DV002'],
            ['KAD00003', '2026-11', 32_000_000, 'Pagu program kelas kerjasama', 'DV006'],
            ['KAD00004', '2026-11', 28_000_000, 'Pagu produksi dan distribusi buku', 'DV007'],
            ['KAD00005', '2026-11', 22_000_000, 'Pagu publikasi jurnal ilmiah', 'DV008'],
            ['KAD00006', '2026-11', 45_000_000, 'Pagu seminar dan kegiatan akademik', 'DV009'],
            ['KAD00007', '2026-11', 30_000_000, 'Pagu produksi konten digital', 'DV010'],

            // Periode sebelumnya
            ['KAD00008', '2026-10', 22_000_000, 'Pagu operasional dan pengembangan IT', 'DV001'],
            ['KAD00009', '2026-10', 16_000_000, 'Pagu rekrutmen dan pengembangan SDM', 'DV002'],
            ['KAD00010', '2026-10', 30_000_000, 'Pagu program kelas kerjasama', 'DV006'],
            ['KAD00011', '2026-10', 25_000_000, 'Pagu produksi dan distribusi buku', 'DV007'],
            ['KAD00012', '2026-10', 20_000_000, 'Pagu publikasi jurnal ilmiah', 'DV008'],
            ['KAD00013', '2026-10', 40_000_000, 'Pagu seminar dan kegiatan akademik', 'DV009'],
            ['KAD00014', '2026-10', 27_000_000, 'Pagu produksi konten digital', 'DV010'],
        ];

        foreach ($rows as [$id, $periode, $nominal, $keterangan, $divisiId]) {
            KeuanganAldiv::updateOrCreate(
                ['id_kad' => $id],
                [
                    'periode' => $periode,
                    'nominal_alokasi' => $nominal,
                    'keterangan' => $keterangan,
                    'divisi_id_divisi' => $divisiId,
                    'user_id_user' => self::FINANCE_USER,
                ]
            );
        }
    }

    private function seedPemasukan(): void
    {
        $rows = [
            // November 2026
            ['KP000001', 'IN261101', 'Proyek', 75_000_000, '2026-11-03', 'Pembayaran termin I proyek pelatihan korporat', 'DV009'],
            ['KP000002', 'IN261102', 'Layanan / Jasa', 32_500_000, '2026-11-05', 'Jasa konsultasi konten digital', 'DV010'],
            ['KP000003', 'IN261103', 'Proyek', 48_000_000, '2026-11-10', 'Penjualan 1.200 eksemplar buku terbitan', 'DV007'],
            ['KP000004', 'IN261104', 'Investasi', 15_000_000, '2026-11-12', 'Bunga deposito dan dividen', null],
            ['KP000005', 'IN261105', 'Lain-lain', 4_750_000, '2026-11-14', 'Pengembalian sisa kas kegiatan', null],

            // Oktober 2026
            ['KP000006', 'IN261001', 'Proyek', 62_000_000, '2026-10-07', 'Pelunasan proyek seminar nasional', 'DV009'],
            ['KP000007', 'IN261002', 'Layanan / Jasa', 21_000_000, '2026-10-12', 'Pendapatan jasa publikasi jurnal', 'DV008'],
            ['KP000008', 'IN261003', 'Suntikan Modal', 50_000_000, '2026-10-20', 'Suntikan modal kerja', null],
            ['KP000009', 'IN261004', 'Proyek', 38_000_000, '2026-10-25', 'Paket pelatihan kelas kerjasama', 'DV006'],
            ['KP000010', 'IN261005', 'Lain-lain', 3_200_000, '2026-10-29', 'Penjualan aset kecil', 'DV001'],
        ];

        foreach ($rows as [$id, $kode, $kategori, $nominal, $tanggal, $keterangan, $divisiId]) {
            KPemasukan::updateOrCreate(
                ['id_kp' => $id],
                [
                    'kode_transaksi' => $kode,
                    'kategori_pemasukan' => $kategori,
                    'nominal' => $nominal,
                    'tanggal_masuk' => $tanggal,
                    'keterangan' => $keterangan,
                    'bukti_dokumen' => null,
                    'divisi_id_divisi' => $divisiId,
                    'user_id_user' => self::FINANCE_USER,
                ]
            );
        }
    }

    private function seedPermintaanDana(): void
    {
        $rows = [
            [
                'id_kpd' => 'KPD00001',
                'nomor_pengajuan' => 'REQ26110001',
                'judul_pengajuan' => 'Pelatihan Public Speaking Batch 12',
                'keperluan' => 'Honor trainer, sewa venue, dan konsumsi peserta',
                'nominal_diajukan' => 12_500_000,
                'status' => KPermintDana::STATUS_DICAIRKAN,
                'catatan_finance' => 'Disetujui sesuai pagu divisi.',
                'tanggal_disetujui' => '2026-11-04',
                'divisi_id_divisi' => 'DV006',
                'user_id_user' => 'US004',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00002',
                'nomor_pengajuan' => 'REQ26110002',
                'judul_pengajuan' => 'Cetak 1.000 Eksemplar Buku',
                'keperluan' => 'Biaya cetak, koreksi, dan distribusi buku',
                'nominal_diajukan' => 18_000_000,
                'status' => KPermintDana::STATUS_DISETUJUI,
                'catatan_finance' => 'Menunggu proses pencairan.',
                'tanggal_disetujui' => '2026-11-10',
                'divisi_id_divisi' => 'DV007',
                'user_id_user' => 'US010',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00003',
                'nomor_pengajuan' => 'REQ26110003',
                'judul_pengajuan' => 'Biaya APC Jurnal Terindeks',
                'keperluan' => 'Article processing charge untuk dua artikel',
                'nominal_diajukan' => 9_750_000,
                'status' => KPermintDana::STATUS_MENUNGGU,
                'catatan_finance' => null,
                'tanggal_disetujui' => null,
                'divisi_id_divisi' => 'DV008',
                'user_id_user' => 'US011',
                'disetujui_oleh_id_user' => null,
            ],
            [
                'id_kpd' => 'KPD00004',
                'nomor_pengajuan' => 'REQ26110004',
                'judul_pengajuan' => 'Seminar Nasional 2026',
                'keperluan' => 'Sewa venue, narasumber, dan publikasi acara',
                'nominal_diajukan' => 35_000_000,
                'status' => KPermintDana::STATUS_DICAIRKAN,
                'catatan_finance' => 'Disetujui, dana sudah dicairkan.',
                'tanggal_disetujui' => '2026-11-06',
                'divisi_id_divisi' => 'DV009',
                'user_id_user' => 'US012',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00005',
                'nomor_pengajuan' => 'REQ26110005',
                'judul_pengajuan' => 'Sewa Studio dan Peralatan Produksi',
                'keperluan' => 'Sewa studio, lighting, dan kamera',
                'nominal_diajukan' => 7_500_000,
                'status' => KPermintDana::STATUS_DITOLAK,
                'catatan_finance' => 'Gunakan aset internal yang masih tersedia.',
                'tanggal_disetujui' => null,
                'divisi_id_divisi' => 'DV010',
                'user_id_user' => 'US014',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00006',
                'nomor_pengajuan' => 'REQ26110006',
                'judul_pengajuan' => 'Lisensi Software Desain',
                'keperluan' => 'Lisensi tahunan untuk tim desain',
                'nominal_diajukan' => 15_000_000,
                'status' => KPermintDana::STATUS_MENUNGGU,
                'catatan_finance' => null,
                'tanggal_disetujui' => null,
                'divisi_id_divisi' => 'DV001',
                'user_id_user' => 'US003',
                'disetujui_oleh_id_user' => null,
            ],
            [
                'id_kpd' => 'KPD00007',
                'nomor_pengajuan' => 'REQ26110007',
                'judul_pengajuan' => 'Transportasi Peserta Workshop',
                'keperluan' => 'Transportasi peserta dari luar kota',
                'nominal_diajukan' => 3_200_000,
                'status' => KPermintDana::STATUS_DISETUJUI,
                'catatan_finance' => 'Disetujui, siap dicairkan.',
                'tanggal_disetujui' => '2026-11-13',
                'divisi_id_divisi' => 'DV006',
                'user_id_user' => 'US004',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00008',
                'nomor_pengajuan' => 'REQ26110008',
                'judul_pengajuan' => 'Konsumsi Rapat Koordinasi',
                'keperluan' => 'Konsumsi dan perlengkapan rapat',
                'nominal_diajukan' => 1_800_000,
                'status' => KPermintDana::STATUS_DITOLAK,
                'catatan_finance' => 'Ajukan melalui pos anggaran operasional.',
                'tanggal_disetujui' => null,
                'divisi_id_divisi' => 'DV009',
                'user_id_user' => 'US012',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],

            // Oktober 2026
            [
                'id_kpd' => 'KPD00009',
                'nomor_pengajuan' => 'REQ26100009',
                'judul_pengajuan' => 'Cetak Katalog Pameran',
                'keperluan' => 'Cetak 500 katalog untuk pameran buku',
                'nominal_diajukan' => 14_000_000,
                'status' => KPermintDana::STATUS_DICAIRKAN,
                'catatan_finance' => 'Sudah dicairkan.',
                'tanggal_disetujui' => '2026-10-15',
                'divisi_id_divisi' => 'DV007',
                'user_id_user' => 'US010',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
            [
                'id_kpd' => 'KPD00010',
                'nomor_pengajuan' => 'REQ26100010',
                'judul_pengajuan' => 'Langganan Database Referensi',
                'keperluan' => 'Langganan tahunan database jurnal',
                'nominal_diajukan' => 6_500_000,
                'status' => KPermintDana::STATUS_DICAIRKAN,
                'catatan_finance' => 'Sudah dicairkan.',
                'tanggal_disetujui' => '2026-10-22',
                'divisi_id_divisi' => 'DV008',
                'user_id_user' => 'US011',
                'disetujui_oleh_id_user' => self::FINANCE_USER,
            ],
        ];

        foreach ($rows as $row) {
            KPermintDana::updateOrCreate(
                ['id_kpd' => $row['id_kpd']],
                $row
            );
        }
    }

    private function seedPengeluaran(): void
    {
        $rows = [
            // November 2026
            ['KO000001', 'OUT26110001', 'KPD00001', 12_500_000, '2026-11-05', 'Transfer Bank', 'Pencairan pelatihan Public Speaking Batch 12', 'DV006'],
            ['KO000002', 'OUT26110002', 'KPD00004', 35_000_000, '2026-11-07', 'Transfer Bank', 'Pencairan Seminar Nasional 2026', 'DV009'],
            ['KO000003', 'OUT26110003', null, 2_250_000, '2026-11-08', 'Kas Kecil', 'Pembelian properti dan perlengkapan konten', 'DV010'],
            ['KO000004', 'OUT26110004', null, 1_500_000, '2026-11-11', 'Transfer Bank', 'Langganan portal rekrutmen', 'DV002'],
            ['KO000005', 'OUT26110005', null, 4_800_000, '2026-11-13', 'Transfer Bank', 'Perpanjangan hosting dan domain', 'DV001'],

            // Oktober 2026
            ['KO000006', 'OUT26100006', 'KPD00009', 14_000_000, '2026-10-16', 'Transfer Bank', 'Pencairan cetak katalog pameran', 'DV007'],
            ['KO000007', 'OUT26100007', 'KPD00010', 6_500_000, '2026-10-23', 'Transfer Bank', 'Pencairan langganan database referensi', 'DV008'],
            ['KO000008', 'OUT26100008', null, 3_400_000, '2026-10-18', 'Kas Kecil', 'Konsumsi dan transportasi peserta', 'DV006'],
            ['KO000009', 'OUT26100009', null, 8_200_000, '2026-10-27', 'Transfer Bank', 'Sewa venue kegiatan akademik', 'DV009'],
        ];

        foreach ($rows as [$id, $kode, $kpdId, $nominal, $tanggal, $metode, $keterangan, $divisiId]) {
            KPengeluaran::updateOrCreate(
                ['id_kpengeluaran' => $id],
                [
                    'kode_pengeluaran' => $kode,
                    'kpd_id_kpd' => $kpdId,
                    'nominal' => $nominal,
                    'tanggal_cair' => $tanggal,
                    'metode_pembayaran' => $metode,
                    'bukti_pencairan' => null,
                    'keterangan' => $keterangan,
                    'divisi_id_divisi' => $divisiId,
                    'user_id_user' => self::FINANCE_USER,
                ]
            );
        }
    }

    private function seedLabaRugi(): void
    {
        $rows = [
            // November 2026 — minggu 1
            ['KLR00001', '2026-11', 1, '2026-11-02', '2026-11-08', 8_000_000, 3_200_000, 'disetujui', 'DV001', 'US003', 'Bukti lengkap.'],
            ['KLR00002', '2026-11', 1, '2026-11-02', '2026-11-08', 5_000_000, 2_100_000, 'disetujui', 'DV002', 'US007', 'Bukti lengkap.'],
            ['KLR00003', '2026-11', 1, '2026-11-02', '2026-11-08', 18_000_000, 7_500_000, 'disetujui', 'DV006', 'US004', 'Sesuai laporan kegiatan.'],
            ['KLR00004', '2026-11', 1, '2026-11-02', '2026-11-08', 15_000_000, 6_000_000, 'disetujui', 'DV007', 'US010', 'Sesuai laporan kegiatan.'],
            ['KLR00005', '2026-11', 1, '2026-11-02', '2026-11-08', 9_000_000, 4_000_000, 'disetujui', 'DV008', 'US011', 'Bukti lengkap.'],
            ['KLR00006', '2026-11', 1, '2026-11-02', '2026-11-08', 25_000_000, 12_000_000, 'disetujui', 'DV009', 'US012', 'Sesuai laporan kegiatan.'],
            ['KLR00007', '2026-11', 1, '2026-11-02', '2026-11-08', 12_000_000, 5_000_000, 'disetujui', 'DV010', 'US014', 'Bukti lengkap.'],

            // November 2026 — minggu 2
            ['KLR00008', '2026-11', 2, '2026-11-09', '2026-11-15', 9_500_000, 4_100_000, 'menunggu', 'DV001', 'US003', null],
            ['KLR00009', '2026-11', 2, '2026-11-09', '2026-11-15', 6_200_000, 2_800_000, 'perlu_revisi', 'DV002', 'US007', 'Lampiran bukti pengeluaran belum lengkap.'],
            ['KLR00010', '2026-11', 2, '2026-11-09', '2026-11-15', 21_500_000, 9_800_000, 'disetujui', 'DV006', 'US004', 'Sesuai laporan kegiatan.'],
            ['KLR00011', '2026-11', 2, '2026-11-09', '2026-11-15', 12_000_000, 5_500_000, 'menunggu', 'DV007', 'US010', null],
            ['KLR00012', '2026-11', 2, '2026-11-09', '2026-11-15', 11_000_000, 5_200_000, 'disetujui', 'DV008', 'US011', 'Bukti lengkap.'],
            ['KLR00013', '2026-11', 2, '2026-11-09', '2026-11-15', 30_000_000, 14_500_000, 'perlu_revisi', 'DV009', 'US012', 'Rincian biaya venue belum dilampirkan.'],
            ['KLR00014', '2026-11', 2, '2026-11-09', '2026-11-15', 14_500_000, 6_200_000, 'menunggu', 'DV010', 'US014', null],

            // Oktober 2026 — minggu 4
            ['KLR00015', '2026-10', 4, '2026-10-19', '2026-10-25', 7_500_000, 3_000_000, 'disetujui', 'DV001', 'US003', 'Sesuai laporan kegiatan.'],
            ['KLR00016', '2026-10', 4, '2026-10-19', '2026-10-25', 4_800_000, 2_000_000, 'disetujui', 'DV002', 'US007', 'Sesuai laporan kegiatan.'],
            ['KLR00017', '2026-10', 4, '2026-10-19', '2026-10-25', 16_500_000, 6_800_000, 'disetujui', 'DV006', 'US004', 'Sesuai laporan kegiatan.'],
            ['KLR00018', '2026-10', 4, '2026-10-19', '2026-10-25', 13_000_000, 5_500_000, 'disetujui', 'DV007', 'US010', 'Sesuai laporan kegiatan.'],
            ['KLR00019', '2026-10', 4, '2026-10-19', '2026-10-25', 8_500_000, 3_600_000, 'disetujui', 'DV008', 'US011', 'Sesuai laporan kegiatan.'],
            ['KLR00020', '2026-10', 4, '2026-10-19', '2026-10-25', 22_000_000, 10_500_000, 'disetujui', 'DV009', 'US012', 'Sesuai laporan kegiatan.'],
            ['KLR00021', '2026-10', 4, '2026-10-19', '2026-10-25', 11_000_000, 4_500_000, 'disetujui', 'DV010', 'US014', 'Sesuai laporan kegiatan.'],
        ];

        foreach ($rows as [$id, $periode, $minggu, $mulai, $selesai, $pendapatan, $pengeluaran, $status, $divisiId, $userId, $catatan]) {
            $laba = KLabaRugi::hitungLaba($pendapatan, $pengeluaran);
            $margin = KLabaRugi::hitungMargin($pendapatan, $pengeluaran);

            $diverifikasi = in_array($status, [KLabaRugi::STATUS_DISETUJUI, KLabaRugi::STATUS_PERLU_REVISI], true);

            KLabaRugi::updateOrCreate(
                ['id_klr' => $id],
                [
                    'periode' => $periode,
                    'minggu_ke' => $minggu,
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai,
                    'total_pendapatan' => $pendapatan,
                    'total_pengeluaran' => $pengeluaran,
                    'laba_bersih' => $laba,
                    'margin_persen' => $margin,
                    'bukti_laporan' => null,
                    'catatan' => $catatan,
                    'status_verifikasi' => $status,
                    'catatan_verifikasi' => $diverifikasi ? $catatan : null,
                    'tanggal_kirim' => $selesai,
                    'divisi_id_divisi' => $divisiId,
                    'user_id_user' => $userId,
                    'diverifikasi_oleh_id_user' => $diverifikasi ? self::FINANCE_USER : null,
                ]
            );
        }
    }
}
