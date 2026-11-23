<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DetailDivisiCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_divisi_only_shows_the_members_table(): void
    {
        Role::insert([
            ['id_role' => 'RL-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif',
            'tipe' => Divisi::TIPE_DIVISI,
        ]);
        Karyawan::create([
            'id_karyawan' => 'K-HRD', 'nama' => 'HRD Satu', 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT',
        ]);
        $hrd = User2::create([
            'id_user' => 'U-HRD', 'username' => 'hrd', 'password' => Hash::make('x'),
            'role_id_role' => 'RL-HRD', 'karyawan_id_karyawan' => 'K-HRD',
        ]);

        $this->actingAs($hrd)
            ->get('/hrd/detailDivisi/DIV-IT')
            ->assertOk()
            ->assertSee('Anggota Divisi')
            ->assertSee('Ketua divisi')
            ->assertDontSee('dialogJabatanDivisi')
            ->assertDontSee('Direktur Utama');
    }
}
