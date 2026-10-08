<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KadivPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv']]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KD-1', 'nama' => 'Kadiv IT', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Kadiv', 'divisi_id_divisi' => 'DIV-IT']);

        $this->kadiv = User2::create([
            'id_user' => 'USR-KD-1',
            'username' => 'kadiv.it',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KD-1',
        ]);
    }

    public function test_kadiv_pages_render_livewire_and_no_token_script(): void
    {
        // The tables are Livewire (server-rendered, session auth); the shared topbar
        // keeps a token fallback but the table no longer depends on it.
        $this->actingAs($this->kadiv)->get('/kadiv/tugas')
            ->assertOk()
            ->assertSee('id="kadivTaskSearch"', false)
            ->assertDontSee('kadiv-tugas-list', false);

        $this->actingAs($this->kadiv)->get('/kadiv/pesan')
            ->assertOk()
            ->assertSee('id="kadivPesanSearch"', false)
            ->assertDontSee('kadiv-pesan-list', false);

        $this->actingAs($this->kadiv)->get('/kadiv/manajemenStaff')
            ->assertOk()
            ->assertSee('id="kadivStaffSearch"', false)
            ->assertDontSee('loadKadivStaffTable', false);
    }

    public function test_tampered_filter_url_does_not_error(): void
    {
        $this->actingAs($this->kadiv)->get('/kadiv/tugas?sort=DROP&dir=x&page=999')->assertOk();
    }
}
