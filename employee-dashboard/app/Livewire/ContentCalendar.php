<?php

namespace App\Livewire;

use App\Models\ContentAccount;
use App\Models\ContentEvent;
use App\Models\ContentEventComment;
use App\Models\ContentType;
use App\Support\ContentCalendarAccess;
use App\Support\ContentTypePalette;
use App\Support\StatusPalette;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

class ContentCalendar extends Component
{
    #[Url]
    public string $month = '';

    #[Url]
    public string $view = 'bulan';

    #[Url]
    public string $week = '';

    #[Url]
    public string $account = '';

    public string $typeFilter = 'semua';

    public string $statusFilter = 'semua';

    /** Detail modal for an existing event (edits autosave). */
    public bool $detailOpen = false;

    /** Create modal. */
    public bool $formOpen = false;

    public bool $formDirty = false;

    public ?int $editingId = null;

    public bool $readOnly = false;

    public bool $canDelete = false;

    public string $creatorName = '';

    public string $creatorAgo = '';

    public string $title = '';

    public string $event_date = '';

    public string $post_time = '';

    public string $content_type = '';

    public string $formStatus = 'process';

    public string $description = '';

    /** @var array<int, array{text: string, done: bool}> */
    public array $todos = [];

    public string $content_url = '';

    public string $commentBody = '';

    public ?string $saveError = null;

    public ?string $popoverDate = null;

    /** @var array{id: int, from: string}|null */
    public ?array $lastMove = null;

    public bool $loadError = false;

    /** Add-account modal. */
    public bool $accountFormOpen = false;

    public string $accountName = '';

