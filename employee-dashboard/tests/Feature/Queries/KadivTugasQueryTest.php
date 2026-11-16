<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Queries\KadivTugasQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KadivTugasQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'ST-1', 'nama' => 'Staff One', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'ST-2', 'nama' => 'Staff Two', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);

        $this->makeTask('T-1', 'ST-1', 'Gamma', Tugas::STATUS_BARU);
        $this->makeTask('T-2', 'ST-2', 'Alpha', Tugas::STATUS_BERJALAN);
        $this->makeTask('T-3', 'ST-1', 'Beta', Tugas::STATUS_SUDAH_ACC);
    }

    private function makeTask(string $id, string $karyawanId, string $judul, string $status): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_tugas' => $judul,
            'deskripsi' => $judul . ' desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => $status,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_filters_by_status_staff_and_search_and_sorts(): void
    {
        $ids = KadivTugasQuery::forDivision('DIV-IT')->apply(['sort' => 'judul', 'dir' => 'asc'])
            ->pluck('id_tugas')->all();
        $this->assertSame(['T-2', 'T-3', 'T-1'], $ids);

        $this->assertSame(
            ['T-1'],
            KadivTugasQuery::forDivision('DIV-IT')->apply(['status' => 'baru'])->pluck('id_tugas')->all()
        );

        $this->assertSame(
            ['T-3', 'T-1'],
            KadivTugasQuery::forDivision('DIV-IT')->apply(['staff' => 'ST-1', 'sort' => 'judul', 'dir' => 'asc'])
                ->pluck('id_tugas')->all()
        );

        DB::statement('PRAGMA case_sensitive_like = ON');
        $this->assertSame(
            ['T-2'],
            KadivTugasQuery::forDivision('DIV-IT')->apply(['q' => 'alph'])->pluck('id_tugas')->all()
        );

        // wildcard characters must not throw
        KadivTugasQuery::forDivision('DIV-IT')->apply(['q' => '%_%'])->get();
    }

    public function test_invalid_sort_and_dir_fall_back(): void
    {
        $this->assertNotEmpty(
            KadivTugasQuery::forDivision('DIV-IT')->apply(['sort' => 'DROP', 'dir' => 'x'])->get()
        );
    }
}
