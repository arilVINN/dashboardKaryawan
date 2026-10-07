<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdListUiTest extends TestCase
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
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Operasional', 'status_aktif' => 'Nonaktif']);

        Karyawan::create(['id_karyawan' => 'KRY-A', 'nama' => 'Andi Wijaya', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_rekrut' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Engineer', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'KRY-B', 'nama' => 'Budi Santoso', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_rekrut' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Operator', 'divisi_id_divisi' => 'DIV-OPS']);

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1990-01-01', 'tanggal_rekrut' => '2020-01-01', 'no_telepon' => '0812', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);
    }

    // ---- sortable headers ----

    public function test_staff_list_marks_sortable_columns_and_leaves_status_plain(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('data-sort="id"', false)
            ->assertSee('data-sort="nama"', false)
            ->assertSee('data-sort="divisi"', false)
            ->assertSee('data-sort="jabatan"', false)
            ->assertDontSee('data-sort="status"', false);
    }

    public function test_staff_default_name_header_offers_desc_toggle(): void
    {
        // Default sort is nama ascending, so the Nama header should link to desc.
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertSee('data-sort="nama" data-dir="desc"', false);
    }

    public function test_staff_header_direction_reflects_current_sort(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?sort=jabatan&dir=asc')
            ->assertSee('data-sort="jabatan" data-dir="desc"', false);

        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?sort=jabatan&dir=desc')
            ->assertSee('data-sort="jabatan" data-dir="asc"', false);
    }

    public function test_divisi_list_marks_sortable_columns(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi')
            ->assertOk()
            ->assertSee('data-sort="kode"', false)
            ->assertSee('data-sort="nama"', false)
            ->assertSee('data-sort="staff"', false)
            ->assertDontSee('data-sort="status"', false);
    }

    // ---- active filter chips ----

    public function test_active_filter_chips_render_only_when_filtered(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?divisi=DIV-IT&status=aktif')
            ->assertSee('data-filter-chip="divisi"', false)
            ->assertSee('data-filter-chip="status"', false);

        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertDontSee('data-filter-chip', false);
    }

    public function test_chip_remove_link_drops_only_its_own_filter(): void
    {
        $base = url('/hrd/daftarKaryawan');

        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?divisi=DIV-IT&status=aktif')
            ->assertSee('data-remove-href="' . $base . '?status=aktif"', false)
            ->assertSee('data-remove-href="' . $base . '?divisi=DIV-IT"', false);
    }

    public function test_divisi_list_renders_filter_chip_for_status(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?status=aktif')
            ->assertSee('data-filter-chip="status"', false);
    }

    // ---- empty states ----

    public function test_staff_list_shows_filtered_empty_state(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?q=ZZZNOMATCH')
            ->assertSee('Tidak ada data yang cocok dengan filter.');
    }

    public function test_divisi_list_shows_filtered_empty_state(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?q=ZZZNOMATCH')
            ->assertSee('Tidak ada data yang cocok dengan filter.');
    }

    public function test_staff_list_shows_plain_empty_state_without_filters(): void
    {
        // No filters and no data would be needed for the plain state; assert the
        // filtered wording is NOT used when no filters are applied.
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertDontSee('Tidak ada data yang cocok dengan filter.');
    }

    // ---- manajemen divisi (shared tabelDivisi component) ----

    public function test_manajemen_divisi_marks_sortable_columns_and_chips(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi')
            ->assertOk()
            ->assertSee('data-sort="kode"', false)
            ->assertSee('data-sort="nama"', false)
            ->assertSee('data-sort="staff"', false)
            ->assertDontSee('data-sort="status"', false);

        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?status=aktif')
            ->assertSee('data-filter-chip="status"', false);
    }

    public function test_dashboard_divisi_table_stays_non_sortable(): void
    {
        // tabelDivisi is shared; the dashboard must not get the sortable markup.
        $this->actingAs($this->hrd)->get('/hrd/dashboard')
            ->assertOk()
            ->assertDontSee('data-sort=', false);
    }

    // ---- navigation vs dashboard cards ----

    public function test_dashboard_stat_cards_are_not_links(): void
    {
        // Stat cards are display-only; page access lives in the sidebar.
        $html = view('component_hrd.statusbar', [
            'total' => ['karyawan' => 1, 'divisi' => 2, 'pesan' => 3],
            'tugasBuckets' => ['ongoing' => 1, 'pending' => 1, 'revisi' => 1],
        ])->render();

        $this->assertStringContainsString('Total Karyawan', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_sidebar_links_to_staff_list(): void
    {
        $html = view('component_hrd.sidebar')->render();

        $this->assertStringContainsString(url('/hrd/daftarKaryawan'), $html);
    }

    // ---- breadcrumbs ----

    public function test_breadcrumb_component_renders_supplied_parent_and_current(): void
    {
        $html = view('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => url('/hrd/dashboard'),
            'currentPage' => 'Karyawan',
        ])->render();

        $this->assertStringContainsString('data-breadcrumb-parent="Dashboard"', $html);
        $this->assertStringContainsString('data-breadcrumb-current="Karyawan"', $html);
        $this->assertStringContainsString('href="' . url('/hrd/dashboard') . '"', $html);
    }

    public function test_hrd_list_pages_show_relevant_breadcrumbs(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Karyawan"', false);

        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Manajemen Divisi"', false);
    }

    public function test_hrd_detail_pesan_breadcrumb_points_to_pesan(): void
    {
        \App\Models\Pesan::create([
            'id_pesan' => 'PSN-BC-1',
            'judul_pesan' => 'Halo',
            'deskripsi' => 'Isi pesan',
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $this->hrd->id_user,
        ]);

        $this->actingAs($this->hrd)->get('/hrd/detailPesan/PSN-BC-1')
            ->assertOk()
            ->assertSee('data-breadcrumb-parent="Pesan"', false)
            ->assertSee('data-breadcrumb-current="Detail Pesan"', false);
    }
}
