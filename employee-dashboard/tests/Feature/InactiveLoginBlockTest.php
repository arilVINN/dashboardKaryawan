<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InactiveLoginBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        $this->createAccount('KRY-AKTIF', 'Pegawai Aktif', 'aktifuser', 'Aktif');
        $this->createAccount('KRY-NONAKTIF', 'Pegawai Nonaktif', 'nonaktifuser', 'Tidak Aktif');
    }

    private function createAccount(string $employeeId, string $name, string $username, string $status): void
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'status_aktif' => $status,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_web_login_blocked_for_inactive_staff(): void
    {
        $response = $this->post('/login', [
            'username' => 'nonaktifuser',
            'password' => 'pass123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_api_login_blocked_for_inactive_staff(): void
    {
        $this->postJson('/api/login', [
            'username' => 'nonaktifuser',
            'password' => 'pass123',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Akun Anda dinonaktifkan. Hubungi HRD untuk informasi lebih lanjut.')
            ->assertJsonMissing(['access_token']);
    }

    public function test_wrong_password_on_inactive_account_stays_unauthorized(): void
    {
        $this->postJson('/api/login', [
            'username' => 'nonaktifuser',
            'password' => 'salah',
        ])->assertUnauthorized();
    }

    public function test_active_staff_login_still_works(): void
    {
        $this->post('/login', [
            'username' => 'aktifuser',
            'password' => 'pass123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();

        $this->postJson('/api/login', [
            'username' => 'aktifuser',
            'password' => 'pass123',
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token']);
    }
}
