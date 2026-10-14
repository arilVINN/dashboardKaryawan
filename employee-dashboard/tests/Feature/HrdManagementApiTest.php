<?php

namespace Tests\Feature;

use App\Events\PesanDikirim;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class HrdManagementApiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([PesanDikirim::class]);

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::insert([
            ['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-EMPTY', 'kode_divisi' => 'EMP', 'nama_divisi' => 'Kosong', 'status_aktif' => 'aktif'],
        ]);

        $this->hrd = $this->createAccount('EMP-HRD', 'HRD', 'DIV-IT', 'ROLE-HRD', 'hrd');
    }

    public function test_hrd_can_edit_staff_and_change_only_between_staff_and_kadiv_roles(): void
    {
        $staff = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');

        $this->actingAs($this->hrd)
            ->putJson('/api/hrd/staff/EMP-STAFF', [
                'nama' => 'Staff Baru',
                'role_id_role' => 'ROLE-KADIV',
            ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Staff Baru')
            ->assertJsonPath('data.user.role.nama_role', 'Kadiv');

        $this->assertDatabaseHas('users2', [
            'id_user' => $staff->id_user,
            'role_id_role' => 'ROLE-KADIV',
        ]);

        $this->actingAs($this->hrd)
            ->patchJson('/api/hrd/staff/EMP-STAFF', ['role_id_role' => 'ROLE-HRD'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Role staff hanya boleh kadiv atau staff');
    }

    public function test_head_division_position_assigns_kadiv_role_and_appears_as_division_head(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('value="Kepala Divisi"', false)
            ->assertSee('value="Wakil Kepala Divisi"', false)
            ->assertSee('value="Staff"', false);

        $response = $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Kepala Baru',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Kepala Divisi',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'kepalabaru',
                'password' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.karyawan.jabatan', 'Kepala Divisi')
            ->assertJsonPath('data.user.role', 'ROLE-KADIV');

        $employeeId = $response->json('data.karyawan.id_karyawan');

        $this->assertDatabaseHas('users2', [
            'username' => 'kepalabaru',
            'role_id_role' => 'ROLE-KADIV',
        ]);

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('Kepala Baru');

        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/' . $employeeId, ['jabatan' => 'Wakil Kepala Divisi'])
            ->assertOk()
            ->assertJsonPath('data.jabatan', 'Wakil Kepala Divisi')
            ->assertJsonPath('data.user.role.nama_role', 'Staff');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('Belum ditentukan');
    }

    public function test_hrd_can_delete_staff_and_login_account(): void
    {
        $staff = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');

        $this->actingAs($this->hrd)
            ->deleteJson('/api/hrd/staff/EMP-STAFF')
            ->assertOk()
            ->assertJsonPath('message', 'Staff dan akun login berhasil dihapus');

        $this->assertDatabaseMissing('karyawans', ['id_karyawan' => 'EMP-STAFF']);
        $this->assertDatabaseMissing('users2', ['id_user' => $staff->id_user]);
    }

    public function test_hrd_can_send_message_to_staff_from_message_page(): void
    {
        $staff = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');

        $this->actingAs($this->hrd)
            ->get('/hrd/pesan')
            ->assertOk()
            ->assertSee('Kirim Pesan')
            ->assertSee('formKirimPesanHrd');

        $this->actingAs($this->hrd)
            ->postJson('/api/hrd/pesan', [
                'penerima_id_user' => $staff->id_user,
                'tipe' => 'pesan',
                'judul_pesan' => 'Informasi HRD',
                'deskripsi' => 'Mohon perbarui data karyawan.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Pesan/Surat berhasil dikirim');

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $staff->id_user,
            'judul_pesan' => 'Informasi HRD',
            'tipe' => 'pesan',
        ]);
        Event::assertDispatched(PesanDikirim::class);
    }

    public function test_hrd_can_edit_division_and_delete_only_empty_divisions(): void
    {
        $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');

        $this->actingAs($this->hrd)
            ->patchJson('/api/hrd/divisi/DIV-IT', ['nama_divisi' => 'Teknologi Baru'])
            ->assertOk()
            ->assertJsonPath('data.nama_divisi', 'Teknologi Baru');

        $this->actingAs($this->hrd)
            ->deleteJson('/api/hrd/divisi/DIV-IT')
            ->assertStatus(409);

        $this->actingAs($this->hrd)
            ->deleteJson('/api/hrd/divisi/DIV-EMPTY')
            ->assertOk()
            ->assertJsonPath('message', 'Divisi berhasil dihapus');

        $this->assertDatabaseMissing('divisis', ['id_divisi' => 'DIV-EMPTY']);
    }

    public function test_division_delete_action_is_hrd_only_and_refuses_divisions_with_staff(): void
    {
        $this->get('/hrd/manajemenDivisi')
            ->assertRedirect('/login');

        $this->assertDatabaseHas('divisis', ['id_divisi' => 'DIV-EMPTY']);

        $staffUser = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');

        $this->actingAs($staffUser)
            ->deleteJson('/hrd/divisi/DIV-EMPTY')
            ->assertForbidden();

        $this->assertDatabaseHas('divisis', ['id_divisi' => 'DIV-EMPTY']);

        $this->actingAs($this->hrd)
            ->get('/hrd/manajemenDivisi')
            ->assertOk()
            ->assertSee('hapusDivisi(this)', false)
            ->assertSee('DIV-EMPTY');

        $this->actingAs($this->hrd)
            ->deleteJson('/hrd/divisi/DIV-IT')
            ->assertStatus(409);

        $this->assertDatabaseHas('divisis', ['id_divisi' => 'DIV-IT']);
    }

    public function test_new_division_is_shown_on_hrd_dashboard_and_management_page(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/divisi', [
                'kode_divisi' => 'DIV-MKT',
                'nama_divisi' => 'Pemasaran',
                'status_aktif' => 'Aktif',
            ])
            ->assertCreated();

        $this->actingAs($this->hrd)
            ->get('/hrd/dashboard')
            ->assertOk()
            ->assertSee('Pemasaran');

        $this->actingAs($this->hrd)
            ->get('/hrd/manajemenDivisi')
            ->assertOk()
            ->assertSee('Pemasaran');
    }

    public function test_hrd_dashboard_widgets_use_real_database_metrics(): void
    {
        $staff = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-IT', 'ROLE-STAFF', 'staff');
        $otherStaff = $this->createAccount('EMP-OTHER', 'Staff Lain', 'DIV-IT', 'ROLE-STAFF', 'other');
        Divisi::create([
            'id_divisi' => 'DIV-NEW',
            'kode_divisi' => 'NEW',
            'nama_divisi' => 'Divisi Baru',
            'status_aktif' => 'Aktif',
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-DASHBOARD',
            'judul_pesan' => 'Informasi dashboard',
            'deskripsi' => 'Pesan untuk perhitungan dashboard.',
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $staff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-DASHBOARD-2',
            'judul_pesan' => 'Balasan dashboard',
            'deskripsi' => 'Pesan kedua yang melibatkan HRD.',
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $staff->id_user,
            'penerima_id_user' => $this->hrd->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-DASHBOARD-3',
            'judul_pesan' => 'Pesan antar-staff',
            'deskripsi' => 'Pesan yang tidak melibatkan HRD.',
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $staff->id_user,
            'penerima_id_user' => $otherStaff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-DASHBOARD-4',
            'judul_pesan' => 'Pesan antar-staff lainnya',
            'deskripsi' => 'Pesan lain yang tidak melibatkan HRD.',
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $otherStaff->id_user,
            'penerima_id_user' => $staff->id_user,
        ]);
        $expected = [
            'karyawan' => Karyawan::count(),
            'divisi' => Divisi::count(),
            'pesan' => Pesan::count(),
        ];

        $this->actingAs($this->hrd)
            ->get('/hrd/dashboard')
            ->assertOk()
            ->assertViewIs('hrd.dashboard')
            ->assertViewHas('total', fn ($total) => $total == $expected)
            ->assertViewHas('tugasBuckets', fn ($buckets) => isset(
                $buckets['ongoing'],
                $buckets['pending'],
                $buckets['revisi']
            ))
            ->assertSee('data-dashboard-metric="total-staff"', false)
            ->assertSee('data-dashboard-metric="total-divisi"', false)
            ->assertSee('data-dashboard-metric="total-pesan"', false);
    }

    public function test_division_detail_shows_its_staff_and_can_add_a_staff_member(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/dashboard')
            ->assertOk()
            ->assertSee('/hrd/detailDivisi/DIV-IT');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('Belum ada staff di divisi ini.')
            ->assertDontSee('Budi Santoso');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Staff Baru',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '2000-01-02',
                'email' => 'staff.baru@example.test',
                'no_telepon' => '081234567890',
                'jabatan' => 'Analis',
                'username' => 'staffbaru',
                'password' => 'password',
                'divisi_id_divisi' => 'DIV-EMPTY',
            ])
            ->assertCreated();

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('Staff Baru')
            ->assertSee('staff.baru@example.test')
            ->assertDontSee('Budi Santoso');
    }

    public function test_staff_added_to_hr_division_appears_on_hr_kadiv_dashboard(): void
    {
        Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'Aktif',
        ]);
        $kadiv = $this->createAccount('EMP-KADIV-HR', 'Kadiv HR', 'DIV-HR', 'ROLE-KADIV', 'kadivhr');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Staff HR Baru',
                'jenis_kelamin' => 'Perempuan',
                'jabatan' => 'Staff HR',
                'username' => 'staffhrbaru',
                'password' => 'password',
                'divisi_id_divisi' => 'DIV-HR',
            ])
            ->assertCreated()
            ->assertJsonPath('data.karyawan.divisi_id_divisi', 'DIV-HR')
            ->assertJsonPath('data.user.role', 'ROLE-STAFF');

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/dashboard')
            ->assertOk()
            ->assertJsonPath('data.divisi.id_divisi', 'DIV-HR')
            ->assertJsonFragment([
                'nama' => 'Staff HR Baru',
                'jumlah_tugas_dikerjakan' => 0,
            ]);
    }

    public function test_staff_can_be_edited_and_deleted_from_web_division_detail(): void
    {
        $staff = $this->createAccount('EMP-STAFF', 'Staff Lama', 'DIV-EMPTY', 'ROLE-STAFF', 'stafflama');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('Edit')
            ->assertSee('Hapus')
            ->assertSee('Staff Lama');

        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/EMP-STAFF', [
                'nama' => 'Staff Diperbarui',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => null,
                'email' => 'staff.updated@example.test',
                'no_telepon' => null,
                'jabatan' => 'Analis',
                'username' => 'staffbaru',
            ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Staff Diperbarui');

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => $staff->karyawan_id_karyawan,
            'nama' => 'Staff Diperbarui',
            'email' => 'staff.updated@example.test',
        ]);

        $this->actingAs($this->hrd)
            ->deleteJson('/hrd/staff/EMP-STAFF')
            ->assertOk()
            ->assertJsonPath('message', 'Staff dan akun login berhasil dihapus');

        $this->assertDatabaseMissing('karyawans', ['id_karyawan' => 'EMP-STAFF']);
        $this->assertDatabaseMissing('users2', ['id_user' => $staff->id_user]);
    }

    public function test_hrd_can_monitor_all_messages_and_reply_to_participating_threads(): void
    {
        $sender = $this->createAccount('EMP-KADIV', 'Ketua Divisi', 'DIV-IT', 'ROLE-KADIV', 'kadiv');
        $otherUser = $this->createAccount('EMP-OTHER', 'Staff Lain', 'DIV-IT', 'ROLE-STAFF', 'other');

        Pesan::create([
            'id_pesan' => 'PSN-HRD-001',
            'judul_pesan' => 'Permintaan data karyawan',
            'deskripsi' => 'Mohon data terbaru.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-06',
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->hrd->id_user,
        ]);

        Pesan::create([
            'id_pesan' => 'PSN-PRIVATE',
            'judul_pesan' => 'Pesan pribadi',
            'deskripsi' => 'Pesan antar pengguna untuk dipantau HRD.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-06',
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $otherUser->id_user,
        ]);

        $this->actingAs($this->hrd)
            ->get('/hrd/pesan')
            ->assertOk()
            ->assertSee('Permintaan data karyawan')
            ->assertSee('Pesan pribadi')
            ->assertSee('Ketua Divisi');

        $this->actingAs($this->hrd)
            ->get('/hrd/daftarPesan')
            ->assertOk()
            ->assertViewIs('hrd.daftarPesan')
            ->assertSee('Daftar Pesan')
            ->assertDontSee('Pusat Pesan &amp; Komunikasi')
            ->assertSee('PSN-PRIVATE');

        $this->actingAs($this->hrd)
            ->getJson('/api/hrd/pesan')
            ->assertOk()
            ->assertJsonPath('data.metrics.total', 2)
            ->assertJsonCount(2, 'data.list_pesan');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailPesan/PSN-HRD-001')
            ->assertOk()
            ->assertSee('Mohon data terbaru.')
            ->assertSee('Balas Pesan');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailPesan/PSN-PRIVATE')
            ->assertOk()
            ->assertSee('Pesan antar pengguna untuk dipantau HRD.')
            ->assertDontSee('Balas Pesan')
            ->assertSee('tidak dapat dibalas');

        $this->actingAs($otherUser)
            ->get('/hrd/detailPesan/PSN-HRD-001')
            ->assertForbidden();

        $this->actingAs($this->hrd)
            ->postJson('/hrd/detailPesan/PSN-HRD-001/balas', [
                'deskripsi' => 'Akan kami siapkan.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.deskripsi', 'Akan kami siapkan.');

        $this->assertDatabaseHas('pesans', [
            'balasan_dari_id_pesan' => 'PSN-HRD-001',
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $sender->id_user,
        ]);
        Event::assertDispatched(PesanDikirim::class);
    }

    private function createAccount(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username,
    ): User2 {
        DB::table('karyawans')->insert([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $username.'@example.test',
            'jabatan' => $name,
            'divisi_id_divisi' => $divisionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }
}
