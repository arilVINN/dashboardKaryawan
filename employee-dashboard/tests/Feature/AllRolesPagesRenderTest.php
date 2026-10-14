<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AllRolesPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;
    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-S', 'nama' => 'Staff Satu', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        $this->staff = User2::create([
            'id_user' => 'USR-S',
            'username' => 'staffsatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S',
        ]);

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);
    }

    public function test_staff_and_hrd_pages_render_livewire(): void
    {
        $this->actingAs($this->staff)->get('/tugas')->assertOk()->assertSee('id="staffTugasSearch"', false);
        $this->actingAs($this->staff)->get('/pesan')->assertOk()->assertSee('id="staffPesanSearch"', false);
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')->assertOk()->assertSee('id="hrdKaryawanSearch"', false);
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi')->assertOk()->assertSee('id="hrdDivisiSearch"', false);
    }

    public function test_tampered_filters_do_not_error(): void
    {
        $this->actingAs($this->staff)->get('/tugas?sort=DROP&dir=x&page=999')->assertOk();
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?sort=DROP&status=x&page=999')->assertOk();
    }
}
