<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdJabatanRoleTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private Jabatan $jabatanStaff;
    private Jabatan $jabatanKadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);

        $this->jabatanStaff = Jabatan::create([
            'division_id' => 'DIV-IT',
            'nama' => 'Staff Editorial',
            'role_id_role' => 'ROLE-STAFF',
        ]);
        $this->jabatanKadiv = Jabatan::create([
            'division_id' => 'DIV-IT',
            'nama' => 'Kepala Divisi IT',
            'role_id_role' => 'ROLE-KADIV',
        ]);

        $karyawanHrd = Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_masuk' => '2020-01-01',
            'jabatan' => 'HRD Manager',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => $karyawanHrd->id_karyawan,
        ]);
    }

    public function test_new_employee_role_follows_the_jabatan_mapping(): void
    {
        $response = $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan_id' => $this->jabatanKadiv->id,
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'budi.santoso',
            'password' => 'rahasia123',
        ])->assertCreated();

        $this->assertDatabaseHas('users2', [
            'id_user' => $response->json('data.user.id_user'),
            'role_id_role' => 'ROLE-KADIV',
        ]);
    }

    public function test_changing_jabatan_updates_role_and_ignores_explicit_role_on_ui_payload(): void
    {
        $id = $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan_id' => $this->jabatanStaff->id,
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'budi.santoso',
            'password' => 'rahasia123',
        ])->assertCreated()->json('data.karyawan.id_karyawan');

        $this->actingAs($this->hrd)->putJson('/hrd/staff/'.$id, [
            'jabatan_id' => $this->jabatanKadiv->id,
        ])->assertOk();

        $this->assertDatabaseHas('users2', [
            'karyawan_id_karyawan' => $id,
            'role_id_role' => 'ROLE-KADIV',
        ]);
    }

    public function test_renaming_jabatan_does_not_change_the_employees_role(): void
    {
        $id = $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan_id' => $this->jabatanStaff->id,
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'budi.santoso',
            'password' => 'rahasia123',
        ])->assertCreated()->json('data.karyawan.id_karyawan');

        $this->actingAs($this->hrd)
            ->putJson('/hrd/jabatan/'.$this->jabatanStaff->id, [
                'nama' => 'Editor Utama',
                'role_id_role' => 'ROLE-STAFF',
            ])->assertOk();

        // The employee keeps a snapshot of the hire-time title; the link is
        // jabatan_id. What must NOT change is the granted role.
        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => $id,
            'jabatan_id' => $this->jabatanStaff->id,
        ]);
        $this->assertDatabaseHas('users2', [
            'karyawan_id_karyawan' => $id,
            'role_id_role' => 'ROLE-STAFF',
        ]);
    }

    public function test_jabatan_api_rejects_superadmin_role_and_requires_role(): void
    {
        $this->actingAs($this->hrd)->postJson('/hrd/jabatan', [
            'division_id' => 'DIV-IT',
            'nama' => 'Staff Baru',
        ])->assertUnprocessable()->assertJsonValidationErrors('role_id_role');

        Role::insert([['id_role' => 'ROLE-SUPER', 'nama_role' => 'superadmin']]);
        $this->actingAs($this->hrd)->postJson('/hrd/jabatan', [
            'division_id' => 'DIV-IT',
            'nama' => 'Staff Baru',
            'role_id_role' => 'ROLE-SUPER',
        ])->assertUnprocessable()->assertJsonValidationErrors('role_id_role');
    }


}
