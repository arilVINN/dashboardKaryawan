<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    private function divisiIds($response): array
    {
        return $response->viewData('divisis')->getCollection()->pluck('id_divisi')->all();
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


    public function test_divisi_list_sorts_by_kode(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?sort=kode&dir=asc')->assertOk();

        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->divisiIds($response));
    }

    public function test_divisi_list_sorts_by_nama(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?sort=nama&dir=asc')->assertOk();

        $this->assertSame(['DIV-OPS', 'DIV-HR', 'DIV-IT'], $this->divisiIds($response));
    }

    public function test_divisi_list_sorts_by_staff_count(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?sort=staff&dir=desc')->assertOk();

        // DIV-HR and DIV-IT each have 2 staff (tie broken by id asc), DIV-OPS has 1.
        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->divisiIds($response));
    }


    public function test_divisi_list_filters_by_status(): void
    {
        $aktif = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?status=aktif')->assertOk();
        $this->assertSame(['DIV-IT', 'DIV-HR'], $this->divisiIds($aktif));

        $nonaktif = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?status=nonaktif')->assertOk();
        $this->assertSame(['DIV-OPS'], $this->divisiIds($nonaktif));
    }

    public function test_divisi_list_searches_kode_or_nama(): void
    {
        $byNama = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?q=Operasional')->assertOk();
        $this->assertSame(['DIV-OPS'], $this->divisiIds($byNama));

        $byKode = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?q=IT')->assertOk();
        $this->assertSame(['DIV-IT'], $this->divisiIds($byKode));
    }


    public function test_manajemen_divisi_sorts_by_nama(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?sort=nama&dir=asc')
            ->assertOk()
            ->assertViewIs('hrd.manajemenDivisi');

        $this->assertSame(
            ['DIV-OPS', 'DIV-HR', 'DIV-IT'],
            $response->viewData('divisis')->pluck('id_divisi')->all()
        );
    }

    public function test_manajemen_divisi_sorts_by_staff_count(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?sort=staff&dir=desc')->assertOk();

        $this->assertSame(
            ['DIV-HR', 'DIV-IT', 'DIV-OPS'],
            $response->viewData('divisis')->pluck('id_divisi')->all()
        );
    }

    public function test_manajemen_divisi_filters_by_status(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?status=nonaktif')->assertOk();

        $this->assertSame(['DIV-OPS'], $response->viewData('divisis')->pluck('id_divisi')->all());
    }

    public function test_manajemen_divisi_searches_kode_or_nama(): void
    {
        $response = $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?q=Operasional')->assertOk();

        $this->assertSame(['DIV-OPS'], $response->viewData('divisis')->pluck('id_divisi')->all());
    }


    // NOTE: the case-insensitive Karyawan search assertion moved to
    // tests/Feature/Livewire/Hrd/KaryawanTableTest.php.

    public function test_divisi_search_is_case_insensitive(): void
    {
        DB::statement('PRAGMA case_sensitive_like = ON');

        $byNama = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?q=operasional')->assertOk();
        $this->assertSame(['DIV-OPS'], $this->divisiIds($byNama));

        $byKode = $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?q=it')->assertOk();
        $this->assertSame(['DIV-IT'], $this->divisiIds($byKode));
    }
}
