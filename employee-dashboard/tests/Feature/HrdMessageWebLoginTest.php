<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdMessageWebLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_login_from_web_form_creates_session_for_hrd_messages_page(): void
    {
        $this->withoutVite();

        Role::create(['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd']);
        Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'aktif',
        ]);
        Karyawan::create([
            'id_karyawan' => 'EMP-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'hrd@example.test',
            'jabatan' => 'HRD',
            'divisi_id_divisi' => 'DIV-HR',
        ]);
        $user = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'EMP-HRD',
        ]);

        $this->postJson('/login', [
            'username' => 'hrduser',
            'password' => 'pass123',
        ])
            ->assertOk()
            ->assertJsonPath('role', 'hrd');

        $this->assertAuthenticatedAs($user, 'web');

        $this->get('/hrd/pesan')
            ->assertOk()
            ->assertViewIs('hrd.pesan')
            ->assertSee('Pusat Pesan');
    }
}
