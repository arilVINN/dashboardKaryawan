<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\StaffPesanQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffPesanQueryTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->staff = $this->account('KRY-S', 'USR-S', 'budist', 'ROLE-STAFF');
        $abby = $this->account('KRY-A', 'USR-A', 'abby', 'ROLE-KADIV');
        $zed = $this->account('KRY-Z', 'USR-Z', 'zed', 'ROLE-KADIV');

        // Tugas thread: two pesans, so latestPesan must resolve to the newer one.
        $this->task('T-1', 'Desain Logo');
        $this->task('T-2', 'Tugas Tanpa Pesan');
        $this->pesan('P-T0', 'Versi Awal', $abby->id_user, $this->staff->id_user, '2026-09-20', 'T-1');
        $this->pesan('P-T1', 'Versi Revisi', $abby->id_user, $this->staff->id_user, '2026-10-01', 'T-1');

        // Direct message to the staff (no tugas) — newer than the thread latest.
        $this->pesan('P-D1', 'Rapat Mingguan', $zed->id_user, $this->staff->id_user, '2026-10-02', null);
    }

    private function account(string $karyawan, string $user, string $username, string $role): User2
    {
        Karyawan::create([
            'id_karyawan' => $karyawan,
            'nama' => $username,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $username,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => $user,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $karyawan,
        ]);
    }

    private function task(string $id, string $judul): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => $judul,
            'deskripsi' => $judul.' desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    private function pesan(string $id, string $judul, string $pengirim, string $penerima, string $tanggal, ?string $tugasId): void
    {
        Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $judul,
            'deskripsi' => $judul.' body',
            'tipe' => 'pesan',
            'tanggal_pesan' => $tanggal,
            'tugas_id_tugas' => $tugasId,
            'tugas_karyawan_id_karyawan' => $tugasId ? 'KRY-S' : null,
            'pengirim_id_user' => $pengirim,
            'penerima_id_user' => $penerima,
        ]);
    }

    public function test_unified_merges_threads_and_direct_messages_with_expected_shape(): void
    {
        $items = StaffPesanQuery::forStaff($this->staff)->unified([]);

        // Default sort: tanggal desc → direct (10-02) before thread latest (10-01).
        // T-2 has no pesan and must be excluded.
        $this->assertSame(['langsung', 'tugas'], $items->pluck('jenis')->all());

        $tugas = $items->firstWhere('jenis', 'tugas');
        $this->assertSame(
            [
                'jenis' => 'tugas',
                'link_id' => 'T-1',
                'judul' => 'Tugas: Desain Logo',
                'pengirim' => 'abby',
                'tanggal_raw' => '2026-10-01',
                'tanggal' => '1 Okt 2026',
            ],
            $tugas
        );

        $langsung = $items->firstWhere('jenis', 'langsung');
        $this->assertSame(
            [
                'jenis' => 'langsung',
                'link_id' => 'P-D1',
                'judul' => 'Rapat Mingguan',
                'pengirim' => 'zed',
                'tanggal_raw' => '2026-10-02',
                'tanggal' => '2 Okt 2026',
            ],
            $langsung
        );
    }

    public function test_filters_by_jenis(): void
    {
        $items = StaffPesanQuery::forStaff($this->staff)->unified(['jenis' => 'langsung']);

        $this->assertSame(['langsung'], $items->pluck('jenis')->all());
        $this->assertSame(['P-D1'], $items->pluck('link_id')->all());
    }

    public function test_filters_by_q_over_judul_and_pengirim(): void
    {
        $this->assertSame(
            ['P-D1'],
            StaffPesanQuery::forStaff($this->staff)->unified(['q' => 'rapat'])->pluck('link_id')->all()
        );

        $this->assertSame(
            ['T-1'],
            StaffPesanQuery::forStaff($this->staff)->unified(['q' => 'abby'])->pluck('link_id')->all()
        );
    }

    public function test_sorts_by_pengirim_ascending(): void
    {
        $items = StaffPesanQuery::forStaff($this->staff)->unified(['sort' => 'pengirim', 'dir' => 'asc']);

        // abby (thread) before zed (direct).
        $this->assertSame(['tugas', 'langsung'], $items->pluck('jenis')->all());
    }

    public function test_whitespace_only_q_still_filters(): void
    {
        // The old client-side search filtered on the raw (untrimmed) value,
        // so only an empty string skips the filter; whitespace-only filters to nothing.
        $this->assertCount(2, StaffPesanQuery::forStaff($this->staff)->unified([]));
        $this->assertCount(2, StaffPesanQuery::forStaff($this->staff)->unified(['q' => '']));
        $this->assertCount(0, StaffPesanQuery::forStaff($this->staff)->unified(['q' => '   ']));
    }
}
