# Livewire All Roles (Staff + HRD lists) — Design Spec

- **Date:** 2026-10-08
- **Status:** Approved for planning
- **Topic:** Extend the Livewire list pattern (already used for Kadiv) to the Staff and HRD list screens

## 1. Context

The Kadiv list screens (`/kadiv/tugas`, `/kadiv/pesan`, `/kadiv/manajemenStaff`) are already Livewire components backed by shared query classes (`app/Queries/Kadiv*Query`), render server-side from the session (no `staff_token`), and use `WithPagination` + `#[Url]` + real-time `wire:model`.

Two list groups remain on older mechanisms:

- **Staff** lists (`/tugas`, `/pesan`, and the dashboard's compact tables) are **client-side JavaScript tables** that call the JSON APIs with a `Bearer` token from `sessionStorage` (`staff_token`). The staff web routes are **public closures** with no session guard.
- **HRD** lists (`/hrd/daftarKaryawan`, `/hrd/daftarDivisi`, `/hrd/manajemenDivisi`) are **server-rendered Blade** driven by query-string params through `HrdKaryawanController` / `HrdDivisiController`, with sortable headers, filter bars, active-filter chips, and (for Karyawan) create/edit/delete modals.

Livewire v4.4.7 is installed and declared in `composer.json`.

## 2. Goal & success criteria

Convert the **Staff** and **HRD** list screens to **Livewire components** using the proven Kadiv pattern, with shared query classes.

- Staff `/tugas` and `/pesan` render server-side from the session, react in real time, paginate, and need no `staff_token`.
- HRD `/hrd/daftarKaryawan`, `/hrd/daftarDivisi`, `/hrd/manajemenDivisi` render from Livewire components with real-time filter/sort; the Karyawan CRUD controls keep working (unchanged JS + API).
- Staff web routes become `auth` + `role:staff` guarded.
- JSON APIs and other screens keep working; full test suite green.

## 3. Scope

**In scope:** Staff Tugas + Pesan lists; HRD Karyawan + Divisi/Manajemen-Divisi lists; the staff route auth guard.

**Out of scope (later phases):** dashboard mini-tables/widgets for any role (kadiv/staff/HRD); staff submit-task and profile forms; HRD staff create/edit/delete *behaviour* (kept as the existing JS + API); HRD message list; HRD dashboard widgets; kadiv screens.

## 4. Architecture

### 4.1 Shared query layer (`app/Queries/`)

**Staff**
- `StaffTugasQuery` — `forStaff(User2 $staff): self`; `apply(array $filters): Builder`.
  - Scope: the staff's own `Tugas`. Filters: `sort` ∈ {`judul`,`tenggat`,`status`}, `dir` ∈ {`asc`,`desc`} (default `tanggal_update desc`), `status` ∈ effective buckets via `statusEfektif`, `q` (case-insensitive `judul_tugas`). Extracts `StaffTugasController::index`.
- `StaffPesanQuery` — `forStaff(User2 $staff): self`; `unified(array $filters): \Illuminate\Support\Collection`.
  - Builds the **one merged list** the current JS assembles: task threads (`Tugas` for the staff with a `latestPesan`) + direct messages (`Pesan` to the staff with `tugas_id_tugas` null), normalised to `{jenis, link_id, judul, pengirim, pengirim_id, tanggal_raw, tanggal}`. Applies `q` (judul + pengirim), `jenis` ∈ {`tugas`,`langsung`}, and sort (`tanggal` default desc / `judul` / `pengirim`, `dir`) in PHP.

**HRD**
- `HrdKaryawanQuery` — `apply(array $filters): Builder`. Extracts `HrdKaryawanController::karyawanPage`: `with(['divisi','user.role'])`, `withCount('tugas')`; filters `divisi`, `jabatan`, `status` ∈ {`aktif`,`belum`}, `q` (nama, case-insensitive); sort `id`/`nama`/`divisi`/`jabatan` (`divisi` via subquery).
- `HrdDivisiQuery` — `apply(array $filters): Builder`. Extracts the existing `HrdDivisiController::divisiListQuery`: `withCount('karyawans')`; filters `status` ∈ {`aktif`,`nonaktif`}, `q` (kode/nama, case-insensitive); sort `kode`/`nama`/`staff` (default `id_divisi desc`).

`HrdKaryawanController::karyawanPage` and `HrdDivisiController::listPage`/`manajemenPage` are refactored onto these classes (kept, not deleted). JSON APIs are untouched.

### 4.2 Staff Livewire components + route auth

**Auth:** wrap the staff web routes (`/`, `/tugas`, `/pesan`, `/tugas/detail/{id}`, `/pesan/detail/{id}`, `/profile`) in `auth` + `role:staff`. Unauthenticated → `/login`; wrong role → 403. The staff login form already establishes a session.

**Components** (`app/Livewire/Staff/`):
- `TugasTable` — `#[Url]` `$search`, `$sort`, `$dir`, `$status`; `mount()` guards the session staff (`abort(403)`); `render()` → `StaffTugasQuery::forStaff($staff)->apply([...])->paginate(10)`; rows: judul / tenggat / status badge / Detail link; real-time search + selects + sortable headers.
- `PesanTable` — `#[Url]` `$search`, `$jenis`, `$sort`, `$dir`; `mount()` guard; `render()` → `StaffPesanQuery::forStaff($staff)->unified([...])` wrapped in a manual `LengthAwarePaginator` (page size 10); rows: Pengirim / Topik / Tanggal / Baca.

**Views** (`resources/views/livewire/staff/*.blade.php`), single root element, `wire:key` per row.

**Page wiring:**
- `staff/tugas.blade.php`: `@include('component.tableTugas')` → `<livewire:staff.tugas-table />`.
- `staff/pesan.blade.php`: `@include('component.tablePesan')` → `<livewire:staff.pesan-table />`.
- **Keep** `component/tableTugas.blade.php` and `component/tablePesan.blade.php` for the staff dashboard's compact tables.

### 4.3 HRD Livewire components + CRUD retention

**Components** (`app/Livewire/Hrd/`):
- `KaryawanTable` — `#[Url]` `$search`, `$divisi`, `$jabatan`, `$status`, `$sort`, `$dir`; `mount()` guards the session HRD; `render()` → `HrdKaryawanQuery::apply([...])->paginate(6)`; filter bar + active-filter chips + sortable headers (ID/Nama/Divisi/Jabatan) + filtered empty state; **rows include the `data-staff-*` attributes** the existing modal reads.
- `DivisiTable` — `#[Url]` `$search`, `$status`, `$sort`, `$dir`; `mount(bool $manage = false)`; `render()` → `HrdDivisiQuery::apply([...])`; sortable headers (Kode/Nama/Jumlah Staff) + filter bar + pagination.
  - `manage = false` (Daftar Divisi): read-only rows, page size 5.
  - `manage = true` (Manajemen Divisi): adds Tambah trigger + Hapus action (data attrs), page size 10.

**CRUD retention (out of scope behaviour):**
- Extract the CRUD **modals + JS** from `component_hrd/tabelKaryawan.blade.php` into `component_hrd/karyawanModals.blade.php` (included by the page). Delete `tabelKaryawan`.
- Extract the **Tambah Divisi modal** from `component_hrd/tabelDivisi.blade.php` into `component_hrd/divisiModal.blade.php`, used by the dashboard's `tabelDivisi` and the Manajemen Divisi page.
- **Keep** `component_hrd/tabelDivisi` for the HRD dashboard (compact, non-sortable).

**Page wiring:**
- `hrd/daftarKaryawan.blade.php`: filter bar + table → `<livewire:hrd.karyawan-table />`; include `karyawanModals`.
- `hrd/daftarDivisi.blade.php` → `<livewire:hrd.divisi-table />`.
- `hrd/manajemenDivisi.blade.php` → `<livewire:hrd.divisi-table manage />` + `divisiModal`.

### 4.4 Reuse of the modal toggle

The Kadiv work restored a shared `[data-modal-open]`/`[data-modal-close]` handler in `resources/js/app.js`; the HRD modals continue to use the same attributes, so they keep working after the split.

## 5. Data flow

```
page view (shell)
  └── <livewire:… />
        mount() -> session user -> abort(403) if wrong role
        render() -> Query::…->apply(filters)->paginate()/unified()
        wire:model.live -> re-render
```

Session auth only; no `staff_token` for the tables.

## 6. Error handling

- Unauthenticated → redirect `/login`; wrong role → 403 (route middleware + `mount()` guard).
- Invalid URL filters fall back to defaults (query-class whitelists).
- Empty result sets render the existing empty-state copy.
- Missing staff/HRD session user in `mount()` → `abort(403)`.

## 7. Testing

- **Query tests:** `StaffTugasQueryTest`, `StaffPesanQueryTest`, `HrdKaryawanQueryTest`, `HrdDivisiQueryTest` (incl. `PRAGMA case_sensitive_like` case-insensitive search and invalid `sort`/`dir` fallback).
- **Livewire tests:** `Staff\TugasTableTest`, `Staff\PesanTableTest` (search/status/sort, unified Pesan rows, non-staff 403); `Hrd\KaryawanTableTest`, `Hrd\DivisiTableTest` (filters, search, sort, pagination, `data-staff-*` attrs, `manage` variant, non-HRD 403).
- **Route guard tests:** staff pages redirect when unauthenticated; 403 for wrong role.
- **Migrate** `HrdListFilteringTest` + the list parts of `HrdListUiTest` to `Livewire::test(...)`; keep `HrdStaffCrudTest` (CRUD unchanged).
- **Page render tests** for staff + HRD pages.
- Full suite green before finishing.

## 8. Rollout order

1. Staff query classes (`StaffTugasQuery`, `StaffPesanQuery`) + tests.
2. HRD query classes (`HrdKaryawanQuery`, `HrdDivisiQuery`) + tests; refactor the HRD web controllers onto them.
3. Staff route auth guard + tests.
4. Staff Livewire components + views + page wiring + tests; migrate staff UI tests.
5. HRD `KaryawanTable` + modal split + wiring + tests; migrate HRD list tests.
6. HRD `DivisiTable` + modal split + wiring + tests.
7. Full suite green; page render tests.

## 9. Risks

- **HRD test migration:** many existing HRD list tests assert controller view-data; they must move to Livewire tests without losing coverage.
- **Modal split:** the CRUD modals and their JS must remain intact for both the dashboard and the Manajemen Divisi page; extract to shared partials carefully.
- **Staff Pesan merge + pagination:** building a unified collection and paginating it manually (`LengthAwarePaginator`) must keep filter/sort/page in sync.
- **Auth change:** guarding the staff routes turns previously-public pages into redirect-to-login; the login form must establish the session (it does).
- **Parity:** query classes must reproduce today's ordering/filters exactly (guarded by the API/controller tests that remain).
