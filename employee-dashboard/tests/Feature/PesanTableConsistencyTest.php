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

class PesanTableConsistencyTest extends TestCase
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

        $this->hrd = $this->createAccount('KRY-HRD', 'HRD User', 'ROLE-HRD', 'hrdseragam');
        $this->staff = $this->createAccount('KRY-STAFF', 'Staff User', 'ROLE-STAFF', 'staffseragam');
        $this->kadiv = $this->createAccount('KRY-KADIV', 'Kadiv User', 'ROLE-KADIV', 'kadivseragam');

        $this->makePesan('PSN-IN', 'Laporan Masuk', 'USR-KRY-STAFF', 'USR-KRY-HRD', '2026-10-06', '2026-10-09 09:05:00');
        $this->makePesan('PSN-OUT', 'Balasan Keluar', 'USR-KRY-HRD', 'USR-KRY-STAFF', '2026-10-08', '2026-10-08 12:56:00');
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

    private function tableBody(string $html): string
    {
        $start = strpos($html, '<tbody');
        $end = strpos($html, '</tbody>');

        return substr($html, $start, $end - $start);
    }

    private function makePesan(string $id, string $judul, string $pengirim, string $penerima, string $tanggal, string $dibuat): Pesan
    {
        $pesan = Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $judul,
            'deskripsi' => 'Isi '.$judul,
            'tipe' => 'pesan',
            'tanggal_pesan' => $tanggal,
            'pengirim_id_user' => $pengirim,
            'penerima_id_user' => $penerima,
        ]);
        $pesan->created_at = $dibuat;
        $pesan->save();

        return $pesan->fresh();
    }

    public function test_format_tanggal_jam(): void
    {
        $this->assertSame('8 Okt 2026', Pesan::formatTanggalJam('2026-10-08'));
        $this->assertSame('8 Okt 2026 12:56', Pesan::formatTanggalJam('2026-10-08', '2026-10-08 12:56:29'));
        // Instants render in the app timezone (Asia/Jakarta): 09:05 UTC is 16:05 WIB.
        $this->assertSame('6 Okt 2026 16:05', Pesan::formatTanggalJam('2026-10-06', '2026-10-06T09:05:00.000000Z'));
        $this->assertSame('-', Pesan::formatTanggalJam(null));
        $this->assertSame('-', Pesan::formatTanggalJam('bukan-tanggal'));
    }

    public function test_hrd_table_drops_jenis_column(): void
    {
        $html = $this->actingAs($this->hrd)->get('/hrd/daftarPesan')->assertOk()->getContent();

        $this->assertStringNotContainsString('>Jenis<', $html);
        $this->assertStringNotContainsString('bg-cyan-50', $html);
    }

    public function test_hrd_table_shows_direction_pills_and_datetime(): void
    {
        $html = $this->actingAs($this->hrd)->get('/hrd/daftarPesan')->assertOk()->getContent();

        $this->assertStringContainsString('>Arah<', $html);
        $this->assertStringContainsString('>Masuk</span>', $html);
        $this->assertStringContainsString('>Keluar</span>', $html);
        $this->assertStringContainsString('8 Okt 2026 12:56', $html);
        $this->assertStringNotContainsString('2026-10-08', $html);
    }

    public function test_hrd_table_search_filters_rows(): void
    {
        $body = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?q=balasan')->assertOk()->getContent());

        $this->assertStringContainsString('Balasan Keluar', $body);
        $this->assertStringNotContainsString('Laporan Masuk', $body);
    }

    public function test_hrd_table_arah_filter(): void
    {
        $masuk = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?arah=masuk')->assertOk()->getContent());
        $this->assertStringContainsString('Laporan Masuk', $masuk);
        $this->assertStringNotContainsString('Balasan Keluar', $masuk);

        $keluar = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?arah=keluar')->assertOk()->getContent());
        $this->assertStringContainsString('Balasan Keluar', $keluar);
        $this->assertStringNotContainsString('Laporan Masuk', $keluar);
    }

    public function test_third_party_messages_count_as_masuk(): void
    {
        $this->makePesan('PSN-3RD', 'Pihak Ketiga', 'USR-KRY-STAFF', 'USR-KRY-KADIV', '2026-10-03', '2026-10-03 10:00:00');

        $masuk = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?arah=masuk')->assertOk()->getContent());
        $this->assertStringContainsString('Laporan Masuk', $masuk);
        $this->assertStringContainsString('Pihak Ketiga', $masuk);
        $this->assertStringNotContainsString('Balasan Keluar', $masuk);

        $keluar = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?arah=keluar')->assertOk()->getContent());
        $this->assertStringContainsString('Balasan Keluar', $keluar);
        $this->assertStringNotContainsString('Pihak Ketiga', $keluar);

        $stats = $this->actingAs($this->hrd)->get('/hrd/pesan')->assertOk()->getContent();
        $this->assertSame('3', $this->statCard($stats, 'Total Pesan'));
        $this->assertSame('2', $this->statCard($stats, 'Pesan Masuk'));
        $this->assertSame('1', $this->statCard($stats, 'Pesan Keluar'));
    }

    private function statCard(string $html, string $label): ?string
    {
        if (preg_match('/'.preg_quote($label, '/').'<\/span>\s*<span[^>]*>(\d+)<\/span>/', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    public function test_hrd_table_sorts(): void
    {
        $byJudul = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?sort=judul&dir=asc')->assertOk()->getContent());
        $this->assertLessThan(strpos($byJudul, 'Laporan Masuk'), strpos($byJudul, 'Balasan Keluar'));

        $byTanggal = $this->tableBody($this->actingAs($this->hrd)->get('/hrd/daftarPesan?sort=tanggal&dir=desc')->assertOk()->getContent());
        $this->assertLessThan(strpos($byTanggal, 'Laporan Masuk'), strpos($byTanggal, 'Balasan Keluar'));
    }

    public function test_kadiv_table_shows_formatted_datetime(): void
    {
        $this->makePesan('PSN-K', 'Arahan Kadiv', 'USR-KRY-KADIV', 'USR-KRY-STAFF', '2026-10-08', '2026-10-08 12:56:00');

        $html = $this->actingAs($this->kadiv)->get('/kadiv/pesan')->assertOk()->getContent();

        $this->assertStringContainsString('8 Okt 2026 12:56', $html);
    }

    public function test_staff_api_includes_waktu_for_threads(): void
    {
        Tugas::create([
            'id_tugas' => 'TGS-1',
            'karyawan_id_karyawan' => 'KRY-STAFF',
            'judul_tugas' => 'Tugas Contoh',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => '2026-10-01',
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-T',
            'judul_pesan' => 'Update',
            'deskripsi' => 'Isi.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-07',
            'pengirim_id_user' => 'USR-KRY-KADIV',
            'penerima_id_user' => 'USR-KRY-STAFF',
            'tugas_id_tugas' => 'TGS-1',
            'tugas_karyawan_id_karyawan' => 'KRY-STAFF',
        ]);

        $this->actingAs($this->staff)
            ->getJson('/api/staff/pesan')
            ->assertOk()
            ->assertJsonPath('data.0.pesan_terakhir.created_at', fn ($v) => $v !== null);
    }

    public function test_staff_table_template_renders_jenis_pill_and_jam(): void
    {
        $source = file_get_contents(base_path('resources/views/component/tablePesan.blade.php'));

        $this->assertStringContainsString('${p.jenisLabel}', $source);
        $this->assertStringContainsString('bg-sky-100', $source);
        $this->assertStringContainsString('bg-emerald-100', $source);
        $this->assertStringContainsString('getHours', $source);
    }
}
