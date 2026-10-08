# HRD Pesan Filter/Sort/Search Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add filtering, sorting, and searching to both HRD message lists with a Livewire table backed by a shared query class.

**Architecture:** Mirror Tasks 6–9 exactly: extract list logic into `HrdPesanQuery`, render it through `App\Livewire\Hrd\PesanTable` on both pages, extract the Kirim modal verbatim to a page-level partial. JSON APIs and send flow untouched.

**Tech Stack:** Laravel 12, Livewire v4.4 (`#[Url]`, `wire:model.live`, `WithPagination`), PHPUnit feature tests.

**Spec:** `docs/superpowers/specs/2026-10-08-hrd-pesan-filter-sort-search-design.md`

## Global Constraints

- Livewire `^4.4`; v4 API (`#[Url]`, `wire:model.live`, `WithPagination`).
- Session auth only; `mount()` aborts 403 unless the session role is `hrd`; resolve the user per render (no persisted user property); no `staff_token` in new code.
- Query filter/sort whitelists: `arah` ∈ {`masuk`,`keluar`}, `tipe` ∈ {`pesan`,`surat`}, `status` ∈ {`belum_dibaca`,`dibaca`}, `sort` ∈ {`tanggal`,`judul`,`pengirim`}; invalid/absent → default `tanggal_pesan desc, created_at desc`.
- Blank `q` treated as absent (Laravel `filled()` trims).
- JSON APIs (`index`/`show`/`store`/`balas`) and the Kirim send flow byte-identical; modal + `<script>` live page-level, never inside the Livewire view.
- Component view has exactly one root element; full suite green before each commit.

## Review Focus

- `arah` is relative to the viewer: `masuk` = HRD is penerima, `keluar` = HRD is pengirim — Task 1 pins with a send + receive fixture.
- Blank/whitespace-only `q` returns everything (matches `filled()` skip) — Task 1 pins.
- Tampered `sort`/`dir`/`arah`/`tipe`/`status` fall back to defaults without error — Task 1 pins.
- Legacy `?q=` deep link hydrates the search box on both pages — Task 2 pins with page tests.
- Tampered `?page=999` renders 200 with the empty state, not an error — Task 2 pins with a page test.

---

### Task 1: `HrdPesanQuery` + query tests

**Files:**
- Create: `app/Queries/HrdPesanQuery.php`
- Test: `tests/Feature/Queries/HrdPesanQueryTest.php`

**Interfaces:**
- Consumes: `Pesan` model (`pengirim.karyawan`, `penerima.karyawan` relations), `User2` as the factory user.
- Produces: `HrdPesanQuery::forHrd(User2 $hrd): self`; `apply(array $filters): \Illuminate\Database\Eloquent\Builder` with keys `q`, `arah`, `tipe`, `status`, `sort`, `dir`.

- [ ] **Step 1: Write the failing test** — fixtures: HRD user; one pesan HRD→staff, one staff→HRD, one staff→staff (invisible scope check: HRD sees all three — global scope); one `surat` tipe; one `dibaca`, rest `belum_dibaca`. Assert: `apply([])` returns all, newest first; `['arah' => 'masuk']` returns only received; `['tipe' => 'surat']` only surat; `['status' => 'dibaca']` only read; `['q' => 'budi']` matches nama case-insensitively (with `PRAGMA case_sensitive_like = ON`); `['q' => '   ']` returns all; `['sort' => 'DROP', 'dir' => 'x']` falls back to the default order.
- [ ] **Step 2: Run** `php artisan test --filter=HrdPesanQueryTest` → FAIL (class missing).
- [ ] **Step 3: Implement** `HrdPesanQuery` (factory + `apply`): `with(['pengirim.karyawan', 'penerima.karyawan'])`; `q` over `LOWER(judul_pesan)` / pengirim username-or-nama / penerima username-or-nama; `arah` against the factory user's id; strict whitelist switches for `tipe`/`status`/`sort`; `dir` `asc`/`desc` else default.
- [ ] **Step 4: Run** `php artisan test --filter=HrdPesanQueryTest` → PASS.
- [ ] **Step 5: Commit** `git add app/Queries/HrdPesanQuery.php tests/Feature/Queries/HrdPesanQueryTest.php && git commit -m "refactor(hrd): extract HrdPesanQuery"`

### Task 2: `PesanTable` component + modal split + wiring + migration

**Files:**
- Create: `app/Livewire/Hrd/PesanTable.php`, `resources/views/livewire/hrd/pesan-table.blade.php`, `resources/views/component_hrd/pesanModal.blade.php`, `tests/Feature/Livewire/Hrd/PesanTableTest.php`
- Modify: `resources/views/hrd/pesan.blade.php`, `resources/views/hrd/daftarPesan.blade.php`, `app/Http/Controllers/Hrd/HrdPesanController.php` (`page()` metrics-only, `daftarPage()` view-only), migrate message-list assertions in `tests/Feature/HrdMessageWebLoginTest.php` if any assert rows.
- Delete: `resources/views/component_hrd/tabelPesan.blade.php` (verify zero remaining references first).

**Interfaces:**
- Consumes: `HrdPesanQuery::forHrd()->apply()` from Task 1.
- Produces: `App\Livewire\Hrd\PesanTable` with `#[Url]` `$search` (`as: 'q'`), `$arah`, `$tipe`, `$status`, `$sort`, `$dir`; `render()` paginates 10.

- [ ] **Step 1: Write the failing test** — fixtures: HRD + staff users, one incoming + one outgoing pesan. Assert both render; `set('arah', 'masuk')` shows incoming only; `set('search', …)` filters; `set('tipe', 'surat')` / `set('status', 'dibaca')` filter; `call('sortBy', 'judul')` toggles; rows contain `Buka` links to `hrd.detailPesan`; `Kirim Pesan` trigger present; non-HRD `assertForbidden()`. Page tests: `GET /hrd/daftarPesan?q=…` hydrates; `GET /hrd/manajemenDivisi`-style tampered `?sort=DROP&page=999` → 200 on both pages.
- [ ] **Step 2: Run** `php artisan test --filter=PesanTableTest` → FAIL.
- [ ] **Step 3: Implement** the component (guard, `sortBy`/`clearFilter`/`resetFilters`, `updated*` → `resetPage()`, paginate 10) and the view (filter bar with `hrdPesanSearch` id, three selects, chips with `data-filter-chip`, sortable Tanggal/Judul/Pengirim headers, rows preserving today's columns incl. Dari/Ke contact logic + Tipe badge + raw `tanggal_pesan`, filtered/plain empty states).
- [ ] **Step 4: Split + wire** — move the Kirim modal + `<script>` verbatim from `tabelPesan` into `pesanModal.blade.php`; replace the table include with `<livewire:hrd.pesan-table />` on both pages; add `@include('component_hrd.pesanModal')` page-level on both pages; slim the controller; delete `tabelPesan` after confirming no references; migrate row assertions.
- [ ] **Step 5: Run** `php artisan test --filter=PesanTableTest`, then the full `php artisan test` → all green; `vendor/bin/pint --test` on touched PHP files (pre-existing drift elsewhere stays untouched); `php artisan view:cache` + lint compiled views, then `view:clear`.
- [ ] **Step 6: Commit** `feat(hrd): Livewire PesanTable`
