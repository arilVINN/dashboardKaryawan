<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KLabaRugi;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KadivKeuanganDivisiUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $itKadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);

        Karyawan::create([
            'id_karyawan' => 'KRY-IT',
            'nama' => 'IT Lead',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Kadiv IT',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->itKadiv = User2::create([
            'id_user' => 'USR-IT',
            'username' => 'it.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-IT',
        ]);
    }

    public function test_pengajuan_dana_page_uses_finance_design(): void
    {
        $response = $this->actingAs($this->itKadiv)->get('/kadiv/pengajuan-dana');

        $response->assertOk()
            ->assertSee('data-breadcrumb-current="Pengajuan Dana"', false)
            ->assertSee('Pengajuan Dana')
            ->assertSee('Pagu divisi bulan ini')
            ->assertSee('Dana sudah dicairkan')
            ->assertSee('Sisa pagu tersedia')
            ->assertSee('Form permohonan dana baru')
            ->assertSee('Kirim pengajuan')
            ->assertSee('Lihat riwayat')
            ->assertSee('Keuangan Divisi')
            ->assertDontSee('data-breadcrumb-parent="Keuangan Divisi"', false)
            ->assertSee('bg-[#F4F7F9]', false)
            ->assertSee('rounded-lg p-2 bg-[#E3F1F7] text-[#146C94]', false);
    }

    public function test_laporan_laba_rugi_page_uses_finance_design(): void
    {
        KLabaRugi::create([
            'id_klr' => 'KLR-IT-1',
            'periode' => '2026-11',
            'minggu_ke' => 1,
            'tanggal_mulai' => '2026-11-03',
            'tanggal_selesai' => '2026-11-09',
            'total_pendapatan' => 30000000,
            'total_pengeluaran' => 10000000,
            'laba_bersih' => 20000000,
            'margin_persen' => (20000000 / 30000000) * 100,
            'status_verifikasi' => KLabaRugi::STATUS_DISETUJUI,
            'catatan_verifikasi' => 'Revisi rincian biaya operasional.',
            'tanggal_kirim' => '2026-11-09',
            'bukti_laporan' => 'keuangan/laporan/test.pdf',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->itKadiv->id_user,
            'diverifikasi_oleh_id_user' => $this->itKadiv->id_user,
        ]);

        $response = $this->actingAs($this->itKadiv)->get('/kadiv/laporan-laba-rugi?periode=2026-11');

        $response->assertOk()
            ->assertSee('data-breadcrumb-current="Laporan Laba / Rugi"', false)
            ->assertDontSee('data-breadcrumb-parent="Keuangan Divisi"', false)
            ->assertSee('Laporan Laba / Rugi')
            ->assertSee('Kirim laporan')
            ->assertSee('Kirim laporan mingguan')
            ->assertSee('Periode & minggu')
            ->assertSee('Semua minggu')
            ->assertSee('03 - 09 Nov 26')
            ->assertSee('Minggu ke-1')
            ->assertSee('Lihat catatan')
            ->assertSee('Detail laporan')
            ->assertSee('Revisi rincian biaya operasional.')
            ->assertSee('Lihat')
            ->assertSee('Terverifikasi')
            ->assertSee('Status')
            ->assertSee('Catatan finance')
            ->assertSee('Lampiran')
            ->assertSee('Keuangan Divisi')
            ->assertSee('bg-[#F4F7F9]', false)
            ->assertSee('rounded-lg p-2 bg-[#E3F1F7] text-[#146C94]', false);
    }

    public function test_riwayat_pengajuan_dana_page_uses_finance_design(): void
    {
        KPermintDana::create([
            'id_kpd' => 'KPD-IT-1',
            'nomor_pengajuan' => 'REQ-IT-1',
            'judul_pengajuan' => 'Pengadaan perangkat',
            'keperluan' => 'Pembelian laptop tim operasional.',
            'nominal_diajukan' => 2500000,
            'status' => KPermintDana::STATUS_MENUNGGU,
            'lampiran_proposal' => 'keuangan/proposal/test.pdf',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->itKadiv->id_user,
        ]);

        $response = $this->actingAs($this->itKadiv)->get('/kadiv/pengajuan-dana/riwayat');

        $response->assertOk()
            ->assertSee('data-breadcrumb-parent="Pengajuan Dana"', false)
            ->assertSee('data-breadcrumb-current="Riwayat Pengajuan Dana"', false)
            ->assertDontSee('data-breadcrumb-parent="Keuangan Divisi"', false)
            ->assertSee('Riwayat Pengajuan Dana')
            ->assertSee('Buat pengajuan baru')
            ->assertSee('No. pengajuan')
            ->assertSee('Judul & keperluan')
            ->assertSee('Menunggu')
            ->assertSee('Lihat')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Menampilkan 1-1 dari 1 pengajuan')
            ->assertSee('Keuangan Divisi')
            ->assertSee('bg-[#F4F7F9]', false)
            ->assertSee('rounded-lg p-2 bg-[#E3F1F7] text-[#146C94]', false);
    }
}
