<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KadivTopbarTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KRY-KADIV', 'nama' => 'Pak Tono', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1985-01-01', 'tanggal_rekrut' => '2019-01-01', 'no_telepon' => '0812', 'email' => 'tono@silindo.test', 'jabatan' => 'Kadiv', 'divisi_id_divisi' => 'DIV-IT']);

        $this->kadiv = User2::create([
            'id_user' => 'USR-KADIV',
            'username' => 'tonokd',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-KADIV',
        ]);
    }

    public function test_kadiv_topbar_shows_the_authenticated_user(): void
    {
        $this->actingAs($this->kadiv)->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('id="kadiv-topbar-name"', false)
            ->assertSee('Pak Tono')
            ->assertSee('tono@silindo.test');
    }

    public function test_kadiv_topbar_logout_posts_to_the_logout_route(): void
    {
        $this->actingAs($this->kadiv)->get('/kadiv/dashboard')
            ->assertSee('action="' . route('logout') . '"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_kadiv_logout_ends_the_session(): void
    {
        $this->actingAs($this->kadiv)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
