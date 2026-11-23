<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsernameDotLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);
        Role::insert([['id_role' => 'RL-ST', 'nama_role' => 'Staff']]);
        Karyawan::create([
            'id_karyawan' => 'K-DOT',
            'nama' => 'User Dot',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        User2::create([
            'id_user' => 'U-DOT',
            'username' => 'staff.user',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'RL-ST',
            'karyawan_id_karyawan' => 'K-DOT',
        ]);
    }

    public function test_dot_in_username_is_accepted_on_login(): void
    {
        $this->postJson('/api/login', [
            'username' => 'staff.user',
            'password' => 'rahasia123',
        ])
            ->assertOk()
            ->assertJsonPath('user.username', 'staff.user')
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_invalid_username_characters_are_still_rejected(): void
    {
        $this->postJson('/api/login', [
            'username' => "' OR 1=1 --",
            'password' => 'rahasia123',
        ])->assertUnprocessable();
    }
}
