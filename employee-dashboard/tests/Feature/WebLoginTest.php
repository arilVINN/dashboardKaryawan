<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_hrd_can_log_in_from_the_web_form_and_open_the_hrd_dashboard(): void
    {
        $this->withoutVite();

        Role::create([
            'id_role' => 'ROLE-HRD',
            'nama_role' => 'hrd',
        ]);

        DB::table('divisis')->insert([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('karyawans')->insert([
            'id_karyawan' => 'EMP-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'hrd@example.test',
            'jabatan' => 'HRD',
            'divisi_id_divisi' => 'DIV-HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'EMP-HRD',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Login Form')
            ->assertSee('action="/login"', false)
            ->assertSee('name="_token"', false);

        $this->get('/hrd/dashboard')
            ->assertRedirect('/login');

        $this->postJson('/login', [
            'username' => 'hrduser',
            'password' => 'pass123',
        ])
            ->assertOk()
            ->assertJsonPath('role', 'hrd')
            ->assertJsonStructure(['access_token']);

        $this->assertAuthenticatedAs($user, 'web');

        $this->get('/hrd/dashboard')
            ->assertOk()
            ->assertViewIs('hrd.dashboard');
    }
}
