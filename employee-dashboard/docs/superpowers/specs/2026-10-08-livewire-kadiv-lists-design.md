# Livewire Kadiv Lists — Design Spec

- **Date:** 2026-10-08
- **Status:** Approved for planning
- **Topic:** Replace the Kadiv JS-fetch tables with Livewire sorting/searching/filtering

## 1. Context

The Kadiv pages `/kadiv/tugas`, `/kadiv/pesan`, and `/kadiv/manajemenStaff` currently render **empty tables filled by client-side JavaScript** that calls the JSON APIs with a `Bearer` token read from `sessionStorage` (`staff_token`). Sorting, searching, filtering, pagination, and the create-task / send-message modals all live in that JS. The JSON APIs (`/api/kadiv/tugas`, `/api/kadiv/pesan`, `/api/kadiv/dashboard`) already implement sort/filter server-side.

This is fragile (token plumbing, duplicated rendering, no server-rendered content) and inconsistent with the HRD pages, which are server-rendered.

Livewire v4.4.7 is installed (present in `vendor/` and `composer.lock`) and registered, but is **not declared in `composer.json`**, and there are **no working Livewire components**. `app/Livewire/GrafikStatusTugas.php` is a stray file containing raw Blade markup (not a class) and is referenced nowhere.

## 2. Goal & success criteria

Replace the three Kadiv list UIs with **Livewire components** that server-render their rows from the database, react in real time to search / sort / filter, paginate, and require **no `sessionStorage` token**.

Success criteria:

- Visiting the three Kadiv pages with an authenticated Kadiv session shows the table data **without** any client fetch or `staff_token`.
- Typing in a search box (debounced) and changing selects / clicking sortable headers update the table in real time.
- Filters + sort are reflected in the URL (shareable).
- Tables paginate.
- Create-task and send-message modals work with no token.
- The JSON APIs keep working exactly as before (same behaviour, same tests).
- The full test suite is green.

## 3. Scope

**In scope:** the three dedicated Kadiv pages and their tables + modals.

**Out of scope (later phases):** the Kadiv dashboard widgets (`tabelstaff`, `tabelpdant`, `statusbar`) — they keep their current JS + token for now. Staff-side lists, HRD lists, pagination outside these pages.

## 4. Architecture

### 4.1 Shared query layer (`app/Queries/`)

Extract each Kadiv list's sort/filter rules into a query class, used by **both** the JSON API controller and the Livewire component, so there is a single source of truth.

- `KadivTugasQuery` — `forDivision(string $divisiId): self`; `apply(array $filters): Builder`
  - scope: `Tugas` whose `karyawan_id_karyawan` is in the division
  - filters: `sort` ∈ {`judul`,`status`,`tanggal`}, `dir` ∈ {`asc`,`desc`} (default `tanggal_dibuat desc`), `status` ∈ effective buckets (`baru`,`berjalan`,`menunggu di-acc`,`sudah di-acc`,`telat`) via `statusEfektif`, `staff` (employee id, only if within division), `q` (case-insensitive `judul_tugas`).
- `KadivMessageQuery` — `forUser(User2 $kadiv): self`; `apply(array $filters): Builder`
  - scope: `Pesan` where the kadiv is sender or recipient
  - filters: `sort` ∈ {`tanggal`,`judul`,`pengirim`}, `dir`, `tipe` ∈ {`pesan`,`surat`}, `arah` ∈ {`masuk`,`keluar`}, `q` (case-insensitive `judul_pesan` / `deskripsi`).
- `KadivStaffQuery` — `forDivision(string $divisiId): self`; `apply(array $filters): Builder`
  - scope: staff-role `Karyawan` in the division
  - filters: `sort` ∈ {`nama`,`tugas`,`login`}, `dir` (default `nama asc`), `q` (case-insensitive `nama`).

Filter values are validated/whitelisted inside the query class (invalid values fall back to defaults), mirroring today's controller logic.

The three **API controllers are refactored** to build `$filters` from the validated request and call these classes. Behaviour must be byte-for-byte equivalent — existing API tests (`KadivApiTest`) remain the guard.

Also extract the message row-shaping currently in `KadivMessageController::messageData()` into a small presenter (e.g. `app/Presenters/MessagePresenter` or a `messageData()` method on a shared service) so the API response and the Livewire rows are identical.

### 4.2 Livewire components (`app/Livewire/Kadiv/`)

Three components, each `extends Livewire\Component` and uses `WithPagination`:

- `TugasTable`
- `PesanTable`
- `StaffTable`

