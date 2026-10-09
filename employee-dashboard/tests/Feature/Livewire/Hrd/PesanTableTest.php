<?php

namespace Tests\Feature\Livewire\Hrd;

use App\Livewire\Hrd\PesanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PesanTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private User2 $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->hrd = $this->account('KRY-HRD', 'HRD User', 'ROLE-HRD', 'hrduser');
        $this->staffUser = $this->account('KRY-S', 'Budi Santoso', 'ROLE-STAFF', 'budisatu');

        // Incoming (staff → HRD), unread.
        $this->pesan('P-IN', 'Butuh Revisi', $this->staffUser->id_user, $this->hrd->id_user, '2026-10-01', 'pesan', 'belum_dibaca');
        // Outgoing (HRD → staff), read, surat.
        $this->pesan('P-OUT', 'Surat Tugas', $this->hrd->id_user, $this->staffUser->id_user, '2026-10-02', 'surat', 'dibaca');
        // Third-party (staff → staff): visible to HRD but neither masuk nor keluar.
        Karyawan::create(['id_karyawan' => 'KRY-S2', 'nama' => 'Sari Wulandari', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);
        $sari = User2::create([
            'id_user' => 'USR-KRY-S2',
            'username' => 'sarisatu',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-S2',
        ]);
        $this->pesan('P-3RD', 'Koordinasi Shift', $this->staffUser->id_user, $sari->id_user, '2026-10-03', 'pesan', 'belum_dibaca');
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);

        return User2::create([
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
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

    public function test_renders_incoming_and_outgoing_messages(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->assertSee('Butuh Revisi')
            ->assertSee('Surat Tugas');
    }

    public function test_filters_by_arah(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('arah', 'masuk')
            ->assertSee('Butuh Revisi')
            ->assertDontSee('Surat Tugas');
    }

    public function test_searches_by_judul(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('search', 'revisi')
            ->assertSee('Butuh Revisi')
            ->assertDontSee('Surat Tugas');
    }

    public function test_filters_by_tipe_and_status(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('tipe', 'surat')
            ->assertSee('Surat Tugas')
            ->assertDontSee('Butuh Revisi');

        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('status', 'dibaca')
            ->assertSee('Surat Tugas')
            ->assertDontSee('Butuh Revisi');
    }

    public function test_sort_header_toggles_direction(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->call('sortBy', 'judul')
            ->assertSet('sort', 'judul')
            ->assertSet('dir', 'asc')
            ->call('sortBy', 'judul')
            ->assertSet('dir', 'desc');
    }

    public function test_rows_link_to_detail_and_trigger_opens_kirim_modal(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->assertSee('Buka')
            ->assertSee(url('/hrd/detailPesan/P-IN'), false)
            ->assertSee('Kirim Pesan')
            ->assertSee('bukaModalPesanHrd()', false);
    }

    public function test_renders_arah_pills_instead_of_jenis_column(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->assertSee('data-arah="masuk"', false)
            ->assertSee('data-arah="keluar"', false)
            ->assertDontSee('>Jenis</th>', false);
    }

    public function test_third_party_messages_get_a_neutral_pill_and_no_direction_filter_matches_them(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->assertSee('Koordinasi Shift')
            ->assertSee('data-arah="lainnya"', false)
            ->set('arah', 'masuk')
            ->assertDontSee('Koordinasi Shift')
            ->set('arah', 'keluar')
            ->assertDontSee('Koordinasi Shift');
    }

    public function test_filter_chips_clear_only_their_own_filter(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('arah', 'masuk')
            ->set('status', 'belum_dibaca')
            ->assertSee('data-filter-chip="arah"', false)
            ->assertSee('data-filter-chip="status"', false)
            ->call('clearFilter', 'arah')
            ->assertSet('arah', '')
            ->assertSet('status', 'belum_dibaca')
            ->assertDontSee('data-filter-chip="arah"', false)
            ->assertSee('data-filter-chip="status"', false);
    }

    public function test_shows_filtered_empty_state(): void
    {
        Livewire::actingAs($this->hrd)->test(PesanTable::class)
            ->set('search', 'ZZZNOMATCH')
            ->assertSee('Tidak ada data yang cocok dengan filter.');
    }

    public function test_page_deep_links_and_tampered_urls_stay_healthy(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarPesan?q=Revisi')
            ->assertOk()
            ->assertSee('Butuh Revisi')
            ->assertDontSee('Surat Tugas');

        $this->actingAs($this->hrd)->get('/hrd/daftarPesan?sort=DROP&dir=x&page=999')->assertOk();
        $this->actingAs($this->hrd)->get('/hrd/pesan?sort=DROP&dir=x&page=999')->assertOk();
    }

    public function test_non_hrd_is_forbidden(): void
    {
        Livewire::actingAs($this->staffUser)->test(PesanTable::class)->assertForbidden();
    }
}
