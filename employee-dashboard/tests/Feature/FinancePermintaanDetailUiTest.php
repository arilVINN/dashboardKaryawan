<?php

namespace Tests\Feature;

use App\Livewire\Finance\DetailPermintaanDana;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KeuanganAldiv;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\User2;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FinancePermintaanDetailUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    private User2 $itKadiv;

    private User2 $hrKadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-13 12:00:00');

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resources', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);
        Karyawan::create(['id_karyawan' => 'KRY-IT', 'nama' => 'IT Lead', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Kadiv IT', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'KRY-HR', 'nama' => 'HR Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv HR', 'divisi_id_divisi' => 'DIV-HR']);

        $this->finance = $this->user('USR-FIN', 'finance.lead', 'ROLE-KADIV', 'KRY-FIN');
        $this->itKadiv = $this->user('USR-IT', 'it.lead', 'ROLE-KADIV', 'KRY-IT');
        $this->hrKadiv = $this->user('USR-HR', 'hr.lead', 'ROLE-KADIV', 'KRY-HR');

        KeuanganAldiv::create([
            'id_kad' => 'KAD001',
            'periode' => '2026-11',
            'nominal_alokasi' => 10000000,
            'keterangan' => 'Pagu operasional IT',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => 'USR-FIN',
        ]);

        KPengeluaran::create([
            'id_kpengeluaran' => 'KPOUT001',
            'kode_pengeluaran' => 'OUT-DETAIL-1',
            'nominal' => 4000000,
            'tanggal_cair' => '2026-11-10',
            'metode_pembayaran' => 'Transfer Bank',
            'keterangan' => 'Realisasi operasional IT',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => 'USR-FIN',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function user(string $id, string $username, string $role, string $karyawan): User2
    {
        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $karyawan,
        ]);
    }

    private function request(
        string $id,
        string $nomor,
        float $nominal,
        string $userId = 'USR-IT',
        string $status = KPermintDana::STATUS_MENUNGGU,
        ?string $approvedBy = null,
        ?string $approvedDate = null,
        ?string $catatan = null,
        ?string $lampiran = null,
        string $createdAt = '2026-11-13 11:15:00',
        ?string $updatedAt = null,
        string $judul = 'Pengadaan perangkat',
        string $keperluan = 'Pembelian laptop tim IT.',
    ): KPermintDana {
        $permintaan = KPermintDana::create([
            'id_kpd' => $id,
            'nomor_pengajuan' => $nomor,
            'judul_pengajuan' => $judul,
            'keperluan' => $keperluan,
            'nominal_diajukan' => $nominal,
            'status' => $status,
            'tanggal_disetujui' => $approvedDate,
            'catatan_finance' => $catatan,
            'lampiran_proposal' => $lampiran,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $userId,
            'disetujui_oleh_id_user' => $approvedBy,
        ]);

        $permintaan->timestamps = false;
        $permintaan->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt ?? $createdAt,
        ])->save();
        $permintaan->timestamps = true;

        return $permintaan;
    }

    private function seedRequests(): void
    {
        $this->request('DMKPD01', 'DMREQ001', 2500000);
        $this->request('DMKPD02', 'DMREQ002', 8000000, judul: 'Lisensi software', keperluan: 'Perpanjangan lisensi tahunan.');
        $this->request('DMKPD03', 'DMREQ003', 1500000, judul: 'Perjalanan dinas', keperluan: 'Kunjungan klien di Surabaya.');
        $this->request('DMKPD04', 'DMREQ004', 1500000, status: KPermintDana::STATUS_DISETUJUI, approvedBy: 'USR-FIN', approvedDate: '2026-11-14', catatan: 'Disetujui', createdAt: '2026-11-10 08:00:00', updatedAt: '2026-11-14 09:30:00', judul: 'Perjalanan dinas', keperluan: 'Kunjungan klien di Surabaya.');
        $this->request('DMKPD05', 'DMREQ005', 1000000, userId: 'USR-FIN', createdAt: '2026-11-13 11:30:00', judul: 'Kebutuhan finance', keperluan: 'Kebutuhan internal finance.');
    }

    public function test_page_renders_restyled_detail_and_breadcrumbs(): void
    {
        $this->seedRequests();
        $request = KPermintDana::whereKey('DMKPD01')->firstOrFail();

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/permintaan/'.$request->id_kpd);

        $response->assertOk()
            ->assertSee('data-breadcrumb-root="Dashboard"', false)
            ->assertSee('data-breadcrumb-parent="Permintaan Dana Masuk"', false)
            ->assertSee('data-breadcrumb-current="Pengadaan perangkat"', false)
            ->assertSee('Pengadaan perangkat')
            ->assertSee('Menunggu persetujuan')
            ->assertSee('Diajukan oleh IT Lead')
            ->assertSee('Information Technology (IT)')
            ->assertSee('13 Nov 2026, 11:15')
            ->assertSee('Nominal diajukan')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Keperluan')
            ->assertSee('Pembelian laptop tim IT.')
            ->assertSee('Lampiran')
            ->assertSee('Tidak ada proposal atau RAB yang dilampirkan.')
            ->assertSee('Keputusan')
            ->assertSee('Pagu Information Technology, November 2026')
            ->assertSee('Pagu bulan ini')
            ->assertSee('Rp 10.000.000')
            ->assertSee('Sudah dicairkan')
            ->assertSee('Rp 4.000.000')
            ->assertSee('Sisa pagu')
            ->assertSee('Rp 6.000.000')
            ->assertSee('Sisa jika disetujui')
            ->assertSee('Rp 3.500.000')
            ->assertSee('Terserap 40% → 65% jika disetujui')
            ->assertSee('background-color: #FEF0C3', false)
            ->assertSee('Catatan')
            ->assertSee('(opsional)')
            ->assertSee('Tambahkan catatan untuk pengaju')
            ->assertSee('Keputusan dicatat dan dikirim ke pengaju.')
            ->assertSee('Setujui')
            ->assertSee('Tolak')
            ->assertDontSee('Rincian Pengajuan Dana')
            ->assertDontSee('Analisis Pagu Divisi')
            ->assertDontSee('Nomor Dokumen')
            ->assertDontSee('Tanggal Pengajuan')
            ->assertDontSee('Divisi Pemohon')
            ->assertDontSee('(ACC)')
            ->assertDontSee('Setujui Pengajuan Ini')
            ->assertDontSee('Alasan Penolakan');
    }

    public function test_over_pagu_panel_and_approve_dialog_warning(): void
    {
        $this->seedRequests();
        $request = KPermintDana::whereKey('DMKPD02')->firstOrFail();

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/permintaan/'.$request->id_kpd);

        $response->assertOk()
            ->assertSee('(Rp 2.000.000)')
            ->assertSee('Melebihi sisa pagu sebesar Rp 2.000.000')
            ->assertSee('Terserap 40% → 120% jika disetujui');

        Livewire::actingAs($this->finance)
            ->test(DetailPermintaanDana::class, ['id' => $request->id_kpd])
            ->call('openApproveModal')
            ->assertSet('showApproveModal', true)
            ->assertSee('Melebihi sisa pagu sebesar Rp 2.000.000')
            ->assertSee('Tetap setujui');
    }

    public function test_attachment_row_renders_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('keuangan/proposal/proposal.pdf', 'PDF content');

        $this->request('DMKPD03', 'DMREQ003', 1500000, lampiran: 'keuangan/proposal/proposal.pdf', judul: 'Perjalanan dinas', keperluan: 'Kunjungan klien di Surabaya.');

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/permintaan/DMKPD03');

        $response->assertOk()
            ->assertSee('proposal.pdf')
            ->assertSee('PDF')
            ->assertSee('Unduh')
            ->assertDontSee('Tidak ada proposal atau RAB yang dilampirkan.');
    }

    public function test_approve_with_note_updates_panel_audit_and_notification(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(DetailPermintaanDana::class, ['id' => 'DMKPD01'])
            ->set('note', 'Mohon lengkapi nota pembelian')
            ->call('openApproveModal')
            ->assertSet('showApproveModal', true)
            ->assertSee('Setujui pengajuan DMREQ001?')
            ->assertSee('Rp 2.500.000')
            ->assertSee('x-ref="approveCancel"', false)
            ->call('approve')
            ->assertSet('showApproveModal', false)
            ->assertDispatched('permintaan-detail-toast', message: 'Pengajuan disetujui', type: 'success')
            ->assertSee('Disetujui oleh Finance Lead')
            ->assertDontSee('Keputusan dicatat dan dikirim ke pengaju.')
            ->assertDontSee('Tambahkan catatan untuk pengaju');

        $this->assertDatabaseHas('kpermint_dana', [
            'id_kpd' => 'DMKPD01',
            'status' => KPermintDana::STATUS_DISETUJUI,
            'catatan_finance' => 'Mohon lengkapi nota pembelian',
            'disetujui_oleh_id_user' => 'USR-FIN',
        ]);

        $log = AuditLog::where('aksi', 'setujui_permintaan_dana')->latest('id')->firstOrFail();
        $this->assertSame('Mohon lengkapi nota pembelian', $log->detail['alasan']);
        $this->assertSame('Mohon lengkapi nota pembelian', $log->detail['catatan']);

        $notification = Notifikasi::where('user_id_user', 'USR-IT')->latest('id')->firstOrFail();
        $this->assertStringContainsString('Mohon lengkapi nota pembelian', $notification->isi_notif);
        $this->assertStringContainsString('disetujui', $notification->isi_notif);
    }

    public function test_reject_requires_reason_and_updates_panel_audit_and_notification(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(DetailPermintaanDana::class, ['id' => 'DMKPD01'])
            ->set('note', 'Catatan untuk pengaju')
            ->call('openRejectModal')
            ->assertSet('showRejectModal', true)
            ->assertSee('Tolak pengajuan DMREQ001?')
            ->assertSee('Pengaju akan menerima alasan penolakan.')
            ->set('rejectReason', '')
            ->call('reject')
            ->assertSet('showRejectModal', true)
            ->assertHasErrors('rejectReason')
            ->assertSee('Alasan penolakan wajib diisi.')
            ->set('rejectReason', 'Tidak sesuai pagu')
            ->call('reject')
            ->assertSet('showRejectModal', false)
            ->assertDispatched('permintaan-detail-toast', message: 'Pengajuan ditolak', type: 'success')
            ->assertSee('Ditolak oleh Finance Lead')
            ->assertSee('Alasan: Tidak sesuai pagu')
            ->assertDontSee('Keputusan dicatat dan dikirim ke pengaju.');

        $this->assertDatabaseHas('kpermint_dana', [
            'id_kpd' => 'DMKPD01',
            'status' => KPermintDana::STATUS_DITOLAK,
            'catatan_finance' => 'Tidak sesuai pagu',
            'disetujui_oleh_id_user' => 'USR-FIN',
        ]);

        $log = AuditLog::where('aksi', 'tolak_permintaan_dana')->latest('id')->firstOrFail();
        $this->assertSame('Tidak sesuai pagu', $log->detail['alasan']);
        $this->assertSame('Catatan untuk pengaju', $log->detail['catatan']);

        $notification = Notifikasi::where('user_id_user', 'USR-IT')->latest('id')->firstOrFail();
        $this->assertStringContainsString('Tidak sesuai pagu', $notification->isi_notif);
        $this->assertStringContainsString('Catatan untuk pengaju', $notification->isi_notif);
    }

    public function test_decided_request_shows_read_only_summary_without_buttons(): void
    {
        $this->seedRequests();

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/permintaan/DMKPD04');

        $response->assertOk()
            ->assertSee('Disetujui oleh Finance Lead')
            ->assertSee('14 Nov 2026, 09:30')
            ->assertDontSee('Keputusan dicatat dan dikirim ke pengaju.')
            ->assertDontSee('Tambahkan catatan untuk pengaju')
            ->assertDontSee('wire:click="openApproveModal"', false)
            ->assertDontSee('wire:click="openRejectModal"', false);
    }

    public function test_buttons_are_hidden_for_requester_own_request(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(DetailPermintaanDana::class, ['id' => 'DMKPD05'])
            ->assertSee('Keputusan')
            ->assertDontSee('Keputusan dicatat dan dikirim ke pengaju.')
            ->assertDontSee('Tambahkan catatan untuk pengaju')
            ->assertDontSee('wire:click="openApproveModal"', false)
            ->assertDontSee('wire:click="openRejectModal"', false);
    }

    public function test_stale_request_shows_message_and_refreshes_panel(): void
    {
        $this->seedRequests();

        $component = Livewire::actingAs($this->finance)
            ->test(DetailPermintaanDana::class, ['id' => 'DMKPD01'])
            ->call('openApproveModal');

        KPermintDana::whereKey('DMKPD01')->update(['status' => KPermintDana::STATUS_DISETUJUI]);

        $component
            ->call('approve')
            ->assertSet('showApproveModal', false)
            ->assertDispatched('permintaan-detail-toast', message: 'Pengajuan ini sudah diproses', type: 'error')
            ->assertSee('Disetujui oleh Finance')
            ->assertDontSee('Keputusan dicatat dan dikirim ke pengaju.');

        $this->assertSame(0, AuditLog::where('aksi', 'setujui_permintaan_dana')->count());
        $this->assertSame(0, Notifikasi::where('user_id_user', 'USR-IT')->count());
    }

    public function test_non_finance_user_is_forbidden(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->itKadiv)
            ->test(DetailPermintaanDana::class, ['id' => 'DMKPD01'])
            ->assertForbidden();
    }
}
