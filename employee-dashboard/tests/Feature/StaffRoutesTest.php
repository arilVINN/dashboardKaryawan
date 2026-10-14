<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** The staff web pages guarded by auth + role:staff. */
    private const STAFF_PAGES = [
        '/',
        '/tugas',
        '/pesan',
        '/tugas/detail/T-1',
        '/pesan/detail/P-1',
        '/profile',
    ];

    private User2 $staff;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-S', 'nama' => 'Staff Satu', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_rekrut' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'KRY-H', 'nama' => 'HRD Satu', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1990-01-01', 'tanggal_rekrut' => '2023-01-01', 'no_telepon' => '0813', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);

        $this->staff = User2::create([
            'id_user' => 'USR-S',
            'username' => 'staffsatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S',
        ]);

        $this->hrd = User2::create([
            'id_user' => 'USR-H',
            'username' => 'hrdsatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-H',
        ]);
    }

    public function test_staff_pages_require_auth(): void
    {
        foreach (self::STAFF_PAGES as $page) {
            $this->get($page)->assertRedirect('/login');
        }
    }

    public function test_staff_pages_forbidden_for_non_staff(): void
    {
        foreach (self::STAFF_PAGES as $page) {
            $this->actingAs($this->hrd)->get($page)->assertForbidden();
        }
    }

    public function test_staff_can_access_staff_pages(): void
    {
        foreach (self::STAFF_PAGES as $page) {
            $this->actingAs($this->staff)->get($page)->assertOk();
        }
    }
}
