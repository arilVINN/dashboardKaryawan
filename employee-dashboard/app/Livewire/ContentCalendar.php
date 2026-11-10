<?php

namespace App\Livewire;

use App\Models\ContentEvent;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\User2;
use App\Services\NationalHolidayService;
use App\Support\DivisionPalette;
use App\Support\StatusPalette;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class ContentCalendar extends Component
{
    #[Url]
    public string $month = '';

    #[Url]
    public string $view = 'bulan';

    #[Url]
    public string $week = '';

    #[Url]
    public string $division = 'semua';

    public bool $showHolidays = true;

    public bool $showInternal = true;

    /** Read-only side panel. */
    public bool $drawerOpen = false;

    /** Create/edit dialog. */
    public bool $formOpen = false;

    /** True once the user changed a field in the edit dialog. */
    public bool $formDirty = false;

    public ?int $editingId = null;

    public bool $readOnly = false;

    public string $title = '';

    public string $start_date = '';

    public string $start_time = '';

    public string $end_date = '';

    public string $end_time = '';

    public string $formStatus = 'baru';

    public ?string $color = null;

    public ?string $division_id = null;

    public ?string $pic_user_id = null;

    public string $description = '';

    public ?string $popoverDate = null;

    /** @var array{id: int, start: string, end: string|null}|null */
    public ?array $lastMove = null;

    public bool $loadError = false;

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }

        if (! in_array($this->view, ['bulan', 'minggu', 'daftar'], true)) {
            $this->view = 'bulan';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->week)) {
            $this->week = '';
        }

        if ($this->view === 'minggu' && $this->week === '') {
            $this->week = $this->defaultWeek()->toDateString();
        }

        if (in_array($this->division, ['semua', 'umum'], true)) {
            // keep the sentinel values
        } elseif (! Divisi::whereKey($this->division)->exists()) {
            $this->division = 'semua';
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'start_date', 'start_time', 'end_date', 'end_time', 'formStatus', 'color', 'division_id', 'pic_user_id', 'description'], true)) {

            if ($this->formOpen) {
                $this->formDirty = true;
            }
        }

        if (in_array($property, ['month', 'view', 'division'], true)) {
            $this->popoverDate = null;
        }
    }

    public function previous(): void
    {
        if ($this->view === 'minggu') {
            $start = $this->weekAnchor()->subWeek();
            $this->week = $start->toDateString();
            $this->month = $start->format('Y-m');
            $this->popoverDate = null;

            return;
        }

        $this->month = $this->anchor()->subMonth()->format('Y-m');
        $this->popoverDate = null;
    }

    public function next(): void
    {
        if ($this->view === 'minggu') {
            $start = $this->weekAnchor()->addWeek();
            $this->week = $start->toDateString();
            $this->month = $start->format('Y-m');
            $this->popoverDate = null;

            return;
        }

        $this->month = $this->anchor()->addMonth()->format('Y-m');
        $this->popoverDate = null;
    }

    public function goToday(): void
    {
        $this->month = now()->format('Y-m');
        $this->week = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->popoverDate = null;
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['bulan', 'minggu', 'daftar'], true)) {
            return;
        }

        if ($view === 'minggu' && $this->view !== 'minggu') {
            $this->week = $this->defaultWeek()->toDateString();
        }

        $this->view = $view;
        $this->popoverDate = null;
    }

    public function toggleHolidays(): void
    {
        $this->showHolidays = ! $this->showHolidays;
        $this->popoverDate = null;
    }

    public function toggleInternal(): void
    {
        $this->showInternal = ! $this->showInternal;
        $this->popoverDate = null;
    }

    public function resetFilters(): void
    {
        $this->division = 'semua';
        $this->showHolidays = true;
        $this->showInternal = true;
        $this->popoverDate = null;
    }

    public function openDay(string $date): void
    {
        $this->popoverDate = $date;
    }

    public function closePopover(): void
    {
        $this->popoverDate = null;
    }

    public function openCreate(?string $date = null): void
    {
        $this->authorize('create', ContentEvent::class);

        $this->resetValidation();
        $this->drawerOpen = false;
        $this->formOpen = true;
        $this->formDirty = false;
        $this->editingId = null;
        $this->readOnly = false;
        $this->title = '';
        $this->start_date = $date ?: now()->toDateString();
        $this->start_time = '';
        $this->end_date = '';
        $this->end_time = '';
        $this->formStatus = 'baru';
        $this->division_id = auth()->user()?->karyawan?->divisi_id_divisi;
        $this->color = DivisionPalette::eventColorForDivision($this->division_id);
        $this->pic_user_id = null;
        $this->description = '';
        $this->popoverDate = null;

        $this->dispatch('content-focus-title');
    }

    public function openEvent(int $id): void
    {
        $event = ContentEvent::with(['division', 'pic.karyawan', 'creator.karyawan'])->findOrFail($id);

        $this->authorize('view', $event);

        $this->resetValidation();
        $this->formOpen = false;
        $this->formDirty = false;
        $this->drawerOpen = true;
        $this->editingId = $event->id;
        $this->readOnly = ! auth()->user()->can('update', $event);
        $this->title = $event->title;
        $this->start_date = $event->start_date->toDateString();
        $this->start_time = $event->startTimeLabel() ?? '';
        $this->end_date = $event->end_date?->toDateString() ?? '';
        $this->end_time = $event->endTimeLabel() ?? '';
        $this->formStatus = $event->status;
        $this->division_id = $event->division_id;
        $this->color = $event->color ?: DivisionPalette::eventColorForDivision($event->division_id);
        $this->pic_user_id = $event->pic_user_id;
        $this->description = $event->description ?? '';
        $this->popoverDate = null;

        $this->dispatch('content-focus-drawer');
    }

    /**
     * Move from the read-only panel into the edit dialog.
     */
    public function editEvent(): void
    {
        if (! $this->editingId) {
            return;
        }

        $event = ContentEvent::findOrFail($this->editingId);
        $this->authorize('update', $event);

        $this->resetValidation();
        $this->readOnly = false;
        $this->drawerOpen = false;
        $this->formOpen = true;
        $this->formDirty = false;

        $this->dispatch('content-focus-title');
    }

    public function closeDrawer(): void
    {
        $this->drawerOpen = false;
        $this->editingId = null;
        $this->readOnly = false;
        $this->resetValidation();
        $this->dispatch('content-drawer-closed');
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->drawerOpen = false;
        $this->formDirty = false;
        $this->editingId = null;
        $this->readOnly = false;
        $this->resetValidation();
        $this->dispatch('content-drawer-closed');
    }

    public function save(): void
    {
        $this->title = trim($this->title);
        $this->start_date = (string) $this->start_date;
        $this->start_time = (string) $this->start_time;
        $this->end_date = (string) $this->end_date;
        $this->end_time = (string) $this->end_time;
        $this->division_id = $this->division_id ?: null;
        $this->pic_user_id = $this->pic_user_id ?: null;

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if ($this->start_time === '') {
                        $fail('Isi jam mulai terlebih dahulu.');

                        return;
                    }

                    $sameDay = $this->end_date === '' || $this->end_date === $this->start_date;

                    if ($sameDay && $value < $this->start_time) {
                        $fail('Jam selesai tidak boleh sebelum jam mulai.');
                    }
                },
            ],
            'formStatus' => ['required', Rule::in(array_keys(StatusPalette::selectable()))],
            'color' => ['nullable', Rule::in(array_keys(DivisionPalette::EVENT_COLORS))],
            'division_id' => ['nullable', Rule::exists('divisis', 'id_divisi')],
            'pic_user_id' => ['nullable', Rule::exists('users2', 'id_user')],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'start_time' => $validated['start_time'] ?: null,
            'end_date' => $validated['end_date'] ?: null,
            'end_time' => $validated['end_time'] ?: null,
            'division_id' => $validated['division_id'] ?: null,
            'status' => $validated['formStatus'],
            'color' => $validated['color'] ?: null,
            'pic_user_id' => $validated['pic_user_id'] ?: null,
            'description' => $this->description !== '' ? $this->description : null,
        ];

        if ($this->editingId) {
            $event = ContentEvent::findOrFail($this->editingId);
            $this->authorize('update', $event);

            $event->update($payload);
            $this->closeForm();
            $this->dispatch('content-toast', message: 'Perubahan disimpan', type: 'success');

            return;
        }

        $this->authorize('create', ContentEvent::class);

        ContentEvent::create($payload + [
            'created_by' => auth()->id(),
        ]);

        $this->closeForm();
        $this->dispatch('content-toast', message: 'Event ditambahkan', type: 'success');
    }

    public function deleteEvent(): void
    {
        if (! $this->editingId) {
            return;
        }

        $event = ContentEvent::findOrFail($this->editingId);
        $this->authorize('delete', $event);

        $event->delete();
        $this->lastMove = null;
        $this->closeForm();
        $this->dispatch('content-toast', message: 'Event dihapus', type: 'success');
    }

    public function moveEvent(int $id, string $date): void
    {
        $event = ContentEvent::findOrFail($id);
        $this->authorize('update', $event);

        try {
            $target = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        $delta = (int) round($event->start_date->diffInDays($target, false));

        if ($delta === 0) {
            return;
        }

        $snapshot = [
            'id' => $event->id,
            'start' => $event->start_date->toDateString(),
            'end' => $event->end_date?->toDateString(),
        ];

        try {
            $event->start_date = $event->start_date->copy()->addDays($delta);
            $event->end_date = $event->end_date?->copy()->addDays($delta);
            $event->save();
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('content-toast', message: 'Gagal memindahkan event. Coba lagi.', type: 'error');
            $this->dispatch('content-calendar-refresh');

            return;
        }

        $this->lastMove = $snapshot;
        $this->dispatch('content-toast', message: 'Event dipindahkan', type: 'success', action: 'undo');
    }

    public function undoMove(): void
    {
        if (! $this->lastMove || empty($this->lastMove['id'])) {
            return;
        }

        $event = ContentEvent::find($this->lastMove['id']);

        if ($event) {
            try {
                $this->authorize('update', $event);
                $event->update([
                    'start_date' => $this->lastMove['start'],
                    'end_date' => $this->lastMove['end'],
                ]);
            } catch (\Throwable $exception) {
                report($exception);
                $this->dispatch('content-toast', message: 'Tidak dapat membatalkan perubahan.', type: 'error');
                $this->lastMove = null;

                return;
            }
        }

        $this->lastMove = null;
        $this->dispatch('content-toast', message: 'Perubahan dibatalkan', type: 'success');
    }

    public function retry(): void
    {
        $this->loadError = false;
    }

    public function render()
    {
        $anchor = $this->anchor();

        if ($this->view === 'minggu') {
            [$weekStart, $weekEnd] = $this->range();
            $monthLabel = $this->formatRange($weekStart, $weekEnd);
        } else {
            $monthLabel = $anchor->copy()->locale('id')->translatedFormat('F Y');
        }

        $statusOptions = StatusPalette::selectable();
        $eventColors = DivisionPalette::EVENT_COLORS;
        $timeOptions = $this->timeOptions();
        $selectedColorLabel = null;

        $divisionOptions = collect();
        $divisionColors = [];
        $users = collect();
        $events = collect();
        $holidays = collect();
        $days = [];
        $monthWeeks = [];
        $weekDays = [];
        $listGroups = collect();
        $isEmpty = true;
        $drawerDateLabel = 'Belum dipilih';
        $drawerTimeLabel = null;
        $creatorName = null;
        $createdHuman = null;
        $selectedDivisionName = 'Umum';
        $selectedPicName = null;
        $selectedPicInitials = null;

        try {
            $divisionOptions = Divisi::query()->orderBy('id_divisi')->get();
            $divisionColors = DivisionPalette::map();
            $users = User2::query()
                ->with('karyawan')
                ->orderBy('username')
                ->get()
                ->sortBy(fn (User2 $user) => $user->karyawan?->nama ?? $user->username)
                ->values();

            [$rangeStart, $rangeEnd] = $this->range();

            app(NationalHolidayService::class)->ensureYears([
                $rangeStart->year,
                $rangeEnd->year,
            ]);

            if ($this->showInternal) {
                $events = ContentEvent::query()
                    ->with(['division', 'pic.karyawan', 'creator.karyawan'])
                    ->overlapping($rangeStart, $rangeEnd)
                    ->when($this->division === 'umum', fn ($query) => $query->whereNull('division_id'))
                    ->when(! in_array($this->division, ['semua', 'umum'], true), fn ($query) => $query->where('division_id', $this->division))
                    ->orderBy('start_date')
                    ->orderBy('title')
                    ->get();
            }

            if ($this->showHolidays) {
                $holidays = Holiday::query()
                    ->whereDate('date', '>=', $rangeStart->toDateString())
                    ->whereDate('date', '<=', $rangeEnd->toDateString())
                    ->whereIn('type', Holiday::DAY_OFF_TYPES)
                    ->orderBy('date')
                    ->orderBy('name')
                    ->get();
            }

            $canEdit = [];

            foreach ($events as $event) {
                $canEdit[$event->id] = auth()->user()->can('update', $event);
            }

            if ($this->view === 'bulan' || $this->view === 'minggu') {
                $days = $this->buildDays($rangeStart, $rangeEnd, $events, $holidays, $divisionColors, $canEdit);

                if ($this->view === 'bulan') {
                    $monthWeeks = array_chunk(array_values($days), 7);
                } else {
                    $weekDays = array_values($days);
                }
            }

            $listGroups = $this->buildListGroups($events, $holidays, $divisionColors, $canEdit);

            if ($this->view === 'daftar') {
                $isEmpty = $listGroups->isEmpty();
            } else {
                $isEmpty = collect($days)->every(fn (array $day) => $day['items'] === []);
            }

            if ($this->drawerOpen && $this->start_date !== '') {
                try {
                    $start = Carbon::parse($this->start_date);
                    $end = $this->end_date !== '' ? Carbon::parse($this->end_date) : null;
                    $drawerDateLabel = $this->formatRange($start, $end);
                } catch (\Throwable) {
                    $drawerDateLabel = 'Belum dipilih';
                }

                $startTime = trim($this->start_time);

                if ($startTime !== '') {
                    $endTime = trim($this->end_time);
                    $drawerTimeLabel = $endTime !== '' ? "{$startTime}–{$endTime}" : $startTime;
                }
            }

            $selectedDivision = $this->division_id
                ? $divisionOptions->firstWhere('id_divisi', $this->division_id)
                : null;
            $selectedDivisionName = $selectedDivision?->nama_divisi ?? 'Umum';
            $selectedColorLabel = $this->color && isset($eventColors[$this->color])
                ? $eventColors[$this->color]['label']
                : null;

            $selectedUser = $this->pic_user_id
                ? $users->firstWhere('id_user', $this->pic_user_id)
                : null;
            $selectedPicName = $selectedUser?->karyawan?->nama ?? $selectedUser?->username;
            $selectedPicInitials = $selectedPicName ? $this->initials($selectedPicName) : null;

            if ($this->editingId) {
                $drawerEvent = ContentEvent::with(['creator.karyawan', 'creator'])->find($this->editingId);

                if ($drawerEvent) {
                    $creatorName = $drawerEvent->creator?->karyawan?->nama
                        ?? $drawerEvent->creator?->username
                        ?? 'Sistem';
                    $createdHuman = $drawerEvent->created_at?->locale('id')->diffForHumans();
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
            $this->loadError = true;
        }

        $popoverItems = $this->popoverDate && isset($days[$this->popoverDate])
            ? $days[$this->popoverDate]['items']
            : [];

        return view('livewire.content-calendar', [
            'anchor' => $anchor,
            'monthLabel' => $monthLabel,
            'statusOptions' => $statusOptions,
            'eventColors' => $eventColors,
            'timeOptions' => $timeOptions,
            'selectedColorLabel' => $selectedColorLabel,
            'divisionOptions' => $divisionOptions,
            'divisionColors' => $divisionColors,
            'users' => $users,
            'days' => $days,
            'monthWeeks' => $monthWeeks,
            'weekDays' => $weekDays,
            'listGroups' => $listGroups,
            'isEmpty' => $isEmpty,
            'hasActiveFilters' => $this->hasActiveFilters(),
            'drawerDateLabel' => $drawerDateLabel,
            'drawerTimeLabel' => $drawerTimeLabel,
            'creatorName' => $creatorName,
            'createdHuman' => $createdHuman,
            'selectedDivisionName' => $selectedDivisionName,
            'selectedPicName' => $selectedPicName,
            'selectedPicInitials' => $selectedPicInitials,
            'popoverItems' => $popoverItems,
            'today' => Carbon::today()->toDateString(),
            'holidayColors' => DivisionPalette::HOLIDAY,
        ]);
    }

    private function anchor(): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        } catch (\Throwable) {
            $this->month = now()->format('Y-m');

            return now()->startOfMonth();
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        $anchor = $this->anchor();

        if ($this->view === 'minggu') {
            $start = $this->weekAnchor();

            return [$start, $start->copy()->addDays(6)];
        }

        if ($this->view === 'daftar') {
            return [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()];
        }

        $start = $anchor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);

        return [$start, $start->copy()->addDays(41)];
    }

    /**
     * The Monday that anchors the week view.
     */
    private function weekAnchor(): Carbon
    {
        if ($this->week !== '') {
            try {
                return Carbon::parse($this->week)->startOfWeek(Carbon::MONDAY);
            } catch (\Throwable) {
                // fall through to the month anchor
            }
        }

        return $this->defaultWeek();
    }

    /**
     * Current week when browsing this month, otherwise the first week of the month.
     */
    private function defaultWeek(): Carbon
    {
        $anchor = $this->anchor();
        $today = Carbon::today();

        if ($anchor->isSameMonth($today)) {
            return $today->copy()->startOfWeek(Carbon::MONDAY);
        }

        return $anchor->copy()->startOfWeek(Carbon::MONDAY);
    }

    /**
     * @param  Collection<int, ContentEvent>  $events
     * @param  Collection<int, Holiday>  $holidays
     * @param  array<string, array{fill: string, border: string, dot: string}>  $divisionColors
     * @param  array<int, bool>  $canEdit
     * @return array<string, array{date: string, day: int, label: string, is_today: bool, is_other_month: bool, items: array<int, array<string, mixed>>}>
     */
    private function buildDays(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        $events,
        $holidays,
        array $divisionColors,
        array $canEdit
    ): array {
        $anchor = $this->anchor();
        $today = Carbon::today()->toDateString();
        $days = [];

        $cursor = $rangeStart->copy();

        while ($cursor->lessThanOrEqualTo($rangeEnd)) {
            $date = $cursor->toDateString();
            $days[$date] = [
                'date' => $date,
                'day' => (int) $cursor->format('j'),
                'label' => $cursor->copy()->locale('id')->translatedFormat('l, j F Y'),
                'is_today' => $date === $today,
                'is_other_month' => $this->view !== 'minggu'
                    && ($cursor->month !== $anchor->month || $cursor->year !== $anchor->year),
                'items' => [],
            ];
            $cursor->addDay();
        }

        foreach ($events as $event) {
            $base = $this->eventItem($event, $divisionColors, $canEdit);
            $start = $event->start_date->copy();
            $end = $event->effectiveEndDate();

            $from = $start->lessThan($rangeStart) ? $rangeStart->copy() : $start->copy();
            $to = $end->greaterThan($rangeEnd) ? $rangeEnd->copy() : $end->copy();

            if ($from->greaterThan($to)) {
                continue;
            }

            $cursor = $from->copy();

            while ($cursor->lessThanOrEqualTo($to)) {
                $date = $cursor->toDateString();

                if (isset($days[$date])) {
                    $item = $base;
                    $item['time_label'] = $this->eventDayTimeLabel($event, $cursor);
                    $item['time_label_short'] = $this->eventDayTimeShortLabel($event, $cursor);
                    $item['sort_time'] = $item['time_label_short'];

                    $days[$date]['items'][] = $item + [
                        'is_start' => $cursor->equalTo($start),
                        'is_end' => $cursor->equalTo($end),
                    ];
                }

                $cursor->addDay();
            }
        }

        foreach ($holidays as $holiday) {
            $date = $holiday->date->toDateString();

            if (isset($days[$date])) {
                $days[$date]['items'][] = $this->holidayItem($holiday) + [
                    'is_start' => true,
                    'is_end' => true,
                ];
            }
        }

        foreach ($days as &$day) {
            usort($day['items'], fn (array $a, array $b) => $this->compareItems($a, $b));
        }
        unset($day);

        return $days;
    }

    /**
     * @param  Collection<int, ContentEvent>  $events
     * @param  Collection<int, Holiday>  $holidays
     * @param  array<string, array{fill: string, border: string, dot: string}>  $divisionColors
     * @param  array<int, bool>  $canEdit
     */
    private function buildListGroups($events, $holidays, array $divisionColors, array $canEdit)
    {
        $items = [];

        foreach ($events as $event) {
            $items[] = $this->eventItem($event, $divisionColors, $canEdit) + [
                'list_date' => $event->start_date->toDateString(),
            ];
        }

        foreach ($holidays as $holiday) {
            $items[] = $this->holidayItem($holiday) + [
                'list_date' => $holiday->date->toDateString(),
            ];
        }

        usort($items, function (array $a, array $b) {
            $byDate = $a['list_date'] <=> $b['list_date'];

            return $byDate !== 0 ? $byDate : $this->compareItems($a, $b);
        });

        return collect($items)
            ->groupBy('list_date')
            ->sortKeys()
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->locale('id')->translatedFormat('l, j F Y'),
                    'items' => $group->values()->all(),
                ];
            })
            ->values();
    }

    /**
     * @param  array<string, array{fill: string, border: string, dot: string}>  $divisionColors
     * @param  array<int, bool>  $canEdit
     * @return array<string, mixed>
     */
    private function eventItem(ContentEvent $event, array $divisionColors, array $canEdit): array
    {
        $status = StatusPalette::content($event->status);
        $picName = $event->pic?->karyawan?->nama ?? $event->pic?->username;

        return [
            'type' => 'event',
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_date->toDateString(),
            'end' => $event->effectiveEndDate()->toDateString(),
            'date_label' => $this->formatRange($event->start_date, $event->end_date),
            'time_label' => $event->timeLabel(),
            'time_label_short' => $event->startTimeLabel(),
            'sort_time' => $event->startTimeLabel(),
            'end_time' => $event->endTimeLabel(),
            'division_id' => $event->division_id,
            'division_name' => $event->division?->nama_divisi ?? 'Umum',
            'colors' => DivisionPalette::event($event->color) ?? DivisionPalette::for($event->division_id, $divisionColors),
            'color' => $event->color,
            'status' => $event->status,
            'status_label' => $status['label'] ?? $event->status,
            'pic_name' => $picName,
            'pic_initials' => $picName ? $this->initials($picName) : null,
            'description' => $event->description,
            'creator_name' => $event->creator?->karyawan?->nama
                ?? $event->creator?->username
                ?? 'Sistem',
            'created_human' => $event->created_at?->locale('id')->diffForHumans() ?? '-',
            'can_edit' => $canEdit[$event->id] ?? false,
        ];
    }

    /**
     * Show the full time range on single-day events, but only the relevant
     * start/end time on multi-day event pills.
     */
    private function eventDayTimeLabel(ContentEvent $event, Carbon $date): ?string
    {
        if (! $event->isMultiDay()) {
            return $event->timeLabel();
        }

        if ($date->isSameDay($event->start_date)) {
            return $event->startTimeLabel();
        }

        if ($date->isSameDay($event->effectiveEndDate())) {
            return $event->endTimeLabel();
        }

        return null;
    }

    /**
     * Compact start/end time for month cells where space is limited.
     */
    private function eventDayTimeShortLabel(ContentEvent $event, Carbon $date): ?string
    {
        if (! $event->isMultiDay()) {
            return $event->startTimeLabel();
        }

        if ($date->isSameDay($event->start_date)) {
            return $event->startTimeLabel();
        }

        if ($date->isSameDay($event->effectiveEndDate())) {
            return $event->endTimeLabel();
        }

        return null;
    }

    /**
     * Shared shape for read-only holiday entries.
     *
     * @return array<string, mixed>
     */
    private function holidayItem(Holiday $holiday): array
    {
        return [
            'type' => 'holiday',
            'id' => 'holiday-'.$holiday->id,
            'title' => $holiday->name,
            'date' => $holiday->date->toDateString(),
            'date_label' => $holiday->date->copy()->locale('id')->translatedFormat('j M Y'),
            'holiday_type' => $holiday->type,
            'colors' => DivisionPalette::HOLIDAY,
            'can_edit' => false,
        ];
    }

    /**
     * Shared ordering for month cells, week columns, the list, and the day
     * popover: holidays first, then the untimed entries of the day, then the
     * timed entries from earliest to latest.
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function compareItems(array $a, array $b): int
    {
        return [
            $this->itemGroup($a),
            $a['sort_time'] ?? '',
            $a['end_time'] ?? '',
            mb_strtolower($a['title']),
        ] <=> [
            $this->itemGroup($b),
            $b['sort_time'] ?? '',
            $b['end_time'] ?? '',
            mb_strtolower($b['title']),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function itemGroup(array $item): int
    {
        if (($item['type'] ?? '') === 'holiday') {
            return 0;
        }

        // Multi-day events only show a time on their first and last day, so the
        // per-day time (not the event's start time) decides the row.
        return empty($item['sort_time']) ? 1 : 2;
    }

    private function formatRange(Carbon $start, ?Carbon $end): string
    {
        $start = $start->copy()->locale('id');

        if (! $end || $end->equalTo($start)) {
            return $start->translatedFormat('j M Y');
        }

        $end = $end->copy()->locale('id');

        if ($start->year === $end->year && $start->month === $end->month) {
            return $start->translatedFormat('j').'–'.$end->translatedFormat('j M Y');
        }

        if ($start->year === $end->year) {
            return $start->translatedFormat('j M').' – '.$end->translatedFormat('j M Y');
        }

        return $start->translatedFormat('j M Y').' – '.$end->translatedFormat('j M Y');
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, fn ($part) => $part !== ''));

        if ($parts === []) {
            return '?';
        }

        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    /**
     * 5-minute time options, keeping any off-grid value already stored.
     *
     * @return array<int, string>
     */
    private function timeOptions(): array
    {
        $options = [];

        for ($minutes = 0; $minutes < 24 * 60; $minutes += 5) {
            $options[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        foreach ([$this->start_time, $this->end_time] as $value) {
            if ($value !== ''
                && preg_match('/^\d{2}:\d{2}$/', $value)
                && ! in_array($value, $options, true)) {
                $options[] = $value;
            }
        }

        sort($options);

        return $options;
    }

    private function hasActiveFilters(): bool
    {
        return $this->division !== 'semua'
            || ! $this->showHolidays
            || ! $this->showInternal;
    }
}
