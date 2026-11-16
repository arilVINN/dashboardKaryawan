<?php

namespace Tests\Feature;

use App\Livewire\Finance\PermintaanDanaMasuk;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinancePermintaanUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    private User2 $itKadiv;

    private User2 $hrKadiv;

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
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Operasional', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);
        Karyawan::create(['id_karyawan' => 'KRY-IT', 'nama' => 'IT Lead', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Kadiv IT', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'KRY-HR', 'nama' => 'HR Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv HR', 'divisi_id_divisi' => 'DIV-HR']);

        $this->finance = $this->user('USR-FIN', 'finance.lead', 'ROLE-KADIV', 'KRY-FIN');
        $this->itKadiv = $this->user('USR-IT', 'it.lead', 'ROLE-KADIV', 'KRY-IT');
        $this->hrKadiv = $this->user('USR-HR', 'hr.lead', 'ROLE-KADIV', 'KRY-HR');
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
        string $divisiId,
        string $userId,
        string $judul,
        string $keperluan,
        float $nominal,
        string $status = KPermintDana::STATUS_MENUNGGU,
        ?string $approvedBy = null,
        ?string $approvedDate = null,
        ?string $catatan = null,
        string $createdAt = '2026-11-11 09:00:00',
        ?string $updatedAt = null,
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
            'divisi_id_divisi' => $divisiId,
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
        $this->request('DMKPD01', 'DMREQ001', 'DIV-IT', 'USR-IT', 'Pengadaan perangkat', 'Pembelian laptop tim IT', 2500000, createdAt: '2026-11-11 09:00:00');
        $this->request('DMKPD02', 'DMREQ002', 'DIV-HR', 'USR-HR', 'Pelatihan karyawan', 'Workshop leadership', 3500000, createdAt: '2026-11-12 10:00:00');
        $this->request('DMKPD03', 'DMREQ003', 'DIV-IT', 'USR-IT', 'Lisensi software', 'Perpanjangan lisensi tahunan', 7000000, createdAt: '2026-11-13 11:15:00');
        $this->request('DMKPD04', 'DMREQ004', 'DIV-IT', 'USR-IT', 'Perjalanan dinas', 'Kunjungan klien', 4000000, KPermintDana::STATUS_DISETUJUI, 'USR-FIN', '2026-11-14', 'Disetujui', '2026-11-10 08:00:00', '2026-11-14 15:00:00');
        $this->request('DMKPD05', 'DMREQ005', 'DIV-HR', 'USR-HR', 'Konsumsi rapat', 'Konsumsi rapat bulanan', 3000000, KPermintDana::STATUS_DITOLAK, 'USR-FIN', null, 'Tidak sesuai pagu', '2026-11-09 08:00:00', '2026-11-15 10:00:00');
    }

    public function test_page_renders_restyled_list_pill_and_actions(): void
    {
        $this->seedRequests();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/permintaan');

        $response->assertOk()
            ->assertSee('Permintaan Dana Masuk')
            ->assertSee('Review dan berikan persetujuan pengajuan anggaran operasional dari kepala divisi.')
            ->assertSee('data-breadcrumb-current="Permintaan Dana Masuk"', false)
            ->assertSee('3 menunggu · Rp 13.000.000')
            ->assertSee('Semua status')
            ->assertSee('Semua divisi')
            ->assertSee('Pengajuan')
            ->assertSee('Divisi / pengaju')
            ->assertSee('Nominal')
            ->assertSee('Status')
            ->assertSee('Aksi')
            ->assertSee('Pengadaan perangkat')
            ->assertSee('Pembelian laptop tim IT')
            ->assertSee('Information Technology')
            ->assertSee('IT Lead')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Tanggal pengajuan')
            ->assertSee('11 Nov 2026')
            ->assertSee('09:00')
            ->assertSee('Menampilkan 1-5 dari 5 pengajuan')
            ->assertSee('oleh Finance Lead, 14 Nov')
            ->assertSee('oleh Finance Lead, 15 Nov')
            ->assertSee('background-color: #FEF0C3', false)
            ->assertSee('background-color: #D5F3F0', false)
            ->assertSee('background-color: #FDE0E0', false)
            ->assertSee('Detail')
            ->assertSee('Setujui')
            ->assertSee('Tolak')
            ->assertDontSee('ACC');

        $html = $response->getContent();
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $this->assertSame(3, $xpath->query('//main//button[normalize-space(.)="Setujui"]')->length);
        $this->assertSame(3, $xpath->query('//main//button[normalize-space(.)="Tolak"]')->length);
        $this->assertSame(5, $xpath->query('//main//a[normalize-space(.)="Detail"]')->length);
        $this->assertSame(
            1,
            $xpath->query('//main//a[@href="'.route('kadiv.keuangan.permintaan.show', 'DMKPD01').'"]')->length
        );

        $firstPengajuanCell = $xpath->query('//main//tbody/tr[1]/td[1]')->item(0);
        $this->assertNotNull($firstPengajuanCell);
        $this->assertStringContainsString('Pengadaan perangkat', $firstPengajuanCell->textContent);
        $this->assertStringContainsString('Pembelian laptop tim IT', $firstPengajuanCell->textContent);
        $this->assertStringNotContainsString('DMREQ001', $firstPengajuanCell->textContent);
        $this->assertStringNotContainsString('11 Nov 2026', $firstPengajuanCell->textContent);
    }

    public function test_default_sort_puts_pending_oldest_first_then_others_by_date(): void
    {
        $this->seedRequests();

        $component = Livewire::actingAs($this->finance)->test(PermintaanDanaMasuk::class);

        $rows = $component->viewData('permintaan')
            ->getCollection()
            ->pluck('id_kpd')
            ->all();

        $this->assertSame(['DMKPD01', 'DMKPD02', 'DMKPD03', 'DMKPD04', 'DMKPD05'], $rows);
    }

    public function test_filters_run_live_and_reset_appears_only_when_active(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(PermintaanDanaMasuk::class)
            ->assertSee('Pengadaan perangkat')
            ->assertDontSee('Reset')
            ->set('status', KPermintDana::STATUS_MENUNGGU)
            ->assertSee('Pengadaan perangkat')
            ->assertDontSee('Perjalanan dinas')
            ->assertSee('Reset')
            ->call('filterMenunggu')
            ->assertSet('status', KPermintDana::STATUS_MENUNGGU)
            ->set('divisiId', 'DIV-IT')
            ->assertSee('Pengadaan perangkat')
            ->assertDontSee('Pelatihan karyawan')
            ->call('resetFilters')
            ->assertSet('status', '')
            ->assertSet('divisiId', '')
            ->assertDontSee('Reset');
    }

    public function test_empty_state_for_filters_without_results(): void
    {
        $this->seedRequests();

        $response = $this->actingAs($this->finance)
            ->get('/kadiv/keuangan/permintaan?status=ditolak&divisi_id=DIV-OPS');

        $response->assertOk()
            ->assertSee('Tidak ada pengajuan')
            ->assertSee('Coba ubah filter status atau divisi.')
            ->assertSee('Reset');
    }

    public function test_approve_dialog_and_action_update_row_pill_and_audit_log(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(PermintaanDanaMasuk::class)
            ->call('openApproveModal', 'DMKPD01')
            ->assertSet('showApproveModal', true)
            ->assertSee('Setujui pengajuan DMREQ001?')
            ->assertSee('Pengadaan perangkat')
            ->assertSee('Rp 2.500.000')
            ->assertSee('Information Technology')
            ->assertSee('akan disetujui')
            ->assertSee('x-ref="approveCancel"', false)
            ->assertSee('background-color: rgba(15, 40, 55, 0.45)', false)
            ->call('approve')
            ->assertSet('showApproveModal', false)
            ->assertDispatched('permintaan-toast', message: 'Pengajuan disetujui', type: 'success')
            ->assertSee('2 menunggu · Rp 10.500.000')
            ->assertSee('Disetujui');

        $this->assertDatabaseHas('kpermint_dana', [
            'id_kpd' => 'DMKPD01',
            'status' => KPermintDana::STATUS_DISETUJUI,
            'disetujui_oleh_id_user' => 'USR-FIN',
        ]);

        $log = AuditLog::where('aksi', 'setujui_permintaan_dana')->latest('id')->firstOrFail();
        $this->assertSame('USR-FIN', $log->user_id_user);
        $this->assertSame('disetujui', $log->detail['keputusan']);
        $this->assertSame('menunggu', $log->detail['status_lama']);
        $this->assertSame('disetujui', $log->detail['status_baru']);
        $this->assertSame('Disetujui melalui konfirmasi finance', $log->detail['alasan']);
        $this->assertEquals(2500000, $log->detail['nominal']);
    }

    public function test_reject_dialog_requires_reason_and_updates_row_pill_and_audit_log(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->finance)
            ->test(PermintaanDanaMasuk::class)
            ->call('openRejectModal', 'DMKPD02')
            ->assertSet('showRejectModal', true)
            ->assertSee('Tolak pengajuan DMREQ002?')
            ->assertSee('Pengaju akan menerima alasan penolakan.')
            ->assertSee('Jelaskan alasan penolakan')
            ->set('rejectReason', '')
            ->call('reject')
            ->assertSet('showRejectModal', true)
            ->assertHasErrors('rejectReason')
            ->assertSee('Alasan penolakan wajib diisi.')
            ->set('rejectReason', 'Tidak sesuai pagu')
            ->call('reject')
            ->assertSet('showRejectModal', false)
            ->assertDispatched('permintaan-toast', message: 'Pengajuan ditolak', type: 'success')
            ->assertSee('2 menunggu · Rp 9.500.000')
            ->assertSee('Ditolak');

        $this->assertDatabaseHas('kpermint_dana', [
            'id_kpd' => 'DMKPD02',
            'status' => KPermintDana::STATUS_DITOLAK,
            'catatan_finance' => 'Tidak sesuai pagu',
            'disetujui_oleh_id_user' => 'USR-FIN',
        ]);

        $log = AuditLog::where('aksi', 'tolak_permintaan_dana')->latest('id')->firstOrFail();
        $this->assertSame('ditolak', $log->detail['keputusan']);
        $this->assertSame('Tidak sesuai pagu', $log->detail['alasan']);
        $this->assertSame('USR-FIN', $log->detail['user_id']);
    }

    public function test_stale_request_is_rejected_with_message_and_no_audit_log(): void
    {
        $this->seedRequests();

        $component = Livewire::actingAs($this->finance)
            ->test(PermintaanDanaMasuk::class)
            ->call('openApproveModal', 'DMKPD01');

        // Simulasikan proses lain yang lebih dulu menyetujui pengajuan.
        KPermintDana::whereKey('DMKPD01')->update(['status' => KPermintDana::STATUS_DISETUJUI]);

        $component
            ->call('approve')
            ->assertSet('showApproveModal', false)
            ->assertDispatched('permintaan-toast', message: 'Pengajuan ini sudah diproses', type: 'error')
            ->assertSee('2 menunggu · Rp 10.500.000')
            ->assertSee('Disetujui');

        $this->assertSame(0, AuditLog::where('aksi', 'setujui_permintaan_dana')->count());
        $this->assertSame(
            KPermintDana::STATUS_DISETUJUI,
            KPermintDana::whereKey('DMKPD01')->value('status')
        );
    }

    public function test_policy_denies_non_finance_and_own_requests(): void
    {
        $this->seedRequests();
        $pending = KPermintDana::whereKey('DMKPD01')->firstOrFail();

        $own = $this->request('DMKPD06', 'DMREQ006', 'DIV-FIN', 'USR-FIN', 'Kebutuhan finance', 'Kebutuhan internal finance', 1000000, createdAt: '2026-11-11 08:00:00');

        $this->assertTrue(Gate::forUser($this->finance)->allows('approve', $pending));
        $this->assertTrue(Gate::forUser($this->finance)->allows('reject', $pending));
        $this->assertFalse(Gate::forUser($this->itKadiv)->allows('approve', $pending));
        $this->assertFalse(Gate::forUser($this->finance)->allows('approve', $own));
        $this->assertFalse(Gate::forUser($this->finance)->allows('reject', $own));
    }

    public function test_controller_route_denies_own_request_and_writes_audit_for_others(): void
    {
        $this->seedRequests();

        $own = $this->request('DMKPD06', 'DMREQ006', 'DIV-FIN', 'USR-FIN', 'Kebutuhan finance', 'Kebutuhan internal finance', 1000000, createdAt: '2026-11-11 08:00:00');

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.permintaan.approve', $own->id_kpd))
            ->assertRedirect(route('kadiv.dashboard'))
            ->assertSessionHas('forbidden');

        $this->assertSame(
            KPermintDana::STATUS_MENUNGGU,
            KPermintDana::whereKey($own->id_kpd)->value('status')
        );

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.permintaan.approve', 'DMKPD01'), [
                'catatan_finance' => 'Disetujui finance',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kpermint_dana', [
            'id_kpd' => 'DMKPD01',
            'status' => KPermintDana::STATUS_DISETUJUI,
        ]);

        $log = AuditLog::where('aksi', 'setujui_permintaan_dana')->latest('id')->firstOrFail();
        $this->assertSame('Disetujui finance', $log->detail['alasan']);
    }

    public function test_non_finance_user_is_forbidden_from_livewire_component(): void
    {
        $this->seedRequests();

        Livewire::actingAs($this->itKadiv)
            ->test(PermintaanDanaMasuk::class)
            ->assertForbidden();
    }
}
