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

        $this->withoutVite();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direktur'],
        ]);

        $divisiDireksi = Divisi::create([
            'id_divisi' => 'DIV-DIR',
            'kode_divisi' => Divisi::KODE_DIREKTUR,
            'nama_divisi' => 'Direktur Utama',
            'tipe' => Divisi::TIPE_DIREKTUR,
            'level' => 1,
            'kapasitas' => 1,
            'urutan' => 1,
            'status_aktif' => 'Aktif',
        ]);

        $karyawanDirektur = Karyawan::create([
            'id_karyawan' => 'KRY-DIR',
            'nama' => 'Hendra Wijaya',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => Karyawan::JABATAN_DIREKTUR,
            'job_level' => 'Direksi',
            'divisi_id_divisi' => $divisiDireksi->id_divisi,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);

        $this->direktur = User2::create([
            'id_user' => 'USR-DIR',
            'username' => 'direktur',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-DIR',
            'karyawan_id_karyawan' => $karyawanDirektur->id_karyawan,
        ]);
    }

    public function test_direktur_login_redirects_to_executive_dashboard(): void
    {
        $this->post('/login', [
            'username' => 'direktur',
            'password' => 'pass123',
        ])->assertRedirect('/direktur/dashboard');

        $this->assertAuthenticatedAs($this->direktur);
    }

    public function test_login_page_sends_direktur_to_the_executive_dashboard(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee("role === 'direktur'", false)
            ->assertSee("/direktur/dashboard", false);
    }

    public function test_direktur_can_view_executive_dashboard_with_slim_sidebar(): void
    {
        $this->actingAs($this->direktur)
            ->get('/direktur/dashboard')
            ->assertOk()
            ->assertSee('Executive Command Center')
            ->assertSee('Selamat Datang')
            ->assertSee('Dashboard Direktur')
            ->assertSee('Persetujuan')
            ->assertSee('SDM & Organisasi', false)
            ->assertSee('Keuangan')
            ->assertSee('Operasional')
            ->assertSee('Workspace');
    }

    public function test_direktur_can_access_hrd_kadiv_finance_and_staff_pages(): void
    {
        $pages = [
            '/',
            '/tugas',
            '/kalender',
            '/jurnal',
            '/pusat-data',
            '/profile',
            '/kadiv/dashboard',
            '/hrd/daftarKaryawan',
            '/hrd/manajemenDivisi',
            '/hrd/strukturOrganisasi',
            '/suratIzin',
            '/hrd/suratBooking',
            '/kadiv/tugas',
            '/kadiv/manajemenStaff',
            '/kadiv/keuangan',
            '/kadiv/keuangan/rekap-divisi',
            '/kadiv/keuangan/arus-kas',
            '/kadiv/keuangan/manajemen-dana',
            '/kadiv/pengajuan-dana',
            '/hrd/dashboard',
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->direktur)->get($page)->assertOk();
        }
    }

    public function test_direktur_login_ignores_intended_url(): void
    {
        // Simulate a logged-out user landing on a protected page first.
        $this->get('/hrd/daftarKaryawan')->assertRedirect('/login');

        $this->post('/login', [
            'username' => 'direktur',
            'password' => 'pass123',
        ])->assertRedirect('/direktur/dashboard');
    }

    public function test_direktur_can_open_surat_izin_and_legacy_url_redirects(): void
    {
        $this->actingAs($this->direktur)->get('/suratIzin')->assertOk();

        $this->actingAs($this->direktur)
            ->get('/hrd/suratIzin')
            ->assertRedirect('/suratIzin');
    }

    public function test_direktur_is_read_only_for_hrd_and_kadiv_data_mutations(): void
    {
        $this->actingAs($this->direktur)->post('/hrd/divisi', [])->assertForbidden();
        $this->actingAs($this->direktur)->post('/kadiv/tugas', [])->assertForbidden();
        $this->actingAs($this->direktur)->post('/kadiv/keuangan/pemasukan', [])->assertForbidden();
    }
}
