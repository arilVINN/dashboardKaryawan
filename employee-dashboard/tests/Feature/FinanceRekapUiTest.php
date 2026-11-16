<?php

namespace Tests\Feature;

use App\Livewire\Finance\RekapLabaRugi;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KLabaRugi;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceRekapUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv']]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resources', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Divisi Tanpa Laporan', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
    }

    private function laporan(
        string $id,
        string $divisiId,
        int $minggu,
        float $pendapatan,
        float $biaya,
        string $status = KLabaRugi::STATUS_DISETUJUI,
    ): KLabaRugi {
        return KLabaRugi::create([
            'id_klr' => $id,
            'periode' => '2026-11',
            'minggu_ke' => $minggu,
            'tanggal_mulai' => '2026-11-0'.$minggu,
            'tanggal_selesai' => '2026-11-0'.($minggu + 1),
            'total_pendapatan' => $pendapatan,
            'total_pengeluaran' => $biaya,
            'laba_bersih' => $pendapatan - $biaya,
            'margin_persen' => $pendapatan > 0 ? (($pendapatan - $biaya) / $pendapatan) * 100 : 0,
            'status_verifikasi' => $status,
            'tanggal_kirim' => '2026-11-05',
            'divisi_id_divisi' => $divisiId,
            'user_id_user' => $this->finance->id_user,
            'diverifikasi_oleh_id_user' => $status === KLabaRugi::STATUS_DISETUJUI ? $this->finance->id_user : null,
        ]);
    }

    private function seedLaporan(): void
    {
        // IT: dua laporan disetujui + satu laporan menunggu yang harus diabaikan.
        $this->laporan('KLR0001', 'DIV-IT', 1, 60500000, 15000000);
        $this->laporan('KLR0002', 'DIV-IT', 2, 30000000, 5000000);
        $this->laporan('KLR0003', 'DIV-IT', 3, 999000000, 1000000, KLabaRugi::STATUS_MENUNGGU);

        // HR: laporan dengan laba dan margin negatif.
        $this->laporan('KLR0004', 'DIV-HR', 1, 18000000, 21400000);
    }

    public function test_page_renders_restyled_rekap_and_breadcrumbs(): void
    {
        $this->seedLaporan();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/rekap-divisi?periode=2026-11');

        $response->assertOk()
            ->assertSee('Rekap Laba / Rugi per Divisi')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Rekap Laba / Rugi per Divisi"', false)
            ->assertSee('Akumulasi November 2026')
            ->assertSee('Per divisi')
            ->assertSee('Total pendapatan')
            ->assertSee('Total biaya / beban')
            ->assertSee('Total laba / (rugi)')
            ->assertSee('Margin laba keseluruhan')
            ->assertSee('Rp 108.500.000')
            ->assertSee('Rp 41.400.000')
            ->assertSee('Rp 67.100.000')
            ->assertSee('61,8%')
            ->assertSee('Pendapatan')
            ->assertSee('Biaya / beban')
            ->assertSee('Laba / (rugi)')
            ->assertSee('Margin')
            ->assertSee('Laporan')
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertSee('Information Technology')
            ->assertSee('Human Resources')
            ->assertSee('Rp 90.500.000')
            ->assertSee('Rp 20.000.000')
            ->assertSee('Rp 70.500.000')
            ->assertSee('77,9%')
            ->assertSee('Rp 18.000.000')
            ->assertSee('Rp 21.400.000')
            ->assertSee('(Rp 3.400.000)')
            ->assertSee('(18,9%)')
            ->assertSee('Semua minggu (akumulasi bulan)')
            ->assertSee('Minggu ke-1')
            ->assertSee('rekap-divisi/export?periode=2026-11', false)
            ->assertSee('divisi_id_divisi=DIV-IT', false)
            ->assertSee('wire:key="rekap-DIV-IT"', false)
            ->assertSee('wire:key="rekap-DIV-HR"', false)
            ->assertDontSee('wire:key="rekap-DIV-OPS"', false)
            ->assertDontSee('Divisi Tanpa Laporan')
            ->assertDontSee('Periode &amp; Minggu', false)
            ->assertDontSee('Nama Divisi')
            ->assertDontSee('0 Laporan')
            ->assertDontSee('rounded-full bg-[#19A7CE]', false)
            ->assertDontSee('999.000.000')
            ->assertSee('Rugi ditampilkan dalam kurung');
    }

    public function test_week_filter_updates_subtitle_and_rows(): void
    {
        $this->seedLaporan();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/rekap-divisi?periode=2026-11&minggu_ke=2');

        $response->assertOk()
            ->assertSee('Minggu ke-2, November 2026')
            ->assertSee('Rp 30.000.000')
            ->assertSee('Rp 5.000.000')
            ->assertSee('Rp 25.000.000')
            ->assertSee('83,3%')
            ->assertSee('rekap-divisi/export?periode=2026-11&amp;minggu_ke=2', false)
            ->assertDontSee('Human Resources')
            ->assertDontSee('Akumulasi November 2026');
    }

    public function test_empty_state_when_no_reports_exist(): void
    {
        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/rekap-divisi?periode=2026-12');

        $response->assertOk()
            ->assertSee('Belum ada laporan keuangan untuk Desember 2026')
            ->assertSee('Laporan laba rugi yang sudah diverifikasi akan muncul di sini.')
            ->assertSee('Per divisi')
            ->assertDontSee('wire:key="rekap-', false)
            ->assertDontSee('role="link"', false);
    }

    public function test_period_and_week_filters_are_livewire_driven(): void
    {
        $this->seedLaporan();

        Livewire::actingAs($this->finance)
            ->test(RekapLabaRugi::class, ['periode' => '2026-11', 'mingguKe' => ''])
            ->assertSet('periode', '2026-11')
            ->assertSet('mingguKe', '')
            ->assertSee('Akumulasi November 2026')
            ->call('previous')
            ->assertSet('periode', '2026-10')
            ->assertSee('Akumulasi Oktober 2026')
            ->call('next')
            ->assertSet('periode', '2026-11')
            ->assertSee('Akumulasi November 2026')
            ->set('mingguKe', '2')
            ->assertSee('Minggu ke-2, November 2026')
            ->assertSee('Rp 30.000.000');
    }

    public function test_month_picker_modal_changes_period(): void
    {
        Livewire::actingAs($this->finance)
            ->test(RekapLabaRugi::class, ['periode' => '2026-11', 'mingguKe' => ''])
            ->call('openMonthPicker')
            ->assertSet('showMonthPicker', true)
            ->assertSet('pickerYear', 2026)
            ->assertSee('Pilih bulan')
            ->assertSee('x-ref="monthCancel"', false)
            ->call('selectMonth', 12)
            ->assertSet('periode', '2026-12')
            ->assertSet('showMonthPicker', false)
            ->assertSee('Desember 2026');
    }

    public function test_invalid_period_and_week_fall_back_safely(): void
    {
        Livewire::actingAs($this->finance)
            ->test(RekapLabaRugi::class, ['periode' => 'not-a-month', 'mingguKe' => '9'])
            ->assertSet('periode', now()->format('Y-m'))
            ->assertSet('mingguKe', '');
    }
}
