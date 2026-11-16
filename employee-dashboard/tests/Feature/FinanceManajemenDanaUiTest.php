<?php

namespace Tests\Feature;

use App\Exceptions\LaporanSudahDiverifikasiException;
use App\Livewire\Finance\ManajemenDana;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KLabaRugi;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use App\Services\Finance\VerifikasiLaporanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceManajemenDanaUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    private User2 $itHead;

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
        Karyawan::create([
            'id_karyawan' => 'KRY-IT',
            'nama' => 'IT Lead',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Kadiv IT',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
        $this->itHead = User2::create([
            'id_user' => 'USR-IT',
            'username' => 'it.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-IT',
        ]);
    }

    private function request(): KPermintDana
    {
        return KPermintDana::create([
            'id_kpd' => 'KPD-MD-1',
            'nomor_pengajuan' => 'DMREQ001',
            'judul_pengajuan' => 'Pengadaan perangkat',
            'keperluan' => 'Pembelian laptop tim IT',
            'nominal_diajukan' => 2500000,
            'status' => KPermintDana::STATUS_MENUNGGU,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->itHead->id_user,
        ]);
    }

    private function laporan(string $status = KLabaRugi::STATUS_MENUNGGU, string $id = 'KLR-MD-1', int $minggu = 3): KLabaRugi
    {
        return KLabaRugi::create([
            'id_klr' => $id,
            'periode' => '2026-11',
            'minggu_ke' => $minggu,
            'tanggal_mulai' => $minggu === 3 ? '2026-11-03' : '2026-11-10',
            'tanggal_selesai' => $minggu === 3 ? '2026-11-04' : '2026-11-11',
            'total_pendapatan' => 60500000,
            'total_pengeluaran' => 10200000,
            'laba_bersih' => 50300000,
            'margin_persen' => 83.1,
            'status_verifikasi' => $status,
            'tanggal_kirim' => '2026-11-04',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->itHead->id_user,
        ]);
    }

    public function test_page_renders_restyled_tabs_filters_and_rows(): void
    {
        $this->request();
        $this->laporan();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/manajemen-dana');

        $response->assertOk()
            ->assertSee('Permintaan dana')
            ->assertSee('Verifikasi laporan L/R')
            ->assertSee('Semua status')
            ->assertSee('Semua divisi')
            ->assertSee('Pengajuan')
            ->assertSee('Divisi / pengaju')
            ->assertSee('DMREQ001')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Aksi')
            ->assertSee('Detail')
            ->assertSee('Menampilkan 1-1 dari 1 pengajuan')
            ->assertDontSee('Permintaan Menunggu')
            ->assertDontSee('Laporan Belum Diverifikasi');

        $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/manajemen-dana?tab=laporan&periode=2026-11')
            ->assertOk()
            ->assertSee('Divisi / pengirim')
            ->assertSee('Minggu 3')
            ->assertSee('3–4 Nov 2026')
            ->assertSee('Rp 60.500.000')
            ->assertSee('83,1%')
            ->assertSee('Menunggu')
            ->assertSee('Detail')
            ->assertSee('Menampilkan 1-1 dari 1 laporan')
            ->assertSee('wire:click.stop="openVerifikasi', false);
    }

    public function test_filters_and_period_switcher_apply_live(): void
    {
        $this->request();
        $this->laporan();

        $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/manajemen-dana?tab=laporan&periode=bogus')
            ->assertOk()
            ->assertSee('November 2026');

        Livewire::actingAs($this->finance)
            ->test(ManajemenDana::class, ['periode' => '2026-11'])
            ->call('setTab', 'laporan')
            ->assertSet('tab', 'laporan')
            ->assertSee('November 2026')
            ->call('previous')
            ->assertSet('periode', '2026-10')
            ->assertSee('Oktober 2026')
            ->call('next')
            ->assertSet('periode', '2026-11')
            ->set('status', 'disetujui')
            ->assertSee('Terverifikasi');
    }

    public function test_verification_dialog_updates_row_count_and_audit_log(): void
    {
        $laporan = $this->laporan();

        Livewire::actingAs($this->finance)
            ->test(ManajemenDana::class)
            ->call('setTab', 'laporan')
            ->assertSee('Menunggu')
            ->call('openVerifikasi', $laporan->id_klr)
            ->assertSet('showVerifikasiModal', true)
            ->assertSee('Verifikasi laporan Information Technology, Minggu 3?')
            ->assertSee('3–4 Nov 2026')
            ->set('catatanVerifikasi', 'Sesuai dengan bukti')
            ->call('verifikasi')
            ->assertSet('showVerifikasiModal', false)
            ->assertDispatched('laporan-toast', message: 'Laporan diverifikasi', type: 'success')
            ->assertSee('Terverifikasi');

        $this->assertDatabaseHas('klaba_rugi', [
            'id_klr' => $laporan->id_klr,
            'status_verifikasi' => KLabaRugi::STATUS_DISETUJUI,
            'catatan_verifikasi' => 'Sesuai dengan bukti',
            'diverifikasi_oleh_id_user' => $this->finance->id_user,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id_user' => $this->finance->id_user,
            'aksi' => 'verifikasi_laporan_laba_rugi',
            'target_tipe' => 'laporan_laba_rugi',
            'target_id' => $laporan->id_klr,
        ]);
    }

    public function test_submitter_cannot_verify_and_stale_report_is_rejected(): void
    {
        $own = $this->laporan(KLabaRugi::STATUS_MENUNGGU, 'KLR-MD-OWN', 3);
        $own->update(['user_id_user' => $this->finance->id_user]);

        $this->assertFalse($this->finance->can('verify', $own));

        $waiting = $this->laporan(KLabaRugi::STATUS_MENUNGGU, 'KLR-MD-2', 4);
        app(VerifikasiLaporanService::class)->verifikasi($waiting->id_klr, $this->finance);

        $this->expectException(LaporanSudahDiverifikasiException::class);
        $this->expectExceptionMessage('Laporan ini sudah diverifikasi');

        app(VerifikasiLaporanService::class)->verifikasi($waiting->id_klr, $this->finance);
    }

    public function test_unrelated_user_cannot_verify(): void
    {
        $laporan = $this->laporan();

        $this->expectException(AuthorizationException::class);

        app(VerifikasiLaporanService::class)->verifikasi($laporan->id_klr, $this->itHead);
    }
}
