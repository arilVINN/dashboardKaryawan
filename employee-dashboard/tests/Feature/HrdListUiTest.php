<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\SuratIzin;
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

        Karyawan::create(['id_karyawan' => 'KRY-A', 'nama' => 'Andi Wijaya', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_masuk' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Engineer', 'divisi_id_divisi' => 'DIV-IT']);
        Karyawan::create(['id_karyawan' => 'KRY-B', 'nama' => 'Budi Santoso', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-01-01', 'tanggal_masuk' => '2026-01-01', 'no_telepon' => '0812', 'jabatan' => 'Operator', 'divisi_id_divisi' => 'DIV-OPS']);

        Karyawan::create(['id_karyawan' => 'KRY-HRD', 'nama' => 'HRD User', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1990-01-01', 'tanggal_masuk' => '2020-01-01', 'no_telepon' => '0812', 'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-IT']);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);
    }


    public function test_staff_list_marks_sortable_columns_and_leaves_status_plain(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('wire:click="sortBy(\'id\')"', false)
            ->assertSee('wire:click="sortBy(\'nama\')"', false)
            ->assertSee('wire:click="sortBy(\'divisi\')"', false)
            ->assertSee('wire:click="sortBy(\'jabatan\')"', false)
            ->assertDontSee('wire:click="sortBy(\'status\')"', false);
    }

    public function test_employee_analytics_classifies_contract_staff_as_non_permanent(): void
    {
        Karyawan::create([
            'id_karyawan' => 'KRY-CONTRACT',
            'nama' => 'Karyawan Kontrak',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_masuk' => '2026-01-01',
            'tanggal_akhir_kontrak' => '2027-01-01',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->actingAs($this->hrd)
            ->get('/hrd/analitikKaryawan')
            ->assertOk()
            ->assertViewHas('statusKaryawan', function ($statusKaryawan): bool {
                return (int) $statusKaryawan->get('Tetap') === 3
                    && (int) $statusKaryawan->get('Tidak Tetap') === 1;
            });
    }

    // NOTE: header direction toggling moved to
    // tests/Feature/Livewire/Hrd/KaryawanTableTest.php (test_sort_header_toggles_direction).

    public function test_divisi_list_marks_sortable_columns(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi')
            ->assertOk()
            ->assertSee('wire:click="sortBy(\'kode\')"', false)
            ->assertSee('wire:click="sortBy(\'nama\')"', false)
            ->assertSee('wire:click="sortBy(\'staff\')"', false)
            ->assertDontSee('wire:click="sortBy(\'status\')"', false);
    }


    public function test_active_filter_chips_render_only_when_filtered(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan?divisi=DIV-IT&status=aktif')
            ->assertSee('data-filter-chip="divisi"', false)
            ->assertSee('data-filter-chip="status"', false);

        $this->actingAs($this->hrd)->get('/hrd/daftarKaryawan')
            ->assertDontSee('data-filter-chip', false);
    }

    // NOTE: per-filter removal moved to
    // tests/Feature/Livewire/Hrd/KaryawanTableTest.php
    // (test_filter_chips_render_and_clear_only_their_own_filter).

    public function test_divisi_list_renders_filter_chip_for_status(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/daftarDivisi?status=aktif')
            ->assertSee('data-filter-chip="status"', false);
    }


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


    public function test_manajemen_divisi_marks_sortable_columns_and_chips(): void
    {
        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi')
            ->assertOk()
            ->assertSee('wire:click="sortBy(\'kode\')"', false)
            ->assertSee('wire:click="sortBy(\'nama\')"', false)
            ->assertSee('wire:click="sortBy(\'staff\')"', false)
            ->assertDontSee('wire:click="sortBy(\'status\')"', false);

        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi?status=aktif')
            ->assertSee('data-filter-chip="status"', false);
    }

    public function test_dashboard_divisi_table_stays_non_sortable(): void
    {
        // tabelDivisi is shared; the dashboard must not get the sortable markup.
        $response = $this->actingAs($this->hrd)->get('/hrd/dashboard')
            ->assertOk()
            ->assertDontSee('data-sort=', false);

        $this->assertStringContainsString('<td class="px-6 py-4 whitespace-nowrap text-center text-xs">', $response->getContent());
        $this->assertStringContainsString('<div class="flex justify-center items-center gap-2">', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/<td\b[^>]*\bflex\b/', $response->getContent());
    }


    public function test_dashboard_stat_cards_are_not_links(): void
    {
        // Stat cards are display-only; page access lives in the sidebar.
        $html = view('component_hrd.statusbar', [
            'total' => ['karyawan' => 1, 'divisi' => 2, 'pesan' => 3],
            'tugasBuckets' => ['ongoing' => 1, 'pending' => 1, 'revisi' => 1],
            'totalStaff' => 1,
            'totalDivisi' => 2,
            'totalPesanPerusahaan' => 3,
        ])->render();

        $this->assertStringContainsString('Total Karyawan', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_sidebar_links_to_staff_list(): void
    {
        $html = view('component_hrd.sidebar')->render();

        $this->assertStringContainsString(url('/hrd/daftarKaryawan'), $html);
    }


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
            ->assertSee('data-breadcrumb-current="Manajemen Karyawan"', false);

        $this->actingAs($this->hrd)->get('/hrd/manajemenDivisi')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Manajemen Divisi"', false);
    }

    public function test_pesan_keluar_card_links_to_incoming_leave_requests(): void
    {
        SuratIzin::create([
            'id_surat' => 'SRT001',
            'karyawan_id_karyawan' => 'KRY-A',
            'judul_surat' => 'Izin Sakit',
            'tanggal_izin' => '2026-10-20',
            'keterangan' => 'Tidak enak badan',
        ]);

        $this->actingAs($this->hrd)->get('/hrd/pesan')
            ->assertOk()
            ->assertSee('Surat Izin Masuk')
            ->assertSee(route('hrd.suratIzin'), false)
            ->assertSee('>1</span>', false);
    }

    public function test_hrd_can_open_incoming_leave_request_list(): void
    {
        SuratIzin::create([
            'id_surat' => 'SRT002',
            'karyawan_id_karyawan' => 'KRY-A',
            'judul_surat' => 'Izin Sakit',
            'tanggal_izin' => '2026-10-20',
            'keterangan' => 'Tidak enak badan',
        ]);

        $this->actingAs($this->hrd)->get(route('hrd.suratIzin'))
            ->assertOk()
            ->assertSee('Surat Izin Masuk')
            ->assertSee('Andi Wijaya')
            ->assertSee('Izin Sakit')
            ->assertSee('Tidak enak badan');
    }

    public function test_hrd_can_approve_and_reject_pending_leave_requests(): void
    {
        SuratIzin::create([
            'id_surat' => 'SRT003',
            'karyawan_id_karyawan' => 'KRY-A',
            'judul_surat' => 'Izin Disetujui',
            'tanggal_izin' => '2026-10-20',
        ]);
        SuratIzin::create([
            'id_surat' => 'SRT004',
            'karyawan_id_karyawan' => 'KRY-B',
            'judul_surat' => 'Izin Ditolak',
            'tanggal_izin' => '2026-10-20',
        ]);

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.acc', 'SRT003'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Surat izin berhasil disetujui.');
        $this->assertDatabaseHas('surat_izin', ['id_surat' => 'SRT003', 'status' => 'Disetujui']);

        $this->post(route('hrd.suratIzin.tolak', 'SRT004'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Surat izin berhasil ditolak.');
        $this->assertDatabaseHas('surat_izin', ['id_surat' => 'SRT004', 'status' => 'Ditolak']);
    }

    public function test_hrd_cannot_process_a_leave_request_twice(): void
    {
        SuratIzin::create([
            'id_surat' => 'SRT005',
            'karyawan_id_karyawan' => 'KRY-A',
            'judul_surat' => 'Izin Selesai',
            'tanggal_izin' => '2026-10-20',
            'status' => 'Disetujui',
        ]);

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.tolak', 'SRT005'))
            ->assertStatus(409);

        $this->assertDatabaseHas('surat_izin', ['id_surat' => 'SRT005', 'status' => 'Disetujui']);
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
