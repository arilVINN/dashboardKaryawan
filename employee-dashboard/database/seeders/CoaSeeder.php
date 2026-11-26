<?php

namespace Database\Seeders;

use App\Models\CoaAkun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CoaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // [kode lama, nama lama, kode baru, nama baru]
            $pindah = [
                ['1101', 'Kas', '1110', 'Kas'],
                ['1102', 'Bank', '1201', 'Bank BRI OPR'],
                ['2101', 'Utang Usaha', '2110', 'Hutang Usaha'],
                ['3101', 'Modal Pemilik', '3210', 'Setoran Modal Direktur'],
                ['4101', 'Pendapatan Jasa', '4110', 'Pendapatan Jasa Konsultasi'],
                ['4102', 'Pendapatan Lain-lain', '7500', 'Pendapatan Lain - Lain'],
                ['5101', 'Beban Gaji', '6000', 'Biaya Gaji Karyawan'],
                ['5102', 'Beban Operasional', '7700', 'Biaya Lain - Lain'],
                ['5103', 'Beban Perlengkapan', '5710', 'Biaya Perlengkapan Kantor'],
            ];

            foreach ($pindah as [$kodeLama, $namaLama, $kodeBaru, $namaBaru]) {
                $targetExists = CoaAkun::where('kode', $kodeBaru)->exists();
                if ($targetExists) {
                    CoaAkun::where('kode', $kodeLama)->delete();
                } else {
                    CoaAkun::where('kode', $kodeLama)
                        ->where('nama', $namaLama)
                        ->update([
                            'kode' => $kodeBaru,
                            'nama' => $namaBaru,
                            'kelompok' => $this->kelompokUtama($kodeBaru),
                            'kelompok_detail' => $this->kelompokDetail($kodeBaru),
                        ]);
                }
            }
