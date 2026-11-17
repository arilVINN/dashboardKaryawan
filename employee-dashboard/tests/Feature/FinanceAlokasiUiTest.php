<?php

namespace Tests\Feature;

use App\Livewire\Finance\AlokasiPagu;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KeuanganAldiv;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceAlokasiUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resources', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-OPS', 'kode_divisi' => 'OPS', 'nama_divisi' => 'Divisi Tanpa Alokasi', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);
        Karyawan::create(['id_karyawan' => 'KRY-STF', 'nama' => 'Staff User', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
    }

    private function seedAlokasi(): void
    {
        KeuanganAldiv::create([
            'id_kad' => 'KAD001',
            'periode' => '2026-11',
            'nominal_alokasi' => 50000000,
            'keterangan' => 'Pagu operasional IT',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        KeuanganAldiv::create([
            'id_kad' => 'KAD002',
            'periode' => '2026-11',
            'nominal_alokasi' => 25000000,
            'keterangan' => 'Pagu operasional HR',
            'divisi_id_divisi' => 'DIV-HR',
            'user_id_user' => $this->finance->id_user,
        ]);
    }

    public function test_page_renders_restyled_table_and_breadcrumbs(): void
    {
        $this->seedAlokasi();

        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/alokasi?periode=2026-11');

        $response->assertOk()
            ->assertSee('Alokasi Pagu Anggaran')
            ->assertSee('data-breadcrumb-parent="Dashboard"', false)
            ->assertSee('data-breadcrumb-current="Alokasi Pagu Anggaran"', false)
            ->assertSee('Tetapkan dan kelola pagu anggaran operasional bulanan untuk masing-masing divisi.')
            ->assertSee('Tetapkan alokasi')
            ->assertSee('Alokasi per divisi')
            ->assertSee('Total pagu')
            ->assertSee('Rp 75.000.000')
            ->assertSee('Menampilkan 2 dari 3 divisi')
            ->assertSee('Information Technology')
            ->assertSee('Human Resources')
            ->assertSee('Pagu operasional IT')
            ->assertSee('Pagu operasional HR')
            ->assertSee('Finance Lead')
            ->assertSee('Edit')
            ->assertSee('November 2026')
            ->assertSee('Divisi')
            ->assertSee('Nominal pagu')
            ->assertSee('Keterangan')
            ->assertSee('Ditetapkan oleh')
            ->assertSee('Aksi')
            ->assertDontSee('>Periode</th>', false)
            ->assertDontSee('rounded-full bg-[#19A7CE]', false)
            ->assertDontSee('bg-amber-50', false);
    }

    public function test_empty_state_for_period_without_allocations(): void
    {
        $response = $this->actingAs($this->finance)->get('/kadiv/keuangan/alokasi?periode=2026-12');

        $response->assertOk()
            ->assertSee('Belum ada alokasi untuk Desember 2026')
            ->assertSee('Tetapkan pagu per divisi agar penyerapan dana bisa dipantau.')
            ->assertSee('Tetapkan alokasi')
            ->assertSee('Rp 0')
            ->assertDontSee('wire:key="alokasi-', false);
    }

    public function test_period_switcher_is_livewire_driven(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->assertSet('periode', '2026-11')
            ->assertSee('November 2026')
            ->assertSee('Rp 75.000.000')
            ->call('previous')
            ->assertSet('periode', '2026-10')
            ->assertSee('Oktober 2026')
            ->assertSee('Belum ada alokasi untuk Oktober 2026')
            ->call('next')
            ->assertSet('periode', '2026-11')
            ->assertSee('November 2026');
    }

    public function test_month_picker_modal_changes_period(): void
    {
        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openMonthPicker')
            ->assertSet('showMonthPicker', true)
            ->assertSet('pickerYear', 2026)
            ->assertSee('Pilih bulan')
            ->assertSee('x-ref="monthCancel"', false)
            ->call('selectMonth', 2)
            ->assertSet('periode', '2026-02')
            ->assertSet('showMonthPicker', false)
            ->assertSee('Februari 2026');
    }

    public function test_edit_dialog_shows_old_value_and_required_reason(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openEditModal', 'KAD001')
            ->assertSet('showEditModal', true)
            ->assertSee('Edit pagu: Information Technology')
            ->assertSee('November 2026 · IT')
            ->assertSee('Sebelumnya Rp 50.000.000')
            ->assertSee('Alasan perubahan')
            ->assertSee('Contoh: penambahan kebutuhan lisensi')
            ->assertSee('Perubahan dicatat: siapa, kapan, nilai lama dan baru.')
            ->assertSee('value="50.000.000"', false)
            ->assertSee('@input="updateRupiah($event, \'editNominal\')"', false)
            ->assertSee('background-color: rgba(15, 40, 55, 0.45)', false)
            ->assertSee('max-w-[460px]', false)
            ->assertSee('x-ref="editCancel"', false);
    }

    public function test_save_edit_updates_table_total_and_audit_log(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openEditModal', 'KAD001')
            ->set('editNominal', '60000000')
            ->set('editKeterangan', 'Penambahan lisensi')
            ->call('saveEdit')
            ->assertSet('showEditModal', false)
            ->assertSee('Rp 60.000.000')
            ->assertSee('Rp 85.000.000')
            ->assertDispatched('alokasi-toast');

        $this->assertDatabaseHas('keuangan_aldiv', [
            'id_kad' => 'KAD001',
            'nominal_alokasi' => 60000000,
            'keterangan' => 'Penambahan lisensi',
        ]);

        $log = AuditLog::where('aksi', 'ubah_pagu')->latest('id')->firstOrFail();
        $this->assertEquals(50000000, $log->detail['nilai_lama']);
        $this->assertEquals(60000000, $log->detail['nilai_baru']);
        $this->assertSame('Penambahan lisensi', $log->detail['alasan']);
        $this->assertSame('USR-FIN', $log->detail['user_id']);
        $this->assertSame('Finance Lead', $log->detail['user_nama']);
    }

    public function test_edit_validation_rejects_zero_and_empty_reason(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openEditModal', 'KAD001')
            ->set('editNominal', '0')
            ->set('editKeterangan', '')
            ->call('saveEdit')
            ->assertSet('showEditModal', true)
            ->assertHasErrors(['editNominal', 'editKeterangan'])
            ->assertSee('Nominal pagu harus lebih dari 0.')
            ->assertSee('Alasan perubahan wajib diisi.');
    }

    public function test_create_dialog_hides_divisions_that_already_have_allocations(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->assertSee('Divisi Tanpa Alokasi (OPS)')
            ->assertDontSee('Information Technology (IT)')
            ->assertDontSee('Human Resources (HR)')
            ->assertSee('@input="updateRupiah($event, \'createNominal\')"', false)
            ->assertSee('background-color: rgba(15, 40, 55, 0.45)', false)
            ->assertSee('max-w-[460px]', false)
            ->assertSee('x-ref="createCancel"', false);
    }

    public function test_create_dialog_shows_message_when_all_divisions_are_allocated(): void
    {
        $this->seedAlokasi();

        KeuanganAldiv::create([
            'id_kad' => 'KAD003',
            'periode' => '2026-11',
            'nominal_alokasi' => 10000000,
            'keterangan' => 'Pagu operasional OPS',
            'divisi_id_divisi' => 'DIV-OPS',
            'user_id_user' => $this->finance->id_user,
        ]);

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openCreateModal')
            ->assertSee('Semua divisi sudah memiliki pagu untuk November 2026.')
            ->assertDontSee('Pilih divisi');
    }

    public function test_create_allocation_updates_table_total_and_audit_log(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openCreateModal')
            ->set('createDivisiId', 'DIV-OPS')
            ->set('createNominal', '30000000')
            ->set('createKeterangan', 'Pagu operasional OPS')
            ->call('saveCreate')
            ->assertSet('showCreateModal', false)
            ->assertSee('Rp 105.000.000')
            ->assertSee('Menampilkan 3 dari 3 divisi')
            ->assertDispatched('alokasi-toast');

        $this->assertDatabaseHas('keuangan_aldiv', [
            'divisi_id_divisi' => 'DIV-OPS',
            'periode' => '2026-11',
            'nominal_alokasi' => 30000000,
            'keterangan' => 'Pagu operasional OPS',
        ]);

        $log = AuditLog::where('aksi', 'atur_pagu')->latest('id')->firstOrFail();
        $this->assertNull($log->detail['nilai_lama']);
        $this->assertEquals(30000000, $log->detail['nilai_baru']);
        $this->assertSame('Pagu operasional OPS', $log->detail['alasan']);
        $this->assertSame('Divisi Tanpa Alokasi', $log->detail['divisi_nama']);
    }

    public function test_create_validation_rejects_duplicate_and_non_positive_amount(): void
    {
        $this->seedAlokasi();

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openCreateModal')
            ->set('createDivisiId', 'DIV-IT')
            ->set('createNominal', '1000000')
            ->call('saveCreate')
            ->assertHasErrors(['createDivisiId'])
            ->assertSee('Divisi ini sudah memiliki alokasi untuk periode tersebut.');

        Livewire::actingAs($this->finance)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->call('openCreateModal')
            ->set('createDivisiId', 'DIV-OPS')
            ->set('createNominal', '0')
            ->call('saveCreate')
            ->assertHasErrors(['createNominal'])
            ->assertSee('Nominal pagu harus lebih dari 0.');
    }

    public function test_database_unique_index_blocks_duplicate_division_month(): void
    {
        $this->seedAlokasi();

        $this->expectException(QueryException::class);

        KeuanganAldiv::create([
            'id_kad' => 'KAD999',
            'periode' => '2026-11',
            'nominal_alokasi' => 1000000,
            'keterangan' => 'Duplikat',
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);
    }

    public function test_non_finance_user_is_forbidden_from_livewire_component(): void
    {
        $staff = User2::create([
            'id_user' => 'USR-STF',
            'username' => 'staff.user',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-STF',
        ]);

        Livewire::actingAs($staff)
            ->test(AlokasiPagu::class, ['periode' => '2026-11'])
            ->assertForbidden();
    }

    public function test_controller_validates_integer_amount_and_required_reason(): void
    {
        $this->seedAlokasi();

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.alokasi.store'), [
                'periode' => '2026-11',
                'nominal_alokasi' => 1500.5,
                'divisi_id_divisi' => 'DIV-OPS',
            ])
            ->assertSessionHasErrors('nominal_alokasi');

        $this->actingAs($this->finance)
            ->put(route('kadiv.keuangan.alokasi.update', 'KAD001'), [
                'nominal_alokasi' => 60000000,
            ])
            ->assertSessionHasErrors('keterangan');
    }
}
