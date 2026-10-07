<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TopbarTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'Darmawan HRD', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1990-01-01', 'tanggal_rekrut' => '2020-01-01', 'no_telepon' => '0812', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);

        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'darmawanhrd',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);
    }

    public function test_topbar_shows_the_authenticated_user_not_guest(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/dashboard')
            ->assertOk()
            ->assertSee('id="topbar-nama"', false)
            ->assertSee('>Darmawan HRD</p>', false)
            ->assertSee('>Teknologi Informasi</p>', false);
    }

    public function test_topbar_logout_posts_to_the_logout_route(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/dashboard')
            ->assertOk()
            ->assertSee('action="' . route('logout') . '"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->hrd)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
