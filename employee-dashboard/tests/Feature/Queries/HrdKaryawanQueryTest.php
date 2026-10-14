<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\HrdKaryawanQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdKaryawanQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff']]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Sumber Daya Manusia', 'status_aktif' => 'Aktif']);

        $this->staff('KRY-A', 'Andi Wijaya', 'DIV-IT', 'Software Engineer', true);
        $this->staff('KRY-B', 'Budi Santoso', 'DIV-HR', 'HR Specialist', true);
        $this->staff('KRY-C', 'Citra Lestari', 'DIV-IT', 'Analyst', false);
        $this->staff('KRY-D', 'Dedi Kurniawan', 'DIV-HR', 'Operator', false);

        $this->task('T-1', 'KRY-A');
        $this->task('T-2', 'KRY-A');
        $this->task('T-3', 'KRY-C');
    }

    private function staff(string $id, string $nama, string $divisiId, string $jabatan, bool $hasUser): void
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_rekrut' => '2026-01-01',
            'no_telepon' => '081234567890',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisiId,
        ]);

        if (! $hasUser) {
            return;
        }

        User2::create([
            'id_user' => 'USR-'.$id,
            'username' => strtolower($nama),
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $id,
        ]);
    }

    private function task(string $id, string $karyawanId): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_tugas' => 'Tugas '.$id,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => Tugas::STATUS_BARU,
            'progress' => '0',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    private function ids(array $filters): array
    {
        return (new HrdKaryawanQuery)->apply($filters)->pluck('id_karyawan')->all();
    }

    public function test_filters_by_divisi_jabatan_and_status(): void
    {
        $this->assertSame(['KRY-A', 'KRY-C'], $this->ids(['divisi' => 'DIV-IT']));
        $this->assertSame(['KRY-C'], $this->ids(['jabatan' => 'Analyst']));
        $this->assertSame(['KRY-A', 'KRY-B'], $this->ids(['status' => 'aktif']));
        $this->assertSame(['KRY-C', 'KRY-D'], $this->ids(['status' => 'belum']));
    }

    public function test_sorts_by_id_nama_divisi_and_jabatan(): void
    {
        $this->assertSame(['KRY-D', 'KRY-C', 'KRY-B', 'KRY-A'], $this->ids(['sort' => 'id', 'dir' => 'desc']));
        $this->assertSame(['KRY-A', 'KRY-B', 'KRY-C', 'KRY-D'], $this->ids(['sort' => 'nama', 'dir' => 'asc']));

        // Sumber Daya Manusia < Teknologi Informasi (tie broken by id asc).
        $this->assertSame(['KRY-B', 'KRY-D', 'KRY-A', 'KRY-C'], $this->ids(['sort' => 'divisi', 'dir' => 'asc']));

        $this->assertSame(['KRY-C', 'KRY-B', 'KRY-D', 'KRY-A'], $this->ids(['sort' => 'jabatan', 'dir' => 'asc']));
    }

    public function test_loads_relations_and_tugas_count(): void
    {
        $karyawan = (new HrdKaryawanQuery)->apply(['divisi' => 'DIV-IT', 'sort' => 'id'])->get();

        $this->assertTrue($karyawan->first()->relationLoaded('divisi'));
        $this->assertTrue($karyawan->first()->relationLoaded('user'));
        $this->assertSame(2, (int) $karyawan->firstWhere('id_karyawan', 'KRY-A')->tugas_count);
        $this->assertSame(1, (int) $karyawan->firstWhere('id_karyawan', 'KRY-C')->tugas_count);
    }

    public function test_searches_name_case_insensitively(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $this->assertSame(['KRY-B'], $this->ids(['q' => 'budi']));
    }

    public function test_whitespace_only_filters_are_ignored(): void
    {
        $all = $this->ids([]);

        $this->assertSame($all, $this->ids(['q' => '   ']));
        $this->assertSame($all, $this->ids(['divisi' => '   ']));
        $this->assertSame($all, $this->ids(['jabatan' => '   ']));
    }
}
