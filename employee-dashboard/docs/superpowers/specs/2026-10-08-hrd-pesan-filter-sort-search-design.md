# HRD Pesan Filter/Sort/Search — Design

## Outcome
Add message filtering, sorting, and searching to both HRD message lists
(`/hrd/pesan` and `/hrd/daftarPesan`) with the house Livewire pattern, without
changing the JSON APIs or the Kirim Pesan send flow.

## Scope
- In: `HrdPesanQuery`, `App\Livewire\Hrd\PesanTable` + view, wiring on both
  pages, Kirim modal extraction to `component_hrd/pesanModal.blade.php`,
  query + component tests, migration of page-level list assertions.
- Out: JSON APIs (`index`/`show`/`store`/`balas`) byte-identical; Kirim send
  behaviour unchanged (legacy `staff_token` flow kept, page-level only);
  `detailPesan` page untouched; Kadiv/Staff lists untouched.

## Query layer — `App\Queries\HrdPesanQuery`
- `forHrd(User2 $hrd): self` factory (mirrors `StaffPesanQuery::forStaff`);
  `apply(array $filters): Builder`. `arah` is evaluated against the factory user.
- Base: `Pesan::with(['pengirim.karyawan', 'penerima.karyawan'])` (HRD sees all
  company messages — global scope, no per-user restriction).
- Filters: `q` (judul_pesan / pengirim username-or-nama / penerima
  username-or-nama, `LOWER(...) LIKE`, blank treated as absent per
  `filled()` semantics); `arah` ∈ {`masuk`, `keluar`} relative to the
  requesting HRD user; `tipe` ∈ {`pesan`, `surat`}; `status` ∈
  {`belum_dibaca`, `dibaca`}.
- Sorts: `tanggal` (default, `tanggal_pesan desc, created_at desc`),
  `judul` (`judul_pesan`), `pengirim` (pengirim username); invalid/absent →
  default. `dir` = `asc`/`desc`, default `desc`.
- `HrdPesanController::page()` keeps metrics only; `daftarPage()` returns the
  view only. Both stop passing `$pesans`.

## Component — `App\Livewire\Hrd\PesanTable`
- `#[Url]` `$search` (aliased `as: 'q'` so legacy `?q=` deep links hydrate),
  `$arah`, `$tipe`, `$status`, `$sort`, `$dir`.
- Actions `sortBy()` (same column toggles, new column → `asc`),
  `clearFilter()` (whitelisted names), `resetFilters()`; `updated*` →
  `resetPage()`.
- `mount()` + per-render resolver abort 403 unless the session role is `hrd`.
  No persisted user property; no `staff_token`.
- `render()` paginates the query 10/page (current pages are unpaginated —
  this also bounds loading). Filter chips with `data-filter-chip` markers;
  filtered vs plain empty states.

## View, pages, modal split
- New `livewire/hrd/pesan-table.blade.php`, single root: live search
  (`wire:model.live.debounce.300ms`), three selects, Reset; sortable
  Tanggal/Judul/Pengirim headers (▲/▼/↕); today's row columns preserved
  (ID, Judul, Dari/Ke-kontak, Tipe badge, Tanggal, Buka → `hrd.detailPesan`);
  Kirim trigger calls the existing global `bukaModalPesanHrd()`.
- Both pages replace `@include('component_hrd.tabelPesan')` with
  `<livewire:hrd.pesan-table />`. `/hrd/pesan` metrics (`pesanbar`) unchanged.
- Kirim modal + `<script>` extracted verbatim to
  `component_hrd/pesanModal.blade.php`, included page-level (never inside the
  Livewire view, so re-renders can't re-execute top-level script state).
- Delete `component_hrd/tabelPesan.blade.php` iff it has no remaining
  consumers.

## Tests and acceptance
- `HrdPesanQueryTest`: whitelist defaults, each filter, case-insensitive
  search (`PRAGMA case_sensitive_like = ON`), blank-`q` parity.
- `Livewire/Hrd/PesanTableTest`: filters, search, sort toggle, pagination,
  chips incl. single-filter clear, empty states, non-HRD forbidden, page
  deep-link hydration on both pages.
- Existing `HrdMessageWebLoginTest` + HRD Pesan API tests pass unchanged;
  full suite green; `view:cache` + compiled-view lint clean before commit.
