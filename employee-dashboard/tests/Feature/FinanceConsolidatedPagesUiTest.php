<?php

namespace Tests\Feature;

use App\Livewire\Finance\ArusKas;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KLabaRugi;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceConsolidatedPagesUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

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
            'id_karyawan' => 'KRY-FIN',
            'nama' => 'Finance Lead',
            'jenis_kelamin' => 'Perempuan',
            'jabatan' => 'Kadiv Finance',
            'divisi_id_divisi' => 'DIV-FIN',
        ]);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
    }

    private function seedCashRows(): void
    {
        KPemasukan::create([
            'id_kp' => 'KP-CONS-1',
            'kode_transaksi' => 'IN-CONS-1',
            'kategori_pemasukan' => 'Proyek',
            'nominal' => 30000000,
            'tanggal_masuk' => '2026-11-05',
            'keterangan' => 'Pembayaran proyek',
            'bukti_dokumen' => 'bukti/invoice.pdf',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KPemasukan::create([
            'id_kp' => 'KP-CONS-2',
            'kode_transaksi' => 'IN-CONS-2',
            'kategori_pemasukan' => 'Layanan / Jasa',
            'nominal' => 2000000,
            'tanggal_masuk' => '2026-11-06',
            'keterangan' => 'Pendapatan jasa',
            'bukti_dokumen' => null,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KPengeluaran::create([
            'id_kpengeluaran' => 'KO-CONS-1',
            'kode_pengeluaran' => 'OUT-CONS-1',
            'nominal' => 5000000,
            'tanggal_cair' => '2026-11-07',
            'metode_pembayaran' => 'Transfer Bank',
            'keterangan' => 'Pembayaran vendor',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);
    }

    public function test_arus_kas_page_uses_finance_design_and_new_sidebar_group(): void
    {
        $this->seedCashRows();

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/arus-kas?periode=2026-11');

        $response->assertOk()
            ->assertSee('data-breadcrumb-parent="Keuangan"', false)
            ->assertSee('data-breadcrumb-current="Arus Kas"', false)
            ->assertSee('Arus Kas')
            ->assertSee('Total kas masuk')
            ->assertSee('Total kas keluar')
            ->assertSee('Saldo bersih bulan ini')
            ->assertSee('Catat kas masuk')
            ->assertSee('Catat kas keluar')
            ->assertSee('Kas masuk')
            ->assertSee('Kas keluar')
            ->assertSee('id="finance-groups"', false)
            ->assertSee('data-finance-target="finance-anggaran-menu"', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('bg-[#146C94]', false)
            ->assertSee('border-[#D5DEE3]', false)
            ->assertSee('min-h-[92px]', false)
            ->assertSee('wire:click="previous"', false)
            ->assertSee('wire:model.live="kategori"', false)
            ->assertSee('wire:model.live.debounce.300ms="search"', false)
            ->assertSee('Menampilkan 1-2 dari 2 transaksi')
            ->assertSee('Rp 30.000.000')
            ->assertSee('aria-label="Lihat bukti"', false)
            ->assertSee('Belum ada')
            ->assertSee('bg-[#F1F5F9]', false)
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertSee('wire:click.stop="openTransactionDetail', false);
    }

    public function test_arus_kas_toolbar_and_detail_modal_are_livewire_driven(): void
    {
        $this->seedCashRows();

        Livewire::actingAs($this->finance)
            ->test(ArusKas::class, ['periode' => '2026-11'])
            ->assertSet('tab', 'masuk')
            ->assertSee('November 2026')
            ->call('openTransactionDetail', 'masuk', 'KP-CONS-1')
            ->assertSet('showTransactionDetail', true)
            ->assertSee('Detail transaksi')
            ->assertSee('IN-CONS-1')
            ->set('tab', 'keluar')
            ->assertSet('tab', 'keluar')
            ->assertSee('Kas keluar')
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertSee('wire:click.stop="openTransactionDetail', false)
            ->call('previous')
            ->assertSet('periode', '2026-10')
            ->assertSee('Oktober 2026');
    }

    public function test_manajemen_dana_report_tab_uses_date_range_format(): void
    {
        $lap = KLabaRugi::create([
            'id_klr' => 'KLR-MD-1',
            'periode' => '2026-11',
            'minggu_ke' => 1,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-07',
            'total_pendapatan' => 30000000,
            'total_pengeluaran' => 10000000,
            'laba_bersih' => 20000000,
            'margin_persen' => (20000000 / 30000000) * 100,
            'status_verifikasi' => KLabaRugi::STATUS_MENUNGGU,
            'tanggal_kirim' => '2026-11-07',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);
        $lap->timestamps = false;
        $lap->forceFill(['created_at' => '2026-11-16 10:00:00', 'updated_at' => '2026-11-16 10:00:00'])->save();
        $lap->timestamps = true;

        $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/manajemen-dana?periode=2026-11&tab=laporan')
            ->assertOk()
            ->assertSee('Periode')
            ->assertSee('1–7 Nov 2026')
            ->assertSee('Minggu 1');
    }

    public function test_manajemen_dana_page_uses_clickable_rows_and_plain_tab_counts(): void
    {
        $request = KPermintDana::create([
            'id_kpd' => 'KPD-CONS-1',
            'nomor_pengajuan' => 'REQ-CONS-1',
            'judul_pengajuan' => 'Pengadaan perangkat',
            'keperluan' => 'Pembelian laptop tim operasional.',
            'nominal_diajukan' => 2500000,
            'status' => KPermintDana::STATUS_MENUNGGU,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        $detailUrl = route('kadiv.keuangan.permintaan.show', $request->id_kpd);

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/manajemen-dana?periode=2026-11');

        $response->assertOk()
            ->assertSee('data-breadcrumb-parent="Keuangan"', false)
            ->assertSee('data-breadcrumb-current="Permintaan &amp; Verifikasi"', false)
            ->assertSee('Permintaan & Verifikasi')
            ->assertSee('Permintaan dana')
            ->assertSee('Verifikasi laporan L/R')
            ->assertSee('Pengajuan')
            ->assertSee('Divisi / pengaju')
            ->assertSee('Nominal')
            ->assertSee("window.location.href='".$detailUrl."'", false)
            ->assertSee('Menampilkan 1-1 dari 1 pengajuan')
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertDontSee("openApproveModal('KPD-CONS-1'", false)
            ->assertSee('id="finance-groups"', false)
            ->assertSee('data-finance-direct="permintaan"', false);
    }
}
