<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\StaffTugasQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffTugasQueryTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff']]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KRY-S', 'nama' => 'Budi', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);

        $this->staff = User2::create([
            'id_user' => 'USR-S',
            'username' => 'budist',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S',
        ]);

        // Deadlines must be in the future, otherwise 'baru'/'berjalan' fall into 'telat'.
        $this->makeTask('T-2', 'Alpha', Tugas::STATUS_BARU, now()->addDays(10)->toDateString());
        $this->makeTask('T-1', 'Gamma', Tugas::STATUS_BERJALAN, now()->addDays(5)->toDateString());
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

    public function test_sorts_and_filters_staff_tugas(): void
    {
        $this->assertSame(
            ['T-2', 'T-1'],
            StaffTugasQuery::forStaff($this->staff)->apply(['sort' => 'judul', 'dir' => 'asc'])->pluck('id_tugas')->all()
        );

        $this->assertSame(
            ['T-2'],
            StaffTugasQuery::forStaff($this->staff)->apply(['status' => 'baru'])->pluck('id_tugas')->all()
        );

        DB::statement('PRAGMA case_sensitive_like = ON');
        $this->assertSame(
            ['T-2'],
            StaffTugasQuery::forStaff($this->staff)->apply(['q' => 'alph'])->pluck('id_tugas')->all()
        );

        // wildcard characters must not throw
        StaffTugasQuery::forStaff($this->staff)->apply(['q' => '%_%'])->get();
    }

    public function test_whitespace_only_q_is_ignored(): void
    {
        $all = StaffTugasQuery::forStaff($this->staff)->apply([])->pluck('id_tugas')->all();

        $this->assertCount(2, $all);
        $this->assertSame(
            $all,
            StaffTugasQuery::forStaff($this->staff)->apply(['q' => '   '])->pluck('id_tugas')->all()
        );
    }
}
