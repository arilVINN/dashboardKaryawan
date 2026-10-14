<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Queries\HrdDivisiQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrdDivisiQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff']]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Sumber Daya Manusia', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Operasional', 'status_aktif' => 'Nonaktif']);

        // DIV-IT: 2 staff, DIV-HR: 1 staff, DIV-OPS: 1 staff.
        $this->staff('KRY-A', 'DIV-IT');
        $this->staff('KRY-B', 'DIV-IT');
        $this->staff('KRY-C', 'DIV-HR');
        $this->staff('KRY-D', 'DIV-OPS');
    }

    private function staff(string $id, string $divisiId): void
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => 'Staff '.$id,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
        ]);
    }

    private function ids(array $filters): array
    {
        return (new HrdDivisiQuery)->apply($filters)->pluck('id_divisi')->all();
    }

    public function test_defaults_to_id_desc(): void
    {
        $this->assertSame(['DIV-OPS', 'DIV-IT', 'DIV-HR'], $this->ids([]));
    }

    public function test_filters_by_status(): void
    {
        $this->assertSame(['DIV-IT', 'DIV-HR'], $this->ids(['status' => 'aktif']));
        $this->assertSame(['DIV-OPS'], $this->ids(['status' => 'nonaktif']));
    }

    public function test_sorts_by_kode_nama_and_staff_count(): void
    {
        $this->assertSame(['DIV-HR', 'DIV-IT', 'DIV-OPS'], $this->ids(['sort' => 'kode', 'dir' => 'asc']));
        $this->assertSame(['DIV-OPS', 'DIV-HR', 'DIV-IT'], $this->ids(['sort' => 'nama', 'dir' => 'asc']));

        // DIV-IT has 2 staff; DIV-HR/DIV-OPS tie on 1 (broken by id_divisi asc).
        $this->assertSame(['DIV-IT', 'DIV-HR', 'DIV-OPS'], $this->ids(['sort' => 'staff', 'dir' => 'desc']));
    }

    public function test_searches_kode_or_nama(): void
    {
        $this->assertSame(['DIV-OPS'], $this->ids(['q' => 'Operasional']));
        $this->assertSame(['DIV-IT'], $this->ids(['q' => 'IT']));
    }

    public function test_search_is_case_insensitive(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $this->assertSame(['DIV-OPS'], $this->ids(['q' => 'operasional']));
        $this->assertSame(['DIV-IT'], $this->ids(['q' => 'it']));
    }

    public function test_whitespace_only_q_is_ignored(): void
    {
        $all = $this->ids([]);

        $this->assertSame($all, $this->ids(['q' => '   ']));
    }

    public function test_wildcard_characters_do_not_throw(): void
    {
        // Wildcards are passed through as-is; the only contract is not throwing.
        $this->assertIsArray($this->ids(['q' => '%_%']));
    }
}
