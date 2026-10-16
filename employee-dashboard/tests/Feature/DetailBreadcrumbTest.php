<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DetailBreadcrumbTest extends TestCase
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

        $this->hrd = $this->createAccount('KRY-HRD', 'HRD User', 'ROLE-HRD', 'hrdcrumb');
        $this->staff = $this->createAccount('KRY-STAFF', 'Staff User', 'ROLE-STAFF', 'staffcrumb');
        $this->kadiv = $this->createAccount('KRY-KADIV', 'Kadiv User', 'ROLE-KADIV', 'kadivcrumb');

        Tugas::create([
            'id_tugas' => 'TGS-1',
            'karyawan_id_karyawan' => 'KRY-STAFF',
            'judul_tugas' => 'Tugas Contoh',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => '2026-10-01',
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-1',
            'judul_pesan' => 'Pesan Contoh',
            'deskripsi' => 'Isi pesan.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-01',
            'pengirim_id_user' => 'USR-KRY-STAFF',
            'penerima_id_user' => 'USR-KRY-KADIV',
        ]);
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

    private function assertParent(string $html, string $parentText, string $parentUrl, string $currentPage): void
    {
        $this->assertStringContainsString('data-breadcrumb-parent="'.$parentText.'"', $html);
        $this->assertStringContainsString('href="'.$parentUrl.'"', $html);
        $this->assertStringContainsString('data-breadcrumb-current="'.$currentPage.'"', $html);
    }

    private function getPage(User2 $user, string $url): string
    {
        return $this->actingAs($user)->get($url)->assertOk()->getContent();
    }

    public function test_hrd_division_detail_parent_matches_origin(): void
    {
        $base = $this->getPage($this->hrd, '/hrd/detailDivisi/DIV-IT');
        $this->assertParent($base, 'Manajemen Divisi', url('/hrd/manajemenDivisi'), 'Detail Divisi');

        $daftar = $this->getPage($this->hrd, '/hrd/detailDivisi/DIV-IT?from=daftarDivisi');
        $this->assertParent($daftar, 'Daftar Divisi', url('/hrd/daftarDivisi'), 'Detail Divisi');

        $dash = $this->getPage($this->hrd, '/hrd/detailDivisi/DIV-IT?from=dashboard');
        $this->assertParent($dash, 'Dashboard', url('/hrd/dashboard'), 'Detail Divisi');

        $evil = $this->getPage($this->hrd, '/hrd/detailDivisi/DIV-IT?from=https://evil.test');
        $this->assertParent($evil, 'Manajemen Divisi', url('/hrd/manajemenDivisi'), 'Detail Divisi');
    }

    public function test_hrd_pesan_detail_parent_matches_origin(): void
    {
        $base = $this->getPage($this->hrd, '/hrd/detailPesan/PSN-1');
        $this->assertParent($base, 'Pesan', url('/hrd/pesan'), 'Detail Pesan');

        $daftar = $this->getPage($this->hrd, '/hrd/detailPesan/PSN-1?from=daftarPesan');
        $this->assertParent($daftar, 'Daftar Pesan', url('/hrd/daftarPesan'), 'Detail Pesan');
    }

    public function test_staff_detail_parents_match_origin(): void
    {
        $tugas = $this->getPage($this->staff, '/tugas/detail/TGS-1');
        $this->assertParent($tugas, 'Tugas', url('/tugas'), 'Detail Tugas');

        $tugasDash = $this->getPage($this->staff, '/tugas/detail/TGS-1?from=dashboard');
        $this->assertParent($tugasDash, 'Dashboard', url('/'), 'Detail Tugas');

        $pesan = $this->getPage($this->staff, '/pesan/detail/PSN-1');
        $this->assertParent($pesan, 'Pesan', url('/pesan'), 'Detail Pesan');

        $pesanDash = $this->getPage($this->staff, '/pesan/detail/PSN-1?from=dashboard');
        $this->assertParent($pesanDash, 'Dashboard', url('/'), 'Detail Pesan');
    }

    public function test_kadiv_tugas_detail_parent_matches_origin(): void
    {
        $base = $this->getPage($this->kadiv, '/kadiv/detailTugas/TGS-1');
        $this->assertParent($base, 'Tugas', url('/kadiv/tugas'), 'Detail Tugas');

        $dash = $this->getPage($this->kadiv, '/kadiv/detailTugas/TGS-1?from=dashboard');
        $this->assertParent($dash, 'Dashboard', url('/kadiv/dashboard'), 'Detail Tugas');

        $staff = $this->getPage($this->kadiv, '/kadiv/detailTugas/TGS-1?from=staffDetail&staff=KRY-STAFF');
        $this->assertParent($staff, 'Detail Staff', url('/kadiv/manajemenStaff/KRY-STAFF'), 'Detail Tugas');
    }

    public function test_kadiv_pesan_detail_parent_matches_origin(): void
    {
        $base = $this->getPage($this->kadiv, '/kadiv/detailPesan/PSN-1');
        $this->assertParent($base, 'Pesan', url('/kadiv/pesan'), 'Detail Pesan');

        $dash = $this->getPage($this->kadiv, '/kadiv/detailPesan/PSN-1?from=dashboard');
        $this->assertParent($dash, 'Dashboard', url('/kadiv/dashboard'), 'Detail Pesan');
    }

    public function test_list_pages_emit_origin_param(): void
    {
        $this->assertStringContainsString(
            'detailDivisi/DIV-IT?from=daftarDivisi',
            $this->getPage($this->hrd, '/hrd/daftarDivisi')
        );
        $this->assertStringContainsString(
            'detailDivisi/DIV-IT?from=manajemenDivisi',
            $this->getPage($this->hrd, '/hrd/manajemenDivisi')
        );
        $this->assertStringContainsString(
            '?from=dashboard',
            $this->getPage($this->hrd, '/hrd/dashboard')
        );
        $this->assertStringContainsString(
            'detailPesan/PSN-1?from=daftarPesan',
            $this->getPage($this->hrd, '/hrd/daftarPesan')
        );
        $this->assertStringContainsString(
            '?from=dashboard',
            $this->getPage($this->staff, '/')
        );
        $this->assertStringNotContainsString(
            '?from=dashboard',
            $this->getPage($this->staff, '/tugas')
        );
        $this->assertStringContainsString(
            '?from=dashboard',
            $this->getPage($this->kadiv, '/kadiv/dashboard')
        );
        $this->assertStringContainsString(
            'from=staffDetail',
            $this->getPage($this->kadiv, '/kadiv/manajemenStaff/KRY-STAFF')
        );
    }
}
