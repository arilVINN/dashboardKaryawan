<?php

namespace App\Livewire\Finance;

use App\Livewire\Finance\Concerns\HasMonthPicker;
use App\Models\CoaAkun;
use App\Models\JurnalDivisi;
use App\Models\JurnalEntry;
use App\Services\Finance\JurnalEntryService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class JurnalUmumTable extends Component
{
    use HasMonthPicker;
    use WithPagination;

    public string $periode;

    public string $divisiFilter = '';

    public bool $showCoaPanel = false;

    public bool $showDivisionDialog = false;

    public bool $showManualModal = false;

    public string $coaSearch = '';

    public ?int $cancelEntryId = null;

    public ?string $cancelEntryRef = null;

    public string $cancelReason = '';

    public ?string $manualDivisiId = null;

    public string $manualTanggal = '';

    public string $manualKeterangan = '';

    public array $manualLines = [];

    public array $lineSearch = [];

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
        parent::selectMonth($month);
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
        return JurnalDivisi::orderBy('nama')->get();
    }

    public function getCoaGroupsProperty(): Collection
    {
        return $this->filteredAccounts($this->coaSearch);
    }

    public function accountsForLine(int $index): Collection
    {
        return $this->filteredAccounts($this->lineSearch[$index] ?? '');
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
        $this->manualKeterangan = '';
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
            ['account_id' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => '', 'debit' => '', 'credit' => ''],
        ];
        $this->lineSearch = ['', ''];
    }

    public function addLine(): void
    {
        $this->manualLines[] = ['account_id' => '', 'debit' => '', 'credit' => ''];
        $this->lineSearch[] = '';
    }

    public function removeLine(int $index): void
    {
        if (! isset($this->manualLines[$index])) {
            return;
        }

        unset($this->manualLines[$index], $this->lineSearch[$index]);
        $this->manualLines = array_values($this->manualLines);
        $this->lineSearch = array_values($this->lineSearch);
        $this->resetValidation();
    }

    public function updatedManualLines(mixed $value, string $key): void
    {
        if (! str_contains($key, '.')) {
            return;
        }

        [$index, $field] = explode('.', $key, 2);

        if (! isset($this->manualLines[$index])) {
            return;
        }

        if ($field === 'debit' && (float) $value > 0) {
            $this->manualLines[$index]['credit'] = '';
        } elseif ($field === 'credit' && (float) $value > 0) {
            $this->manualLines[$index]['debit'] = '';
        }
    }

    public function getCanWriteKeteranganProperty(): bool
    {
        return filled($this->manualDivisiId)
            && collect($this->manualLines)->contains(fn ($line) => filled($line['account_id']));
    }

    public function getManualTotalsProperty(): array
    {
        $debit = 0;
        $credit = 0;
        $withAccount = 0;

        foreach ($this->manualLines as $line) {
            $debit += (int) round((float) ($line['debit'] ?? 0));
            $credit += (int) round((float) ($line['credit'] ?? 0));

            if (filled($line['account_id'] ?? null)) {
                $withAccount++;
            }
        }

        return [
            'debit' => $debit,
            'credit' => $credit,
            'selisih' => abs($debit - $credit),
            'balanced' => $debit > 0 && $debit === $credit,
            'valid' => $debit > 0 && $debit === $credit && $withAccount >= 2,
        ];
    }

    public function saveManual(): void
    {
        $this->authorizeAbility('create');

        $this->validate([
            'manualDivisiId' => ['required', 'exists:jurnal_divisis,id'],
            'manualTanggal' => ['required', 'date'],
            'manualKeterangan' => ['required', 'string', 'max:500'],
        ], [
            'manualDivisiId.required' => 'Pilih divisi terlebih dahulu.',
            'manualTanggal.required' => 'Tanggal wajib diisi.',
            'manualKeterangan.required' => 'Keterangan wajib diisi.',
        ]);

        if (! $this->canWriteKeterangan || ! $this->manualTotals['valid']) {
            $this->feedbackMessage = 'Pilih divisi dan minimal dua akun, lalu seimbangkan debit dan kredit.';
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
                    'keterangan' => trim($this->manualKeterangan),
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

    // ── Reversing entry ───────────────────────────────────────────────────

    public function openCancelModal(int $id): void
    {
        $this->authorizeAbility('cancel');

        $entry = JurnalEntry::findOrFail($id);
        $this->cancelEntryId = $entry->id;
        $this->cancelEntryRef = $entry->ref_code;
        $this->cancelReason = '';
        $this->resetValidation();
        $this->resetFeedback();
    }

    public function closeCancelModal(): void
    {
        $this->cancelEntryId = null;
        $this->cancelEntryRef = null;
        $this->cancelReason = '';
        $this->resetValidation();
    }

    public function confirmCancel(): void
    {
        $this->authorizeAbility('cancel');

        $this->validate([
            'cancelReason' => ['required', 'string', 'max:500'],
        ], [
            'cancelReason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $entry = JurnalEntry::find($this->cancelEntryId);
        if (! $entry) {
            $this->closeCancelModal();

            return;
        }

        DB::transaction(function () use ($entry) {
            app(JurnalEntryService::class)->reverse($entry, $this->cancelReason, auth()->id());
        });

        $this->feedbackMessage = 'Jurnal ' . $entry->ref_code . ' dibatalkan dengan jurnal balik.';
        $this->feedbackType = 'success';
        $this->closeCancelModal();
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
