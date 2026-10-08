<?php

namespace Tests\Feature\Livewire\Kadiv;

use App\Livewire\Kadiv\TugasTable;
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
        $this->account('ST-1', 'Staff One', 'ROLE-STAFF', 'staff.one');
        $this->account('ST-2', 'Staff Two', 'ROLE-STAFF', 'staff.two');
        $this->staff = User2::where('username', 'staff.one')->first();

        $this->task('T-1', 'ST-1', 'Alpha');
        $this->task('T-2', 'ST-1', 'Gamma');
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

    private function task(string $id, string $karyawanId, string $judul): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_tugas' => $judul,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => Tugas::STATUS_BARU,
            'progress' => '0',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_searches_and_creates_task(): void
    {
        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('search', 'alpha')
            ->assertSee('Alpha')
            ->assertDontSee('Gamma');

        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('judul', 'Tugas X')
            ->set('deskripsi', 'd')
            ->set('tenggat', now()->addWeek()->toDateTimeString())
            ->set('recipient', 'ST-1')
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tugas', ['karyawan_id_karyawan' => 'ST-1', 'judul_tugas' => 'Tugas X']);
    }

    public function test_create_for_semua_assigns_to_every_division_staff(): void
    {
        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('judul', 'Bulk')
            ->set('deskripsi', 'd')
            ->set('tenggat', now()->addWeek()->toDateTimeString())
            ->set('recipient', 'semua')
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertSame(2, Tugas::where('judul_tugas', 'Bulk')->count());
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)->assertForbidden();
    }
}
