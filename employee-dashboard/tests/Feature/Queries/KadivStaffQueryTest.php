<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\KadivStaffQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KadivStaffQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff']]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->account('ST-A', 'Abby', '2026-02-01 08:00:00');
        $this->account('ST-Z', 'Zed', '2026-01-01 08:00:00');

        $this->task('T-1', 'ST-Z');
        $this->task('T-2', 'ST-Z');
        $this->task('T-3', 'ST-A');
    }

    private function account(string $id, string $nama, string $lastLogin): void
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);
        User2::create([
            'id_user' => 'USR-' . $id,
            'username' => strtolower($nama),
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $id,
            'last_login_at' => $lastLogin,
        ]);
    }

    private function task(string $id, string $karyawanId): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_tugas' => 'Tugas ' . $id,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => Tugas::STATUS_BARU,
            'progress' => '0',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_sorts_and_searches(): void
    {
        $this->assertSame(
            ['Abby', 'Zed'],
            KadivStaffQuery::forDivision('DIV-IT')->apply(['sort' => 'nama', 'dir' => 'asc'])->pluck('nama')->all()
        );

        $this->assertSame(
            ['Zed', 'Abby'],
            KadivStaffQuery::forDivision('DIV-IT')->apply(['sort' => 'tugas', 'dir' => 'desc'])->pluck('nama')->all()
        );

        $this->assertSame(
            ['Zed', 'Abby'],
            KadivStaffQuery::forDivision('DIV-IT')->apply(['sort' => 'login', 'dir' => 'asc'])->pluck('nama')->all()
        );

        DB::statement('PRAGMA case_sensitive_like = ON');
        $this->assertSame(
            ['Abby'],
            KadivStaffQuery::forDivision('DIV-IT')->apply(['q' => 'abby'])->pluck('nama')->all()
        );
    }
}
