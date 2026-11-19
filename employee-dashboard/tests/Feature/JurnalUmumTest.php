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
        $this->assertSame('Pembayaran proyek', $entry->keterangan);
        $this->assertCount(2, $entry->lines);
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
        $this->assertSame('Pencairan dana IT', $entry->keterangan);
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
                    'keterangan' => 'Tidak seimbang',
                    'lines' => [
                        ['account_id' => $kas->id, 'debit' => 100000, 'credit' => 0],
                        ['account_id' => $pendapatan->id, 'debit' => 0, 'credit' => 90000],
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
            ->assertSeeHtml('Pilih divisi dan akun terlebih dahulu');

        $component->set('manualDivisiId', (string) $divisi->id)
            ->set('manualKeterangan', 'Jurnal manual')
            ->set('manualLines.0.account_id', (string) $kas->id)
            ->set('manualLines.0.debit', 250000)
            ->call('saveManual')
            ->assertSet('feedbackType', 'error')
            ->assertSee('seimbangkan debit dan kredit');

        $this->assertDatabaseCount('jurnal_entries', 0);

        $component->set('manualLines.1.account_id', (string) $pendapatan->id)
            ->set('manualLines.1.credit', 250000)
            ->call('saveManual')
            ->assertSet('showManualModal', false);

        $entry = JurnalEntry::firstOrFail();
        $this->assertSame('JMU0001', $entry->ref_code);
        $this->assertSame($divisi->id, $entry->jurnal_divisi_id);
        $this->assertSame(250000, (int) $entry->lines->sum('debit'));
        $this->assertSame(250000, (int) $entry->lines->sum('credit'));
    }

    public function test_cancel_creates_reversing_entry_without_touching_original(): void
    {
        $kas = CoaAkun::where('kode', '1101')->firstOrFail();
        $pendapatan = CoaAkun::where('kode', '4101')->firstOrFail();

        $original = app(JurnalEntryService::class)->createManual([
            'entry_date' => '2026-11-12',
            'jurnal_divisi_id' => JurnalDivisi::first()->id,
            'keterangan' => 'Jurnal asli',
            'lines' => [
                ['account_id' => $kas->id, 'debit' => 400000, 'credit' => 0],
                ['account_id' => $pendapatan->id, 'debit' => 0, 'credit' => 400000],
            ],
        ], $this->finance->id_user);

        Livewire::actingAs($this->finance)
            ->test(JurnalUmumTable::class)
            ->call('openCancelModal', $original->id)
            ->set('cancelReason', 'Salah input nominal')
            ->call('confirmCancel');

        $this->assertSame(2, JurnalEntry::count());
        $reversal = JurnalEntry::where('ref_code', 'like', 'RV%')->firstOrFail();
        $this->assertSame((string) $original->id, $reversal->source_id);
        $this->assertStringContainsString('Salah input nominal', $reversal->keterangan);
        $this->assertSame(400000, (int) $reversal->lines->sum('debit'));
        $this->assertSame(400000, (int) $reversal->lines->sum('credit'));
        $this->assertSame('Jurnal asli', $original->fresh()->keterangan);
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
                'keterangan' => 'Jurnal ' . $i,
                'lines' => [
                    ['account_id' => $kas->id, 'debit' => 100000, 'credit' => 0],
                    ['account_id' => $pendapatan->id, 'debit' => 0, 'credit' => 100000],
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
}
