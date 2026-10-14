<?php

namespace Tests\Feature\Livewire\Hrd;

use App\Livewire\Hrd\KaryawanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class KaryawanTableTest extends TestCase
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
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    private function rowIds($component): array
    {
        return $component->viewData('rows')->getCollection()->pluck('id_karyawan')->all();
    }

    public function test_defaults_to_name_ascending(): void
    {
        $component = Livewire::actingAs($this->hrd)->test(KaryawanTable::class);

        $this->assertSame(['KRY-A', 'KRY-B', 'KRY-C', 'KRY-D', 'KRY-HRD'], $this->rowIds($component));
    }

    public function test_sorts_by_id_jabatan_and_divisi(): void
    {
        $byId = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->set('sort', 'id')->set('dir', 'desc');
        $this->assertSame(['KRY-HRD', 'KRY-D', 'KRY-C', 'KRY-B', 'KRY-A'], $this->rowIds($byId));

        $byJabatan = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->set('sort', 'jabatan')->set('dir', 'asc');
        $this->assertSame(['KRY-C', 'KRY-B', 'KRY-HRD', 'KRY-D', 'KRY-A'], $this->rowIds($byJabatan));

        $byDivisi = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->set('sort', 'divisi')->set('dir', 'asc');
        // Operasional < Sumber Daya Manusia < Teknologi Informasi (tie broken by id).
        $this->assertSame(['KRY-D', 'KRY-B', 'KRY-HRD', 'KRY-A', 'KRY-C'], $this->rowIds($byDivisi));
    }

    public function test_sort_header_toggles_direction(): void
    {
        Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->call('sortBy', 'nama')
            ->assertSet('sort', 'nama')
            ->assertSet('dir', 'asc')
            ->call('sortBy', 'nama')
            ->assertSet('dir', 'desc');
    }

    public function test_filters_by_divisi_jabatan_and_account_status(): void
    {
        $byDivisi = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->set('divisi', 'DIV-IT');
        $this->assertSame(['KRY-A', 'KRY-C'], $this->rowIds($byDivisi));

        $byJabatan = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->set('jabatan', 'Analyst');
        $this->assertSame(['KRY-C'], $this->rowIds($byJabatan));

        $aktif = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->set('status', 'aktif');
        $this->assertSame(['KRY-A', 'KRY-B', 'KRY-HRD'], $this->rowIds($aktif));

        $belum = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->set('status', 'belum');
        $this->assertSame(['KRY-C', 'KRY-D'], $this->rowIds($belum));
    }

    public function test_searches_by_name_case_insensitively(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $component = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->set('search', 'budi');

        $this->assertSame(['KRY-B'], $this->rowIds($component));
    }

    public function test_paginates_six_per_page(): void
    {
        $this->makeKaryawan('KRY-E', 'Eko Saputra', 'DIV-IT', 'Engineer');
        $this->makeKaryawan('KRY-F', 'Fajar Nugroho', 'DIV-IT', 'Engineer');

        $pageOne = Livewire::actingAs($this->hrd)->test(KaryawanTable::class);
        $this->assertSame(['KRY-A', 'KRY-B', 'KRY-C', 'KRY-D', 'KRY-E', 'KRY-F'], $this->rowIds($pageOne));

        $pageTwo = Livewire::actingAs($this->hrd)->test(KaryawanTable::class)->call('gotoPage', 2);
        $this->assertSame(['KRY-HRD'], $this->rowIds($pageTwo));
    }

    public function test_rows_keep_crud_hooks_for_the_existing_js(): void
    {
        Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->assertSee('data-staff-edit', false)
            ->assertSee('data-staff-delete', false)
            ->assertSee('data-id="KRY-A"', false)
            ->assertSee('data-nama="Andi Wijaya"', false);
    }

    public function test_filter_chips_render_and_clear_only_their_own_filter(): void
    {
        Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->set('divisi', 'DIV-IT')
            ->set('status', 'aktif')
            ->assertSee('data-filter-chip="divisi"', false)
            ->assertSee('data-filter-chip="status"', false)
            ->call('clearFilter', 'divisi')
            ->assertSet('divisi', '')
            ->assertSet('status', 'aktif')
            ->assertDontSee('data-filter-chip="divisi"', false)
            ->assertSee('data-filter-chip="status"', false);
    }

    public function test_shows_filtered_empty_state(): void
    {
        Livewire::actingAs($this->hrd)->test(KaryawanTable::class)
            ->set('search', 'ZZZNOMATCH')
            ->assertSee('Tidak ada data yang cocok dengan filter.');
    }

    public function test_page_deep_link_hydrates_filters(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?divisi=DIV-IT&status=aktif')
            ->assertOk()
            ->assertSee('Andi Wijaya')
            ->assertDontSee('Budi Santoso')
            ->assertSee('data-filter-chip="divisi"', false);
    }

    public function test_non_hrd_is_forbidden(): void
    {
        Livewire::actingAs($this->staffUser)->test(KaryawanTable::class)->assertForbidden();
    }
}
