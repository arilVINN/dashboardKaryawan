<?php

namespace Tests\Feature\Livewire\Hrd;

use App\Livewire\Hrd\DivisiTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DivisiTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private User2 $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Sumber Daya Manusia', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Operasional', 'status_aktif' => 'Nonaktif']);

        // DIV-HR and DIV-IT have 2 staff each, DIV-OPS has 1.
        $this->makeKaryawan('KRY-A', 'DIV-IT');
        $this->makeKaryawan('KRY-B', 'DIV-IT');
        $this->makeKaryawan('KRY-C', 'DIV-HR');
        $this->makeKaryawan('KRY-D', 'DIV-HR');
        $this->makeKaryawan('KRY-E', 'DIV-OPS');

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-HR']);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);

        Karyawan::create(['id_karyawan' => 'KRY-S', 'nama' => 'Staff Satu', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        $this->staffUser = User2::create([
            'id_user' => 'USR-S',
            'username' => 'staffsatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S',
        ]);
    }

    private function makeKaryawan(string $id, string $divisiId): void
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $id,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
        ]);
    }

    private function rowIds($component): array
    {
        return $component->viewData('rows')->getCollection()->pluck('id_divisi')->all();
    }

    public function test_defaults_to_hierarchy_order(): void
    {
        $component = Livewire::actingAs($this->hrd)->test(DivisiTable::class);

        // All fixtures default to level 3 / urutan 0, so id_divisi asc breaks
        // the tie inside the hierarchy ordering.
        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->rowIds($component));
    }

    public function test_sorts_by_kode_nama_and_staff_count(): void
    {
        $byKode = Livewire::actingAs($this->hrd)->test(DivisiTable::class)
            ->set('sort', 'kode')->set('dir', 'asc');
        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->rowIds($byKode));

        $byNama = Livewire::actingAs($this->hrd)->test(DivisiTable::class)
            ->set('sort', 'nama')->set('dir', 'asc');
        $this->assertSame(['DIV-OPS', 'DIV-HR', 'DIV-IT'], $this->rowIds($byNama));

        $byStaff = Livewire::actingAs($this->hrd)->test(DivisiTable::class)
            ->set('sort', 'staff')->set('dir', 'desc');
        // DIV-HR and DIV-IT each have 3 staff (tie broken by id asc), DIV-OPS has 1.
        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->rowIds($byStaff));
    }

    public function test_filters_by_status(): void
    {
        $aktif = Livewire::actingAs($this->hrd)->test(DivisiTable::class)->set('status', 'aktif');
        $this->assertSame(['DIV-HR', 'DIV-IT'], $this->rowIds($aktif));

        $nonaktif = Livewire::actingAs($this->hrd)->test(DivisiTable::class)->set('status', 'nonaktif');
        $this->assertSame(['DIV-OPS'], $this->rowIds($nonaktif));
    }

    public function test_searches_kode_or_nama_case_insensitively(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $byNama = Livewire::actingAs($this->hrd)->test(DivisiTable::class)->set('search', 'operasional');
        $this->assertSame(['DIV-OPS'], $this->rowIds($byNama));

        $byKode = Livewire::actingAs($this->hrd)->test(DivisiTable::class)->set('search', 'it');
        $this->assertSame(['DIV-IT'], $this->rowIds($byKode));
    }

    public function test_manage_mode_shows_tambah_and_hapus(): void
    {
        Livewire::actingAs($this->hrd)->test(DivisiTable::class, ['manage' => true])
            ->assertSee('bukaModalDivisi()', false)
            ->assertSee('hapusDivisi(this)', false);

        Livewire::actingAs($this->hrd)->test(DivisiTable::class)
            ->assertDontSee('bukaModalDivisi()', false)
            ->assertDontSee('hapusDivisi(this)', false);
    }

    public function test_management_table_hides_hierarchy_presentation(): void
    {
        $manage = Livewire::actingAs($this->hrd)->test(DivisiTable::class, ['manage' => true]);

        $manage->assertDontSee('Tingkatan')
            ->assertDontSee('Struktur Divisi')
            ->assertDontSee('Induk:')
            ->assertDontSee('Kapasitas:')
            ->assertSee('Nama Divisi')
            ->assertSee('Jumlah Staff');

        $list = Livewire::actingAs($this->hrd)->test(DivisiTable::class);

        $list->assertSee('Tingkatan')
            ->assertSee('Struktur Divisi')
            ->assertSee('Kapasitas:');
    }

    public function test_pagination_caps_at_ten_for_the_list_and_twenty_five_for_management(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            Divisi::create([
                'id_divisi' => 'DIV-P'.$suffix,
                'kode_divisi' => 'P'.$suffix,
                'nama_divisi' => 'Divisi Tambahan '.$suffix,
                'status_aktif' => 'Aktif',
            ]);
        }

        $list = Livewire::actingAs($this->hrd)->test(DivisiTable::class);
        $this->assertCount(10, $list->viewData('rows'));

        $manage = Livewire::actingAs($this->hrd)->test(DivisiTable::class, ['manage' => true]);
        $this->assertCount(25, $manage->viewData('rows'));
    }

    public function test_page_deep_links_hydrate(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?status=nonaktif')
            ->assertOk()
            ->assertSee('Operasional')
            ->assertDontSee('Teknologi Informasi')
            ->assertSee('data-filter-chip="status"', false);

        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?q=Operasional')
            ->assertOk()
            ->assertSee('Operasional')
            ->assertSee('data-id="DIV-OPS"', false)
            ->assertDontSee('data-id="DIV-IT"', false)
            ->assertDontSee('data-id="DIV-HR"', false);
    }

    public function test_non_hrd_is_forbidden(): void
    {
        Livewire::actingAs($this->staffUser)->test(DivisiTable::class)->assertForbidden();
    }
}
