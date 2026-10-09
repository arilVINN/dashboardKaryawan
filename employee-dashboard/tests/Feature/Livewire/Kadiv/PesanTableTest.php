<?php

namespace Tests\Feature\Livewire\Kadiv;

use App\Events\PesanDikirim;
use App\Livewire\Kadiv\PesanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PesanTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;
    private User2 $abby;
    private User2 $outsider;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resources', 'status_aktif' => 'Aktif']);

        $this->kadiv = $this->account('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->abby = $this->account('ST-A', 'Abby', 'DIV-IT', 'ROLE-STAFF', 'abby');
        $this->outsider = $this->account('ST-X', 'Outsider', 'DIV-HR', 'ROLE-STAFF', 'outsider');

        Pesan::create([
            'id_pesan' => 'P-1',
            'judul_pesan' => 'Laporan Bulanan',
            'deskripsi' => 'laporan body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $this->abby->id_user,
            'penerima_id_user' => $this->kadiv->id_user,
        ]);
    }

    private function account(string $id, string $nama, string $divisi, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => $divisi]);

        return User2::create([
            'id_user' => 'USR-' . $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_filter_chips_render_and_clear_only_their_own_filter(): void
    {
        Livewire::actingAs($this->kadiv)->test(PesanTable::class)
            ->set('search', 'laporan')
            ->set('tipe', 'pesan')
            ->set('arah', 'masuk')
            ->assertSee('data-filter-chip="q"', false)
            ->assertSee('data-filter-chip="tipe"', false)
            ->assertSee('data-filter-chip="arah"', false)
            ->call('clearFilter', 'tipe')
            ->assertSet('tipe', '')
            ->assertSet('arah', 'masuk')
            ->assertDontSee('data-filter-chip="tipe"', false)
            ->assertSee('data-filter-chip="arah"', false);
    }

    public function test_searches_and_sends(): void
    {
        Livewire::actingAs($this->kadiv)->test(PesanTable::class)
            ->set('search', 'laporan')
            ->assertSee('Laporan Bulanan');

        Livewire::actingAs($this->kadiv)->test(PesanTable::class)
            ->set('recipientUserId', $this->abby->id_user)
            ->set('isi', 'Halo staff')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pesans', [
            'penerima_id_user' => $this->abby->id_user,
            'deskripsi' => 'Halo staff',
        ]);
    }

    public function test_rejects_out_of_division_recipient(): void
    {
        Livewire::actingAs($this->kadiv)->test(PesanTable::class)
            ->set('recipientUserId', $this->outsider->id_user)
            ->set('isi', 'x')
            ->call('sendMessage')
            ->assertHasErrors();

        $this->assertDatabaseMissing('pesans', ['deskripsi' => 'x']);
    }

    public function test_send_dispatches_pesan_dikirim(): void
    {
        Event::fake([PesanDikirim::class]);

        Livewire::actingAs($this->kadiv)->test(PesanTable::class)
            ->set('recipientUserId', $this->abby->id_user)
            ->set('isi', 'Halo staff')
            ->call('sendMessage')
            ->assertHasNoErrors();

        Event::assertDispatched(PesanDikirim::class);
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        Livewire::actingAs($this->abby)->test(PesanTable::class)->assertForbidden();
    }
}