foreach ($this->daftarAkun() as [$kode, $nama]) {
    CoaAkun::updateOrCreate(
        ['kode' => $kode],
        [
            'nama' => $nama,
            'kelompok' => $this->kelompokUtama($kode),
            'kelompok_detail' => $this->kelompokDetail($kode),
            'aktif' => true,
        ]
    );
}
        });
    }
    /**
     * Kolom kelompok hanya menerima 5 nilai (lihat CoaAkun::KELOMPOK),
     * jadi kelompok dihitung dari kode akun.
     */
    private function kelompokUtama(string $kode): string
    {
        if (in_array($kode, ['2300', '2310'], true)) {
            return 'Ekuitas';
        }

        if (in_array($kode, ['7300', '7400', '7500', '7800', '7900'], true)) {
            return 'Pendapatan';
        }

        return match ($kode[0]) {
            '1' => 'Aset',
            '2' => 'Kewajiban',
            '3' => 'Ekuitas',
            '4' => 'Pendapatan',
            default => 'Beban', // 5xxx, 6xxx, 7000-7200, 7600-7700
        };
    }
    /**
     * @return array<int, array{0:string,1:string}> [kode, nama]
     */
    
        /**
     * Menentukan kategori akun sesuai COA PT SILINDO.
     */
    private function kelompokDetail(string $kode): string
    {
        $nomor = (int) $kode;

        return match (true) {
            $nomor === 1100 => 'Aktiva',
            $nomor >= 1110 && $nomor <= 1670 => 'Aktiva Lancar',
            $nomor >= 1700 && $nomor <= 1722 => 'Aktiva Tetap',

            in_array($nomor, [2100, 2200], true) => 'Pasiva',
            $nomor >= 2101 && $nomor <= 2140 => 'Hutang Lancar',
            $nomor === 2201 => 'Titipan',

            in_array($nomor, [2300, 2310, 3100, 3200, 3210], true) => 'Modal',

            $nomor >= 4100 && $nomor <= 4600 => 'Pendapatan Usaha',

            $nomor >= 5000 && $nomor <= 5410 => 'Beban Pokok Jasa',
            $nomor >= 5500 && $nomor <= 6800 => 'Beban Operasional',
            $nomor === 6900 => 'Beban Penyusutan',
            $nomor >= 7000 && $nomor <= 7200 => 'Beban Pemasaran',

            ($nomor >= 7300 && $nomor <= 7500)
                || ($nomor >= 7800 && $nomor <= 7900)
                    => 'Pendapatan Non Operasional',

            $nomor >= 7600 && $nomor <= 7700 => 'Beban Operasional',

            default => '-',
        };
    }
    private function daftarAkun(): array
    {
   

        return [
            // ===== AKTIVA =====
            ['1000', 'Aktiva'],
            ['1100', 'Aktiva Lancar'],
            ['1110', 'Kas'],
            ['1111', 'Kas Besar'],
            ['1112', 'Kas Kecil'],
            ['1200', 'Bank BRI Operasional 008101002352562'],
            ['1201', 'Bank BRI OPR'],
            ['1300', 'Piutang'],
            ['1301', 'Piutang Peserta Pelatihan'],
            ['1302', 'Piutang Publikasi'],
            ['1303', 'Piutang Kelas Kerjasama'],
            ['1310', 'Piutang Karyawan'],
            ['1320', 'Piutang Lain - Lain'],
            ['1400', 'Prepayment'],
            ['1500', 'Persediaan'],
            ['1501', 'Persediaan ATK'],
            ['1502', 'Persediaan Barang Promosi'],
            ['1503', 'Persediaan Barang Hadiah'],
            ['1600', 'Biaya Dibayar di Muka'],
            ['1610', 'Sewa Dibayar di Muka'],
            ['1620', 'Langganan Software Dibayar di Muka'],
            ['1621', 'Biaya Hosting / Domain Dibayar di Muka'],
            ['1630', 'Biaya Kegiatan Dibayar di Muka'],
            ['1640', 'Uang Muka Kegiatan'],
            ['1641', 'Uang Muka Seminar'],
            ['1642', 'Uang Muka Vendor'],
            ['1643', 'Uang Muka Narasumber'],
            ['1644', 'Uang Muka Hotel dan Venue'],
            ['1650', 'Pajak Dibayar di Muka'],
            ['1660', 'PPN Masukan'],
            ['1670', 'Klaim Restitusi Pajak'],
            ['1700', 'Aktiva Tetap'],
            ['1710', 'Kendaraan'],
            ['1711', 'Inventaris Kantor dan Perabotan'],
            ['1720', 'Akm. Penyusutan'],
            ['1721', 'Akm. Penyusutan Kendaraan'],
            ['1722', 'Akm. Penyusutan Inventaris'],

            // ===== PASIVA =====
            ['2000', 'Pasiva'],
            ['2100', 'Hutang Lancar'],
            ['2101', 'Hutang BPJS Ketenagakerjaan'],
            ['2102', 'Hutang BPJS Kesehatan'],
            ['2110', 'Hutang Usaha'],
            ['2120', 'Hutang Honorarium Dosen'],
            ['2121', 'Hutang Narasumber'],
            ['2122', 'Hutang Royalti Penulis'],
            ['2123', 'Hutang Pajak PPh 21'],
            ['2124', 'Hutang Pajak PPN'],
            ['2125', 'Hutang Pajak Lainnya'],
            ['2130', 'Hutang Biaya'],
            ['2140', 'Hutang Kepada Direktur'],
            ['2200', 'Titipan'],
            ['2201', 'PPN Masa (kurang bayar)'],
            ['2300', 'Rugi Laba Sebelum Bulan Berjalan'],
            ['2310', 'Rugi Laba Bulan Berjalan'],

            // ===== MODAL =====
            ['3000', 'Modal'],
            ['3100', 'Laba Ditahan'],
            ['3200', 'Prive Pemilik'],
            ['3210', 'Setoran Modal Direktur'],

            // ===== PENDAPATAN USAHA =====
            ['4000', 'Pendapatan Usaha'],
            ['4100', 'Pendapatan Jasa Penerbitan Buku'],
            ['4101', 'Pendapatan Penjualan Buku Cetak'],
            ['4102', 'Pendapatan Penjualan E-Book'],
            ['4103', 'Pendapatan Editing Naskah'],
            ['4104', 'Pendapatan Layout dan Desain Buku'],
            ['4105', 'Pendapatan ISBN dan Pengurusan Administrasi'],
            ['4106', 'Pendapatan Cetak Buku'],
            ['4107', 'Pendapatan Jasa Parafrase'],
            ['4108', 'Pendapatan Jasa Public speaking'],
            ['4109', 'Pendapatan Jasa Pelatihan'],
            ['4110', 'Pendapatan Jasa Konsultasi'],
            ['4200', 'Pendapatan Publikasi Artikel / Jurnal'],
            ['4201', 'Pendapatan Fast Track Review'],
            ['4202', 'Pendapatan Proofreading Artikel'],
            ['4203', 'Pendapatan DOI dan Metadata'],
            ['4204', 'Pendapatan Pengelolaan Jurnal'],
            ['4205', 'Pendapatan Pendampingan Akreditasi Jurnal'],
            ['4300', 'Pendapatan Webinar'],
            ['4301', 'Pendapatan Workshop'],
            ['4302', 'Pendapatan Pelatihan Institusi'],
            ['4303', 'Pendapatan Pelatihan Offline'],
            ['4304', 'Pendapatan Pelatihan Online'],
            ['4305', 'Pendapatan Sertifikasi'],
            ['4306', 'Pendapatan Mentoring'],
            ['4400', 'Pendapatan Kerja Sama Perguruan Tinggi'],
            ['4401', 'Pendapatan Kerja Sama Penelitian'],
            ['4402', 'Pendapatan Konsultasi Akademik'],
            ['4403', 'Pendapatan Sponsorship'],
            ['4404', 'Pendapatan Program Kemitraan'],
            ['4405', 'Pendapatan Academic Events Internasional'],
            ['4406', 'Pendapatan International Visiting Lecturer'],
            ['4407', 'Pendapatan Visiting Professor'],
            ['4408', 'Pendapatan Student Mobility'],
            ['4409', 'Pendapatan Short Course Internasional'],
            ['4410', 'Pendapatan Kelas Kerjasama'],
            ['4500', 'Pendapatan Sewa Kamar Bulanan'],
            ['4501', 'Pendapatan Sewa Tahunan'],
            ['4510', 'Pendapatan Fasilitas Tambahan'],
            ['4600', 'Digital Services (Hosting, OJS, Website, DOI, dll.)'],

            // ===== BEBAN POKOK JASA =====
            ['5000', 'Biaya Cetak Buku'],
            ['5100', 'Biaya Editing'],
            ['5101', 'Biaya Layout dan Desain'],
            ['5102', 'Royalti Penulis'],
            ['5103', 'Honor Editor'],
            ['5104', 'Honor Reviewer'],
            ['5105', 'Biaya Hosting Jurnal'],
            ['5106', 'Biaya Publikasi Artikel'],
            ['5107', 'Biaya Submission Artikel'],
            ['5108', 'Biaya APC (Article Processing Charge)'],
            ['5109', 'Biaya ISBN/Legalitas Penerbit'],
            ['5200', 'Honor Dosen/Narasumber/Konsultan'],
            ['5201', 'Biaya Training'],
            ['5202', 'Honorarium'],
            ['5300', 'Biaya Sertifikat'],
            ['5301', 'Beban Venue Event'],
            ['5302', 'Beban Narasumber'],
            ['5303', 'Beban Konsumsi Event'],
            ['5304', 'Beban Promosi Event'],
            ['5305', 'Beban Cashback/Insentif Mitra Event'],
            ['5400', 'Biaya Platform Zoom / Lainnya'],
            ['5410', 'Biaya Modul Pelatihan'],

            // ===== BEBAN OPERASIONAL =====
            ['5500', 'Biaya Perjalanan Dinas'],
            ['5600', 'Biaya Listrik Kantor'],
            ['5601', 'Biaya Listrik Kos'],
            ['5610', 'Biaya Air Kantor'],
            ['5611', 'Biaya Air Kos'],
            ['5700', 'Biaya Kebersihan dan Perawatan Kantor'],
            ['5701', 'Biaya Kebersihan dan Perawatan Kos'],
            ['5710', 'Biaya Perlengkapan Kantor'],
            ['5711', 'Biaya Perlengkapan Kos'],
            ['5720', 'Biaya Keamanan Kantor'],
            ['5721', 'Biaya Keamanan Kos'],
            ['5722', 'Biaya Telekomunikasi'],
            ['5730', 'Biaya Pos dan Kurir'],
            ['5800', 'Biaya Kelas Kerjasama'],
            ['6000', 'Biaya Gaji Karyawan'],
            ['6001', 'Biaya Jasa Konsultan'],
            ['6010', 'Tunjangan Karyawan'],
            ['6011', 'Beban BPJS (JHT) - Perusahaan'],
            ['6012', 'Beban BPJS (JKK) - Perusahaan'],
            ['6013', 'Beban BPJS (JKM) - Perusahaan'],
            ['6020', 'Biaya Alat Tulis Kantor dan Barang Cetakan'],
            ['6021', 'Biaya Materai'],
            ['6022', 'Biaya E-Materai'],
            ['6023', 'Biaya Internet Kantor'],
            ['6024', 'Biaya Internet Kos'],
            ['6100', 'Biaya Transportasi'],
            ['6101', 'Biaya Pemeliharaan Kendaraan'],
            ['6110', 'Beban Jasa dan Legal Notaris'],
            ['6200', 'Biaya Konsumsi'],
            ['6210', 'Biaya Representasi'],
            ['6300', 'Biaya Sewa'],
            ['6400', 'Biaya Bank'],
            ['6410', 'Biaya Pajak dan Perijinan'],
            ['6411', 'Biaya PPN'],
            ['6412', 'Biaya PPH21'],
            ['6413', 'Biaya PPH22'],
            ['6414', 'Biaya PPH23'],
            ['6415', 'Biaya PPH25/29'],
            ['6416', 'Biaya PPH4(2) Sewa'],
            ['6417', 'Biaya Pajak Lainnya'],
            ['6418', 'Biaya Pajak Bumi dan Bangunan (PBB)'],
            ['6500', 'Biaya Asuransi'],
            ['6600', 'Biaya Sumbangan'],
            ['6700', 'Biaya Langganan Aplikasi / Software'],
            ['6701', 'Biaya Rekrutmen Karyawan'],
            ['6800', 'Biaya Rapat Kerja dan Pertemuan'],

            // ===== PENYUSUTAN & PEMASARAN =====
            ['6900', 'Biaya Penyusutan Aset'],
            ['7000', 'Biaya Pemasaran / Iklan Digital'],
            ['7100', 'Biaya Promosi Media Sosial'],
            ['7200', 'Biaya Event dan Pameran'],

            // ===== NON OPERASIONAL & LAINNYA =====
            ['7300', 'Pendapatan Non Operasional'],
            ['7400', 'Pendapatan Bunga Bank'],
            ['7500', 'Pendapatan Lain - Lain'],
            ['7600', 'Biaya Administrasi Bank'],
            ['7610', 'Biaya Administrasi SMK'],
            ['7700', 'Biaya Lain - Lain'],
            ['7800', 'Pendapatan Non Operasional'],
            ['7900', 'Pendapatan Non Operasional Lainnya'],
        ];
    }
}
