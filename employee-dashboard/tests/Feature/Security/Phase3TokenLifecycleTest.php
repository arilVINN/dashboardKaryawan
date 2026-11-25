<?php

namespace Tests\Feature\Security;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use App\Services\EmployeeWriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the Phase 3 hardening:
 *  - password resets and deactivation revoke all bearer tokens,
 *  - API requests from inactive / forced-change accounts are rejected,
 *  - changing your own password also invalidates old tokens.
 */
class Phase3TokenLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
        ]);

        Divisi::insert([
            ['id_divisi' => 'DIV-A', 'kode_divisi' => 'A', 'nama_divisi' => 'Divisi A', 'status_aktif' => 'aktif'],
        ]);
    }

    private function createUser(
        string $id,
        string $username,
        string $roleId,
        string $status = 'Aktif'
    ): User2 {
        Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $username,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2024-01-01',
            'no_telepon' => '081234567890',
            'email' => $username.'@example.test',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-A',
            'status_aktif' => $status,
            'aktif' => $status === 'Aktif',
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'K-'.$id,
        ]);
    }

    private function forgetAuth(): void
    {
        // The test client keeps guard instances between requests; drop them so
        // each request re-authenticates from the token header.
        $this->app['auth']->forgetGuards();
    }

    public function test_admin_password_reset_revokes_existing_api_tokens(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $hrd = $this->createUser('U-HRD', 'hrd', 'ROLE-HRD');
        $token = $staff->createToken('auth_token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertOk();

        app(EmployeeWriteService::class)->resetPassword($staff->karyawan, $hrd);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->forgetAuth();

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertUnauthorized();
    }

    public function test_deactivation_revokes_existing_api_tokens(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $hrd = $this->createUser('U-HRD', 'hrd', 'ROLE-HRD');
        $token = $staff->createToken('auth_token')->plainTextToken;

        $this->actingAs($hrd)
            ->patch('/hrd/detailKaryawan/'.$staff->karyawan_id_karyawan.'/keluar', [
                'tanggal_keluar' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->forgetAuth();

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertUnauthorized();
    }

    public function test_changing_own_password_via_api_revokes_existing_tokens(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $token = $staff->createToken('auth_token')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/staff/profile/password', [
                'current_password' => 'pass123',
                'new_password' => 'new-password-123',
            ])
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->forgetAuth();

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertUnauthorized();
    }

    public function test_api_requests_are_blocked_for_inactive_accounts(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $token = $staff->createToken('auth_token')->plainTextToken;

        $staff->karyawan->update([
            'status_aktif' => 'Tidak Aktif',
            'aktif' => false,
        ]);

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akun Anda dinonaktifkan. Hubungi HRD untuk informasi lebih lanjut.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_requests_are_blocked_while_password_change_is_forced(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $staff->update(['must_change_password' => true]);
        $token = $staff->createToken('auth_token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertForbidden()
            ->assertJsonPath('message', 'Password sementara masih berlaku. Ganti password melalui halaman web terlebih dahulu.');

        $staff->update(['must_change_password' => false]);

        $this->forgetAuth();

        $this->withToken($token)
            ->getJson('/api/staff/tugas')
            ->assertOk();
    }

    public function test_web_password_change_rotates_session_version_and_revokes_tokens(): void
    {
        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF');
        $oldVersion = $staff->session_version;
        $token = $staff->createToken('auth_token')->plainTextToken;

        app(EmployeeWriteService::class)->changeOwnPassword($staff, 'new-password-123');

        $staff->refresh();

        $this->assertNotSame($oldVersion, $staff->session_version);
        $this->assertFalse((bool) $staff->must_change_password);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
