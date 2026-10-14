# Livewire Kadiv Lists Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the Kadiv JS-fetch tables (`/kadiv/tugas`, `/kadiv/pesan`, `/kadiv/manajemenStaff`) with Livewire components that server-render, react in real time, paginate, and need no `sessionStorage` token.

**Architecture:** Extract each list's sort/filter into shared query classes under `app/Queries/` used by both the JSON API controllers and the Livewire components. The JSON APIs keep their current behaviour. Three Livewire components (`app/Livewire/Kadiv/`) hold filter state synced to the URL, call the query classes, paginate, and own the create-task / send-message forms. Auth is the existing session (`auth` + `role:kadiv`).

**Tech Stack:** Laravel 12, PHP 8.5, Livewire v4.4.7, Blade, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-08-livewire-kadiv-lists-design.md`

## Global Constraints

- Livewire pinned to `^4.4` (installed `v4.4.7`). Use v4 API: `#[Livewire\Attributes\Url]`, `wire:model.live` / `wire:model.live.debounce.300ms`, `Livewire\WithPagination`, `Livewire\WithFileUploads`.
- Kadiv pages are behind `auth` + `role:kadiv` in `routes/web.php`; Livewire components additionally `abort(403)` in `mount()` when the session user is not a kadiv.
- No `staff_token` / `sessionStorage` may be required by these pages after the change.
- The JSON API responses and behaviour must not change (guarded by `tests/Feature/KadivApiTest.php`).
- Filter whitelists (verbatim): Tugas `sort` ∈ {`judul`,`status`,`tanggal`}, `dir` ∈ {`asc`,`desc`}; Pesan `sort` ∈ {`tanggal`,`judul`,`pengirim`}, `tipe` ∈ {`pesan`,`surat`}, `arah` ∈ {`masuk`,`keluar`}; Staff `sort` ∈ {`nama`,`tugas`,`login`}. Invalid/absent → defaults (Tugas `tanggal_dibuat desc`, Pesan `created_at desc`, Staff `nama asc`).
- Tugas recipients: one `Tugas` per selected employee; `semua` means every staff-role employee in the kadiv's division.
- Task attachment mimes: `pdf,doc,docx,xls,xlsx,jpg,jpeg,png`, max 20480 KB, stored on the `public` disk under `tugas_pendukung` (mirror `KadivTaskController::store`). Message attachment: same mimes, max 5120 KB, `pesan-lampiran`.
- Page size: `paginate(10)`.

## Review Focus

Failure modes the spec implies but whose behaviour is easy to get wrong; each is pinned by a test in the owning task.

