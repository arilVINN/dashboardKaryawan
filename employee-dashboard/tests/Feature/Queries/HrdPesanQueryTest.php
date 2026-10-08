<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use App\Queries\HrdPesanQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdPesanQueryTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);

        $this->account('KRY-B', 'Budi Santoso', 'budisatu');
        $this->account('KRY-S', 'Sari Wulandari', 'sarisatu');

        // HRD → Budi (keluar, read).
        $this->pesan('P-1', 'Tugas Selesai', 'USR-HRD', 'USR-KRY-B', '2026-09-01', 'pesan', 'dibaca');
        // Budi → HRD (masuk, unread).
        $this->pesan('P-2', 'Butuh Revisi', 'USR-KRY-B', 'USR-HRD', '2026-10-01', 'pesan', 'belum_dibaca');
        // Budi → Sari (staff→staff: HRD still sees it — global scope).
        $this->pesan('P-3', 'Rapat Surat', 'USR-KRY-B', 'USR-KRY-S', '2026-10-05', 'surat', 'belum_dibaca');
    }

    private function account(string $karyawan, string $nama, string $username): void
    {
        Karyawan::create([
            'id_karyawan' => $karyawan,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        User2::create([
            'id_user' => 'USR-'.$karyawan,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $karyawan,
        ]);
    }

    private function pesan(string $id, string $judul, string $pengirim, string $penerima, string $tanggal, string $tipe, string $status): void
    {
        // `status` is not mass-assignable, so set it outside `create`.
        $pesan = Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $judul,
            'deskripsi' => $judul.' body',
            'tipe' => $tipe,
            'tanggal_pesan' => $tanggal,
            'pengirim_id_user' => $pengirim,
            'penerima_id_user' => $penerima,
        ]);
        $pesan->forceFill(['status' => $status])->save();
    }

    private function ids(array $filters): array
    {
        return HrdPesanQuery::forHrd($this->hrd)->apply($filters)->pluck('id_pesan')->all();
    }

    public function test_defaults_to_newest_first_and_sees_all_messages(): void
    {
        $this->assertSame(['P-3', 'P-2', 'P-1'], $this->ids([]));
    }

    public function test_filters_by_arah_relative_to_hrd(): void
    {
        $this->assertSame(['P-2'], $this->ids(['arah' => 'masuk']));
        $this->assertSame(['P-1'], $this->ids(['arah' => 'keluar']));
    }

    public function test_filters_by_tipe_and_status(): void
    {
        $this->assertSame(['P-3'], $this->ids(['tipe' => 'surat']));
        $this->assertSame(['P-1'], $this->ids(['status' => 'dibaca']));
    }

    public function test_searches_judul_and_contact_names_case_insensitively(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        // 'SARI' matches the penerima nama of P-3 only.
        $this->assertSame(['P-3'], $this->ids(['q' => 'SARI']));

        $this->assertSame(['P-2'], $this->ids(['q' => 'revisi']));
    }

    public function test_whitespace_only_q_returns_everything(): void
    {
        $this->assertSame(['P-3', 'P-2', 'P-1'], $this->ids(['q' => '   ']));
    }

    public function test_invalid_sort_and_dir_fall_back_to_default_order(): void
    {
        $this->assertSame(['P-3', 'P-2', 'P-1'], $this->ids(['sort' => 'DROP', 'dir' => 'x']));
    }

    public function test_sorts_by_judul(): void
    {
        $this->assertSame(['P-2', 'P-3', 'P-1'], $this->ids(['sort' => 'judul', 'dir' => 'asc']));
    }
}