    public function booted(): void
    {
        abort_unless(ContentCalendarAccess::allows(auth()->user()), 403);
    }

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }

        if (! in_array($this->view, ['bulan', 'minggu', 'daftar'], true)) {
            $this->view = 'bulan';
        }

        if ($this->view === 'minggu' && $this->week === '') {
            $this->week = $this->defaultWeek()->toDateString();
        }

        $account = ContentAccount::resolve($this->account);
        $this->account = $account?->slug ?? '';
    }

    public function updatedAccount(): void
    {
        $account = ContentAccount::resolve($this->account);

        if ($account) {
            $this->account = $account->slug;
        }

        $this->popoverDate = null;

        $this->dispatch('content-account-changed', name: $account?->name ?? '');
    }

    public function openAccountForm(): void
    {
        $this->resetValidation();
        $this->accountName = '';
        $this->accountFormOpen = true;

        $this->dispatch('content-focus-account-name');
    }

    public function closeAccountForm(): void
    {
        $this->accountFormOpen = false;
        $this->accountName = '';
        $this->resetValidation();
    }

    public function saveAccount(): void
    {
        $this->validate([
            'accountName' => ['required', 'string', 'max:80'],
        ], [
            'accountName.required' => 'Nama akun wajib diisi.',
            'accountName.max' => 'Nama akun maksimal 80 karakter.',
        ]);

        $name = trim($this->accountName);

        $duplicate = ContentAccount::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($duplicate) {
            $this->addError('accountName', 'Nama akun sudah dipakai.');

            return;
        }

        $account = ContentAccount::create([
            'slug' => $this->uniqueAccountSlug($name),
            'name' => $name,
            'sort_order' => (int) ContentAccount::query()->active()->max('sort_order') + 1,
            'is_active' => true,
        ]);

        $this->account = $account->slug;
        $this->popoverDate = null;
        $this->accountFormOpen = false;
        $this->accountName = '';
        $this->resetValidation();

        $this->dispatch('content-account-changed', name: $account->name);
        $this->dispatch('content-toast', message: 'Akun ditambahkan', type: 'success');
    }

    private function uniqueAccountSlug(string $name): string
    {
        $base = rtrim(Str::slug($name), '-');
        $base = $base !== '' ? substr($base, 0, 40) : 'akun';

        $slug = $base;
        $suffix = 2;

        while (ContentAccount::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function updated(string $property): void
    {
        if ($property === 'post_time') {
            $this->post_time = $this->normalizeTime($this->post_time);
        }

        if (str_starts_with($property, 'todos.')) {
            if ($this->formOpen) {
                $this->formDirty = true;

                return;
            }

            if ($this->detailOpen && ! $this->readOnly) {
                $this->persistTodos();
            }

            return;
        }

        if ($this->formOpen) {
            if (in_array($property, ['title', 'event_date', 'post_time', 'content_type', 'formStatus', 'description', 'content_url'], true)) {
                $this->formDirty = true;
            }

            return;
        }

        if ($this->detailOpen && ! $this->readOnly) {
            $map = [
                'title' => 'title',
                'event_date' => 'event_date',
                'post_time' => 'post_time',
                'content_type' => 'content_type',
                'formStatus' => 'status',
                'description' => 'description',
                'content_url' => 'content_url',
            ];

            if (isset($map[$property])) {
                $this->saveField($property);
            }
        }
    }

    public function previous(): void
    {
        if ($this->view === 'minggu') {
            $this->week = $this->weekAnchor()->subWeek()->toDateString();
            $this->month = $this->weekAnchor()->format('Y-m');
        } else {
            $this->month = $this->anchor()->subMonthNoOverflow()->format('Y-m');
        }

        $this->popoverDate = null;
    }

    public function next(): void
    {
        if ($this->view === 'minggu') {
            $this->week = $this->weekAnchor()->addWeek()->toDateString();
            $this->month = $this->weekAnchor()->format('Y-m');
        } else {
            $this->month = $this->anchor()->addMonthNoOverflow()->format('Y-m');
        }

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

    public function resetFilters(): void
    {
        $this->typeFilter = 'semua';
        $this->statusFilter = 'semua';
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
        $this->popoverDate = null;
        $this->detailOpen = false;
        $this->formOpen = true;
        $this->formDirty = false;
        $this->editingId = null;
        $this->readOnly = false;
        $this->canDelete = false;
        $this->saveError = null;
        $this->title = '';
        $this->event_date = $date ?: now()->toDateString();
        $this->post_time = '';
        $this->content_type = '';
        $this->formStatus = 'process';
        $this->description = '';
        $this->todos = [];
        $this->content_url = '';
        $this->commentBody = '';

        $this->dispatch('content-focus-title');
    }

    public function openEvent(int $id): void
    {
        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($id);

        $this->authorize('view', $event);

        $this->resetValidation();
        $this->popoverDate = null;
        $this->formOpen = false;
        $this->formDirty = false;
        $this->detailOpen = true;
        $this->editingId = $event->id;
        $this->readOnly = ! auth()->user()->can('update', $event);
        $this->canDelete = auth()->user()->can('delete', $event);
        $this->saveError = null;
        $this->title = $event->title;
        $this->event_date = $event->event_date->toDateString();
        $this->post_time = $event->timeLabel() ?? '';
        $this->content_type = $event->content_type ?? '';
        $this->formStatus = $event->status;
        $this->description = $event->description ?? '';
        $this->todos = $this->normalizeTodos($event->todos ?? []);
        $this->content_url = $event->content_url ?? '';
        $this->commentBody = '';
        $this->creatorName = $event->creator?->karyawan?->nama ?? $event->creator?->username ?? 'Pengguna';
        $this->creatorAgo = $event->created_at
            ? $event->created_at->copy()->locale('id')->diffForHumans()
            : '';

        $this->dispatch('content-focus-modal');
    }

    public function closeModal(bool $force = false): void
    {
        if ($this->formOpen && $this->formDirty && ! $force) {
            return;
        }

        $this->detailOpen = false;
        $this->formOpen = false;
        $this->formDirty = false;
        $this->editingId = null;
        $this->readOnly = false;
        $this->canDelete = false;
        $this->saveError = null;
        $this->todos = [];
        $this->commentBody = '';
        $this->resetValidation();

        $this->dispatch('content-modal-closed');
    }

    public function addComment(): void
    {
        if (! $this->editingId || ! $this->detailOpen) {
            return;
        }

        $event = ContentEvent::findOrFail($this->editingId);

        $this->authorize('view', $event);

        $validated = $this->validate([
            'commentBody' => ['required', 'string', 'max:1000'],
        ], $this->messages());

        ContentEventComment::create([
            'content_event_id' => $event->id,
            'user_id_user' => auth()->id(),
            'body' => $validated['commentBody'],
        ]);

        $this->commentBody = '';

        $this->dispatch('content-toast', message: 'Komentar ditambahkan', type: 'success');
    }

    public function deleteComment(int $id): void
    {
        $comment = ContentEventComment::findOrFail($id);

        abort_unless((int) $comment->content_event_id === (int) $this->editingId, 403);
        abort_unless((string) $comment->user_id_user === (string) auth()->id(), 403);

        $comment->delete();

        $this->dispatch('content-toast', message: 'Komentar dihapus', type: 'success');
    }

    public function addTodo(): void
    {
        abort_if($this->detailOpen && $this->readOnly, 403);

        $this->todos[] = ['text' => '', 'done' => false];

        if ($this->formOpen) {
            $this->formDirty = true;
        }
    }

    public function removeTodo(int $index): void
    {
        abort_if($this->detailOpen && $this->readOnly, 403);

        if (! isset($this->todos[$index])) {
            return;
        }

        unset($this->todos[$index]);
        $this->todos = array_values($this->todos);

        if ($this->formOpen) {
            $this->formDirty = true;

            return;
        }

        $this->persistTodos();
    }

    public function toggleTodo(int $index): void
    {
        if ($this->formOpen) {
            if (! isset($this->todos[$index])) {
                return;
            }

            $this->todos[$index]['done'] = ! ($this->todos[$index]['done'] ?? false);
            $this->formDirty = true;

            return;
        }

        if (! $this->editingId || ! $this->detailOpen) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($this->editingId);

        // Everyone with calendar access may tick a to-do, even when the rest of
        // the event is read-only for them.
        $this->authorize('view', $event);

        $todos = $this->normalizeTodos($event->todos ?? []);

        if (! isset($todos[$index])) {
            return;
        }

        $todos[$index]['done'] = ! $todos[$index]['done'];

        try {
            $event->update(['todos' => $todos]);
        } catch (Throwable $exception) {
            report($exception);
            $this->saveError = 'Perubahan gagal disimpan. Coba lagi.';

            return;
        }

        $this->todos = $todos;
        $this->saveError = null;

        $this->dispatch('content-saved');
    }

    private function persistTodos(): void
    {
        if (! $this->editingId || ! $this->detailOpen || $this->readOnly) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($this->editingId);

        $this->authorize('update', $event);

        try {
            $event->update(['todos' => $this->normalizeTodos($this->todos)]);
        } catch (Throwable $exception) {
            report($exception);
            $this->saveError = 'Perubahan gagal disimpan. Coba lagi.';

            return;
        }

        $this->saveError = null;

        $this->dispatch('content-saved');
    }

    /**
     * @param  array<int, array{text?: string, done?: bool}>|null  $todos
     * @return array<int, array{text: string, done: bool}>
     */
    private function normalizeTodos(?array $todos): array
    {
        return collect($todos ?? [])
            ->map(fn (array $todo) => [
                'text' => trim((string) ($todo['text'] ?? '')),
                'done' => (bool) ($todo['done'] ?? false),
            ])
            ->filter(fn (array $todo) => $todo['text'] !== '')
            ->take(50)
            ->values()
            ->all();
    }

    /**
     * Autosave a single field from the detail modal.
     */
    public function saveField(string $property): void
    {
        if (! $this->editingId || $this->readOnly || ! $this->detailOpen) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($this->editingId);

        $this->authorize('update', $event);

        $rules = $this->fieldRules();

        if (! isset($rules[$property])) {
            return;
        }

        if ($property === 'post_time') {
            $this->post_time = $this->normalizeTime($this->post_time);
        }

        $this->validateOnly($property, [$property => $rules[$property]], $this->messages());

        try {
            $event->update([$this->columnFor($property) => $this->valueFor($property)]);
        } catch (Throwable $exception) {
            report($exception);
            $this->saveError = 'Perubahan gagal disimpan. Coba lagi.';

            return;
        }

        $this->saveError = null;

        $this->dispatch('content-saved');
    }

    public function save(): void
    {
        if (! $this->formOpen) {
            return;
        }

        $this->authorize('create', ContentEvent::class);

        $this->post_time = $this->normalizeTime($this->post_time);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'post_time' => ['nullable', 'date_format:H:i'],
            'content_type' => ['required', Rule::exists('content_types', 'slug')],
            'formStatus' => ['required', Rule::in(array_keys(StatusPalette::contentStatuses()))],
            'description' => ['nullable', 'string', 'max:5000'],
            'todos' => ['array'],
            'todos.*.text' => ['nullable', 'string', 'max:200'],
            'todos.*.done' => ['boolean'],
            'content_url' => ['nullable', 'url:http,https', 'max:2048'],
        ], $this->messages());

        $payload = [
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'post_time' => $validated['post_time'] ?: null,
            'content_type' => $validated['content_type'],
            'status' => $validated['formStatus'],
            'description' => $validated['description'] ?: null,
            'todos' => $this->normalizeTodos($this->todos),
            'content_url' => $validated['content_url'] ?: null,
        ];

        $account = ContentAccount::resolve($this->account);

        ContentEvent::create($payload + [
            'content_account_id' => $account?->id,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('content-toast', message: 'Event ditambahkan', type: 'success');

        $this->closeModal(true);
    }

    public function deleteEvent(): void
    {
        if (! $this->editingId) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($this->editingId);

        $this->authorize('delete', $event);

        $event->delete();

        $this->closeModal(true);
        $this->dispatch('content-toast', message: 'Event dihapus', type: 'success');
    }

    public function moveEvent(int $id, string $date): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->findOrFail($id);

        $this->authorize('update', $event);

        $from = $event->event_date->toDateString();

        if ($from === $date) {
            return;
        }

        $event->update(['event_date' => $date]);

        $this->lastMove = ['id' => $event->id, 'from' => $from];

        $this->dispatch(
            'content-toast',
            message: 'Event dipindahkan ke '.Carbon::parse($date)->locale('id')->translatedFormat('j M Y'),
            type: 'success',
            action: 'undo'
        );
    }

    public function undoMove(): void
    {
        if (! $this->lastMove) {
            return;
        }

        $event = ContentEvent::with(['creator.karyawan'])->find($this->lastMove['id']);

        if ($event) {
            $this->authorize('update', $event);
            $event->update(['event_date' => $this->lastMove['from']]);
        }

        $this->lastMove = null;

        $this->dispatch('content-toast', message: 'Pemindahan dibatalkan', type: 'success');
    }

    public function retry(): void
    {
        $this->loadError = false;
        $this->popoverDate = null;
    }

    public function render()
    {
        $contentTypes = $this->contentTypeOptions();
        $statusOptions = StatusPalette::contentStatuses();

        $comments = [];

        if ($this->detailOpen && $this->editingId) {
            $comments = ContentEventComment::query()
                ->with(['author.karyawan'])
                ->where('content_event_id', $this->editingId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(fn (ContentEventComment $comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'author' => $comment->author?->karyawan?->nama ?? $comment->author?->username ?? 'Pengguna',
                    'ago' => $comment->created_at?->copy()->locale('id')->diffForHumans() ?? '',
                    'can_delete' => (string) $comment->user_id_user === (string) auth()->id(),
                ])
                ->all();
        }

        $accounts = ContentAccount::query()->active()->ordered()->get();
        $activeAccount = $accounts->firstWhere('slug', $this->account) ?? $accounts->first();

        $events = collect();
        $days = [];
        $monthWeeks = [];
        $weekDays = [];
        $listGroups = [];
        $popoverItems = [];
        $isEmpty = false;

        try {
            [$rangeStart, $rangeEnd] = $this->range();

            $events = ContentEvent::query()
                ->with(['creator.karyawan', 'account'])
                ->inRange($rangeStart, $rangeEnd)
                ->when($activeAccount, fn ($query) => $query->where('content_account_id', $activeAccount->id))
                ->when($this->typeFilter !== 'semua', fn ($query) => $query->where('content_type', $this->typeFilter))
                ->when($this->statusFilter !== 'semua', fn ($query) => $query->where('status', $this->statusFilter))
                ->get();

            $sorted = $this->sortEvents($events);

            if ($this->view === 'minggu') {
                $weekDays = $this->buildDays($sorted, $rangeStart, $rangeEnd, $contentTypes, false);
            } else {
                $days = $this->buildDays($sorted, $rangeStart, $rangeEnd, $contentTypes, true);
                $monthWeeks = array_chunk(array_values($days), 7);
            }

            $listGroups = $this->buildListGroups($sorted, $contentTypes);

            if ($this->popoverDate && isset($days[$this->popoverDate])) {
                $popoverItems = $days[$this->popoverDate]['items'];
            }

            $isEmpty = $sorted->isEmpty();
        } catch (Throwable $exception) {
            report($exception);
            $this->loadError = true;
        }

        return view('livewire.content-calendar', [
            'accounts' => $accounts,
            'activeAccount' => $activeAccount,
            'contentTypes' => $contentTypes,
            'statusOptions' => $statusOptions,
            'monthWeeks' => $monthWeeks,
            'weekDays' => $weekDays,
            'listGroups' => $listGroups,
            'popoverItems' => $popoverItems,
            'isEmpty' => $isEmpty,
            'comments' => $comments,
            'monthLabel' => $this->periodLabel(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    /**
     * @return array<string, array{slug: string, name: string, fill: string, border: string, text: string}>
     */
    private function contentTypeOptions(): array
    {
        $options = [];

        foreach (ContentType::ordered() as $index => $type) {
            $options[$type->slug] = [
                'slug' => $type->slug,
                'name' => $type->name,
            ] + ContentTypePalette::for($index);
        }

        return $options;
    }

    /**
     * @param  Collection<int, ContentEvent>  $events
     * @return Collection<int, ContentEvent>
     */
    private function sortEvents(Collection $events): Collection
    {
        return $events
            ->sortBy(fn (ContentEvent $event) => [
                $event->post_time === null || $event->post_time === '' ? 1 : 0,
                substr((string) $event->post_time, 0, 5),
                mb_strtolower($event->title),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, ContentEvent>  $events
     * @param  array<string, array{slug: string, name: string, fill: string, border: string, text: string}>  $contentTypes
     * @return array<string, array<string, mixed>>
     */
    private function buildDays(Collection $events, Carbon $rangeStart, Carbon $rangeEnd, array $contentTypes, bool $markOtherMonth): array
    {
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
                'is_other_month' => $markOtherMonth
                    && ($cursor->month !== $anchor->month || $cursor->year !== $anchor->year),
                'is_weekend' => in_array($cursor->dayOfWeekIso, [6, 7], true),
                'items' => [],
            ];

            $cursor->addDay();
        }

        foreach ($events as $event) {
            $date = $event->event_date->toDateString();

            if (! isset($days[$date])) {
                continue;
            }

            $days[$date]['items'][] = $this->eventItem($event, $contentTypes);
        }

        return $days;
    }

    /**
     * @param  Collection<int, ContentEvent>  $events
     * @param  array<string, array{slug: string, name: string, fill: string, border: string, text: string}>  $contentTypes
     * @return array<int, array{date: string, label: string, items: array<int, array<string, mixed>>}>
     */
    private function buildListGroups(Collection $events, array $contentTypes): array
    {
        return $events
            ->sortBy(fn (ContentEvent $event) => $event->event_date->toDateString())
            ->groupBy(fn (ContentEvent $event) => $event->event_date->toDateString())
            ->map(function (Collection $group, string $date) use ($contentTypes) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->locale('id')->translatedFormat('l, j F Y'),
                    'items' => $group->map(fn (ContentEvent $event) => $this->eventItem($event, $contentTypes))->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array{slug: string, name: string, fill: string, border: string, text: string}>  $contentTypes
     * @return array<string, mixed>
     */
    private function eventItem(ContentEvent $event, array $contentTypes): array
    {
        $type = $contentTypes[$event->content_type] ?? null;

        return [
            'id' => $event->id,
            'title' => $event->title,
            'date' => $event->event_date->toDateString(),
            'date_label' => $event->dateLabel(),
            'time_label' => $event->timeLabel(),
            'type_slug' => $event->content_type,
            'type_name' => $type['name'] ?? 'Tanpa jenis',
            'type_fill' => $type['fill'] ?? '#F1F5F9',
            'type_border' => $type['border'] ?? '#CBD5E1',
            'status' => $event->status,
            'can_edit' => auth()->user()?->can('update', $event) ?? false,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'title.required' => 'Judul event wajib diisi.',
            'title.max' => 'Judul event maksimal 150 karakter.',
            'event_date.required' => 'Tanggal wajib diisi.',
            'event_date.date' => 'Tanggal tidak valid.',
            'post_time.date_format' => 'Jam posting harus dalam format HH:MM.',
            'content_type.required' => 'Jenis konten wajib dipilih.',
            'content_type.exists' => 'Jenis konten tidak dikenal.',
            'formStatus.required' => 'Status wajib dipilih.',
            'formStatus.in' => 'Status tidak valid.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            'content_url.url' => 'Link konten harus berupa URL http atau https yang valid.',
            'content_url.max' => 'Link konten terlalu panjang.',
            'todos.*.text.max' => 'Setiap to-do maksimal 200 karakter.',
            'commentBody.required' => 'Komentar wajib diisi.',
            'commentBody.max' => 'Komentar maksimal 1000 karakter.',
        ];
    }

    private function fieldRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'post_time' => ['nullable', 'date_format:H:i'],
            'content_type' => ['required', Rule::exists('content_types', 'slug')],
            'formStatus' => ['required', Rule::in(array_keys(StatusPalette::contentStatuses()))],
            'description' => ['nullable', 'string', 'max:5000'],
            'content_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    private function columnFor(string $property): string
    {
        return match ($property) {
            'formStatus' => 'status',
            default => $property,
        };
    }

    private function valueFor(string $property): ?string
    {
        $value = match ($property) {
            'formStatus' => $this->formStatus,
            default => $this->{$property},
        };

        return $value === '' ? null : $value;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        if ($this->view === 'minggu') {
            $start = $this->weekAnchor();

            return [$start->copy(), $start->copy()->endOfWeek(Carbon::SUNDAY)];
        }

        $start = $this->anchor()->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $this->anchor()->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        return [$start, $end];
    }

    private function anchor(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
    }

    private function weekAnchor(): Carbon
    {
        if ($this->week !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->week)) {
            return Carbon::parse($this->week)->startOfWeek(Carbon::MONDAY);
        }

        return $this->defaultWeek();
    }

    private function defaultWeek(): Carbon
    {
        $anchor = now()->startOfWeek(Carbon::MONDAY);

        if (! now()->isSameMonth($this->anchor())) {
            return $this->anchor()->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        }

        return $anchor;
    }

    private function periodLabel(): string
    {
        if ($this->view === 'minggu') {
            $start = $this->weekAnchor();
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

            if ($start->month === $end->month && $start->year === $end->year) {
                return $start->format('j').'–'.$end->copy()->locale('id')->translatedFormat('j M Y');
            }

            if ($start->year === $end->year) {
                return $start->copy()->locale('id')->translatedFormat('j M').' – '.$end->copy()->locale('id')->translatedFormat('j M Y');
            }

            return $start->copy()->locale('id')->translatedFormat('j M Y').' – '.$end->copy()->locale('id')->translatedFormat('j M Y');
        }

        return $this->anchor()->copy()->locale('id')->translatedFormat('F Y');
    }

    private function hasActiveFilters(): bool
    {
        return $this->typeFilter !== 'semua'
            || $this->statusFilter !== 'semua';
    }

    /**
     * Accept manual input such as "9:30", "930", "17.30" or "9" and normalize
     * it to the HH:MM value the calendar uses for sorting.
     */
    private function normalizeTime(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^(\d{1,2})[:.](\d{1,2})(?::\d{2})?$/', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
        } elseif (preg_match('/^(\d{3,4})$/', $value, $matches)) {
            $digits = str_pad($matches[1], 4, '0', STR_PAD_LEFT);
            $hour = (int) substr($digits, 0, 2);
            $minute = (int) substr($digits, 2, 2);
        } elseif (preg_match('/^(\d{1,2})$/', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = 0;
        } else {
            return $value;
        }

        if ($hour > 23 || $minute > 59) {
            return $value;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }
}
