<?php

namespace Tests\Feature\Livewire\Staff;

use App\Livewire\Staff\TugasTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TugasTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->staff = $this->account('KRY-S', 'Staff Satu', 'ROLE-STAFF', 'staffsatu');
        $this->account('KRY-H', 'Hrd Satu', 'ROLE-HRD', 'hrdsatu');

        $this->task('T-1', 'Gamma', Tugas::STATUS_BARU, now()->addDays(10)->toDateString());
        $this->task('T-2', 'Alpha', Tugas::STATUS_BERJALAN, now()->addDays(5)->toDateString());
        $this->task('T-3', 'Beta', Tugas::STATUS_SUDAH_ACC, now()->addDays(20)->toDateString());
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

    private function task(string $id, string $judul, string $status, string $deadline): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => $judul,
            'deskripsi' => $judul . ' desc',
            'deadline' => $deadline,
            'progress' => '0',
            'status' => $status,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_searches_by_judul(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)
            ->set('search', 'alph')
            ->assertSee('Alpha')
            ->assertDontSee('Gamma');
    }

    public function test_filters_by_status(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)
            ->set('status', Tugas::STATUS_BARU)
            ->assertSee('Gamma')
            ->assertDontSee('Alpha');
    }

    public function test_sorts_by_judul_toggling_direction(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)
            ->call('sortBy', 'judul')
            ->assertSet('sort', 'judul')
            ->assertSet('dir', 'asc')
            ->call('sortBy', 'judul')
            ->assertSet('dir', 'desc');
    }

    public function test_reset_filters_clears_state(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)
            ->set('search', 'alph')
            ->set('status', Tugas::STATUS_BARU)
            ->call('sortBy', 'judul')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('sort', '')
            ->assertSet('dir', 'desc');
    }

    public function test_filter_chips_render_and_clear_only_their_own_filter(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)
            ->set('search', 'alph')
            ->set('status', Tugas::STATUS_BARU)
            ->assertSee('data-filter-chip="q"', false)
            ->assertSee('data-filter-chip="status"', false)
            ->call('clearFilter', 'search')
            ->assertSet('search', '')
            ->assertSet('status', Tugas::STATUS_BARU)
            ->assertDontSee('data-filter-chip="q"', false)
            ->assertSee('data-filter-chip="status"', false);
    }

    public function test_non_staff_is_forbidden(): void
    {
        $hrd = User2::where('username', 'hrdsatu')->first();

        Livewire::actingAs($hrd)->test(TugasTable::class)->assertForbidden();
    }
}
