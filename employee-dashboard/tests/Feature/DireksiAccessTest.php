<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DireksiAccessTest extends TestCase
{
    use RefreshDatabase;

    private User2 $direksi;

    private User2 $pemimpin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-DIREKSI', 'nama_role' => 'direksi'],
            ['id_role' => 'ROLE-PEMIMPIN', 'nama_role' => 'pemimpin'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        $this->direksi = $this->createAccount('KRY-DIR', 'direksi', 'ROLE-DIREKSI', 'Bapak Direktur');
        $this->pemimpin = $this->createAccount('KRY-PEM', 'pemimpin', 'ROLE-PEMIMPIN', 'Pemimpin Besar');

        $this->createAccount('KRY-STF', 'staffsatu', 'ROLE-STAFF', 'Staff Biasa');
    }

    private function createAccount(string $employeeId, string $username, string $roleId, string $nama): User2
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1980-01-01',
            'tanggal_rekrut' => '2020-01-01',
            'no_telepon' => '081234567890',
            'jabatan' => 'Direktur',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_direksi_can_open_hrd_and_kadiv_web_pages(): void
    {
        $pages = [
            '/hrd/dashboard',
            '/hrd/daftarKaryawan',
            '/hrd/manajemenDivisi',
            '/hrd/daftarPesan',
            '/kadiv/dashboard',
            '/kadiv/tugas',
            '/kadiv/manajemenStaff',
            '/kadiv/pesan',
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->direksi)
                ->get($page)
                ->assertOk();
        }
    }

    public function test_direksi_can_reach_hrd_and_kadiv_api_endpoints(): void
    {
        $this->actingAs($this->direksi)->getJson('/api/hrd/dashboard')->assertOk();
        $this->actingAs($this->direksi)->getJson('/api/hrd/divisi')->assertOk();
        $this->actingAs($this->direksi)->getJson('/api/hrd/staff')->assertOk();

        $this->actingAs($this->direksi)->getJson('/api/kadiv/dashboard')->assertOk();
        $this->actingAs($this->direksi)->getJson('/api/kadiv/tugas')->assertOk();
        $this->actingAs($this->direksi)->getJson('/api/kadiv/staff')->assertOk();
    }

    public function test_pemimpin_alias_also_inherits_hrd_and_kadiv_access(): void
    {
        $this->actingAs($this->pemimpin)->get('/hrd/dashboard')->assertOk();
        $this->actingAs($this->pemimpin)->get('/kadiv/dashboard')->assertOk();
        $this->actingAs($this->pemimpin)->getJson('/api/kadiv/dashboard')->assertOk();
    }

    public function test_direksi_login_is_redirected_to_the_direksi_dashboard(): void
    {
        $this->post('/login', ['username' => 'direksi', 'password' => 'pass123'])
            ->assertRedirect('/direksi/dashboard');
    }

    public function test_direksi_is_still_blocked_from_staff_only_routes(): void
    {
        $this->actingAs($this->direksi)->get('/tugas')->assertForbidden();
        $this->actingAs($this->direksi)->getJson('/api/staff/tugas')->assertForbidden();
    }

    public function test_direksi_can_open_the_company_wide_task_monitor(): void
    {
        // The dedicated Direksi sidebar (with the Monitor link) is used even on
        // the reused HRD dashboard view.
        $this->actingAs($this->direksi)
            ->get('/direksi/dashboard')
            ->assertOk()
            ->assertSee('Monitor Tugas');

        $html = $this->actingAs($this->direksi)
            ->get('/direksi/tugas')
            ->assertOk()
            ->getContent();

        // Two-level heading like the other list pages: page-level h1 with an
        // icon badge names the page, the row-level h2 names the table.
        $this->assertStringContainsString('<span>Monitor Tugas</span>', $html);
        $this->assertStringContainsString('<span class="p-2 rounded-lg bg-blue-100 text-blue-600">', $html);
        $this->assertStringContainsString('<h2 class="text-xl font-bold text-slate-800">Daftar Tugas</h2>', $html);
        $this->assertStringContainsString('<body class="bg-gray-100', $html);
        $this->assertStringNotContainsString('Read-only', $html);
        $this->assertStringNotContainsString('Tambah Tugas', $html);
    }

    public function test_pemimpin_alias_can_open_the_task_monitor(): void
    {
        $this->actingAs($this->pemimpin)->get('/direksi/dashboard')->assertOk();
        $this->actingAs($this->pemimpin)->get('/direksi/tugas')->assertOk();
    }

    public function test_task_monitor_is_restricted_to_direksi(): void
    {
        $kadiv = $this->createAccount('KRY-KDV', 'kadivsatu', 'ROLE-KADIV', 'Kepala Divisi');

        $this->actingAs($kadiv)->get('/direksi/tugas')->assertForbidden();
        $this->actingAs($this->direksi)->get('/direksi/tugas')->assertOk();
    }

    public function test_direksi_can_read_task_detail_but_cannot_mutate_tasks(): void
    {
        Event::fake();

        Tugas::create([
            'id_tugas' => 'TGS-1',
            'karyawan_id_karyawan' => 'KRY-STF',
            'judul_tugas' => 'Riset Pasar',
            'deskripsi' => 'Deskripsi tugas',
            'deadline' => now()->addDay(),
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
        ]);

        $this->actingAs($this->direksi)
            ->get('/direksi/tugas/TGS-1')
            ->assertOk()
            ->assertSee('Riset Pasar');

        // Read endpoints stay available for monitoring.
        $this->actingAs($this->direksi)->getJson('/api/kadiv/tugas')->assertOk();
        $this->actingAs($this->direksi)->getJson('/api/kadiv/tugas/TGS-1')->assertOk();

        // Every mutation is rejected for the read-only super-admin role.
        $this->actingAs($this->direksi)->postJson('/api/kadiv/tugas', [
            'judul_tugas' => 'Tugas Baru',
            'deskripsi' => 'x',
            'deadline' => now()->addDay()->toDateTimeString(),
            'karyawan_id_karyawan' => 'KRY-STF',
        ])->assertForbidden();

        $this->actingAs($this->direksi)
            ->putJson('/api/kadiv/tugas/TGS-1', ['judul_tugas' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->deleteJson('/api/kadiv/tugas/TGS-1')
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->postJson('/api/kadiv/tugas/TGS-1/review', ['status_review' => 'revisi'])
            ->assertForbidden();
    }

    public function test_direksi_cannot_mutate_hrd_staff_or_divisions(): void
    {
        // API endpoints
        $this->actingAs($this->direksi)->postJson('/api/hrd/staff', [
            'nama' => 'Orang Baru',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'orangbaru',
            'password' => 'pass123',
        ])->assertForbidden();

        $this->actingAs($this->direksi)
            ->putJson('/api/hrd/staff/KRY-STF', ['nama' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->deleteJson('/api/hrd/staff/KRY-STF')
            ->assertForbidden();

        $this->actingAs($this->direksi)->postJson('/api/hrd/divisi', [
            'kode_divisi' => 'XX',
            'nama_divisi' => 'Divisi X',
            'status_aktif' => 'Aktif',
        ])->assertForbidden();

        $this->actingAs($this->direksi)
            ->putJson('/api/hrd/divisi/DIV-IT', ['nama_divisi' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->deleteJson('/api/hrd/divisi/DIV-IT')
            ->assertForbidden();

        // Web routes used by the fetch-based HRD forms
        $this->actingAs($this->direksi)->post('/hrd/staff', [
            'nama' => 'Orang Baru',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ])->assertForbidden();

        $this->actingAs($this->direksi)
            ->put('/hrd/staff/KRY-STF', ['nama' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->delete('/hrd/staff/KRY-STF')
            ->assertForbidden();

        $this->actingAs($this->direksi)
            ->delete('/hrd/karyawan/KRY-STF')
            ->assertForbidden();

        $this->actingAs($this->direksi)->post('/hrd/divisi', [
            'kode_divisi' => 'XX',
            'nama_divisi' => 'Divisi X',
            'status_aktif' => 'Aktif',
        ])->assertForbidden();

        $this->actingAs($this->direksi)
            ->delete('/hrd/divisi/DIV-IT')
            ->assertForbidden();
    }

    public function test_direksi_ui_hides_hrd_crud_actions(): void
    {
        $daftarKaryawan = $this->actingAs($this->direksi)->get('/hrd/daftarKaryawan')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-staff-create', $daftarKaryawan);
        $this->assertStringNotContainsString('data-staff-edit', $daftarKaryawan);
        $this->assertStringNotContainsString('data-staff-delete', $daftarKaryawan);
        $this->assertStringContainsString('/hrd/detailKaryawan/', $daftarKaryawan); // detail stays

        $manajemenDivisi = $this->actingAs($this->direksi)->get('/hrd/manajemenDivisi')->assertOk()->getContent();
        $this->assertStringNotContainsString('onclick="bukaModalDivisi()"', $manajemenDivisi);
        $this->assertStringNotContainsString('hapusDivisi(this)', $manajemenDivisi);

        $dashboard = $this->actingAs($this->direksi)->get('/direksi/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('onclick="bukaModalDivisi()"', $dashboard);
        $this->assertStringNotContainsString('hapusDivisi(this)', $dashboard);
    }

    public function test_direksi_can_still_send_messages(): void
    {
        Event::fake();

        // API messaging (kadiv) – Direksi may message a staff in the same division.
        $this->actingAs($this->direksi)->postJson('/api/kadiv/pesan', [
            'penerima_id_user' => 'USR-KRY-STF',
            'tipe' => 'pesan',
            'judul_pesan' => 'Halo',
            'deskripsi' => 'Pesan dari Direksi.',
        ])->assertCreated();

        // Web messaging (hrd) – session route used by the HRD compose modal.
        $this->actingAs($this->direksi)->post('/hrd/pesan', [
            'penerima_id_user' => 'USR-KRY-STF',
            'tipe' => 'pesan',
            'judul_pesan' => 'Halo',
            'deskripsi' => 'Pesan dari Direksi.',
        ])->assertCreated();
    }
}
