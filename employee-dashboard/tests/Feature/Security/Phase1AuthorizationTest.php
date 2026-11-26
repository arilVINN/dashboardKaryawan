<?php

namespace Tests\Feature\Security;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the Phase 1 hardening:
 *  - Direktur/Direksi are read-only (API and web).
 *  - Kadiv staff/task endpoints are division-scoped and hide bank/PII fields.
 *  - Login throttling cannot be reset by rotating X-Forwarded-For.
 */
class Phase1AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direktur'],
            ['id_role' => 'ROLE-DIRKSI', 'nama_role' => 'direksi'],
            ['id_role' => 'ROLE-SUPER', 'nama_role' => 'superadmin'],
        ]);

        Divisi::insert([
            ['id_divisi' => 'DIV-A', 'kode_divisi' => 'A', 'nama_divisi' => 'Divisi A', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-B', 'kode_divisi' => 'B', 'nama_divisi' => 'Divisi B', 'status_aktif' => 'aktif'],
        ]);
    }

    private function createUser(string $id, string $username, string $roleId, string $divisiId): User2
    {
        Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $username,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2024-01-01',
            'no_rekening' => 'BCA-'.$id,
            'no_telepon' => '081234567890',
            'email' => $username.'@example.test',
            'alamat' => 'Jl. Rahasia No. 1',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'K-'.$id,
        ]);
    }

    public function test_direktur_cannot_write_hrd_master_data_via_api(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');

        $this->actingAs($direktur)
            ->postJson('/api/hrd/staff', [])
            ->assertForbidden();

        $this->actingAs($direktur)
            ->deleteJson('/api/hrd/divisi/DIV-A')
            ->assertForbidden();
    }

    public function test_direksi_cannot_mutate_kadiv_tasks_via_api(): void
    {
        $direksi = $this->createUser('U-DIRKSI', 'direksi', 'ROLE-DIRKSI', 'DIV-A');

        $this->actingAs($direksi)
            ->postJson('/api/kadiv/tugas', [])
            ->assertForbidden();

        $this->actingAs($direksi)
            ->deleteJson('/api/kadiv/tugas/TG001')
            ->assertForbidden();
    }

    public function test_director_can_still_read_hrd_data_via_api(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');

        $this->actingAs($direktur)
            ->getJson('/api/hrd/staff')
            ->assertOk();
    }

    public function test_superadmin_keeps_break_glass_write_access(): void
    {
        $superadmin = $this->createUser('U-SUPER', 'rootadmin', 'ROLE-SUPER', 'DIV-A');

        $this->actingAs($superadmin)
            ->postJson('/api/hrd/divisi', [
                'kode_divisi' => 'NEW',
                'nama_divisi' => 'Divisi Baru',
            ])
            ->assertCreated();
    }

    public function test_kadiv_staff_list_ignores_client_division_and_hides_bank_data(): void
    {
        $kadiv = $this->createUser('U-KADIV', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $staffA = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $this->createUser('U-STB', 'staff.b', 'ROLE-STAFF', 'DIV-B');

        // Empty `divisi_id` used to bypass the filter and list everyone.
        $response = $this->actingAs($kadiv)
            ->getJson('/api/kadiv/staff?divisi_id=')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_karyawan', $staffA->karyawan_id_karyawan)
            ->assertJsonMissingPath('data.0.no_rekening')
            ->assertJsonMissingPath('data.0.alamat')
            ->assertJsonMissingPath('data.0.tanggal_lahir');

        // Asking for another division must not widen the scope either.
        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/staff?divisi_id=DIV-B')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_karyawan', $staffA->karyawan_id_karyawan);

        $this->assertNotNull($response);
    }

    public function test_kadiv_staff_detail_hides_bank_data(): void
    {
        $kadiv = $this->createUser('U-KADIV', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/staff/'.$staff->karyawan_id_karyawan)
            ->assertOk()
            ->assertJsonPath('data.id_karyawan', $staff->karyawan_id_karyawan)
            ->assertJsonMissingPath('data.no_rekening')
            ->assertJsonMissingPath('data.alamat');
    }

    public function test_kadiv_task_detail_hides_employee_bank_data(): void
    {
        $kadiv = $this->createUser('U-KADIV', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');

        Tugas::create([
            'id_tugas' => 'TG001',
            'karyawan_id_karyawan' => $staff->karyawan_id_karyawan,
            'judul_tugas' => 'Bikin laporan',
            'deskripsi' => 'Laporan bulanan.',
            'deadline' => now()->addDays(3)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/tugas/TG001')
            ->assertOk()
            ->assertJsonPath('data.karyawan.id_karyawan', $staff->karyawan_id_karyawan)
            ->assertJsonMissingPath('data.karyawan.no_rekening')
            ->assertJsonMissingPath('data.karyawan.alamat');
    }

    public function test_kadiv_task_list_hides_employee_bank_data(): void
    {
        $kadiv = $this->createUser('U-KADIV', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');

        Tugas::create([
            'id_tugas' => 'TG001',
            'karyawan_id_karyawan' => $staff->karyawan_id_karyawan,
            'judul_tugas' => 'Bikin laporan',
            'deskripsi' => 'Laporan bulanan.',
            'deadline' => now()->addDays(3)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        $this->actingAs($kadiv)
            ->getJson('/api/kadiv/tugas')
            ->assertOk()
            ->assertJsonPath('data.0.karyawan.id_karyawan', $staff->karyawan_id_karyawan)
            ->assertJsonPath('data.0.karyawan.divisi.nama_divisi', 'Divisi A')
            ->assertJsonMissingPath('data.0.karyawan.no_rekening')
            ->assertJsonMissingPath('data.0.karyawan.alamat');
    }

    public function test_direktur_cannot_deactivate_employee_via_web(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');
        $target = $this->createUser('U-ST', 'target', 'ROLE-STAFF', 'DIV-A');

        $this->actingAs($direktur)
            ->patch('/hrd/detailKaryawan/'.$target->karyawan_id_karyawan.'/keluar', [
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => $target->karyawan_id_karyawan,
            'status_aktif' => 'Aktif',
        ]);
    }

    public function test_hrd_can_still_deactivate_employee_via_web(): void
    {
        $hrd = $this->createUser('U-HRD', 'hrduser', 'ROLE-HRD', 'DIV-A');
        $target = $this->createUser('U-ST', 'target', 'ROLE-STAFF', 'DIV-A');

        $this->actingAs($hrd)
            ->patch('/hrd/detailKaryawan/'.$target->karyawan_id_karyawan.'/keluar', [
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => $target->karyawan_id_karyawan,
            'status_aktif' => 'Tidak Aktif',
        ]);
    }

    public function test_direktur_cannot_decide_finance_or_manage_master_data(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');

        $this->actingAs($direktur)
            ->post('/kadiv/keuangan/permintaan/ANY/approve')
            ->assertForbidden();

        $this->actingAs($direktur)
            ->post('/kadiv/keuangan/laporan/ANY/verifikasi')
            ->assertForbidden();

        $this->actingAs($direktur)
            ->post('/hrd/jabatan', [])
            ->assertForbidden();

        $this->actingAs($direktur)
            ->post('/struktur-organisasi', [])
            ->assertForbidden();
    }

    public function test_finance_policies_block_read_only_director_but_allow_superadmin(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');
        $superadmin = $this->createUser('U-SUPER', 'rootadmin', 'ROLE-SUPER', 'DIV-A');

        $permintaan = new KPermintDana;
        $permintaan->user_id_user = 'SOMEONE-ELSE';

        $this->assertFalse(Gate::forUser($direktur)->allows('approve', $permintaan));
        $this->assertTrue(Gate::forUser($superadmin)->allows('approve', $permintaan));
    }

    public function test_employee_write_policy_blocks_read_only_director(): void
    {
        $direktur = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');
        $superadmin = $this->createUser('U-SUPER', 'rootadmin', 'ROLE-SUPER', 'DIV-A');

        $this->assertFalse(Gate::forUser($direktur)->allows('create', Karyawan::class));
        $this->assertTrue(Gate::forUser($superadmin)->allows('create', Karyawan::class));
    }

    public function test_login_throttle_cannot_be_reset_with_rotating_forwarded_for(): void
    {
        $this->createUser('U-ST', 'throttleuser', 'ROLE-STAFF', 'DIV-A');

        $last = null;

        for ($i = 1; $i <= 21; $i++) {
            $last = $this->postJson('/api/login', [
                'username' => 'throttleuser',
                'password' => 'wrong-password',
            ], [
                'X-Forwarded-For' => '203.0.113.'.$i,
            ]);
        }

        $last->assertStatus(429);
    }
}
