<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdListFilteringTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private User2 $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Sumber Daya Manusia', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Operasional', 'status_aktif' => 'Nonaktif']);

        $this->staffUser = $this->makeKaryawan('KRY-A', 'Andi Wijaya', 'DIV-IT', 'Software Engineer', 'andi');
        $this->makeKaryawan('KRY-B', 'Budi Santoso', 'DIV-HR', 'HR Specialist', 'budi');
        $this->makeKaryawan('KRY-C', 'Citra Lestari', 'DIV-IT', 'Analyst');
        $this->makeKaryawan('KRY-D', 'Dedi Kurniawan', 'DIV-OPS', 'Operator');

        $this->makeKaryawan('KRY-HRD', 'HRD User', 'DIV-HR', 'HRD', 'hrduser', 'ROLE-HRD');
        $this->hrd = User2::where('username', 'hrduser')->first();
    }

    private function makeKaryawan(
        string $id,
        string $nama,
        string $divisiId,
        string $jabatan,
        ?string $username = null,
        string $roleId = 'ROLE-STAFF'
    ): ?User2 {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_rekrut' => '2026-01-01',
            'no_telepon' => '081234567890',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisiId,
        ]);

        if ($username === null) {
            return null;
        }

        return User2::create([
            'id_user' => 'USR-' . $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_staff_list_requires_authentication(): void
    {
        $this->get('/hrd/daftarKaryawan')->assertRedirect('/login');
    }

    public function test_staff_list_forbidden_for_non_hrd(): void
    {
        $this->actingAs($this->staffUser)->get('/hrd/daftarKaryawan')->assertForbidden();
    }

    public function test_divisi_list_requires_authentication(): void
    {
        $this->get('/hrd/daftarDivisi')->assertRedirect('/login');
    }

    public function test_divisi_list_forbidden_for_non_hrd(): void
    {
        $this->actingAs($this->staffUser)->get('/hrd/daftarDivisi')->assertForbidden();
    }


    // NOTE: Karyawan sort/filter/search assertions moved to
    // tests/Feature/Livewire/Hrd/KaryawanTableTest.php — the list now renders
    // inside the App\Livewire\Hrd\KaryawanTable component.


    // NOTE: Divisi sort/filter/search assertions moved to
    // tests/Feature/Livewire/Hrd/DivisiTableTest.php — both lists now render
    // inside the App\Livewire\Hrd\DivisiTable component.


    // NOTE: the case-insensitive Karyawan search assertion moved to
    // tests/Feature/Livewire/Hrd/KaryawanTableTest.php.

    // NOTE: the case-insensitive Divisi search assertion moved to
    // tests/Feature/Livewire/Hrd/DivisiTableTest.php.
}
