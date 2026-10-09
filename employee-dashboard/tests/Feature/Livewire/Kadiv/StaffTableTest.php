<?php

namespace Tests\Feature\Livewire\Kadiv;

use App\Livewire\Kadiv\StaffTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;
    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->kadiv = $this->account('KD-1', 'Kadiv IT', 'ROLE-KADIV', 'kadiv.it');
        $this->account('ST-A', 'Abby', 'ROLE-STAFF', 'abby');
        $this->account('ST-Z', 'Zed', 'ROLE-STAFF', 'zed');
        $this->staff = User2::where('username', 'abby')->first();
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);

        return User2::create([
            'id_user' => 'USR-' . $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_searches_and_sorts(): void
    {
        Livewire::actingAs($this->kadiv)->test(StaffTable::class)
            ->set('search', 'ab')
            ->assertSee('Abby')
            ->assertDontSee('Zed');

        Livewire::actingAs($this->kadiv)->test(StaffTable::class)
            ->call('sortBy', 'tugas')->assertSet('dir', 'asc')
            ->call('sortBy', 'tugas')->assertSet('dir', 'desc');
    }

    public function test_filter_chip_renders_and_clears_search(): void
    {
        Livewire::actingAs($this->kadiv)->test(StaffTable::class)
            ->set('search', 'ab')
            ->assertSee('Abby')
            ->assertSee('data-filter-chip="q"', false)
            ->call('clearFilter', 'search')
            ->assertSet('search', '')
            ->assertDontSee('data-filter-chip="q"', false);
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        Livewire::actingAs($this->staff)->test(StaffTable::class)->assertForbidden();
    }
}
