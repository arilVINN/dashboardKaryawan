<?php

namespace Tests\Feature;

use App\Livewire\Finance\RingkasanKeuangan;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KeuanganAldiv;
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

class FinanceDashboardUiTest extends TestCase
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
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resources', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'sarifin',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
    }

    private function seedPeriodeData(): void
    {
        KPemasukan::create([
            'id_kp' => 'KP001',
            'kode_transaksi' => 'IN001',
            'kategori_pemasukan' => 'Operasional',
            'nominal' => 50000000,
            'tanggal_masuk' => '2026-11-05',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KPengeluaran::create([
            'id_kpengeluaran' => 'KO001',
            'kode_pengeluaran' => 'OUT001',
            'nominal' => 9400000,
            'tanggal_cair' => '2026-11-10',
            'metode_pembayaran' => 'Transfer Bank',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KeuanganAldiv::create([
            'id_kad' => 'KAD001',
            'periode' => '2026-11',
            'nominal_alokasi' => 10000000,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KPermintDana::create([
            'id_kpd' => 'KPD001',
            'nomor_pengajuan' => 'REQ001',
            'judul_pengajuan' => 'Pengadaan perangkat',
            'keperluan' => 'Pengadaan perangkat',
            'nominal_diajukan' => 2500000,
            'status' => KPermintDana::STATUS_MENUNGGU,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);
    }

    public function test_finance_user_can_log_in_and_open_the_kadiv_dashboard(): void
    {
        $this->postJson('/login', [
            'username' => 'sarifin',
            'password' => 'pass123',
        ])
            ->assertOk()
            ->assertJsonPath('role', 'Kadiv')
            ->assertJsonStructure(['access_token']);

        $this->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('Keuangan Perusahaan');
    }

    public function test_page_renders_restyled_summary_and_pagu_table(): void
    {
        $this->seedPeriodeData();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan?periode=2026-11');

        $response->assertOk()
            ->assertSee('Ringkasan Keuangan')
            ->assertSee('id="finance-groups"', false)
            ->assertSee('data-finance-target="finance-ringkasan-menu"', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('id="finance-ringkasan-menu"', false)
            ->assertSee('Keuangan Perusahaan')
            ->assertSee('Anggaran & Kas')
            ->assertSee('Permintaan & Verifikasi')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Ringkasan Keuangan"', false)
            ->assertSee('November 2026')
            ->assertSee('Total kas masuk')
            ->assertSee('Total kas keluar')
            ->assertSee('Saldo kas bersih')
            ->assertSee('Pengajuan menunggu')
            ->assertSee('Rp 50.000.000')
            ->assertSee('Rp 9.400.000')
            ->assertSee('Rp 40.600.000')
            ->assertSee('Review pengajuan')
            ->assertSee('border-[#F5C85C]', false)
            ->assertSee('bg-[#FFFBEB]', false)
            ->assertSee('Pagu anggaran operasional divisi')
            ->assertSee('Batas anggaran dan penyerapan dana, November 2026')
            ->assertSee('Pagu ditetapkan')
            ->assertSee('Dana dicairkan')
            ->assertSee('Sisa pagu')
            ->assertSee('Penyerapan')
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertSee('Information Technology')
            ->assertSee('aria-label="94% terserap"', false)
            ->assertSee('Lihat rekap laba rugi')
            ->assertSee('Atur pagu')
            ->assertSee('Catat kas masuk')
            ->assertSee('Pencairan dana')
            ->assertSee('Verifikasi laporan laba rugi');

        // Kolom kode terpisah dan dot warna lama sudah tidak ada.
        $response->assertDontSee('>Kode</th>', false);
        $response->assertDontSee('rounded-full bg-[#19A7CE]', false);
        // Tile besar di bagian bawah halaman sudah dihapus.
        $response->assertDontSee('Input pendapatan atau modal baru');
        $response->assertDontSee('Eksekusi pengeluaran operasional');
        $response->assertDontSee('Review laporan laba rugi mingguan');
    }

    public function test_finance_laporan_verification_page_uses_finance_design(): void
    {
        KLabaRugi::create([
            'id_klr' => 'KLR-FIN-1',
            'periode' => '2026-11',
            'minggu_ke' => 4,
            'tanggal_mulai' => '2026-11-28',
            'tanggal_selesai' => '2026-12-05',
            'total_pendapatan' => 30000000,
            'total_pengeluaran' => 10000000,
            'laba_bersih' => 20000000,
            'margin_persen' => (20000000 / 30000000) * 100,
            'status_verifikasi' => KLabaRugi::STATUS_MENUNGGU,
            'tanggal_kirim' => '2026-12-05',
            'bukti_laporan' => 'keuangan/laporan/fin.pdf',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/laporan?periode=2026-11')
            ->assertOk()
            ->assertSee('data-breadcrumb-parent="Keuangan"', false)
            ->assertSee('data-breadcrumb-current="Verifikasi Laporan L/R"', false)
            ->assertSee('Verifikasi Laporan Laba / Rugi Divisi')
            ->assertSee('Semua divisi')
            ->assertSee('Semua status')
            ->assertSee('Periode & minggu')
            ->assertSee('28 Nov - 05 Des 26')
            ->assertSee('Minggu ke-4')
            ->assertSee('Lihat')
            ->assertSee('Verifikasi')
            ->assertSee('bg-[#F4F7F9]', false)
            ->assertSee('min-w-[1100px]', false);
    }

    public function test_finance_sidebar_menu_is_collapsed_on_non_finance_pages(): void
    {
        $this->actingAs($this->finance)
            ->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('id="finance-groups"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('hidden ml-[46px]', false)
            ->assertSee('Keuangan Perusahaan');
    }

    public function test_finance_sidebar_opens_the_active_group(): void
    {
        $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/alokasi?periode=2026-11')
            ->assertOk()
            ->assertSee('id="finance-anggaran-menu"', false)
            ->assertSee('data-finance-target="finance-anggaran-menu"', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('id="finance-ringkasan-menu"', false)
            ->assertSee('hidden ml-[46px]', false);
    }

    public function test_empty_pagu_state_hides_zero_rows(): void
    {
        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan?periode=2026-11');

        $response->assertOk()
            ->assertSee('Belum ada pagu untuk November 2026')
            ->assertSee('Tetapkan pagu per divisi agar penyerapan dana bisa dipantau.')
            ->assertSee('Atur pagu')
            ->assertDontSee('Review pengajuan')
            ->assertDontSee('border-[#F5C85C]', false)
            ->assertDontSee('bg-[#FFFBEB]', false)
            ->assertDontSee('wire:key="pagu-', false)
            ->assertDontSee('role="progressbar"', false);
    }

    public function test_period_switcher_moves_month_with_livewire(): void
    {
        Livewire::actingAs($this->finance)
            ->test(RingkasanKeuangan::class, ['periode' => '2026-11'])
            ->assertSet('periode', '2026-11')
            ->assertSee('November 2026')
            ->call('previous')
            ->assertSet('periode', '2026-10')
            ->assertSee('Oktober 2026')
            ->call('next')
            ->assertSet('periode', '2026-11')
            ->assertSee('November 2026');
    }

    public function test_month_picker_modal_changes_period(): void
    {
        Livewire::actingAs($this->finance)
            ->test(RingkasanKeuangan::class, ['periode' => '2026-11'])
            ->call('openMonthPicker')
            ->assertSet('showMonthPicker', true)
            ->assertSet('pickerYear', 2026)
            ->assertSee('Pilih bulan')
            ->assertSee('x-ref="monthCancel"', false)
            ->assertSee('2026')
            ->call('selectMonth', 3)
            ->assertSet('periode', '2026-03')
            ->assertSet('showMonthPicker', false)
            ->assertSee('Maret 2026');
    }

    public function test_invalid_period_falls_back_to_current_month(): void
    {
        Livewire::actingAs($this->finance)
            ->test(RingkasanKeuangan::class, ['periode' => 'not-a-month'])
            ->assertSet('periode', now()->format('Y-m'));
    }

    public function test_atur_pagu_and_pencairan_are_audited_with_old_and_new_values(): void
    {
        $this->seedPeriodeData();

        // Atur pagu baru: nilai lama null, nilai baru nominal pagu.
        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.alokasi.store'), [
                'periode' => '2026-11',
                'nominal_alokasi' => 7500000,
                'divisi_id_divisi' => 'DIV-HR',
            ])
            ->assertRedirect();

        $aturPagu = AuditLog::where('aksi', 'atur_pagu')->latest('id')->firstOrFail();
        $this->assertSame('USR-FIN', $aturPagu->user_id_user);
        $this->assertNull($aturPagu->detail['nilai_lama']);
        $this->assertEquals(7500000, $aturPagu->detail['nilai_baru']);
        $this->assertSame('Human Resources', $aturPagu->detail['divisi_nama']);

        // Ubah pagu: nilai lama dan nilai baru tersimpan beserta alasannya.
        $alokasi = KeuanganAldiv::where('id_kad', 'KAD001')->firstOrFail();
        $this->actingAs($this->finance)
            ->put(route('kadiv.keuangan.alokasi.update', $alokasi->id_kad), [
                'nominal_alokasi' => 12000000,
                'keterangan' => 'Penambahan anggaran',
            ])
            ->assertRedirect();

        $ubahPagu = AuditLog::where('aksi', 'ubah_pagu')->latest('id')->firstOrFail();
        $this->assertEquals(10000000, $ubahPagu->detail['nilai_lama']);
        $this->assertEquals(12000000, $ubahPagu->detail['nilai_baru']);
        $this->assertSame('Penambahan anggaran', $ubahPagu->detail['alasan']);
        $this->assertSame('USR-FIN', $ubahPagu->user_id_user);

        // Pencairan: status lama disetujui -> status baru dicairkan.
        $permintaan = KPermintDana::create([
            'id_kpd' => 'KPD002',
            'nomor_pengajuan' => 'REQ002',
            'judul_pengajuan' => 'Perjalanan dinas',
            'keperluan' => 'Perjalanan dinas',
            'nominal_diajukan' => 3000000,
            'status' => KPermintDana::STATUS_DISETUJUI,
            'tanggal_disetujui' => '2026-11-12',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
            'disetujui_oleh_id_user' => $this->finance->id_user,
        ]);

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.cairkan', $permintaan->id_kpd), [
                'tanggal_cair' => '2026-11-20',
                'metode_pembayaran' => 'Transfer Bank',
            ])
            ->assertRedirect();

        $pencairan = AuditLog::where('aksi', 'pencairan_dana')->latest('id')->firstOrFail();
        $this->assertSame(KPermintDana::STATUS_DISETUJUI, $pencairan->detail['nilai_lama']['status']);
        $this->assertSame(KPermintDana::STATUS_DICAIRKAN, $pencairan->detail['nilai_baru']['status']);
        $this->assertEquals(3000000, $pencairan->detail['nilai_baru']['nominal']);
        $this->assertSame('Information Technology', $pencairan->detail['divisi_nama']);
    }
}
