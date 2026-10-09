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

    private User2 $hrdDua;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->hrd = $this->account('KRY-HRD', 'HRD User', 'ROLE-HRD', 'hrduser');
        $this->hrdDua = $this->account('KRY-H2', 'HRD Dua', 'ROLE-HRD', 'hrddua');
        $this->account('KRY-B', 'Budi Santoso', 'ROLE-STAFF', 'budisatu');
        $this->account('KRY-S', 'Sari Wulandari', 'ROLE-STAFF', 'sarisatu');

        // HRD → Budi (keluar, read, surat).
        $this->pesan('P-1', 'Tugas Selesai', 'USR-KRY-HRD', 'USR-KRY-B', '2026-09-01', 'surat', 'dibaca');
        // Budi → HRD (masuk, unread).
        $this->pesan('P-2', 'Butuh Revisi', 'USR-KRY-B', 'USR-KRY-HRD', '2026-10-01', 'pesan', 'belum_dibaca');
        // Budi → Sari (third-party: invisible to HRD).
        $this->pesan('P-3', 'Rapat Surat', 'USR-KRY-B', 'USR-KRY-S', '2026-10-05', 'surat', 'belum_dibaca');
        // HRD-dua → Budi (another HRD's thread: invisible to hrduser).
        $this->pesan('P-X', 'Jadwal Briefing', 'USR-KRY-H2', 'USR-KRY-B', '2026-10-06', 'pesan', 'belum_dibaca');
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $nama,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    private function pesan(string $id, string $judul, string $pengirim, string $penerima, string $tanggal, string $tipe, string $status, ?string $balasanDari = null): void
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
            'balasan_dari_id_pesan' => $balasanDari,
        ]);
        $pesan->forceFill(['status' => $status])->save();
    }

    private function ids(array $filters): array
    {
        return HrdPesanQuery::forHrd($this->hrd)->apply($filters)->pluck('id_pesan')->all();
    }

    public function test_defaults_to_newest_first_and_hides_third_party_messages(): void
    {
        $this->assertSame(['P-2', 'P-1'], $this->ids([]));
    }

    public function test_excludes_other_hrds_messages(): void
    {
        $this->assertNotContains('P-X', $this->ids([]));

        $duaIds = HrdPesanQuery::forHrd($this->hrdDua)->apply([])->pluck('id_pesan')->all();
        $this->assertSame(['P-X'], $duaIds);
    }

    public function test_filters_by_arah_relative_to_hrd(): void
    {
        $this->assertSame(['P-2'], $this->ids(['arah' => 'masuk']));
        $this->assertSame(['P-1'], $this->ids(['arah' => 'keluar']));
    }

    public function test_filters_by_tipe_and_status(): void
    {
        $this->assertSame(['P-1'], $this->ids(['tipe' => 'surat']));
        $this->assertSame(['P-2'], $this->ids(['tipe' => 'pesan']));
        $this->assertSame(['P-1'], $this->ids(['status' => 'dibaca']));
    }

    public function test_searches_without_leaking_third_party_messages(): void
    {
        // SQLite LIKE is case-insensitive by default; turn that off so this
        // test reproduces Postgres and fails if search is not normalised.
        DB::statement('PRAGMA case_sensitive_like = ON');

        $this->assertSame(['P-2'], $this->ids(['q' => 'revisi']));

        // Budi is pengirim of P-2 and penerima of P-1 (P-X is not mine).
        $this->assertSame(['P-2', 'P-1'], $this->ids(['q' => 'BUDI']));

        // Sari only appears in the third-party P-3: nothing may leak.
        $this->assertSame([], $this->ids(['q' => 'SARI']));
    }

    public function test_whitespace_only_q_returns_participant_messages(): void
    {
        $this->assertSame(['P-2', 'P-1'], $this->ids(['q' => '   ']));
    }

    public function test_invalid_sort_and_dir_fall_back_to_default_order(): void
    {
        $this->assertSame(['P-2', 'P-1'], $this->ids(['sort' => 'DROP', 'dir' => 'x']));
    }

    public function test_sorts_by_judul(): void
    {
        $this->assertSame(['P-2', 'P-1'], $this->ids(['sort' => 'judul', 'dir' => 'asc']));
    }

    public function test_reply_addressed_to_me_is_visible_despite_third_party_root(): void
    {
        $this->pesan('P-R', 'Akar Thread', 'USR-KRY-B', 'USR-KRY-S', '2026-08-01', 'pesan', 'belum_dibaca');
        $this->pesan('P-R1', 'Balasan Untuk Saya', 'USR-KRY-S', 'USR-KRY-HRD', '2026-08-02', 'pesan', 'belum_dibaca', 'P-R');

        $this->assertSame(['P-2', 'P-1', 'P-R1'], $this->ids([]));
    }
}
