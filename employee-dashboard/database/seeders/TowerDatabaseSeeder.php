<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\TowerDatabase;
use Illuminate\Database\Seeder;

class TowerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menambahkan data random realistis untuk Pusat Data (Tower Database).
     */
    public function run(): void
    {
        // Cari ID karyawan Kadiv atau HRD sebagai pembuat data
        $managerKaryawanIds = Karyawan::whereHas('user.role', function ($q) {
            $q->whereIn('nama_role', ['kadiv', 'hrd']);
        })->pluck('id_karyawan')->toArray();

        // Fallback jika belum ada manager
        if (empty($managerKaryawanIds)) {
            $managerKaryawanIds = Karyawan::pluck('id_karyawan')->toArray();
        }

        $pickAuthor = function () use ($managerKaryawanIds) {
            if (empty($managerKaryawanIds)) {
                return null;
            }
            return $managerKaryawanIds[array_rand($managerKaryawanIds)];
        };

        $items = [
            // --- JENIS: FILE ---
            [
                'nama' => 'SOP Operasional Pemeliharaan Tower BTS 2026',
                'keterangan' => 'Standar operasional prosedur terkait checklist inspeksi fisik dan kelistrikan tower BTS area regional.',
                'jenis' => TowerDatabase::JENIS_FILE,
                'file_path' => 'tower_files/SOP_Pemeliharaan_Tower_BTS_2026.pdf',
                'file_name' => 'SOP_Pemeliharaan_Tower_BTS_2026.pdf',
                'file_size' => '3.8 MB',
                'file_ext' => 'pdf',
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => null,
            ],
            [
                'nama' => 'Formulir Pengajuan Izin Site Access Tower',
                'keterangan' => 'Template formulir permohonan akses lokasi tower untuk vendor pihak ketiga dan tim teknisi lapangan.',
                'jenis' => TowerDatabase::JENIS_FILE,
                'file_path' => 'tower_files/Form_Izin_Site_Access.docx',
                'file_name' => 'Form_Izin_Site_Access.docx',
                'file_size' => '620 KB',
                'file_ext' => 'docx',
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => null,
            ],
            [
                'nama' => 'Data Inventaris Perangkat & Transceiver Q3',
                'keterangan' => 'Daftar serial number perangkat radio, genset cadangan, dan modul baterai cadangan seluruh unit.',
                'jenis' => TowerDatabase::JENIS_FILE,
                'file_path' => 'tower_files/Inventaris_Perangkat_Q3.xlsx',
                'file_name' => 'Inventaris_Perangkat_Q3.xlsx',
                'file_size' => '1.5 MB',
                'file_ext' => 'xlsx',
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => null,
            ],
            [
                'nama' => 'Buku Pedoman K3 & Mitigasi Risiko Lapangan',
                'keterangan' => 'Panduan keselamatan kerja di ketinggian, standar APD wajib, dan prosedur tanggap darurat kecelakaan kerja.',
                'jenis' => TowerDatabase::JENIS_FILE,
                'file_path' => 'tower_files/Pedoman_K3_Tower_2026.pdf',
                'file_name' => 'Pedoman_K3_Tower_2026.pdf',
                'file_size' => '4.2 MB',
                'file_ext' => 'pdf',
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => null,
            ],
            [
                'nama' => 'Template Laporan Gangguan & Root Cause Analysis',
                'keterangan' => 'Format pelaporan resmi insiden downtime, analisis penyebab utama, dan tindakan korektif (CAPA).',
                'jenis' => TowerDatabase::JENIS_FILE,
                'file_path' => 'tower_files/Template_Laporan_RCA.docx',
                'file_name' => 'Template_Laporan_RCA.docx',
                'file_size' => '840 KB',
                'file_ext' => 'docx',
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => null,
            ],

            // --- JENIS: NOMOR ---
            [
                'nama' => 'Pusat Komando NOC & Dispatcher 24 Jam',
                'keterangan' => 'Kontak pusat monitoring NOC untuk eskalasi outage jaringan dan darurat kelistrikan transmisi.',
                'jenis' => TowerDatabase::JENIS_NOMOR,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => '0271-7890123 / 0812-9876-5432',
                'email_kontak' => 'noc.dispatch@silindo.co.id',
                'url_link' => null,
            ],
            [
                'nama' => 'Vendor Genset & Suplai Bahan Bakar Solo-Semarang',
                'keterangan' => 'Penyedia suplai solar cadangan darurat dan perbaikan mekanik genset site tower Jawa Tengah.',
                'jenis' => TowerDatabase::JENIS_NOMOR,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => '0813-2890-1122',
                'email_kontak' => 'order@suplaigenset-jtg.com',
                'url_link' => null,
            ],
            [
                'nama' => 'Hotline Tim Medis & RS Rujukan Darurat Kerja',
                'keterangan' => 'Instalasi Gawat Darurat rekanan BPJS Ketenagakerjaan untuk insiden kerja lapangan.',
                'jenis' => TowerDatabase::JENIS_NOMOR,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => '119 / 0271-714567',
                'email_kontak' => 'igd.rujukan@rs-mitra.co.id',
                'url_link' => null,
            ],
            [
                'nama' => 'Helpdesk Layanan Provider Fiber Optik Backhaul',
                'keterangan' => 'Kontak pelaporan putus kabel fiber dan penurunan performa bandwidth trunking utama.',
                'jenis' => TowerDatabase::JENIS_NOMOR,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => '0800-1-998877',
                'email_kontak' => 'support@fiber-backhaul.net',
                'url_link' => null,
            ],
            [
                'nama' => 'Kontak Pengamanan Area & Kepolisian Sektor Lokal',
                'keterangan' => 'Nomor piket Babinsa dan Polsek setempat untuk pengamanan aset tower di area rawan.',
                'jenis' => TowerDatabase::JENIS_NOMOR,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => '0298-321110',
                'email_kontak' => 'keamanan.wilayah@silindo.co.id',
                'url_link' => null,
            ],

            // --- JENIS: LINK ---
            [
                'nama' => 'Dashboard Live Monitoring Uptime Jaringan & Sensor',
                'keterangan' => 'Grafik real-time metrik tegangan rectifier, suhu shelter, dan status baterai tower regional.',
                'jenis' => TowerDatabase::JENIS_LINK,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => 'https://monitoring.silindo.co.id/dashboard/regional-central',
            ],
            [
                'nama' => 'Peta Koordinat & Tata Letak Site Tower (GIS Map)',
                'keterangan' => 'Aplikasi web peta geografis persebaran tower, ketinggian elevasi, dan radius cakupan sinyal.',
                'jenis' => TowerDatabase::JENIS_LINK,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => 'https://gis.silindo.co.id/maps/site-locations',
            ],
            [
                'nama' => 'Cloud Storage Repositori Foto & Dokumentasi Site',
                'keterangan' => 'Folder cloud bersama berisi dokumentasi foto fisik shelter, grounding, dan antena terbaru.',
                'jenis' => TowerDatabase::JENIS_LINK,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => 'https://drive.google.com/drive/folders/silindo-tower-documentation',
            ],
            [
                'nama' => 'Portal Ticketing Trouble Ticket & Maintenance Log',
                'keterangan' => 'Sistem tiket terpusat untuk pelaporan eskalasi perbaikan hardware dan instalasi baru.',
                'jenis' => TowerDatabase::JENIS_LINK,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => 'https://jira.silindo.co.id/projects/TOWER',
            ],
            [
                'nama' => 'Dokumentasi Skema Kelistrikan & Single Line Diagram',
                'keterangan' => 'Tautan ke repository schematic diagram kelistrikan genset, PLN, dan automatic transfer switch (ATS).',
                'jenis' => TowerDatabase::JENIS_LINK,
                'file_path' => null,
                'file_name' => null,
                'file_size' => null,
                'file_ext' => null,
                'nilai_kontak' => null,
                'email_kontak' => null,
                'url_link' => 'https://docs.silindo.co.id/schematics/electrical-single-line',
            ],
        ];

        $insertedCount = 0;
        foreach ($items as $data) {
            $data['dibuat_oleh_id_karyawan'] = $pickAuthor();
            $data['created_at'] = now()->subDays(rand(1, 14))->subHours(rand(1, 12));
            $data['updated_at'] = $data['created_at'];

            TowerDatabase::create($data);
            $insertedCount++;
        }

        $this->command->info("Berhasil menambahkan {$insertedCount} data pada Pusat Data (Tower Database).");
    }
}
