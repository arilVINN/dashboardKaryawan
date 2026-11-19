<?php

namespace Tests\Feature;

use App\Livewire\Finance\JurnalUmumTable;
use App\Models\AuditLog;
use App\Models\CoaAkun;
use App\Models\Divisi;
use App\Models\JurnalDivisi;
use App\Models\JurnalEntry;
use App\Models\JurnalLine;
use App\Models\Karyawan;
use App\Models\KPemasukan;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use App\Services\Finance\JurnalEntryService;
use Database\Seeders\JurnalUmumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class JurnalUmumTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);
        Karyawan::create(['id_karyawan' => 'KRY-STAF', 'nama' => 'Staff', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT']);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);

        $this->staff = User2::create([
            'id_user' => 'USR-STAF',
            'username' => 'staff',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-STAF',
        ]);

        $this->seed(JurnalUmumSeeder::class);
    }

    public function test_finance_can_open_jurnal_umum_and_staff_cannot(): void
    {
        $url = route('kadiv.keuangan.jurnalUmum');

        $this->actingAs($this->finance)->get($url)
            ->assertOk()
            ->assertSee('Jurnal Umum')
            ->assertSee('Daftar akun (COA)')
            ->assertSee('Panduan kode divisi')
            ->assertSee('Semua divisi');

        // Middleware role:kadiv mengalihkan user non-kadiv ke dashboard mereka.
        $this->actingAs($this->staff)->get($url)->assertRedirect();
    }

    public function test_kas_masuk_creates_balanced_journal_with_audit_log(): void
    {
        $this->actingAs($this->finance)->post(route('kadiv.keuangan.pemasukan.store'), [
            'kategori_pemasukan' => 'Layanan / Jasa',
            'nominal' => 1500000,
            'tanggal_masuk' => '2026-11-12',
            'keterangan' => 'Pembayaran proyek',
            'divisi_id_divisi' => 'DIV-IT',
        ])->assertRedirect();

        $this->assertDatabaseCount('kpemasukan', 1);

        $entry = JurnalEntry::with('lines.account')->firstOrFail();
        $this->assertSame('DMIN001', $entry->ref_code);
        $this->assertSame('2026-11-12', $entry->entry_date->toDateString());
        $this->assertCount(2, $entry->lines);
        $entry->lines->each(fn (JurnalLine $line) => $this->assertSame('Pembayaran proyek', $line->keterangan));
        $this->assertSame(1500000, (int) $entry->lines->sum('debit'));
        $this->assertSame(1500000, (int) $entry->lines->sum('credit'));

        $debitLine = $entry->lines->first(fn (JurnalLine $line) => $line->debit > 0);
        $creditLine = $entry->lines->first(fn (JurnalLine $line) => $line->credit > 0);
        $this->assertSame('1101', $debitLine->account->kode);
        $this->assertSame('4101', $creditLine->account->kode);
        $this->assertSame(0, $debitLine->sort_order);
        $this->assertSame(1, $creditLine->sort_order);

        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'jurnal_umum_dibuat',
            'target_tipe' => 'jurnal_entry',
            'target_id' => (string) $entry->id,
        ]);
        $this->assertSame(1, AuditLog::where('aksi', 'jurnal_umum_dibuat')->count());
    }

    public function test_kas_keluar_uses_beban_and_kas_accounts(): void
    {
        $this->actingAs($this->finance)->post(route('kadiv.keuangan.pengeluaran.store'), [
            'nominal' => 750000,
            'tanggal_cair' => '2026-11-13',
            'metode_pembayaran' => 'Transfer Bank',
            'keterangan' => 'Operasional',
            'divisi_id_divisi' => 'DIV-IT',
        ])->assertRedirect();

        $entry = JurnalEntry::with('lines.account')->firstOrFail();
        $this->assertStringStartsWith('DMOUT', $entry->ref_code);
        $this->assertSame('5102', $entry->lines->first(fn (JurnalLine $l) => $l->debit > 0)->account->kode);
        $this->assertSame('1101', $entry->lines->first(fn (JurnalLine $l) => $l->credit > 0)->account->kode);
    }

    public function test_pencairan_dana_creates_journal_in_same_transaction(): void
    {
        $permintaan = KPermintDana::create([
            'id_kpd' => 'KPD0001',
            'nomor_pengajuan' => 'REQ26110001',
            'judul_pengajuan' => 'Dana operasional IT',
            'keperluan' => 'Operasional',
            'nominal_diajukan' => 500000,
            'status' => KPermintDana::STATUS_DISETUJUI,
            'divisi_id_divisi' => 'DIV-IT',
            'user_id_user' => $this->finance->id_user,
        ]);

        $this->actingAs($this->finance)
            ->post(route('kadiv.keuangan.cairkan', $permintaan->id_kpd), [
                'tanggal_cair' => '2026-11-14',
                'metode_pembayaran' => 'Transfer Bank',
                'keterangan' => 'Pencairan dana IT',
            ])->assertRedirect();

        $this->assertSame(KPermintDana::STATUS_DICAIRKAN, $permintaan->fresh()->status);
        $this->assertDatabaseCount('kpengeluaran', 1);

        $entry = JurnalEntry::with('lines.account', 'source')->firstOrFail();
        $this->assertStringStartsWith('DMOUT', $entry->ref_code);
        $this->assertSame('2026-11-14', $entry->entry_date->toDateString());
        $entry->lines->each(fn (JurnalLine $line) => $this->assertSame('Pencairan dana IT', $line->keterangan));
        $this->assertSame('KPD0001', $entry->source->kpd_id_kpd);
        $this->assertSame(500000, (int) $entry->lines->sum('debit'));
        $this->assertSame(500000, (int) $entry->lines->sum('credit'));
        $this->assertStringContainsString('kadiv/keuangan/permintaan/', $entry->source_url);
    }

    public function test_imbalanced_journal_is_rejected_and_transaction_rolls_back(): void
    {
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        try {
            DB::transaction(function () use ($kas, $pendapatan) {
                app(JurnalEntryService::class)->createManual([
                    'entry_date' => '2026-11-12',
                    'jurnal_divisi_id' => JurnalDivisi::first()->id,
                    'lines' => [
                        ['account_id' => $kas->id, 'keterangan' => 'Tidak seimbang', 'debit' => 100000, 'credit' => 0],
                        ['account_id' => $pendapatan->id, 'keterangan' => 'Tidak seimbang', 'debit' => 0, 'credit' => 90000],
                    ],
                ], $this->finance->id_user);
            });
            $this->fail('Jurnal tidak seimbang seharusnya ditolak.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('harus sama', $e->getMessage());
        }

        $this->assertDatabaseCount('jurnal_entries', 0);
        $this->assertDatabaseCount('jurnal_lines', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_manual_journal_is_locked_until_division_and_account_chosen(): void
    {
        $divisi = JurnalDivisi::firstOrFail();
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        $component = Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openManualModal')
            ->assertSeeHtml('Pilih akun terlebih dahulu');

        $component->set('manualDivisiId', (string) $divisi->id)
            ->set('manualLines.0.account_id', (string) $kas->id)
            ->set('manualLines.0.debit', 250000)
            ->call('saveManual')
            ->assertSet('feedbackType', 'error')
            ->assertSee('seimbangkan debit dan kredit');

        $this->assertDatabaseCount('jurnal_entries', 0);

        $component->set('manualLines.1.account_id', (string) $pendapatan->id)
            ->set('manualLines.1.credit', 250000)
            ->call('saveManual')
            ->assertSet('feedbackType', 'error')
            ->assertSee('isi keterangan tiap baris');

        $this->assertDatabaseCount('jurnal_entries', 0);

        $component->set('manualLines.0.keterangan', 'Kas masuk')
            ->set('manualLines.1.keterangan', 'Pendapatan jasa')
            ->call('saveManual')
            ->assertSet('showManualModal', false);

        $entry = JurnalEntry::with('lines')->firstOrFail();
        $this->assertNull($entry->ref_code);
        $this->assertSame($divisi->id, $entry->jurnal_divisi_id);
        $this->assertSame(250000, (int) $entry->lines->sum('debit'));
        $this->assertSame(250000, (int) $entry->lines->sum('credit'));
        $this->assertSame('Kas masuk', $entry->lines->first(fn (JurnalLine $l) => $l->debit > 0)->keterangan);
        $this->assertSame('Pendapatan jasa', $entry->lines->first(fn (JurnalLine $l) => $l->credit > 0)->keterangan);
    }

    public function test_cancel_creates_reversing_entry_without_touching_original(): void
    {
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        $original = app(JurnalEntryService::class)->createManual([
            'entry_date' => '2026-11-12',
            'jurnal_divisi_id' => JurnalDivisi::first()->id,
            'lines' => [
                ['account_id' => $kas->id, 'keterangan' => 'Jurnal asli', 'debit' => 400000, 'credit' => 0],
                ['account_id' => $pendapatan->id, 'keterangan' => 'Jurnal asli', 'debit' => 0, 'credit' => 400000],
            ],
        ], $this->finance->id_user);

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openCancelModal', $original->id)
            ->set('cancelReason', 'Salah input nominal')
            ->call('confirmCancel');

        $this->assertSame(2, JurnalEntry::count());
        $reversal = JurnalEntry::where('source_type', \App\Models\JurnalEntry::class)
            ->where('source_id', (string) $original->id)
            ->firstOrFail();
        $this->assertNull($reversal->ref_code);
        $this->assertSame((string) $original->id, $reversal->source_id);
        $reversal->loadMissing('lines');
        $reversal->lines->each(fn (JurnalLine $l) => $this->assertStringContainsString('Salah input nominal', $l->keterangan));
        $this->assertSame(400000, (int) $reversal->lines->sum('debit'));
        $this->assertSame(400000, (int) $reversal->lines->sum('credit'));
        $this->assertSame('Jurnal asli', $original->fresh()->lines->first()->keterangan);
        $this->assertTrue($original->fresh()->lines->first(fn (JurnalLine $l) => $l->debit > 0)->debit === 400000);
    }

    public function test_coa_panel_hides_inactive_accounts_and_filters_by_search(): void
    {
        $inactive = CoaAkun::create([
            'kode' => '9999',
            'nama' => 'Akun Nonaktif',
            'kelompok' => 'Beban',
            'aktif' => false,
        ]);

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('toggleCoaPanel')
            ->assertSee('Kas')
            ->assertDontSee($inactive->nama)
            ->set('coaSearch', '1101')
            ->assertSee('Kas')
            ->assertDontSee('Pendapatan Jasa')
            ->set('coaSearch', 'tidak-ada')
            ->assertSee('Akun tidak ditemukan');
    }

    public function test_totals_are_computed_over_all_entries_not_only_current_page(): void
    {
        $service = app(JurnalEntryService::class);
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();
        $divisiId = JurnalDivisi::first()->id;

        for ($i = 1; $i <= 12; $i++) {
            $service->createManual([
                'entry_date' => sprintf('2026-11-%02d', $i),
                'jurnal_divisi_id' => $divisiId,
                'lines' => [
                    ['account_id' => $kas->id, 'keterangan' => 'Jurnal ' . $i, 'debit' => 100000, 'credit' => 0],
                    ['account_id' => $pendapatan->id, 'keterangan' => 'Jurnal ' . $i, 'debit' => 0, 'credit' => 100000],
                ],
            ], $this->finance->id_user);
        }

        $this->assertSame(12, JurnalEntry::count());

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->assertSeeHtml('Menampilkan 1-10 dari 12 transaksi')
            ->assertSeeHtml(number_format(1200000, 0, ',', '.'))
            ->assertSee('Seimbang');
    }

    /**
     * Regresi: browser mengirim seluruh array manualLines saat "+ Tambah baris",
     * sehingga hook updatedManualLines dipanggil dengan $key = null.
     */
    public function test_whole_array_update_does_not_crash(): void
    {
        $lines = [
            ['account_id' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => '', 'debit' => '100000', 'credit' => '100000'],
        ];

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openManualModal')
            ->set('manualLines', $lines)
            ->assertSet('manualLines.2.debit', 100000)
            ->assertSet('manualLines.2.credit', '');
    }

    public function test_adding_two_rows_can_be_balanced_and_saved(): void
    {
        $divisi = JurnalDivisi::firstOrFail();
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openManualModal')
            ->call('addLine')
            ->call('addLine')
            ->set('manualDivisiId', (string) $divisi->id)
            ->set('manualLines.0.account_id', (string) $kas->id)
            ->set('manualLines.0.keterangan', 'Kas')
            ->set('manualLines.0.debit', 300000)
            ->set('manualLines.2.account_id', (string) $pendapatan->id)
            ->set('manualLines.2.keterangan', 'Pendapatan')
            ->set('manualLines.2.credit', 300000)
            ->assertSet('manualTotals.valid', true)
            ->call('saveManual')
            ->assertSet('showManualModal', false);

        $entry = JurnalEntry::with('lines')->firstOrFail();
        $this->assertCount(2, $entry->lines);
        $this->assertSame(300000, (int) $entry->lines->sum('debit'));
        $this->assertSame(300000, (int) $entry->lines->sum('credit'));
    }

    public function test_remove_then_add_row_keeps_stable_keys(): void
    {
        $component = Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openManualModal')
            ->call('addLine')
            ->call('removeLine', 0)
            ->call('addLine');

        $ids = $component->get('manualLineIds');

        $this->assertCount(3, $ids);
        $this->assertCount(3, array_unique($ids));
        $this->assertCount(3, $component->get('manualLines'));
        $component->assertSeeHtml('Pilih akun');
    }

    public function test_month_picker_selection_changes_period_without_redirect(): void
    {
        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openMonthPicker')
            ->assertSet('showMonthPicker', true)
            ->call('selectMonth', 8)
            ->assertSet('periode', '2026-08')
            ->assertSet('showMonthPicker', false)
            ->call('previousMonth')
            ->assertSet('periode', '2026-07')
            ->call('nextMonth')
            ->assertSet('periode', '2026-08')
            ->call('resetFilters')
            ->assertSet('periode', now()->format('Y-m'));
    }

    public function test_ref_column_renders_coa_code_and_manual_entries_have_no_ref_code(): void
    {
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        app(JurnalEntryService::class)->createManual([
            'entry_date' => '2026-11-12',
            'jurnal_divisi_id' => JurnalDivisi::first()->id,
            'lines' => [
                ['account_id' => $kas->id, 'keterangan' => 'Manual tanpa ref', 'debit' => 100000, 'credit' => 0],
                ['account_id' => $pendapatan->id, 'keterangan' => 'Manual tanpa ref', 'debit' => 0, 'credit' => 100000],
            ],
        ], $this->finance->id_user);

        $this->assertNull(JurnalEntry::first()->ref_code);

        $component = Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->assertSee('1101')
            ->assertSee('4101')
            ->assertSee('Manual tanpa ref');

        $this->assertMatchesRegularExpression('/font-mono[^>]*>\s*1101\s*<\/td>/', $component->html());
        $this->assertMatchesRegularExpression('/font-mono[^>]*>\s*4101\s*<\/td>/', $component->html());
    }

    public function test_each_line_keeps_its_own_description_and_entry_has_none(): void
    {
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        $entry = app(JurnalEntryService::class)->createManual([
            'entry_date' => '2026-11-15',
            'jurnal_divisi_id' => JurnalDivisi::first()->id,
            'lines' => [
                ['account_id' => $kas->id, 'keterangan' => 'Uang masuk dari klien', 'debit' => 500000, 'credit' => 0],
                ['account_id' => $pendapatan->id, 'keterangan' => 'Pendapatan jasa klien', 'debit' => 0, 'credit' => 500000],
            ],
        ], $this->finance->id_user);

        $this->assertArrayNotHasKey('keterangan', $entry->getAttributes());
        $this->assertSame(
            ['Uang masuk dari klien', 'Pendapatan jasa klien'],
            $entry->lines->pluck('keterangan')->all()
        );

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->assertSee('Uang masuk dari klien')
            ->assertSee('Pendapatan jasa klien');
    }
}
