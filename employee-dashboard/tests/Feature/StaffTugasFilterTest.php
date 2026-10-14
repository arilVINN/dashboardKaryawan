<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
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
            'deskripsi' => $judul.' desc',
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
            ->assertSee('aria-label="Arah pesan"', false)
            ->assertSee('wire:click="$set(\'arah\', \'masuk\')"', false)
            ->assertSee('wire:click="$set(\'arah\', \'keluar\')"', false)
            ->assertSee('wire:click="sortBy(\'tanggal\')"', false)
            ->assertDontSee('setStaffPesanSort', false)
            ->assertDontSee('data-sort-indicator', false);
    }

    public function test_staff_pesan_rows_show_arah_and_penerima(): void
    {
        Role::insert([['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv']]);
        Karyawan::create([
            'id_karyawan' => 'KRY-K', 'nama' => 'Kadiv Kawan', 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Kepala Divisi', 'divisi_id_divisi' => 'DIV-IT',
        ]);
        $kadiv = User2::create([
            'id_user' => 'USR-K', 'username' => 'kadivkawan', 'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV', 'karyawan_id_karyawan' => 'KRY-K',
        ]);

        $this->makeTaskWithDeadline('T-10', 'Laporan Harian', Tugas::STATUS_BARU, '2026-10-21 10:00:00');
        Pesan::create([
            'id_pesan' => 'PSN-OUT', 'judul_pesan' => 'Siap Dilaksanakan', 'deskripsi' => 'Baik.',
            'tipe' => 'pesan', 'tanggal_pesan' => '2026-10-09',
            'tugas_id_tugas' => 'T-10', 'tugas_karyawan_id_karyawan' => 'KRY-S',
            'pengirim_id_user' => 'USR-S', 'penerima_id_user' => $kadiv->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-IN', 'judul_pesan' => 'Mohon Laporan', 'deskripsi' => 'Tolong.',
            'tipe' => 'pesan', 'tanggal_pesan' => '2026-10-09',
            'pengirim_id_user' => $kadiv->id_user, 'penerima_id_user' => 'USR-S',
        ]);

        $html = $this->actingAs($this->staff)->get('/pesan')->assertOk()->getContent();

        $this->assertStringContainsString('>Masuk</span>', $html);
        $this->assertStringContainsString('>Keluar</span>', $html);
        $this->assertStringContainsString('Kadiv Kawan', $html);
    }

    public function test_staff_pesan_rows_show_datetime_with_jam(): void
    {
        $pesan = Pesan::create([
            'id_pesan' => 'PSN-DT',
            'judul_pesan' => 'Pengumuman',
            'deskripsi' => 'Isi pengumuman.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-07',
            'pengirim_id_user' => 'USR-S',
            'penerima_id_user' => 'USR-S',
        ]);
        $pesan->created_at = '2026-10-07 16:45:00';
        $pesan->save();

        $this->actingAs($this->staff)
            ->get('/pesan')
            ->assertOk()
            ->assertSee('7 Okt 2026 16:45', false);
    }

    public function test_staff_tugas_rows_show_deadline_with_jam(): void
    {
        $this->makeTaskWithDeadline('T-9', 'Rapat Koordinasi', Tugas::STATUS_BARU, '2026-10-20 15:30:00');

        $this->actingAs($this->staff)
            ->get('/tugas')
            ->assertOk()
            ->assertSee('20 Okt 2026 15:30', false);
    }

    private function makeTaskWithDeadline(string $id, string $judul, string $status, string $deadline): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => $judul,
            'deskripsi' => $judul.' desc',
            'deadline' => $deadline,
            'progress' => '0',
            'status' => $status,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_staff_tugas_detail_has_labeled_file_field(): void
    {
        $this->actingAs($this->staff)
            ->get('/tugas/detail/T-1')
            ->assertOk()
            ->assertSee('File Hasil');
    }
}
