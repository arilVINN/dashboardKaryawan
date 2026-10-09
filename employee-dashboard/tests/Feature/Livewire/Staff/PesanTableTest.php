<?php

namespace Tests\Feature\Livewire\Staff;

use App\Livewire\Staff\PesanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PesanTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->staff = $this->account('KRY-S', 'Staff Satu', 'ROLE-STAFF', 'staffsatu');
        $sender = $this->account('KRY-A', 'Abby Sender', 'ROLE-KADIV', 'abby');
        $this->account('KRY-H', 'Hrd Satu', 'ROLE-HRD', 'hrdsatu');

        Tugas::create([
            'id_tugas' => 'T-1',
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => 'Desain Logo',
            'deskripsi' => 'Desain Logo desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        Pesan::create([
            'id_pesan' => 'P-T1',
            'judul_pesan' => 'Versi Revisi',
            'deskripsi' => 'Versi Revisi body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-01',
            'tugas_id_tugas' => 'T-1',
            'tugas_karyawan_id_karyawan' => 'KRY-S',
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'P-D1',
            'judul_pesan' => 'Rapat Mingguan',
            'deskripsi' => 'Rapat Mingguan body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-02',
            'tugas_id_tugas' => null,
            'tugas_karyawan_id_karyawan' => null,
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);

        return User2::create([
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_renders_threads_and_direct_messages(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->assertSee('Tugas: Desain Logo')
            ->assertSee('Rapat Mingguan');
    }

    public function test_filters_by_jenis(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('jenis', 'langsung')
            ->assertSee('Rapat Mingguan')
            ->assertDontSee('Desain Logo');
    }

    public function test_searches_by_judul_or_pengirim(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('search', 'rapat')
            ->assertSee('Rapat Mingguan')
            ->assertDontSee('Desain Logo');
    }

    public function test_filter_chips_render_and_clear_only_their_own_filter(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('search', 'rapat')
            ->set('jenis', 'langsung')
            ->assertSee('data-filter-chip="q"', false)
            ->assertSee('data-filter-chip="jenis"', false)
            ->call('clearFilter', 'jenis')
            ->assertSet('jenis', '')
            ->assertSet('search', 'rapat')
            ->assertDontSee('data-filter-chip="jenis"', false)
            ->assertSee('data-filter-chip="q"', false);
    }

    public function test_non_staff_is_forbidden(): void
    {
        $hrd = User2::where('username', 'hrdsatu')->first();

        Livewire::actingAs($hrd)->test(PesanTable::class)->assertForbidden();
    }
}
