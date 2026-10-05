<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrdManagementApiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

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
