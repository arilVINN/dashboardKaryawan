<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PagePolishTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    private User2 $staff;

    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->hrd = $this->createAccount('KRY-HRD', 'HRD User', 'ROLE-HRD', 'hrdpolish');
        $this->staff = $this->createAccount('KRY-STAFF', 'Staff User', 'ROLE-STAFF', 'staffpolish');
        $this->kadiv = $this->createAccount('KRY-KADIV', 'Kadiv User', 'ROLE-KADIV', 'kadivpolish');
    }

    private function createAccount(string $employeeId, string $name, string $roleId, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    private function assertPagePolish(string $html, string $parent, string $current, string $title, ?string $heading = null): void
    {
        $this->assertStringContainsString('data-breadcrumb-parent="'.$parent.'"', $html);
        $this->assertStringContainsString('data-breadcrumb-current="'.$current.'"', $html);
        $this->assertStringContainsString('<title>'.$title.'</title>', $html);
        if ($heading !== null) {
            $this->assertSame($current, $heading);
            $this->assertStringContainsString('<h1', $html);
            $this->assertStringContainsString($heading, $html);
        }
    }

    private function getPage(User2 $user, string $url): string
    {
        return $this->actingAs($user)->get($url)->assertOk()->getContent();
    }

    private function assertRowHeading(string $html, string $parent, string $current, string $title, string $rowHeading): void
    {
        $this->assertStringContainsString('data-breadcrumb-parent="'.$parent.'"', $html);
        $this->assertStringContainsString('data-breadcrumb-current="'.$current.'"', $html);
        $this->assertStringContainsString('<title>'.$title.'</title>', $html);
        // No page-level h1: the button-row h2 below owns the visible heading.
        // (The sidebar brand mark is an h1 too, so pin the heading scale.)
        $this->assertStringNotContainsString('<h1 class="text-2xl', $html);
        $this->assertStringContainsString('<h2 class="text-xl font-bold text-slate-800">'.$rowHeading.'</h2>', $html);
    }

    private function assertTwoLevelHeading(string $html, string $parent, string $current, string $title, string $pageHeading, string $rowHeading): void
    {
        $this->assertStringContainsString('data-breadcrumb-parent="'.$parent.'"', $html);
        $this->assertStringContainsString('data-breadcrumb-current="'.$current.'"', $html);
        $this->assertStringContainsString('<title>'.$title.'</title>', $html);
        $this->assertStringContainsString('<h1 class="text-2xl font-bold text-slate-800 mb-6">'.$pageHeading.'</h1>', $html);
        $this->assertStringContainsString('<h2 class="text-xl font-bold text-slate-800">'.$rowHeading.'</h2>', $html);
    }

    public function test_staff_tugas_page_polish(): void
    {
        $this->assertPagePolish(
            $this->getPage($this->staff, '/tugas'),
            'Dashboard', 'Daftar Tugas', 'Daftar Tugas - PT Silindo', 'Daftar Tugas'
        );
    }

    public function test_staff_pesan_page_polish(): void
    {
        $this->assertPagePolish(
            $this->getPage($this->staff, '/pesan'),
            'Dashboard', 'Daftar Pesan', 'Daftar Pesan - PT Silindo', 'Daftar Pesan'
        );
    }

    public function test_staff_profile_page_polish(): void
    {
        $this->assertPagePolish(
            $this->getPage($this->staff, '/profile'),
            'Dashboard', 'Profil Saya', 'Profil Saya - PT Silindo'
        );
    }

    public function test_kadiv_tugas_page_polish(): void
    {
        $this->assertRowHeading(
            $this->getPage($this->kadiv, '/kadiv/tugas'),
            'Dashboard', 'Daftar Tugas', 'Daftar Tugas - PT Silindo', 'Daftar Tugas'
        );
    }

    public function test_kadiv_pesan_page_polish(): void
    {
        $this->assertRowHeading(
            $this->getPage($this->kadiv, '/kadiv/pesan'),
            'Dashboard', 'Daftar Pesan', 'Daftar Pesan - PT Silindo', 'Daftar Pesan'
        );
    }

    public function test_kadiv_manajemen_staff_page_polish(): void
    {
        $this->assertTwoLevelHeading(
            $this->getPage($this->kadiv, '/kadiv/manajemenStaff'),
            'Dashboard', 'Manajemen Staff', 'Manajemen Staff - PT Silindo', 'Manajemen Staff', 'Daftar Staff'
        );
    }

    public function test_kadiv_profile_page_polish(): void
    {
        $this->assertPagePolish(
            $this->getPage($this->kadiv, '/kadiv/profile'),
            'Dashboard', 'Profil Saya', 'Profil Kadiv - PT Silindo'
        );
    }

    public function test_hrd_pesan_page_polish(): void
    {
        $this->assertRowHeading(
            $this->getPage($this->hrd, '/hrd/pesan'),
            'Dashboard', 'Daftar Pesan', 'Daftar Pesan - PT Silindo', 'Daftar Pesan'
        );
    }

    public function test_hrd_daftar_karyawan_page_polish(): void
    {
        $this->assertTwoLevelHeading(
            $this->getPage($this->hrd, '/hrd/daftarKaryawan'),
            'Dashboard', 'Manajemen Karyawan', 'Manajemen Karyawan - PT Silindo', 'Manajemen Karyawan', 'Daftar Karyawan'
        );
    }

    public function test_hrd_daftar_divisi_page_polish(): void
    {
        $this->assertRowHeading(
            $this->getPage($this->hrd, '/hrd/daftarDivisi'),
            'Dashboard', 'Daftar Divisi', 'Daftar Divisi - PT Silindo', 'Daftar Divisi'
        );
    }

    public function test_hrd_daftar_pesan_page_polish(): void
    {
        $this->assertRowHeading(
            $this->getPage($this->hrd, '/hrd/daftarPesan'),
            'Dashboard', 'Daftar Pesan', 'Daftar Pesan - PT Silindo', 'Daftar Pesan'
        );
    }

    public function test_hrd_manajemen_divisi_page_polish(): void
    {
        $this->assertTwoLevelHeading(
            $this->getPage($this->hrd, '/hrd/manajemenDivisi'),
            'Dashboard', 'Manajemen Divisi', 'Manajemen Divisi - PT Silindo', 'Manajemen Divisi', 'Daftar Divisi'
        );
    }

    public function test_hrd_profile_page_polish(): void
    {
        $this->assertPagePolish(
            $this->getPage($this->hrd, '/hrd/profile'),
            'Dashboard', 'Profil Saya', 'Profil HRD - PT Silindo'
        );
    }

    public function test_dashboard_titles(): void
    {
        $this->assertStringContainsString(
            '<title>Dashboard - PT Silindo</title>',
            $this->getPage($this->staff, '/')
        );
        $this->assertStringContainsString(
            '<title>Dashboard - PT Silindo</title>',
            $this->getPage($this->kadiv, '/kadiv/dashboard')
        );
        $this->assertStringContainsString(
            '<title>Dashboard - PT Silindo</title>',
            $this->getPage($this->hrd, '/hrd/dashboard')
        );
    }

    public function test_hrd_pesan_keeps_stats_section_label(): void
    {
        $html = $this->getPage($this->hrd, '/hrd/pesan');

        $this->assertSame(1, substr_count($html, 'Pusat Pesan & Komunikasi'));
    }

    public function test_dashboard_compact_tables_keep_labels(): void
    {
        $html = $this->getPage($this->staff, '/');

        $this->assertStringContainsString('Tabel Tugas', $html);
        $this->assertStringContainsString('Tabel Pesan', $html);
    }

    public function test_custom_pagination_view_renders_branded_nav(): void
    {
        $paginator = new LengthAwarePaginator(
            collect([1, 2, 3]), 30, 10, 2, ['path' => 'http://localhost']
        );

        $html = (string) $paginator->links('vendor.pagination.custom');

        $this->assertStringContainsString('data-polish-pagination', $html);
        $this->assertStringContainsString('aria-label="Navigasi halaman"', $html);
        $this->assertStringContainsString('?page=1', $html);
        $this->assertStringContainsString('?page=3', $html);
        $this->assertStringContainsString('bg-[#004A65]', $html);
    }

    public function test_daftar_karyawan_pagination_uses_custom_view(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            Karyawan::create([
                'id_karyawan' => 'KRY-P'.$i,
                'nama' => 'Pegawai '.$i,
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-IT',
            ]);
        }

        $this->assertStringContainsString(
            'data-polish-pagination',
            $this->getPage($this->hrd, '/hrd/daftarKaryawan')
        );
    }

    public function test_all_livewire_tables_use_custom_pagination_view(): void
    {
        $tables = [
            'resources/views/livewire/hrd/karyawan-table.blade.php',
            'resources/views/livewire/hrd/divisi-table.blade.php',
            'resources/views/livewire/kadiv/tugas-table.blade.php',
            'resources/views/livewire/kadiv/pesan-table.blade.php',
            'resources/views/livewire/kadiv/staff-table.blade.php',
            'resources/views/livewire/staff/tugas-table.blade.php',
            'resources/views/livewire/staff/pesan-table.blade.php',
        ];

        foreach ($tables as $table) {
            $this->assertStringContainsString(
                "links('vendor.pagination.custom')",
                file_get_contents(base_path($table)),
                "Custom pagination view missing in {$table}"
            );
        }
    }
}
