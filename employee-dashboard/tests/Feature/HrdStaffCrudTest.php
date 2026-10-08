<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdStaffCrudTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-1', 'nama' => 'Andi Wijaya', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-05-05', 'tanggal_rekrut' => '2026-01-01', 'no_telepon' => '081234567890', 'email' => 'andi@example.test', 'jabatan' => 'Engineer', 'divisi_id_divisi' => 'DIV-IT']);
        User2::create(['id_user' => 'USR-1', 'username' => 'andi', 'password' => Hash::make('pass123'), 'role_id_role' => 'ROLE-STAFF', 'karyawan_id_karyawan' => 'KRY-1']);

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1990-01-01', 'tanggal_rekrut' => '2020-01-01', 'no_telepon' => '0812', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);
        $this->hrd = User2::create(['id_user' => 'USR-HRD', 'username' => 'hrduser', 'password' => Hash::make('pass123'), 'role_id_role' => 'ROLE-HRD', 'karyawan_id_karyawan' => 'KRY-HRD']);

        Tugas::create([
            'id_tugas' => 'TG-1',
            'karyawan_id_karyawan' => 'KRY-1',
            'judul_tugas' => 'Bikin API',
            'deskripsi' => 'Buat backend.',
            'deadline' => now()->addDays(5)->toDateString(),
            'progress' => '50',
            'status' => Tugas::STATUS_BERJALAN,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    // ---- staff list CRUD controls ----

    public function test_karyawan_list_shows_crud_controls_and_endpoints(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('data-staff-create', false)
            ->assertSee('data-staff-edit', false)
            ->assertSee('data-staff-delete', false)
            ->assertSee('data-staff-modal="create"', false)
            ->assertSee('data-staff-modal="edit"', false)
            ->assertSee(url('/hrd/staff'), false);
    }

    public function test_karyawan_create_modal_has_a_divisi_select(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('name="divisi_id_divisi"', false)
            ->assertSee('Teknologi Informasi');
    }

    // ---- staff detail page ----

    public function test_staff_detail_page_shows_profile_and_task_list(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/detailKaryawan/KRY-1')
            ->assertOk()
            ->assertSee('Andi Wijaya')
            ->assertSee('Teknologi Informasi')
            ->assertSee('Bikin API')
            ->assertSee('data-breadcrumb-parent="Karyawan"', false)
            ->assertSee('data-breadcrumb-current="Detail Karyawan"', false);
    }

    public function test_staff_detail_page_returns_404_for_unknown_id(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/detailKaryawan/NOPE')->assertNotFound();
    }

    public function test_staff_detail_page_requires_authentication(): void
    {
        $this->get('/hrd/detailKaryawan/KRY-1')->assertRedirect('/login');
    }
}
