<?php

namespace Tests\Feature;

use App\Events\PesanDikirim;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KadivApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([PesanDikirim::class]);

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::insert([
            ['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resource', 'status_aktif' => 'aktif'],
        ]);
    }

    public function test_dashboard_returns_division_metrics_and_staff_summary(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it', '2026-09-29 08:30:00');
        $outsideStaff = $this->createAccount('ST-2', 'Staff HR', 'DIV-HR', 'ROLE-STAFF', 'staff.hr');

        $this->createTask('T-1', $staff->karyawan_id_karyawan, Tugas::STATUS_BARU);
        $this->createTask('T-2', $staff->karyawan_id_karyawan, Tugas::STATUS_MENUNGGU_ACC);
        $this->createTask('T-3', $staff->karyawan_id_karyawan, Tugas::STATUS_SUDAH_ACC);
        $this->createTask('T-4', $staff->karyawan_id_karyawan, Tugas::STATUS_BERJALAN);
        // Deadline lewat, belum di-ACC → bucket "telat", bukan "berjalan".
        $this->createTask(
            'T-5',
            $staff->karyawan_id_karyawan,
            Tugas::STATUS_BERJALAN,
            now()->subDays(5)->toDateString()
        );
        $this->createTask('T-OUT', $outsideStaff->karyawan_id_karyawan);

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/dashboard')
            ->assertOk()
            ->assertJsonPath('data.metrics.tugas_baru', 1)
            ->assertJsonPath('data.metrics.tugas_berjalan', 1)
            ->assertJsonPath('data.metrics.tugas_menunggu_di_acc', 1)
            ->assertJsonPath('data.metrics.tugas_sudah_di_acc', 1)
            ->assertJsonPath('data.metrics.tugas_telat', 1)
            ->assertJsonPath('data.metrics.persentase_penyelesaian', 20)
            ->assertJsonCount(1, 'data.staff')
            ->assertJsonPath('data.staff.0.nama', 'Staff IT')
            ->assertJsonPath('data.staff.0.jumlah_tugas_dikerjakan', 5);
    }

    public function test_kadiv_can_send_and_list_a_letter_to_hrd(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $hrd = $this->createAccount('HR-1', 'HRD', 'DIV-HR', 'ROLE-HRD', 'hrd');

        $sendResponse = $this->actingAs($kadiv)
            ->postJson('/api/kadiv/pesan', [
                'penerima_id_user' => $hrd->id_user,
                'tipe' => 'surat',
                'judul_pesan' => 'Permintaan Rekrutmen',
                'deskripsi' => 'Mohon tindak lanjut.',
                'link_lampiran' => 'https://example.com/dokumen',
            ])
            ->assertCreated()
            ->assertJsonPath('data.tipe', 'surat')
            ->assertJsonPath('data.arah', 'keluar')
            ->assertJsonPath('data.penerima.id_user', $hrd->id_user);

        $messageId = $sendResponse->json('data.id_pesan');

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/pesan/'.$messageId)
            ->assertOk()
            ->assertJsonPath('data.id_pesan', $messageId);

        $this->actingAs($hrd)
            ->getJson('/api/hrd/pesan/'.$messageId)
            ->assertOk()
            ->assertJsonPath('data.penerima.id_user', $hrd->id_user);

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $kadiv->id_user,
            'penerima_id_user' => $hrd->id_user,
            'tipe' => 'surat',
        ]);

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/pesan?tipe=surat&arah=keluar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_kadiv_cannot_send_to_staff_from_another_division(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $outsideStaff = $this->createAccount('ST-2', 'Staff HR', 'DIV-HR', 'ROLE-STAFF', 'staff.hr');

        $this->actingAs($kadiv)
            ->postJson('/api/kadiv/pesan', [
                'penerima_id_user' => $outsideStaff->id_user,
                'tipe' => 'pesan',
                'judul_pesan' => 'Tidak boleh terkirim',
                'deskripsi' => 'Lintas divisi.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Penerima harus Staff di divisi Anda atau HRD.');

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_kadiv_and_hrd_can_reply_to_messages_but_not_letters(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $hrd = $this->createAccount('HR-1', 'HRD', 'DIV-HR', 'ROLE-HRD', 'hrd');
        Storage::fake('public');

        Pesan::create([
            'id_pesan' => 'PSN-TO-HRD',
            'judul_pesan' => 'Permintaan data',
            'deskripsi' => 'Mohon bantuannya.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $kadiv->id_user,
            'penerima_id_user' => $hrd->id_user,
        ]);

        $this->actingAs($hrd)
            ->post('/api/hrd/pesan/PSN-TO-HRD/balas', [
                'deskripsi' => 'Data akan dikirim hari ini.',
                'file_lampiran' => UploadedFile::fake()->create('data.pdf', 1, 'application/pdf'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.balasan_dari_id_pesan', 'PSN-TO-HRD');

        $this->actingAs($kadiv)
            ->postJson('/api/kadiv/pesan/PSN-TO-HRD/balas', [
                'deskripsi' => 'Terima kasih atas informasinya.',
            ])
            ->assertCreated();

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/pesan/PSN-TO-HRD')
            ->assertOk()
            ->assertJsonCount(2, 'data.balasan');

        $this->actingAs($hrd)
            ->getJson('/api/hrd/pesan/PSN-TO-HRD')
            ->assertOk()
            ->assertJsonCount(2, 'data.balasan')
            ->assertJsonFragment(['file' => Storage::disk('public')->url(Pesan::where('balasan_dari_id_pesan', 'PSN-TO-HRD')->first()->file_lampiran)]);

        Pesan::create([
            'id_pesan' => 'PSN-LETTER-HRD',
            'judul_pesan' => 'Surat resmi',
            'deskripsi' => 'Surat tidak dapat dibalas.',
            'tipe' => 'surat',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $kadiv->id_user,
            'penerima_id_user' => $hrd->id_user,
        ]);

        $this->actingAs($kadiv)
            ->postJson('/api/kadiv/pesan/PSN-LETTER-HRD/balas', [
                'deskripsi' => 'Percobaan balas surat.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Surat tidak dapat dibalas.');

        $this->actingAs($hrd)
            ->postJson('/api/hrd/pesan/PSN-LETTER-HRD/balas', [
                'deskripsi' => 'Percobaan balas surat.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Surat tidak dapat dibalas.');
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->actingAs($staff)
            ->getJson('/api/kadiv/dashboard')
            ->assertForbidden();
    }

    public function test_tugas_list_sorts_by_judul(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->createTask('T-1', 'ST-1', Tugas::STATUS_BARU);
        $this->createTask('T-2', 'ST-1', Tugas::STATUS_BARU);
        $this->createTask('T-3', 'ST-1', Tugas::STATUS_BARU);
        Tugas::where('id_tugas', 'T-1')->update(['judul_tugas' => 'Gamma']);
        Tugas::where('id_tugas', 'T-2')->update(['judul_tugas' => 'Alpha']);
        Tugas::where('id_tugas', 'T-3')->update(['judul_tugas' => 'Beta']);

        $asc = $this->actingAs($kadiv)->getJson('/api/kadiv/tugas?sort=judul&dir=asc')->assertOk();
        $this->assertSame(['T-2', 'T-3', 'T-1'], collect($asc->json('data'))->pluck('id_tugas')->all());

        $desc = $this->actingAs($kadiv)->getJson('/api/kadiv/tugas?sort=judul&dir=desc')->assertOk();
        $this->assertSame(['T-1', 'T-3', 'T-2'], collect($desc->json('data'))->pluck('id_tugas')->all());
    }

    public function test_tugas_list_sorts_by_tanggal(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->createTask('T-1', 'ST-1', Tugas::STATUS_BARU, '2026-01-10');
        $this->createTask('T-2', 'ST-1', Tugas::STATUS_BARU, '2026-01-05');
        $this->createTask('T-3', 'ST-1', Tugas::STATUS_BARU, '2026-01-20');

        $asc = $this->actingAs($kadiv)->getJson('/api/kadiv/tugas?sort=tanggal&dir=asc')->assertOk();
        $this->assertSame(['T-2', 'T-1', 'T-3'], collect($asc->json('data'))->pluck('id_tugas')->all());
    }

    public function test_tugas_list_filters_by_status_and_staff(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');
        $this->createAccount('ST-2', 'Staff Lain', 'DIV-IT', 'ROLE-STAFF', 'staff.lain');

        $this->createTask('T-1', 'ST-1', Tugas::STATUS_BARU);
        $this->createTask('T-2', 'ST-2', Tugas::STATUS_SUDAH_ACC);

        $byStatus = $this->actingAs($kadiv)
            ->getJson('/api/kadiv/tugas?status=' . urlencode('sudah di-acc'))
            ->assertOk();
        $this->assertSame(['T-2'], collect($byStatus->json('data'))->pluck('id_tugas')->all());

        $byStaff = $this->actingAs($kadiv)->getJson('/api/kadiv/tugas?staff=ST-1')->assertOk();
        $this->assertSame(['T-1'], collect($byStaff->json('data'))->pluck('id_tugas')->all());
    }

    public function test_tugas_list_searches_judul_case_insensitively(): void
    {
        // Force SQLite LIKE to be case-sensitive so this mirrors Postgres.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->createTask('T-1', 'ST-1', Tugas::STATUS_BARU);
        $this->createTask('T-2', 'ST-1', Tugas::STATUS_BARU);
        Tugas::where('id_tugas', 'T-1')->update(['judul_tugas' => 'Bikin API']);
        Tugas::where('id_tugas', 'T-2')->update(['judul_tugas' => 'Laporan']);

        $response = $this->actingAs($kadiv)->getJson('/api/kadiv/tugas?q=bikin')->assertOk();
        $this->assertSame(['T-1'], collect($response->json('data'))->pluck('id_tugas')->all());
    }

    public function test_kadiv_tugas_page_renders_sort_filter_controls(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');

        $this->actingAs($kadiv)->get('/kadiv/tugas')
            ->assertOk()
            ->assertSee('id="kadivTaskSearch"', false)
            ->assertSee('id="kadivTaskStatus"', false)
            ->assertSee('id="kadivTaskStaff"', false)
            ->assertSee('setKadivTaskSort', false)
            ->assertSee('data-sort-indicator="judul"', false);
    }

    public function test_pesan_list_sorts_by_judul_and_filters_by_arah(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->makeMessage('P-1', 'Gamma', $staff->id_user, $kadiv->id_user);
        $this->makeMessage('P-2', 'Alpha', $staff->id_user, $kadiv->id_user);
        $this->makeMessage('P-3', 'Beta', $kadiv->id_user, $staff->id_user);

        $asc = $this->actingAs($kadiv)->getJson('/api/kadiv/pesan?sort=judul&dir=asc&per_page=100')->assertOk();
        $this->assertSame(['P-2', 'P-3', 'P-1'], collect($asc->json('data'))->pluck('id_pesan')->all());

        $masuk = $this->actingAs($kadiv)->getJson('/api/kadiv/pesan?arah=masuk&per_page=100')->assertOk();
        $this->assertSame(
            ['P-1', 'P-2'],
            collect($masuk->json('data'))->pluck('id_pesan')->sort()->values()->all()
        );
    }

    public function test_pesan_list_sorts_by_pengirim(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $abby = $this->createAccount('ST-A', 'Abby', 'DIV-IT', 'ROLE-STAFF', 'abby');
        $zed = $this->createAccount('ST-Z', 'Zed', 'DIV-IT', 'ROLE-STAFF', 'zed');

        $this->makeMessage('P-A', 'From Abby', $abby->id_user, $kadiv->id_user);
        $this->makeMessage('P-Z', 'From Zed', $zed->id_user, $kadiv->id_user);
        // Default order is created_at desc → Zed first; sorting by sender must flip it.
        Pesan::where('id_pesan', 'P-A')->update(['created_at' => '2026-09-01 08:00:00']);
        Pesan::where('id_pesan', 'P-Z')->update(['created_at' => '2026-09-02 08:00:00']);

        $asc = $this->actingAs($kadiv)->getJson('/api/kadiv/pesan?sort=pengirim&dir=asc&per_page=100')->assertOk();
        $this->assertSame(['P-A', 'P-Z'], collect($asc->json('data'))->pluck('id_pesan')->all());
    }

    public function test_pesan_list_searches_judul_and_deskripsi_case_insensitively(): void
    {
        DB::statement('PRAGMA case_sensitive_like = ON');

        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->makeMessage('P-1', 'Laporan Bulanan', $staff->id_user, $kadiv->id_user);
        $this->makeMessage('P-2', 'Rapat', $staff->id_user, $kadiv->id_user);

        $response = $this->actingAs($kadiv)->getJson('/api/kadiv/pesan?q=laporan&per_page=100')->assertOk();
        $this->assertSame(['P-1'], collect($response->json('data'))->pluck('id_pesan')->all());
    }

    public function test_kadiv_pesan_page_renders_sort_filter_controls(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');

        $this->actingAs($kadiv)->get('/kadiv/pesan')
            ->assertOk()
            ->assertSee('id="kadivPesanSearch"', false)
            ->assertSee('id="kadivPesanTipe"', false)
            ->assertSee('id="kadivPesanArah"', false)
            ->assertSee('setKadivPesanSort', false)
            ->assertSee('data-sort-indicator="judul"', false);
    }

    public function test_staff_list_sorts_by_nama_tugas_and_login(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-A', 'Abby', 'DIV-IT', 'ROLE-STAFF', 'abby', '2026-02-01 08:00:00');
        $this->createAccount('ST-Z', 'Zed', 'DIV-IT', 'ROLE-STAFF', 'zed', '2026-01-01 08:00:00');
        $this->createTask('T-1', 'ST-Z', Tugas::STATUS_BARU);
        $this->createTask('T-2', 'ST-Z', Tugas::STATUS_BARU);
        $this->createTask('T-3', 'ST-A', Tugas::STATUS_BARU);

        $this->actingAs($kadiv)->getJson('/api/kadiv/dashboard?sort=nama&dir=asc')
            ->assertOk()
            ->assertJsonCount(2, 'data.staff')
            ->assertJsonPath('data.staff.0.nama', 'Abby')
            ->assertJsonPath('data.staff.1.nama', 'Zed');

        $this->actingAs($kadiv)->getJson('/api/kadiv/dashboard?sort=tugas&dir=desc')
            ->assertOk()
            ->assertJsonPath('data.staff.0.nama', 'Zed')
            ->assertJsonPath('data.staff.1.nama', 'Abby');

        $this->actingAs($kadiv)->getJson('/api/kadiv/dashboard?sort=login&dir=asc')
            ->assertOk()
            ->assertJsonPath('data.staff.0.nama', 'Zed')
            ->assertJsonPath('data.staff.1.nama', 'Abby');
    }

    public function test_staff_list_searches_nama_case_insensitively(): void
    {
        DB::statement('PRAGMA case_sensitive_like = ON');

        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->createAccount('ST-A', 'Abby', 'DIV-IT', 'ROLE-STAFF', 'abby');
        $this->createAccount('ST-Z', 'Zed', 'DIV-IT', 'ROLE-STAFF', 'zed');

        $this->actingAs($kadiv)->getJson('/api/kadiv/dashboard?q=abby')
            ->assertOk()
            ->assertJsonCount(1, 'data.staff')
            ->assertJsonPath('data.staff.0.nama', 'Abby');
    }

    public function test_kadiv_manajemen_staff_page_renders_sort_filter_controls(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');

        $this->actingAs($kadiv)->get('/kadiv/manajemenStaff')
            ->assertOk()
            ->assertSee('id="kadivStaffSearch"', false)
            ->assertSee('setKadivStaffSort', false)
            ->assertSee('data-sort-indicator="nama"', false);
    }

    private function makeMessage(
        string $id,
        string $judul,
        string $pengirimId,
        string $penerimaId,
        string $tipe = 'pesan'
    ): void {
        Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $judul,
            'deskripsi' => $judul . ' body',
            'tipe' => $tipe,
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $pengirimId,
            'penerima_id_user' => $penerimaId,
        ]);
    }

    private function createAccount(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username,
        ?string $lastLoginAt = null,
    ): User2 {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $name,
            'divisi_id_divisi' => $divisionId,
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
            'last_login_at' => $lastLoginAt,
        ]);
    }

    private function createTask(
        string $id,
        string $employeeId,
        string $status = Tugas::STATUS_BERJALAN,
        ?string $deadline = null
    ): Tugas {
        return Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $employeeId,
            'judul_tugas' => 'Tugas '.$id,
            'deadline' => $deadline ?? now()->addDays(30)->toDateString(),
            'status' => $status,
            'tanggal_dibuat' => '2026-09-29',
        ]);
    }
}
