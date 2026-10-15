<?php

namespace Tests\Feature\Http\Controllers\Hrd;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdStaffControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hrd_can_add_employee_with_default_active_status(): void
    {
        $hrdRole = Role::create([
            'id_role' => 'ROLE-HRD',
            'nama_role' => 'hrd',
        ]);
        $staffRole = Role::create([
            'id_role' => 'ROLE-STAFF',
            'nama_role' => 'staff',
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        Karyawan::create([
            'id_karyawan' => 'KR-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $hrd = User2::create([
            'id_user' => 'US-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('password'),
            'role_id_role' => $hrdRole->id_role,
            'karyawan_id_karyawan' => 'KR-HRD',
        ]);

        $response = $this->actingAs($hrd)->postJson('/hrd/staff', [
            'nama' => 'Karyawan Baru',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'karyawanbaru',
            'password' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.karyawan.status_aktif', 'Aktif')
            ->assertJsonPath('data.karyawan.email', null);

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Karyawan Baru',
            'status_aktif' => 'Aktif',
            'email' => null,
        ]);
        $this->assertDatabaseHas('users2', [
            'username' => 'karyawanbaru',
            'role_id_role' => $staffRole->id_role,
        ]);
    }
}
