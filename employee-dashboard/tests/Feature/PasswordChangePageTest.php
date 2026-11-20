<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_password_visibility_toggles(): void
    {
        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);
        Role::insert([['id_role' => 'RL-ST', 'nama_role' => 'Staff']]);
        Karyawan::create([
            'id_karyawan' => 'K-PW',
            'nama' => 'User Password',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $user = User2::create([
            'id_user' => 'U-PW',
            'username' => 'userpw',
            'password' => Hash::make('password-sementara'),
            'role_id_role' => 'RL-ST',
            'karyawan_id_karyawan' => 'K-PW',
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get('/ganti-password')
            ->assertOk()
            ->assertSee('aria-label="Tampilkan password"', false)
            ->assertSee('data-target="gp-lama"', false)
            ->assertSee('data-target="gp-baru"', false)
            ->assertSee('data-target="gp-konfirmasi"', false);
    }
}