**State (public properties, synced to URL with `#[Url]`):**
- common: `$search`, `$sort`, `$dir` (pagination state is managed by `WithPagination`'s `$page`)
- `TugasTable`: `$status`, `$staff`; plus create-form state `$judul`, `$deskripsi`, `$tenggat`, `$recipient` (employee id or `semua`), `$attachment` (uses `WithFileUploads`)
- `PesanTable`: `$tipe`, `$arah`; plus send-form state `$recipientUserId`, `$isi`
- `StaffTable`: none beyond common

**Behaviour:**
- `mount()` resolves the authenticated Kadiv from the **session** (`auth()->user()`), loads their division, and `abort(403)` when the user is not a kadiv (defence in depth in addition to the route middleware).
- Real-time: `wire:model.live.debounce.300ms` for text search; `wire:model.live` for selects; sortable header buttons call `sortBy('column')`.
- `sortBy($column)` sets/dir-toggles and resets to page 1. Changing any filter resets to page 1.
- `resetFilters()` clears filters + sort.
- `render()` calls the shared query class, `paginate(10)`, passes rows + division staff (for selects) to the view.
- `TugasTable::createTask()`: validate (mirroring the existing API store rules) and create one `Tugas` per recipient (all division staff when recipient is `semua`); store the optional attachment on the `public` disk; flash success; reset the form.
- `PesanTable::sendMessage()`: validate and create a `Pesan` with `tipe = 'pesan'` (subject derived from the body, mirroring the current JS); flash; reset.

### 4.3 Views (`resources/views/livewire/kadiv/`)

- `tugas-table.blade.php`, `pesan-table.blade.php`, `staff-table.blade.php`.
- Reuse the current filter-bar and sortable-header styling; render rows, `{{ $rows->links() }}`, and an empty state.
- `wire:key` on each row; `wire:loading` feedback on the table body; modal markup + `wire:model` forms for create-task / send-message.
- Components render as **fragments**; each page keeps its existing sidebar/topbar shell.

### 4.4 Page wiring & dependency

- `composer.json`: add `livewire/livewire: ^4.4` to `require` (already installed at v4.4.7). No config publish required.
- Delete the stray `app/Livewire/GrafikStatusTugas.php`.
- `resources/views/kadiv/tugas.blade.php` → `<livewire:kadiv.tugas-table />`
- `resources/views/kadiv/pesan.blade.php` → `<livewire:kadiv.pesan-table />`
- `resources/views/kadiv/manajemenStaff.blade.php` → `<livewire:kadiv.staff-table />`
- Delete the now-unused `component_kadiv/tabelTugas.blade.php` and `component_kadiv/tabelPesan.blade.php`. **Keep** `component_kadiv/tabelstaff.blade.php` (the dashboard uses it).

## 5. Data flow

```
page view (sidebar/topbar)
  └── <livewire:kadiv.tugas-table />
        mount() -> session kadiv -> division
        render() -> KadivTugasQuery::forDivision()->apply(filters)->paginate(10)
        wire:model.live -> property change -> Livewire re-render -> render() again
```

No HTTP fetch, no token. Auth is the existing session (`auth` + `role:kadiv` route guard + mount abort).

## 6. Error handling

- Non-kadiv / unauthenticated: route middleware redirects to login (unauthenticated) or 403 (wrong role); `mount()` also aborts 403 as defence in depth.
- Validation failures in `createTask()` / `sendMessage()` surface as Livewire validation errors next to the fields.
- Empty result sets render the existing empty-state copy.

## 7. Testing

- **Livewire component tests** using `Livewire::test(...)`:
  - `TugasTable`: set `search`/`status`/`staff`/`sort`, assert rendered rows/order; `sortBy()` toggles `dir`; pagination; `createTask()` creates row(s) and assigns to all; non-kadiv `mount()` → 403.
  - `PesanTable`: filters/sort render; `sendMessage()` creates a `Pesan`.
  - `StaffTable`: sort by nama/tugas/login; search.
- **Page render tests**: each page returns 200, renders the Livewire component, and contains **no** `staff_token` script.
- **Regression**: `KadivApiTest` (API behaviour unchanged) plus the full suite stay green.
- Coverage note: the case-sensitive-LIKE behaviour is already guarded via `PRAGMA case_sensitive_like` in existing tests; reuse that pattern where a new query path is introduced.

## 8. Rollout order

1. Query layer (`app/Queries/*` + presenter) + refactor the three API controllers; existing API tests must stay green.
2. Declare Livewire in `composer.json`; delete the stray file.
3. `StaffTable` (simplest), then `TugasTable`, then `PesanTable` (modals add complexity) — each with component tests.
4. Swap the three page includes; delete the two obsolete partials; page render tests.
5. Full suite green.

## 9. Risks

- **Livewire v4 API surface** differs from v3 (`#[Url]`, `wire:model.live`, `WithFileUploads`); verify attributes against the installed version while implementing.
- **File upload** in `createTask()` must mirror the existing storage/validation (mimes/size) of the API store path.
- **Behaviour parity**: the API and Livewire must produce identical filtering; the shared query classes are what guarantee this — do not re-implement filters in components.
- **Dashboard coupling**: `tabelstaff.blade.php` is shared; only the `manajemenStaff` page switches to Livewire, the dashboard keeps the partial.
