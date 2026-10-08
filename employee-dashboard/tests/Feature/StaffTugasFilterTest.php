<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffTugasFilterTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff']]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KRY-S', 'nama' => 'Staff Satu', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_rekrut' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        $this->staff = User2::create([
            'id_user' => 'USR-S',
            'username' => 'staffsatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S',
        ]);

        // Deadlines must be in the future, otherwise 'baru'/'berjalan' fall into 'telat'.
        $this->makeTask('T-1', 'Gamma', Tugas::STATUS_BARU, now()->addDays(10)->toDateString());
        $this->makeTask('T-2', 'Alpha', Tugas::STATUS_BERJALAN, now()->addDays(5)->toDateString());
        $this->makeTask('T-3', 'Beta', Tugas::STATUS_SUDAH_ACC, now()->addDays(20)->toDateString());
    }

    private function makeTask(string $id, string $judul, string $status, string $deadline): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => $judul,
            'deskripsi' => $judul . ' desc',
            'deadline' => $deadline,
            'progress' => '0',
            'status' => $status,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_staff_tugas_sorts_by_judul_and_tenggat(): void
    {
        $judul = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/staff/tugas?sort=judul&dir=asc')->assertOk();
        $this->assertSame(['T-2', 'T-3', 'T-1'], collect($judul->json('data'))->pluck('id_tugas')->all());

        $tenggat = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/staff/tugas?sort=tenggat&dir=asc')->assertOk();
        $this->assertSame(['T-2', 'T-1', 'T-3'], collect($tenggat->json('data'))->pluck('id_tugas')->all());
    }

    public function test_staff_tugas_filters_by_status(): void
    {
        $baru = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/staff/tugas?status=baru')->assertOk();
        $this->assertSame(['T-1'], collect($baru->json('data'))->pluck('id_tugas')->all());
    }

    public function test_staff_tugas_searches_judul_case_insensitively(): void
    {
        DB::statement('PRAGMA case_sensitive_like = ON');

        $response = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/staff/tugas?q=alph')->assertOk();

        $this->assertSame(['T-2'], collect($response->json('data'))->pluck('id_tugas')->all());
    }

    public function test_staff_tugas_page_renders_sort_filter_controls(): void
    {
        $this->actingAs($this->staff)
            ->get('/tugas')
            ->assertOk()
            ->assertSee('id="staffTugasSearch"', false)
            ->assertSee('id="staffTugasStatus"', false)
            ->assertSee('wire:click="sortBy(\'judul\')"', false)
            ->assertDontSee('setStaffTugasSort', false)
            ->assertDontSee('data-sort-indicator', false);
    }

    public function test_staff_pesan_page_renders_sort_filter_controls(): void
    {
        $this->actingAs($this->staff)
            ->get('/pesan')
            ->assertOk()
            ->assertSee('id="staffPesanSearch"', false)
            ->assertSee('id="staffPesanJenis"', false)
            ->assertSee('setStaffPesanSort', false)
            ->assertSee('data-sort-indicator="tanggal"', false);
    }

    public function test_staff_tugas_detail_has_labeled_file_field(): void
    {
        $this->actingAs($this->staff)
            ->get('/tugas/detail/T-1')
            ->assertOk()
            ->assertSee('File Hasil');
    }
}
