<?php

namespace App\Livewire\Finance;

use App\Livewire\Finance\Concerns\HasMonthPicker;
use App\Models\AuditLog;
use App\Models\CoaAkun;
use App\Models\JurnalDivisi;
use App\Models\JurnalEntry;
use App\Models\JurnalLine;
use App\Services\Finance\JurnalEntryService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class JurnalUmumTable extends Component
{
    use HasMonthPicker {
        selectMonth as protected selectMonthFromTrait;
    }
    use WithPagination;

    public string $periode;

    public string $divisiFilter = '';

    public bool $showCoaPanel = false;

    public bool $showDivisionDialog = false;

    public bool $showManualModal = false;

    public string $coaSearch = '';

    public ?string $manualDivisiId = null;

    public string $manualTanggal = '';

    public array $manualLines = [];

    /** Id stabil per baris, dipakai sebagai wire:key agar DOM tidak tertukar saat reindex. */
    public array $manualLineIds = [];

    public ?int $editingLineId = null;

    public string $editAccountId = '';

    public string $editKeterangan = '';

    public string $editDebit = '';

    public string $editCredit = '';

    public ?int $deletingLineId = null;

    public ?string $feedbackMessage = null;

    public string $feedbackType = 'success';

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->periode = now()->format('Y-m');
        $this->manualTanggal = now()->toDateString();
        $this->resetLines();
    }

    // ── Filters ───────────────────────────────────────────────────────────

    public function previousMonth(): void
    {
        $this->periode = Carbon::createFromFormat('Y-m', $this->periode)
            ->startOfMonth()->subMonth()->format('Y-m');
        $this->resetPage();
        $this->resetFeedback();
    }

    public function nextMonth(): void
    {
        $this->periode = Carbon::createFromFormat('Y-m', $this->periode)
            ->startOfMonth()->addMonth()->format('Y-m');
        $this->resetPage();
        $this->resetFeedback();
    }

    public function selectMonth(int $month): void
    {
        $this->selectMonthFromTrait($month);
        $this->resetPage();
        $this->resetFeedback();
    }

    public function updatedPeriode(): void
    {
        $this->resetPage();
    }

    public function updatedDivisiFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->periode = now()->format('Y-m');
        $this->divisiFilter = '';
        $this->resetPage();
        $this->resetFeedback();
    }

    public function getHasActiveFilterProperty(): bool
    {
        return $this->periode !== now()->format('Y-m') || filled($this->divisiFilter);
    }

    public function getPeriodeLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->periode)
            ->locale('id')->translatedFormat('F Y');
    }

    // ── Reference data ────────────────────────────────────────────────────

    public function getDivisionsProperty(): Collection
    {
        return JurnalDivisi::orderBy('kode')->get();
    }

    public function getCoaGroupsProperty(): Collection
    {
        return $this->filteredAccounts($this->coaSearch);
    }

    public function getActiveAccountsProperty(): Collection
    {
        return CoaAkun::aktif()->orderBy('kode')->get()
            ->groupBy('kelompok')
            ->sortBy(fn ($group, $key) => array_search($key, CoaAkun::KELOMPOK, true));
    }

    private function filteredAccounts(string $search): Collection
    {
        $query = CoaAkun::aktif()->orderBy('kode');
        $search = trim($search);

        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(kode) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(nama) LIKE ?', [$term]);
            });
        }

        return $query->get()
            ->groupBy('kelompok')
            ->sortBy(fn ($group, $key) => array_search($key, CoaAkun::KELOMPOK, true));
    }

    // ── Panel / dialog toggles ────────────────────────────────────────────

    public function toggleCoaPanel(): void
    {
        $this->showCoaPanel = ! $this->showCoaPanel;
    }

    public function closeCoaPanel(): void
    {
        $this->showCoaPanel = false;
        $this->dispatch('focus-trigger', id: 'coaTrigger');
    }

    public function toggleDivisionDialog(): void
    {
        $this->showDivisionDialog = ! $this->showDivisionDialog;
    }

    public function closeDivisionDialog(): void
    {
        $this->showDivisionDialog = false;
        $this->dispatch('focus-trigger', id: 'divisionTrigger');
    }

    // ── Manual entry ──────────────────────────────────────────────────────

    public function openManualModal(): void
    {
        $this->authorizeAbility('create');
        $this->resetLines();
        $this->manualDivisiId = null;
        $this->manualTanggal = now()->toDateString();
        $this->coaSearch = '';
        $this->resetFeedback();
        $this->resetValidation();
        $this->showManualModal = true;
    }

    public function closeManualModal(): void
    {
        $this->showManualModal = false;
        $this->dispatch('focus-trigger', id: 'manualTrigger');
    }

    private function resetLines(): void
    {
        $this->manualLines = [
            ['account_id' => '', 'keterangan' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => '', 'keterangan' => '', 'debit' => '', 'credit' => ''],
        ];
        $this->manualLineIds = [$this->newLineId(), $this->newLineId()];
    }

    public function addLine(): void
    {
        $this->manualLines[] = ['account_id' => '', 'keterangan' => '', 'debit' => '', 'credit' => ''];
        $this->manualLineIds[] = $this->newLineId();
    }

    public function removeLine(int $index): void
    {
        if (! isset($this->manualLines[$index])) {
            return;
        }

        unset($this->manualLines[$index], $this->manualLineIds[$index]);
        $this->manualLines = array_values($this->manualLines);
        $this->manualLineIds = array_values($this->manualLineIds);
        $this->normalizeManualLines();
        $this->resetValidation();
    }

    /**
     * Livewire memanggil hook ini dengan $key = null ketika seluruh array
     * manualLines dikirim ulang (mis. saat "+ Tambah baris" dari browser).
     */
    public function updatedManualLines(mixed $value, ?string $key = null): void
    {
        $this->syncLineIds();

        if ($key === null || ! str_contains($key, '.')) {
            $this->normalizeManualLines();

            return;
        }

        [$index, $field] = explode('.', $key, 2);

        if (! isset($this->manualLines[$index])) {
            $this->normalizeManualLines();

            return;
        }

        if ($field === 'debit' && (float) $value > 0) {
            $this->manualLines[$index]['credit'] = '';
        } elseif ($field === 'credit' && (float) $value > 0) {
            $this->manualLines[$index]['debit'] = '';
        }
    }

    /** Satu baris hanya boleh berisi debit atau kredit. */
    private function normalizeManualLines(): void
    {
        foreach ($this->manualLines as $index => $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit <= 0 || $credit <= 0) {
                continue;
            }

            if ($debit >= $credit) {
                $this->manualLines[$index]['credit'] = '';
            } else {
                $this->manualLines[$index]['debit'] = '';
            }
        }
    }

    /** Pastikan setiap baris selalu punya id, juga saat array dikirim utuh dari browser. */
    private function syncLineIds(): void
    {
        $lines = array_values($this->manualLines);
        $ids = [];

        foreach ($lines as $index => $line) {
            $ids[$index] = $this->manualLineIds[$index] ?? $this->newLineId();
        }

        $this->manualLines = $lines;
        $this->manualLineIds = $ids;
    }

    private function newLineId(): string
    {
        return Str::uuid()->toString();
    }

    public function getManualTotalsProperty(): array
    {
        $debit = 0;
        $credit = 0;
        $withAccount = 0;
        $descriptionsReady = true;

        foreach ($this->manualLines as $line) {
            $debit += (int) round((float) ($line['debit'] ?? 0));
            $credit += (int) round((float) ($line['credit'] ?? 0));

            if (filled($line['account_id'] ?? null)) {
                $withAccount++;

                if (! filled(trim((string) ($line['keterangan'] ?? '')))) {
                    $descriptionsReady = false;
                }
            }
        }

        return [
            'debit' => $debit,
            'credit' => $credit,
            'selisih' => abs($debit - $credit),
            'balanced' => $debit > 0 && $debit === $credit,
            'descriptionsReady' => $descriptionsReady,
            'valid' => $debit > 0 && $debit === $credit && $withAccount >= 2 && $descriptionsReady,
        ];
    }

    public function saveManual(): void
    {
        $this->authorizeAbility('create');

        $this->validate([
            'manualDivisiId' => ['required', 'exists:jurnal_divisis,id'],
            'manualTanggal' => ['required', 'date'],
        ], [
            'manualDivisiId.required' => 'Pilih divisi terlebih dahulu.',
            'manualTanggal.required' => 'Tanggal wajib diisi.',
        ]);

        if (! $this->manualTotals['valid']) {
            $this->feedbackMessage = 'Pilih divisi dan minimal dua akun, isi keterangan tiap baris, lalu seimbangkan debit dan kredit.';
            $this->feedbackType = 'error';

            return;
        }

        $lines = array_values(array_filter(
            $this->manualLines,
            fn ($line) => filled($line['account_id'] ?? null)
                || (float) ($line['debit'] ?? 0) > 0
                || (float) ($line['credit'] ?? 0) > 0
        ));

        try {
            DB::transaction(function () use ($lines) {
                app(JurnalEntryService::class)->createManual([
                    'entry_date' => $this->manualTanggal,
                    'jurnal_divisi_id' => (int) $this->manualDivisiId,
                    'lines' => $lines,
                ], auth()->id());
            });

            $this->feedbackMessage = 'Jurnal manual berhasil disimpan.';
            $this->feedbackType = 'success';
            $this->closeManualModal();
        } catch (InvalidArgumentException $e) {
            $this->feedbackMessage = $e->getMessage();
            $this->feedbackType = 'error';
        }
    }

    // ── Edit / hapus baris jurnal ─────────────────────────────────────────

    public function openLineEdit(int $lineId): void
    {
        $this->authorizeAbility('update');

        $line = JurnalLine::with('account')->findOrFail($lineId);
        $this->editingLineId = $line->id;
        $this->editAccountId = (string) $line->account_id;
        $this->editKeterangan = (string) ($line->keterangan ?? '');
        $this->editDebit = $line->debit > 0 ? (string) $line->debit : '';
        $this->editCredit = $line->credit > 0 ? (string) $line->credit : '';
        $this->resetValidation();
        $this->resetFeedback();
    }

    public function closeLineEdit(): void
    {
        $this->editingLineId = null;
        $this->resetValidation();
    }

    public function updatedEditDebit(mixed $value): void
    {
        if ((float) $value > 0) {
            $this->editCredit = '';
        }
    }

    public function updatedEditCredit(mixed $value): void
    {
        if ((float) $value > 0) {
            $this->editDebit = '';
        }
    }

    public function saveLine(): void
    {
        $this->authorizeAbility('update');

        $this->validate([
            'editAccountId' => ['required', 'exists:coa_akuns,id'],
            'editKeterangan' => ['required', 'string', 'max:500'],
            'editDebit' => ['nullable', 'numeric', 'min:0'],
            'editCredit' => ['nullable', 'numeric', 'min:0'],
        ], [
            'editAccountId.required' => 'Akun wajib dipilih.',
            'editKeterangan.required' => 'Keterangan baris wajib diisi.',
        ]);

        $debit = (int) round((float) ($this->editDebit ?: 0));
        $credit = (int) round((float) ($this->editCredit ?: 0));

        if (($debit > 0) === ($credit > 0)) {
            $this->addError('editDebit', 'Satu baris hanya boleh berisi debit atau kredit.');

            return;
        }

        $line = JurnalLine::find($this->editingLineId);
        if (! $line) {
            $this->closeLineEdit();

            return;
        }

        $lama = [
            'account_id' => $line->account_id,
            'keterangan' => $line->keterangan,
            'debit' => $line->debit,
            'credit' => $line->credit,
        ];

        DB::transaction(function () use ($line, $debit, $credit, $lama) {
            $line->update([
                'account_id' => (int) $this->editAccountId,
                'keterangan' => trim($this->editKeterangan),
                'debit' => $debit,
                'credit' => $credit,
            ]);

            AuditLog::catat(auth()->id(), 'jurnal_baris_diubah', 'jurnal_line', (string) $line->id, [
                'lama' => $lama,
                'baru' => [
                    'account_id' => $line->account_id,
                    'keterangan' => $line->keterangan,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ],
            ]);
        });

        $this->feedbackMessage = 'Baris jurnal berhasil diperbarui.';
        $this->feedbackType = 'success';
        $this->closeLineEdit();
    }

    public function askDeleteLine(int $lineId): void
    {
        $this->authorizeAbility('delete');
        $this->deletingLineId = $lineId;
        $this->resetValidation();
        $this->resetFeedback();
    }

    public function cancelDeleteLine(): void
    {
        $this->deletingLineId = null;
    }

    public function deleteLine(): void
    {
        $this->authorizeAbility('delete');

        $line = JurnalLine::find($this->deletingLineId);
        if (! $line) {
            $this->deletingLineId = null;

            return;
        }

        $lama = [
            'jurnal_entry_id' => $line->jurnal_entry_id,
            'account_id' => $line->account_id,
            'keterangan' => $line->keterangan,
            'debit' => $line->debit,
            'credit' => $line->credit,
        ];

        DB::transaction(function () use ($line, $lama) {
            $line->delete();

            AuditLog::catat(auth()->id(), 'jurnal_baris_dihapus', 'jurnal_line', (string) $line->id, [
                'lama' => $lama,
            ]);
        });

        $this->feedbackMessage = 'Baris jurnal berhasil dihapus.';
        $this->feedbackType = 'success';
        $this->deletingLineId = null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function resetFeedback(): void
    {
        $this->feedbackMessage = null;
    }

    private function authorizeAccess(): void
    {
        $this->authorizeAbility('viewAny');
    }

    private function authorizeAbility(string $ability): void
    {
        abort_unless(auth()->user()?->can($ability, JurnalEntry::class), 403);
    }

    public function render()
    {
        $this->authorizeAccess();

        [$tahun, $bulan] = explode('-', $this->periode);

        $query = JurnalEntry::query()
            ->with(['lines.account', 'divisi', 'source'])
            ->whereYear('entry_date', $tahun)
            ->whereMonth('entry_date', $bulan);

        if ($this->divisiFilter !== '') {
            $query->where('jurnal_divisi_id', (int) $this->divisiFilter);
        }

        $entries = (clone $query)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(10);

        $totalsQuery = JurnalEntry::query()
            ->join('jurnal_lines', 'jurnal_lines.jurnal_entry_id', '=', 'jurnal_entries.id')
            ->whereYear('jurnal_entries.entry_date', $tahun)
            ->whereMonth('jurnal_entries.entry_date', $bulan);

        if ($this->divisiFilter !== '') {
            $totalsQuery->where('jurnal_entries.jurnal_divisi_id', (int) $this->divisiFilter);
        }

        $totals = [
            'debit' => (int) (clone $totalsQuery)->sum('jurnal_lines.debit'),
            'credit' => (int) (clone $totalsQuery)->sum('jurnal_lines.credit'),
        ];

        return view('livewire.finance.jurnal-umum-table', [
            'entries' => $entries,
            'totals' => $totals,
            'divisions' => $this->divisions,
        ]);
    }
}
