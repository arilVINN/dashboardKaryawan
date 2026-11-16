<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DirekturRoleTest extends TestCase
{
    use RefreshDatabase;

    private User2 $direktur;

    protected function setUp(): void
    {
        parent::setUp();

        $roleDirektur = Role::firstOrCreate(
            ['nama_role' => 'direktur'],
            ['id_role' => 'RL004']
        );

        $divisiDireksi = Divisi::firstOrCreate(
            ['kode_divisi' => 'DIR'],
            [
                'id_divisi' => 'DV000',
                'nama_divisi' => 'Direksi & Pimpinan',
                'status_aktif' => 'Aktif',
            ]
        );

        $karyawanDirektur = Karyawan::firstOrCreate(
            ['email' => 'direktur@silindo.co.id'],
            [
                'id_karyawan' => 'KR000',
                'nama' => 'Hendra Prasetya',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1975-01-01',
                'tanggal_masuk' => '2010-01-01',
                'tanggal_akhir_kontrak' => now()->addYears(10)->toDateString(),
                'no_rekening' => '9999999999',
                'no_telepon' => '081199998888',
                'alamat' => 'Salatiga, Jawa Tengah',
                'jabatan' => 'Direktur Utama',
                'status_aktif' => 'Aktif',
                'foto' => null,
                'divisi_id_divisi' => $divisiDireksi->id_divisi,
                'pendidikan' => 'S2 Manajemen Bisnis',
                'job_level' => 'Direksi',
                'status_karyawan' => 'tetap',
                'aktif' => true,
                'atasan_id' => null,
            ]
        );

        $this->direktur = User2::firstOrCreate(
            ['username' => 'direktur'],
            [
                'id_user' => 'US000',
                'password' => Hash::make('pass123'),
                'role_id_role' => $roleDirektur->id_role,
                'karyawan_id_karyawan' => $karyawanDirektur->id_karyawan,
            ]
        );
    }

    public function test_direktur_can_login_and_redirects_to_direktur_dashboard(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('/build/assets/app-');

        $response = $this->post('/login', [
            'username' => 'direktur',
            'password' => 'pass123',
        ]);

        $response->assertRedirect('/direktur/dashboard');
        $this->assertAuthenticatedAs($this->direktur);
    }

    public function test_direktur_can_view_executive_dashboard(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee('Executive Command Center');
        $response->assertSee('Saldo Kas Bersih');
        $response->assertSee('Karyawan Aktif');
        $response->assertSee('Menunggu Approval');
    }

    public function test_direktur_can_access_all_four_pillars(): void
    {
        // 1. Staff Routes
        $this->actingAs($this->direktur)->get('/')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/tugas')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kalender')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/jurnal')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/pusat-data')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/profile')->assertStatus(200);

        // 2. Kadiv Routes
        $this->actingAs($this->direktur)->get('/kadiv/dashboard')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/tugas')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/manajemenStaff')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/pengajuan-dana')->assertStatus(200);

        // 3. Finance Routes
        $this->actingAs($this->direktur)->get('/kadiv/keuangan')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/keuangan/rekap-divisi')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/keuangan/arus-kas')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/kadiv/keuangan/manajemen-dana')->assertStatus(200);

        // 4. HRD Routes
        $this->actingAs($this->direktur)->get('/hrd/dashboard')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/hrd/daftarKaryawan')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/hrd/manajemenDivisi')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/hrd/suratIzin')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/suratIzin')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/hrd/suratBooking')->assertStatus(200);
        $this->actingAs($this->direktur)->get('/hrd/struktur-organisasi')->assertStatus(200);
    }
}