1. **Tampered/invalid URL filters** — e.g. `?sort=DROP`, `?arah=x`, `?page=999`. Expect: silently fall back to defaults / a valid page, never a 500. (Task 1–3 query tests, Task 6 page test.)
2. **`semua` recipient with an empty division** — expect a validation error, no `Tugas` created, no exception. (Task 5.)
3. **Non-kadiv session hitting the component directly** — expect `abort(403)` from `mount()`. (Tasks 4–6.)
4. **Search with SQL wildcard characters** (`%`, `_`) or non-ASCII — expect no error. Wildcards are **not** escaped (matching the API's current behaviour); do not "fix" this here. (Task 1 & 6.)
5. **Send message to a recipient outside the kadiv's division (and not HRD)** — expect rejection, no `Pesan` created. (Task 6.)

---

## File Structure

**Create**
- `app/Queries/KadivTugasQuery.php` — division-scoped Tugas query + filters
- `app/Queries/KadivMessageQuery.php` — kadiv-message query + filters
- `app/Queries/KadivStaffQuery.php` — division staff query + filters
- `app/Presenters/MessagePresenter.php` — shared pesan row shape
- `app/Livewire/Kadiv/TugasTable.php` — component (+ create-task)
- `app/Livewire/Kadiv/PesanTable.php` — component (+ send-message)
- `app/Livewire/Kadiv/StaffTable.php` — component
- `resources/views/livewire/kadiv/tugas-table.blade.php`
- `resources/views/livewire/kadiv/pesan-table.blade.php`
- `resources/views/livewire/kadiv/staff-table.blade.php`
- `tests/Feature/Queries/KadivTugasQueryTest.php`, `KadivMessageQueryTest.php`, `KadivStaffQueryTest.php`
- `tests/Feature/Livewire/Kadiv/TugasTableTest.php`, `PesanTableTest.php`, `StaffTableTest.php`
- `tests/Feature/KadivPagesRenderTest.php`

**Modify**
- `composer.json` — declare `livewire/livewire`
- `app/Http/Controllers/Kadiv/KadivTaskController.php` — use `KadivTugasQuery`
- `app/Http/Controllers/Kadiv/KadivMessageController.php` — use `KadivMessageQuery` + presenter
- `app/Http/Controllers/Kadiv/KadivDashboardController.php` — use `KadivStaffQuery` for the staff array
- `resources/views/kadiv/tugas.blade.php`, `pesan.blade.php`, `manajemenStaff.blade.php` — swap include → `<livewire:…>`

**Delete**
- `app/Livewire/GrafikStatusTugas.php` (stray, not a class)
- `resources/views/component_kadiv/tabelTugas.blade.php`, `tabelPesan.blade.php` (after their pages switch)

**Keep**
- `resources/views/component_kadiv/tabelstaff.blade.php` (dashboard still uses it)

---

## Task 1: `KadivTugasQuery` + refactor `KadivTaskController::index`

**Files:**
- Create: `app/Queries/KadivTugasQuery.php`
- Modify: `app/Http/Controllers/Kadiv/KadivTaskController.php` (method `index`)
- Test: `tests/Feature/Queries/KadivTugasQueryTest.php`

**Interfaces:**
- Consumes: models `App\Models\Tugas`, `App\Models\Karyawan`.
- Produces: `App\Queries\KadivTugasQuery` with `public static function forDivision(string $divisiId): self` and `public function apply(array $filters): \Illuminate\Database\Eloquent\Builder`. Filter keys: `sort`, `dir`, `status`, `staff`, `q`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Queries/KadivTugasQueryTest.php
public function test_filters_by_status_staff_and_search_and_sorts(): void
{
    // fixtures: kadiv DIV-IT; staff ST-1 (2 tugas), ST-2 (1 tugas)
    $ids = KadivTugasQuery::forDivision('DIV-IT')->apply(['sort' => 'judul', 'dir' => 'asc'])
        ->pluck('id_tugas')->all();
    $this->assertSame([...], $ids);

    $this->assertSame(['T-1'], KadivTugasQuery::forDivision('DIV-IT')
        ->apply(['status' => 'baru'])->pluck('id_tugas')->all());

    DB::statement('PRAGMA case_sensitive_like = ON');
    $this->assertSame(['T-2'], KadivTugasQuery::forDivision('DIV-IT')
        ->apply(['q' => 'alph'])->pluck('id_tugas')->all());

    // wildcard characters must not throw
    KadivTugasQuery::forDivision('DIV-IT')->apply(['q' => '%_%'])->get();
}

public function test_invalid_sort_and_page_values_fall_back(): void
{
    // ?sort=DROP, ?dir=x → default ordering, no exception
    $this->assertNotEmpty(KadivTugasQuery::forDivision('DIV-IT')->apply(['sort' => 'DROP', 'dir' => 'x'])->get());
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=KadivTugasQueryTest`
Expected: FAIL — `Class "App\Queries\KadivTugasQuery" not found`.

- [ ] **Step 3: Implement `app/Queries/KadivTugasQuery.php`**

Move the exact logic currently in `KadivTaskController::index`: `whereIn('karyawan_id_karyawan', Karyawan::where('divisi_id_divisi',$divisiId)->pluck('id_karyawan'))`; staff filter only when the id is in that set; `statusEfektif($status)` when `$status ∈ Tugas::ALL_STATUSES`; `whereRaw('LOWER(judul_tugas) LIKE ?', ['%'.mb_strtolower($q).'%'])`; the `sort` whitelist switch with default `orderBy('tanggal_dibuat','desc')`.

- [ ] **Step 4: Refactor `KadivTaskController::index` to call it; run tests**

```php
$tugas = KadivTugasQuery::forDivision($divisiId)->apply($request->only(['sort','dir','status','staff','q']))->get();
```
Run: `php artisan test --filter=KadivTugasQueryTest` → PASS, then `php artisan test --filter=KadivApiTest` → PASS (unchanged API).

- [ ] **Step 5: Commit**

```bash
git add app/Queries/KadivTugasQuery.php app/Http/Controllers/Kadiv/KadivTaskController.php tests/Feature/Queries/KadivTugasQueryTest.php
git commit -m "refactor(kadiv): extract KadivTugasQuery shared by API"
```

---

## Task 2: `KadivMessageQuery` + `MessagePresenter` + refactor `KadivMessageController::index`

**Files:**
- Create: `app/Queries/KadivMessageQuery.php`, `app/Presenters/MessagePresenter.php`
- Modify: `app/Http/Controllers/Kadiv/KadivMessageController.php` (`index`, remove `messageData` in favour of the presenter)
- Test: `tests/Feature/Queries/KadivMessageQueryTest.php`

**Interfaces:**
- Produces: `KadivMessageQuery::forUser(\App\Models\User2 $kadiv): self`; `->apply(array $filters): Builder` (`sort`, `dir`, `tipe`, `arah`, `q`). `MessagePresenter::for(\App\Models\Pesan $message, \App\Models\User2 $user): array` returning the same keys currently in `messageData()`.

- [ ] **Step 1: Write the failing test** — assert `KadivMessageQuery::forUser($kadiv)->apply(['sort'=>'judul','dir'=>'asc'])->pluck('id_pesan')`, `apply(['sort'=>'pengirim','dir'=>'asc'])` orders by sender name, `apply(['arah'=>'masuk'])` returns only received, and `apply(['q'=>'laporan'])` matches case-insensitively (with `PRAGMA case_sensitive_like = ON`).

- [ ] **Step 2: Run test to verify it fails** — Run: `php artisan test --filter=KadivMessageQueryTest` → FAIL (`class not found`).

- [ ] **Step 3: Implement the query** — move the scope (sender OR recipient = kadiv), `tipe`, `arah`, case-insensitive `q`, and the `sort` switch (`tanggal`→`tanggal_pesan`, `judul`→`judul_pesan`, `pengirim`→the existing sender-name subquery, default `latest('created_at')`).

- [ ] **Step 4: Implement `MessagePresenter::for()`** — move the body of `KadivMessageController::messageData()` verbatim.

- [ ] **Step 5: Refactor the controller** — build `$filters` from validated input (`tipe`,`arah`,`sort`,`dir`,`q`) and call the query; map rows via the presenter. Keep `per_page` pagination and the response envelope identical.

- [ ] **Step 6: Run tests** — `php artisan test --filter=KadivMessageQueryTest` → PASS; `php artisan test --filter=KadivApiTest` → PASS.

- [ ] **Step 7: Commit** — `git commit -m "refactor(kadiv): extract KadivMessageQuery and MessagePresenter"`

---

## Task 3: `KadivStaffQuery` + refactor `KadivDashboardController` staff array

**Files:**
- Create: `app/Queries/KadivStaffQuery.php`
- Modify: `app/Http/Controllers/Kadiv/KadivDashboardController.php` (the `$staff` block only; metrics untouched)
- Test: `tests/Feature/Queries/KadivStaffQueryTest.php`

**Interfaces:**
- Produces: `KadivStaffQuery::forDivision(string $divisiId): self`; `->apply(array $filters): Builder` (`sort` ∈ {`nama`,`tugas`,`login`}, `dir`, `q`), returning `Karyawan` with `withCount('tugas')` and `user` loaded (so `tugas_count` and `user.last_login_at` are available).

- [ ] **Step 1: Write the failing test** — staff `Abby`/`Zed`; assert `apply(['sort'=>'nama','dir'=>'asc'])`, `apply(['sort'=>'tugas','dir'=>'desc'])`, `apply(['sort'=>'login','dir'=>'asc'])`, and `apply(['q'=>'abby'])` (case-insensitive with `PRAGMA case_sensitive_sensitive_like`). Use `pluck('nama')`.
- [ ] **Step 2: Run test to verify it fails** — `php artisan test --filter=KadivStaffQueryTest` → FAIL.
- [ ] **Step 3: Implement the query** — move the division + staff-role scope, `withCount('tugas')`, `with('user:...')`, case-insensitive `q`, and the `sort` switch (`tugas`→`tugas_count`, `login`→the `last_login_at` subquery, default `nama asc`).
- [ ] **Step 4: Refactor the controller** — replace the inline staff builder with `KadivStaffQuery::forDivision($divisionId)->apply($request->only(['sort','dir','q']))`; keep the `->map()` output shape.
- [ ] **Step 5: Run tests** — filter test PASS; `php artisan test --filter=KadivApiTest` (dashboard staff assertions) PASS.
- [ ] **Step 6: Commit** — `git commit -m "refactor(kadiv): extract KadivStaffQuery shared by dashboard API"`

---

## Task 4: Livewire dependency + `StaffTable` component (first component)

**Files:**
- Modify: `composer.json` (require `livewire/livewire`)
- Delete: `app/Livewire/GrafikStatusTugas.php`
- Create: `app/Livewire/Kadiv/StaffTable.php`, `resources/views/livewire/kadiv/staff-table.blade.php`
- Modify: `resources/views/kadiv/manajemenStaff.blade.php`
- Test: `tests/Feature/Livewire/Kadiv/StaffTableTest.php`

**Interfaces:**
- Consumes: `KadivStaffQuery::forDivision()->apply()`.
- Produces: `App\Livewire\Kadiv\StaffTable` with public `#[Url]` props `$search`, `$sort`, `$dir`; actions `sortBy(string $column)`, `resetFilters()`; `render()` paginates 10.

- [ ] **Step 1: Declare the dependency and remove the stray file**
  - `composer require livewire/livewire:^4.4 --no-update` (edit `composer.json` `require`), then `composer update livewire/livewire --lock` (already installed).
  - `git rm app/Livewire/GrafikStatusTugas.php`.

- [ ] **Step 2: Write the failing component test**

```php
// tests/Feature/Livewire/Kadiv/StaffTableTest.php
use Livewire\Livewire;
Livewire::actingAs($kadiv)->test(StaffTable::class)
    ->set('search', 'ab')
    ->assertSee('Abby')->assertDontSee('Zed');
Livewire::actingAs($kadiv)->test(StaffTable::class)
    ->call('sortBy', 'tugas')->assertSet('dir', 'asc')->call('sortBy', 'tugas')->assertSet('dir', 'desc');
Livewire::actingAs($staff)->test(StaffTable::class)->assertForbidden();
```

- [ ] **Step 3: Run test to verify it fails** — `php artisan test --filter=StaffTableTest` → FAIL (`Class ... StaffTable not found`).

- [ ] **Step 4: Implement the component + view** — `mount()` resolves the session kadiv (`auth()->user()`), stores `$divisionId`, `abort(403)` if not a kadiv; `render()` calls the query and `paginate(10)`; the view renders the filter bar (`wire:model.live.debounce.300ms="search"`, a search input), sortable headers with ▲/▼ from `$sort`/`$dir`, rows with `wire:key="staff-{{ $row->id_karyawan }}"`, `{{ $rows->links() }}`, and an empty state. Reuse the current filter-bar/sortable-header classes.

- [ ] **Step 5: Wire the page** — in `resources/views/kadiv/manajemenStaff.blade.php` replace `@include('component_kadiv.tabelstaff')` with `<livewire:kadiv.staff-table />` (keep the sidebar/topbar shell).

- [ ] **Step 6: Run tests** — `php artisan test --filter=StaffTableTest` → PASS. Manual: `php artisan serve` + log in as a kadiv, open `/kadiv/manajemenStaff`, type in search, click headers.

- [ ] **Step 7: Commit** — `git commit -m "feat(kadiv): Livewire StaffTable for manajemenStaff"`

---

## Task 5: `TugasTable` component (+ create task) + wiring

**Files:**
- Create: `app/Livewire/Kadiv/TugasTable.php`, `resources/views/livewire/kadiv/tugas-table.blade.php`
- Modify: `resources/views/kadiv/tugas.blade.php`
- Delete: `resources/views/component_kadiv/tabelTugas.blade.php`
- Test: `tests/Feature/Livewire/Kadiv/TugasTableTest.php`

**Interfaces:**
- Consumes: `KadivTugasQuery::forDivision()`; staff list via `KadivStaffQuery::forDivision()` (unfiltered).
- Produces: `App\Livewire\Kadiv\TugasTable` public `#[Url]` props `$search`, `$sort`, `$dir`, `$status`, `$staff`; create-form props `$judul`, `$deskripsi`, `$tenggat`, `$recipient`, `$attachment`; actions `sortBy`, `resetFilters`, `createTask()`; uses `WithPagination`, `WithFileUploads`.

- [ ] **Step 1: Write the failing component test**

```php
Livewire::actingAs($kadiv)->test(TugasTable::class)
    ->set('search', 'alpha')->assertSee('Alpha')->assertDontSee('Gamma');
Livewire::actingAs($kadiv)->test(TugasTable::class)
    ->set('judul', 'Tugas X')->set('deskripsi', 'd')->set('tenggat', now()->addWeek()->toDateTimeString())
    ->set('recipient', 'ST-1')->call('createTask')->assertHasNoErrors();
$this->assertDatabaseHas('tugas', ['karyawan_id_karyawan' => 'ST-1', 'judul_tugas' => 'Tugas X']);
Livewire::actingAs($kadiv)->test(TugasTable::class)->set('recipient', 'semua')->set('judul','Y')
    ->set('deskripsi','d')->set('tenggat', now()->addWeek()->toDateTimeString())
    ->call('createTask')->assertHasNoErrors();
$this->assertSame(2, Tugas::where('judul_tugas','Y')->count()); // one per division staff
```

(Empty-division `semua` — Review Focus #2: in a separate test, a kadiv whose division has no staff calls `createTask` with `recipient = 'semua'` and expects `assertHasErrors('recipient')` and zero `Tugas` created.)

- [ ] **Step 2: Run test to verify it fails** — `php artisan test --filter=TugasTableTest` → FAIL.

- [ ] **Step 3: Implement the component** — mount/guard as in Task 4; `render()` → `KadivTugasQuery::forDivision($this->divisionId)->apply([...])->paginate(10)` plus `$this->staffOptions = KadivStaffQuery::forDivision($this->divisionId)->apply([])->get()`; `createTask()` validates `judul`(required max100), `deskripsi`(required), `tenggat`(required date), `recipient`(required), `attachment`(nullable file max20480 mimes…); resolves recipients (single id must belong to the division, else validation error; `semua` = all division staff, and **must fail validation if none**); creates one `Tugas` per recipient (id `TGxxx`, status `baru`, store attachment to `tugas_pendukung`/public); resets the form; flashes.

- [ ] **Step 4: Implement the view** — filter bar (search, Status select, Staff select), sortable headers (Nama Tugas/Keterangan/Tanggal), rows, pagination, empty state, and the create-task modal (fields + `wire:model` bindings + `wire:submit="createTask"`), matching the current modal styling.

- [ ] **Step 5: Wire the page + delete the old partial** — swap the include in `kadiv/tugas.blade.php`; `git rm resources/views/component_kadiv/tabelTugas.blade.php`.

- [ ] **Step 6: Run tests** — `php artisan test --filter=TugasTableTest` → PASS; `php artisan test --filter=KadivApiTest` → PASS. Manual smoke as in Task 4.

- [ ] **Step 7: Commit** — `git commit -m "feat(kadiv): Livewire TugasTable with create-task"`

---

## Task 6: `PesanTable` component (+ send message) + wiring

**Files:**
- Create: `app/Livewire/Kadiv/PesanTable.php`, `resources/views/livewire/kadiv/pesan-table.blade.php`
- Modify: `resources/views/kadiv/pesan.blade.php`
- Delete: `resources/views/component_kadiv/tabelPesan.blade.php`
- Test: `tests/Feature/Livewire/Kadiv/PesanTableTest.php`

**Interfaces:**
- Consumes: `KadivMessageQuery::forUser()`, `MessagePresenter::for()`, `KadivStaffQuery::forDivision()`.
- Produces: `App\Livewire\Kadiv\PesanTable` public `#[Url]` props `$search`, `$sort`, `$dir`, `$tipe`, `$arah`; send props `$recipientUserId`, `$isi`; actions `sortBy`, `resetFilters`, `sendMessage()`.

- [ ] **Step 1: Write the failing component test**

```php
Livewire::actingAs($kadiv)->test(PesanTable::class)
    ->set('search','laporan')->assertSee('Laporan Bulanan');
Livewire::actingAs($kadiv)->test(PesanTable::class)
    ->set('recipientUserId', $staffUser->id_user)->set('isi','Halo staff')
    ->call('sendMessage')->assertHasNoErrors();
$this->assertDatabaseHas('pesans', ['penerima_id_user' => $staffUser->id_user, 'deskripsi' => 'Halo staff']);
// out-of-division recipient rejected:
Livewire::actingAs($kadiv)->test(PesanTable::class)
    ->set('recipientUserId', $otherDivisionUser->id_user)->set('isi','x')
    ->call('sendMessage')->assertHasErrors();
```

- [ ] **Step 2: Run test to verify it fails** — `php artisan test --filter=PesanTableTest` → FAIL.

- [ ] **Step 3: Implement the component** — mount/guard; `render()` → `KadivMessageQuery::forUser($kadiv)->apply([...])->paginate(10)` mapped through `MessagePresenter`, plus recipient options (division staff + HRD, mirroring `store`); `sendMessage()` validates `recipientUserId`(required exists) + `isi`(required string max2000); enforces the same recipient rule as the API store (staff in the kadiv's division, or HRD); creates a `Pesan` (`tipe='pesan'`, `judul_pesan = Str::limit($isi,200,'') ?: 'Pesan baru'`, `deskripsi=$isi`, `tanggal_pesan=now()`); resets; flashes.

- [ ] **Step 4: Implement the view** — filter bar (search, Tipe, Arah), sortable headers (Isi Pesan/Pengirim/Tanggal), rows (`wire:key="pesan-{{ $row['id_pesan'] }}"`), pagination, empty state, and the send-message modal.

- [ ] **Step 5: Wire the page + delete the old partial** — swap the include in `kadiv/pesan.blade.php`; `git rm resources/views/component_kadiv/tabelPesan.blade.php`.

- [ ] **Step 6: Run tests** — `php artisan test --filter=PesanTableTest` → PASS; `php artisan test --filter=KadivApiTest` → PASS.

- [ ] **Step 7: Commit** — `git commit -m "feat(kadiv): Livewire PesanTable with send-message"`

---

## Task 7: Page render tests + final verification

**Files:**
- Create: `tests/Feature/KadivPagesRenderTest.php`

- [ ] **Step 1: Write the failing/edge tests**

```php
public function test_kadiv_pages_render_livewire_and_no_token_script(): void
{
    $this->actingAs($kadiv)->get('/kadiv/tugas')->assertOk()
        ->assertSee('livewire', false)->assertDontSee('staff_token', false);
    $this->actingAs($kadiv)->get('/kadiv/pesan')->assertOk()->assertDontSee('staff_token', false);
    $this->actingAs($kadiv)->get('/kadiv/manajemenStaff')->assertOk()->assertDontSee('staff_token', false);
}

public function test_tampered_filter_url_does_not_error(): void
{
    $this->actingAs($kadiv)->get('/kadiv/tugas?sort=DROP&dir=x&page=999')->assertOk();
}
```

- [ ] **Step 2: Run to verify they pass** (they should after Tasks 4–6; if not, fix) — `php artisan test --filter=KadivPagesRenderTest`.

- [ ] **Step 3: Full suite** — `php artisan test` → all green (existing 118 + new). `npm run build` not required (no CSS changes). Optionally `php artisan view:cache` + lint compiled views.

- [ ] **Step 4: Manual smoke** — `php artisan serve`; log in as kadiv; on each page: type search, change selects, click headers, page through; create a task (single + semua, with and without a file); send a message; confirm no network calls to `/api/kadiv/*` for the table and no `staff_token` in `sessionStorage`.

- [ ] **Step 5: Commit** — `git commit -m "test(kadiv): page render + no-token regression tests"`
