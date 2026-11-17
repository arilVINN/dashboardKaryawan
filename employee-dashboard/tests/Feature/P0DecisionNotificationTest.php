<?php

namespace Tests\Feature;

use App\Events\TugasDitugasan;
use App\Models\BookingRequest;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KeuanganAldiv;
use App\Models\KLabaRugi;
use App\Models\KPermintDana;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\SubmitTugas;
use App\Models\SuratIzin;
use App\Models\Tugas;
use App\Models\User2;
use App\Services\Finance\AlokasiPaguService;
use App\Services\Finance\VerifikasiLaporanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class P0DecisionNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;
    private User2 $hrd;
    private User2 $kadiv;
    private User2 $finance;
    private User2 $direktur;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direktur'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-DIR', 'kode_divisi' => 'DIR', 'nama_divisi' => 'Direktur Utama', 'status_aktif' => 'Aktif']);

        $this->createEmployee('STF', 'Staff One', 'Staff', 'DIV-IT');
        $this->createEmployee('HRD', 'HRD One', 'HRD Manager', 'DIV-IT');
        $this->createEmployee('KAD', 'Kadiv One', 'Kepala Divisi IT', 'DIV-IT');
        $this->createEmployee('FIN', 'Finance Lead', 'Kadiv Finance', 'DIV-FIN');
        $this->createEmployee('DIR', 'Direktur One', 'Direktur Utama', 'DIV-DIR');

        $this->staff = $this->createUser('STAFF', 'ROLE-STAFF', 'STF', 'staff');
        $this->hrd = $this->createUser('HRD', 'ROLE-HRD', 'HRD', 'hrd.user');
        $this->kadiv = $this->createUser('KAD', 'ROLE-KADIV', 'KAD', 'kadiv.user');
        $this->finance = $this->createUser('FIN', 'ROLE-KADIV', 'FIN', 'finance.lead');
        $this->direktur = $this->createUser('DIR', 'ROLE-DIR', 'DIR', 'direktur');
    }

    // ── 1. Surat izin keputusan ────────────────────────────────────────────────

    public function test_surat_izin_decision_notifies_requester_only_once(): void
    {
        $izin = SuratIzin::create([
            'id_surat' => 'SRT-P0-1',
            'karyawan_id_karyawan' => 'KRY-STF',
            'judul_surat' => 'Izin Sakit',
            'jenis_izin' => 'Izin',
            'tanggal_izin' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.acc', $izin))
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->staff->id_user)->firstOrFail();
        $this->assertSame('Surat Izin Disetujui', $notification->judul_notifikasi);
        $this->assertStringContainsString('Izin Sakit', $notification->isi_notif);
        $this->assertStringContainsString('disetujui', $notification->isi_notif);
        $this->assertSame(0, Notifikasi::where('user_id_user', $this->hrd->id_user)->count());

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.tolak', $izin))
            ->assertStatus(409);
        $this->assertSame(1, Notifikasi::where('user_id_user', $this->staff->id_user)->count());
    }

    public function test_rejected_surat_izin_notifies_requester(): void
    {
        $izin = SuratIzin::create([
            'id_surat' => 'SRT-P0-2',
            'karyawan_id_karyawan' => 'KRY-STF',
            'judul_surat' => 'Izin Keluarga',
            'tanggal_izin' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.tolak', $izin))
            ->assertRedirect();

        $notification = Notifikasi::where('user_id_user', $this->staff->id_user)->firstOrFail();
        $this->assertSame('Surat Izin Ditolak', $notification->judul_notifikasi);
        $this->assertStringContainsString('ditolak', $notification->isi_notif);
    }

    // ── 2. Task approval ──────────────────────────────────────────────────────

    public function test_task_approval_notifies_staff_owner(): void
    {
        Event::fake([TugasDitugasan::class]);

        $tugas = Tugas::create([
            'id_tugas' => 'TGS-P0-1',
            'karyawan_id_karyawan' => 'KRY-STF',
            'judul_tugas' => 'Buat laporan bulanan',
            'status' => Tugas::STATUS_MENUNGGU_ACC,
            'deadline' => now()->addDays(3),
            'tanggal_dibuat' => now(),
        ]);
        SubmitTugas::create([
            'id_submit_tugas' => 'SB-P0-1',
            'tugas_id_tugas' => $tugas->id_tugas,
            'tugas_karyawan_id_karyawan' => 'KRY-STF',
            'catatan_karyawan' => 'Sudah selesai',
            'status_review' => 'submitted',
        ]);

        $this->actingAs($this->kadiv, 'sanctum')
            ->postJson("/api/kadiv/tugas/{$tugas->id_tugas}/review", ['status_review' => 'acc'])
            ->assertOk();

        $notification = Notifikasi::where('user_id_user', $this->staff->id_user)
            ->where('judul_notifikasi', 'Tugas Disetujui')
            ->firstOrFail();
        $this->assertSame('Tugas Disetujui', $notification->judul_notifikasi);
        $this->assertStringContainsString('Buat laporan bulanan', $notification->isi_notif);
        $this->assertStringContainsString('disetujui', $notification->isi_notif);
        $this->assertSame($tugas->id_tugas, $notification->tugas_id_tugas);
    }

    // ── 3. Finance decision chain ─────────────────────────────────────────────

    public function test_pencairan_dana_notifies_requester(): void
    {
        $permintaan = KPermintDana::create([
            'id_kpd' => 'KPD-P0-1',
            'nomor_pengajuan' => 'DMP0-001',
            'judul_pengajuan' => 'Pengadaan laptop',
            'keperluan' => 'Kebutuhan tim IT',
            'nominal_diajukan' => 2500000,
            'status' => KPermintDana::STATUS_DISETUJUI,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->kadiv->id_user,
        ]);

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.cairkan', $permintaan->id_kpd), [
                'tanggal_cair' => now()->toDateString(),
                'metode_pembayaran' => 'Transfer Bank',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->kadiv->id_user)->firstOrFail();
        $this->assertSame('Dana Dicairkan', $notification->judul_notifikasi);
        $this->assertStringContainsString('Pengadaan laptop', $notification->isi_notif);
        $this->assertStringContainsString('DMP0-001', $notification->isi_notif);
    }

    public function test_verifikasi_laporan_notifies_pelapor_with_note(): void
    {
        $laporan = KLabaRugi::create([
            'id_klr' => 'KLR-P0-1',
            'periode' => '2026-11',
            'minggu_ke' => 3,
            'tanggal_mulai' => '2026-11-03',
            'tanggal_selesai' => '2026-11-04',
            'total_pendapatan' => 60000000,
            'total_pengeluaran' => 10000000,
            'laba_bersih' => 50000000,
            'margin_persen' => 83.3,
            'status_verifikasi' => KLabaRugi::STATUS_MENUNGGU,
            'tanggal_kirim' => '2026-11-04',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->kadiv->id_user,
        ]);

        app(VerifikasiLaporanService::class)
            ->verifikasi($laporan->id_klr, $this->finance, 'Bukti sudah sesuai');

        $notification = Notifikasi::where('user_id_user', $this->kadiv->id_user)->firstOrFail();
        $this->assertSame('Laporan L/R Disetujui', $notification->judul_notifikasi);
        $this->assertStringContainsString('disetujui', $notification->isi_notif);
        $this->assertStringContainsString('Bukti sudah sesuai', $notification->isi_notif);
    }

    public function test_perlu_revisi_laporan_notifies_pelapor(): void
    {
        $laporan = KLabaRugi::create([
            'id_klr' => 'KLR-P0-2',
            'periode' => '2026-11',
            'minggu_ke' => 4,
            'tanggal_mulai' => '2026-11-10',
            'tanggal_selesai' => '2026-11-11',
            'total_pendapatan' => 60000000,
            'total_pengeluaran' => 10000000,
            'laba_bersih' => 50000000,
            'margin_persen' => 83.3,
            'status_verifikasi' => KLabaRugi::STATUS_MENUNGGU,
            'tanggal_kirim' => '2026-11-11',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->kadiv->id_user,
        ]);

        app(VerifikasiLaporanService::class)
            ->putuskan($laporan->id_klr, $this->finance, KLabaRugi::STATUS_PERLU_REVISI, 'Lengkapi bukti');

        $notification = Notifikasi::where('user_id_user', $this->kadiv->id_user)->firstOrFail();
        $this->assertSame('Laporan L/R Perlu Revisi', $notification->judul_notifikasi);
        $this->assertStringContainsString('memerlukan revisi', $notification->isi_notif);
        $this->assertStringContainsString('Lengkapi bukti', $notification->isi_notif);
    }

    public function test_alokasi_pagu_notifies_division_head(): void
    {
        $alokasi = app(AlokasiPaguService::class)->create([
            'periode' => '2026-11',
            'nominal_alokasi' => 50000000,
            'keterangan' => 'Pagu operasional IT',
            'divisi_id_divisi' => 'DIV-IT',
        ], $this->finance);

        $notification = Notifikasi::where('user_id_user', $this->kadiv->id_user)->firstOrFail();
        $this->assertSame('Pagu Anggaran Ditetapkan', $notification->judul_notifikasi);
        $this->assertStringContainsString('Information Technology', $notification->isi_notif);
        $this->assertStringContainsString('50.000.000', $notification->isi_notif);

        app(AlokasiPaguService::class)->update($alokasi->fresh(), [
            'nominal_alokasi' => 75000000,
            'keterangan' => 'Pagu revisi',
        ], $this->finance);

        $this->assertSame(
            2,
            Notifikasi::where('user_id_user', $this->kadiv->id_user)->count()
        );
        $this->assertSame(
            1,
            Notifikasi::where('user_id_user', $this->kadiv->id_user)
                ->where('judul_notifikasi', 'Pagu Anggaran Diperbarui')
                ->count()
        );
        $this->assertDatabaseHas('keuangan_aldiv', [
            'id_kad' => $alokasi->id_kad,
            'nominal_alokasi' => 75000000,
        ]);
    }

    // ── 4. Direktur queue & notification feed ─────────────────────────────────

    public function test_direktur_dashboard_shows_pending_queues_and_notifications(): void
    {
        $this->withoutVite();

        SuratIzin::create([
            'id_surat' => 'SRT-P0-DIR',
            'karyawan_id_karyawan' => 'KRY-STF',
            'judul_surat' => 'Izin Pending',
            'tanggal_izin' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        BookingRequest::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'kategori' => 'Ruangan',
            'jenis' => 'Ruang Meeting',
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'keperluan' => 'Rapat direksi',
            'pemohon_id_user' => $this->staff->id_user,
            'status' => 'Pending',
        ]);

        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Booking Disetujui',
            'isi_notif' => 'Booking Ruangan (Ruang Meeting) disetujui.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->direktur->id_user,
        ]);
        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Notifikasi orang lain',
            'isi_notif' => 'Tidak boleh tampil.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->staff->id_user,
        ]);

        $this->actingAs($this->direktur)
            ->get('/direktur/dashboard')
            ->assertOk()
            ->assertSee('Notifikasi Terbaru')
            ->assertSee('Booking Disetujui')
            ->assertViewHas('suratIzinPending', 1)
            ->assertViewHas('bookingPending', 1);

        $this->actingAs($this->direktur)
            ->get('/direktur/notifikasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul_notifikasi', 'Booking Disetujui');

        $this->actingAs($this->direktur, 'sanctum')
            ->getJson('/api/direktur/notifikasi')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    private function createEmployee(string $suffix, string $nama, string $jabatan, string $divisi): void
    {
        Karyawan::create([
            'id_karyawan' => 'KRY-'.$suffix,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisi,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);
    }

    private function createUser(string $suffix, string $roleId, string $employeeSuffix, string $username): User2
    {
        return User2::create([
            'id_user' => 'USR-'.$suffix,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'KRY-'.$employeeSuffix,
        ]);
    }
}
