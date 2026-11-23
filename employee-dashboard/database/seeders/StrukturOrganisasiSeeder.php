<?php

namespace Database\Seeders;

use App\Models\StrukturOrganisasi;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class StrukturOrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama jika ada
        StrukturOrganisasi::truncate();

        // =========================================================================
        // 1. LEVEL 1: PENGAWAS PERUSAHAAN (Pucuk Tertinggi)
        // =========================================================================
        $pengawas = StrukturOrganisasi::create([
            'nama'         => 'Sudiyono, S.E., M.M.',
            'jabatan'      => 'Pengawas Perusahaan',
            'divisi'       => 'Pengawasan',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => null,
            'urutan'       => 1,
            'level'        => 1,
            'email'        => 'sudiyono@silindo.co.id',
            'no_telepon'   => '081234567801',
            'keterangan'   => 'Dewan Pengawas Utama PT Sinergi Ilmiah Indonesia',
        ]);

        // =========================================================================
        // 2. LEVEL 2: DIREKTUR UTAMA
        // =========================================================================
        $dirUtamaKaryawan = Karyawan::where('nama', 'LIKE', '%Meida%')->orWhere('jabatan', 'LIKE', '%Direktur Utama%')->first();
        $dirUtama = StrukturOrganisasi::create([
            'nama'         => 'Dr. (c) Meida R., M.M., M.H., Ph.D',
            'jabatan'      => 'Direktur Utama',
            'divisi'       => 'Direksi',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $pengawas->id,
            'urutan'       => 1,
            'level'        => 2,
            'email'        => 'meida@silindo.co.id',
            'no_telepon'   => '081234567802',
            'keterangan'   => 'Direktur Utama PT Sinergi Ilmiah Indonesia',
            'id_karyawan'  => $dirUtamaKaryawan?->id_karyawan,
        ]);

        // =========================================================================
        // 3. DIREKTUR OPERASIONAL (Di bawah Dir Utama)
        // =========================================================================
        $dirOps = StrukturOrganisasi::create([
            'nama'         => 'Jeffry R., S.E., M.H.',
            'jabatan'      => 'Direktur Operasional',
            'divisi'       => 'Operasional',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirUtama->id,
            'urutan'       => 1,
            'level'        => 3,
            'email'        => 'jeffry@silindo.co.id',
            'no_telepon'   => '081234567803',
            'keterangan'   => 'Membawahi seluruh operasional divisi kerja PT Silindo',
        ]);

        // =========================================================================
        // 4. LEVEL PENDUKUNG: LEGAL, PA, WRITER, FINANCE, HR & GA
        // =========================================================================
        // Legal
        StrukturOrganisasi::create([
            'nama'         => 'Yemima N.P., S.H.',
            'jabatan'      => 'Legal',
            'divisi'       => 'Legal & Compliance',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirUtama->id,
            'urutan'       => 1,
            'level'        => 3,
            'email'        => 'yemima@silindo.co.id',
            'no_telepon'   => '081234567804',
            'keterangan'   => 'Penasihat hukum dan kepatuhan regulasi perusahaan',
        ]);

        // Personal Assistant
        StrukturOrganisasi::create([
            'nama'         => 'Setianingsih, S.Pd.',
            'jabatan'      => 'Personal Assistant',
            'divisi'       => 'Direksi',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirUtama->id,
            'urutan'       => 2,
            'level'        => 3,
            'email'        => 'setianingsih@silindo.co.id',
            'no_telepon'   => '081234567805',
            'keterangan'   => 'Asisten Pribadi Direksi & Manajemen Eksekutif',
        ]);

        // Writer
        StrukturOrganisasi::create([
            'nama'         => 'Cihuyyy.',
            'jabatan'      => 'Content Writer',
            'divisi'       => 'Writer',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirUtama->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'rian@silindo.co.id',
            'no_telepon'   => '081234567806',
            'keterangan'   => 'Penulis konten naskah dan artikel perusahaan',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Prabowo sb, S.Pd.',
            'jabatan'      => 'Copywriter',
            'divisi'       => 'Writer',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirUtama->id,
            'urutan'       => 2,
            'level'        => 4,
            'email'        => 'dina@silindo.co.id',
            'no_telepon'   => '081234567807',
            'keterangan'   => 'Penyusun teks promosi dan komunikasi tertulis',
        ]);

        // Finance & Accounting
        StrukturOrganisasi::create([
            'nama'         => 'Jeffry R., S.E., M.H.',
            'jabatan'      => 'Finance & Accounting',
            'divisi'       => 'Finance',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'finance@silindo.co.id',
            'no_telepon'   => '081234567808',
            'keterangan'   => 'Penanggung jawab pembukuan, keuangan, dan perpajakan',
        ]);

        // HR & GA
        StrukturOrganisasi::create([
            'nama'         => 'Agnes Galih C.R., M.Si.',
            'jabatan'      => 'HR & GA',
            'divisi'       => 'HR & GA',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'agnes@silindo.co.id',
            'no_telepon'   => '081234567809',
            'keterangan'   => 'Pengelolaan Sumber Daya Manusia dan Sarana Umum',
        ]);

        // =========================================================================
        // 5. DEPARTEMEN OPERASIONAL (Level 4: 6 Kolom Utama di bawah Dir Ops)
        // =========================================================================

        // 5.1 Kelas Kerjasama
        StrukturOrganisasi::create([
            'nama'         => 'Siti Z.B., S.Pd., M.A.',
            'jabatan'      => 'Admin Kerjasama',
            'divisi'       => 'Kelas Kerjasama',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'siti@silindo.co.id',
            'no_telepon'   => '081234567814',
            'keterangan'   => 'Administrasi dan relasi program kelas kerjasama mitra',
        ]);

        // 5.2 Publikasi Buku
        $koordBuku = StrukturOrganisasi::create([
            'nama'         => 'Durotur Rohma, S.Sos.',
            'jabatan'      => 'Koord. Buku',
            'divisi'       => 'Publikasi Buku',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'durotur@silindo.co.id',
            'no_telepon'   => '081234567815',
            'keterangan'   => 'Koordinator utama penerbitan dan publikasi buku',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Cindy Reta Q., S.S.',
            'jabatan'      => 'Admin Buku',
            'divisi'       => 'Publikasi Buku',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordBuku->id,
            'urutan'       => 2,
            'level'        => 5,
            'email'        => 'cindy@silindo.co.id',
            'no_telepon'   => '081234567816',
            'keterangan'   => 'Administrasi naskah, ISBN, dan arsip penerbitan buku',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Vega Rossy D.P., S.E.',
            'jabatan'      => 'Marcom Buku',
            'divisi'       => 'Publikasi Buku',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordBuku->id,
            'urutan'       => 3,
            'level'        => 5,
            'email'        => 'vega@silindo.co.id',
            'no_telepon'   => '081234567817',
            'keterangan'   => 'Pemasaran dan komunikasi produk buku',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Argha Y.P., M.Hum.',
            'jabatan'      => 'Layouter',
            'divisi'       => 'Publikasi Buku',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordBuku->id,
            'urutan'       => 4,
            'level'        => 5,
            'email'        => 'argha@silindo.co.id',
            'no_telepon'   => '081234567818',
            'keterangan'   => 'Tata letak dan layout sampul buku ilmiah',
        ]);

        // 5.3 Publikasi Jurnal
        $koordJurnal = StrukturOrganisasi::create([
            'nama'         => 'Vini A., S.P., M.P.',
            'jabatan'      => 'Koord. Jurnal',
            'divisi'       => 'Publikasi Jurnal',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'vini@silindo.co.id',
            'no_telepon'   => '081234567819',
            'keterangan'   => 'Koordinator penerbitan jurnal terakreditasi',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Yovita A., S.Tp., M.P.',
            'jabatan'      => 'Staff Operasional',
            'divisi'       => 'Publikasi Jurnal',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordJurnal->id,
            'urutan'       => 2,
            'level'        => 5,
            'email'        => 'yovita@silindo.co.id',
            'no_telepon'   => '081234567823',
            'keterangan'   => 'Operasional penerimaan naskah artikel ilmiah',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Annisa S.F., S.Pd., M.P.',
            'jabatan'      => 'Admin Jurnal',
            'divisi'       => 'Publikasi Jurnal',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordJurnal->id,
            'urutan'       => 3,
            'level'        => 5,
            'email'        => 'annisa@silindo.co.id',
            'no_telepon'   => '081234567825',
            'keterangan'   => 'Administrasi OJS dan korespondensi reviewer',
        ]);

        // 5.4 Academic Event
        $koordAe = StrukturOrganisasi::create([
            'nama'         => 'Puntoadji P., M.A.B.',
            'jabatan'      => 'Koord. AE',
            'divisi'       => 'Academic Event',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'puntoadji@silindo.co.id',
            'no_telepon'   => '081234567826',
            'keterangan'   => 'Koordinator event pelatihan ilmiah dan workshop',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Grace Juliana Leu, S.H.',
            'jabatan'      => 'Admin AE',
            'divisi'       => 'Academic Event',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $koordAe->id,
            'urutan'       => 2,
            'level'        => 5,
            'email'        => 'grace@silindo.co.id',
            'no_telepon'   => '081234567827',
            'keterangan'   => 'Administrasi peserta pelatihan dan sertifikat event',
        ]);

        // 5.5 Kreatif Digital
        $leadKreatif = StrukturOrganisasi::create([
            'nama'         => 'Muhammad M., S.Or.',
            'jabatan'      => 'Digital Marketing',
            'divisi'       => 'Kreatif Digital',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'muhammad@silindo.co.id',
            'no_telepon'   => '081234567828',
            'keterangan'   => 'Pemasaran media sosial dan promosi digital produk',
        ]);

        StrukturOrganisasi::create([
            'nama'         => 'Eko Rustanto, S.Ds.',
            'jabatan'      => 'Desain Grafis',
            'divisi'       => 'Kreatif Digital',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $leadKreatif->id,
            'urutan'       => 2,
            'level'        => 5,
            'email'        => 'eko@silindo.co.id',
            'no_telepon'   => '081234567829',
            'keterangan'   => 'Desain aset visual feed, promosi, dan banner perusahaan',
        ]);

        // Sample Magang dengan Garis Putus-Putus
        StrukturOrganisasi::create([
            'nama'         => 'Naufal Rizky',
            'jabatan'      => 'Magang Desain Grafis',
            'divisi'       => 'Kreatif Digital',
            'tipe_node'    => 'orang',
            'status_kerja' => 'magang',
            'garis_putus'  => true,
            'parent_id'    => $leadKreatif->id,
            'urutan'       => 3,
            'level'        => 5,
            'email'        => 'naufal.magang@silindo.co.id',
            'no_telepon'   => '081234567890',
            'keterangan'   => 'Peserta Magang Desain Multimedia (Garis Putus-Putus)',
        ]);

        // 5.6 IT
        $leadIt = StrukturOrganisasi::create([
            'nama'         => 'Wardi, S.Kom., M.Pd.',
            'jabatan'      => 'IT Support',
            'divisi'       => 'IT',
            'tipe_node'    => 'orang',
            'status_kerja' => 'tetap',
            'garis_putus'  => false,
            'parent_id'    => $dirOps->id,
            'urutan'       => 1,
            'level'        => 4,
            'email'        => 'wardi@silindo.co.id',
            'no_telepon'   => '081234567830',
            'keterangan'   => 'Dukungan teknis sistem informasi kantor & jaringan',
        ]);

        // Sample Probation dengan Garis Putus-Putus
        StrukturOrganisasi::create([
            'nama'         => 'Rina Astuti, S.Kom.',
            'jabatan'      => 'Web Developer (Probation)',
            'divisi'       => 'IT',
            'tipe_node'    => 'orang',
            'status_kerja' => 'probation',
            'garis_putus'  => true,
            'parent_id'    => $leadIt->id,
            'urutan'       => 2,
            'level'        => 5,
            'email'        => 'rina.astuti@silindo.co.id',
            'no_telepon'   => '081234567891',
            'keterangan'   => 'Frontend Web Developer masa percobaan 3 bulan (Garis Putus-Putus)',
        ]);

        $this->command?->info('Berhasil menyemai data Struktur Organisasi PT Silindo sesuai struktur awal!');
    }
}
