# Livewire All Roles (Staff + HRD lists) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the Staff (`/tugas`, `/pesan`) and HRD (`/hrd/daftarKaryawan`, `/hrd/daftarDivisi`, `/hrd/manajemenDivisi`) list screens to Livewire components using the proven Kadiv pattern (shared query classes, `WithPagination` + `#[Url]` + real-time), and guard the staff web routes with session auth.

**Architecture:** Extract each list's sort/filter into `app/Queries/*` shared by the existing controllers/APIs and the new Livewire components. Staff tables become session-based (no `staff_token`). HRD tables become Livewire while their CRUD modals + JS stay on the page (out of scope behaviour).

**Tech Stack:** Laravel 12, PHP 8.5, Livewire v4.4.7, Blade, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-08-livewire-all-roles-design.md`

## Global Constraints

- Livewire `^4.4` (installed v4.4.7). v4 API: `#[Livewire\Attributes\Url]`, `wire:model.live` / `wire:model.live.debounce.300ms`, `Livewire\WithPagination`.
- Every Livewire view has exactly **one root element**.
- No `staff_token` may be required by the converted tables; auth is the session.
- Query classes must reproduce today's ordering/filters exactly; JSON APIs unchanged.
- Page sizes: Staff Tugas/Pesan `10`; HRD Karyawan `6`; HRD Divisi `5` (Daftar Divisi) / `10` (Manajemen Divisi).
- Staff web routes become `auth` + `role:staff`; HRD stays `auth` + `role:hrd`; components also `abort(403)` in `mount()` for the wrong role.
- HRD CRUD behaviour is unchanged: rows keep the `data-staff-*` (Karyawan) / `data-id`,`data-nama` (Divisi Hapus) attributes the page's existing JS reads; the modals move to shared partials.
- Query filter whitelists (verbatim): Staff Tugas `sort` ∈ {`judul`,`tenggat`,`status`}; Staff Pesan `sort` ∈ {`tanggal`,`judul`,`pengirim`}, `jenis` ∈ {`tugas`,`langsung`}; HRD Karyawan `sort` ∈ {`id`,`nama`,`divisi`,`jabatan`}, `status` ∈ {`aktif`,`belum`}; HRD Divisi `sort` ∈ {`kode`,`nama`,`staff`}, `status` ∈ {`aktif`,`nonaktif`}. Invalid/absent → defaults (Staff Tugas `tanggal_update desc`; Staff Pesan `tanggal desc`; HRD Karyawan `nama asc`; HRD Divisi `id_divisi desc`).

## Review Focus

Each is pinned by a test in the owning task.

1. **Tampered/invalid URL filters** (`?sort=DROP`, `?status=x`, `?jenis=x`, `?page=999`) — silent fallback to defaults / a valid page, never a 500. (Tasks 1–4, 10.)
2. **Staff Pesan unified merge + manual pagination** — both a task thread and a direct message appear; `jenis`/`q`/sort/page stay in sync; out-of-range page is safe. (Task 2, 7.)
3. **HRD modal split integrity** — after moving the modals to partials, Karyawan edit/delete rows still carry every `data-staff-*` attribute and the dashboard `tabelDivisi` still works. (Tasks 8, 9.)
4. **Wrong-role session hitting a component** — non-staff → `abort(403)`; non-HRD → `abort(403)`. (Tasks 6, 7, 8, 9.)
5. **Empty states** — no rows → the plain empty copy; active filters with no matches → the filtered copy. (Tasks 6, 8.)

---

## File Structure

**Create**
- `app/Queries/StaffTugasQuery.php`, `StaffPesanQuery.php`, `HrdKaryawanQuery.php`, `HrdDivisiQuery.php`
- `app/Livewire/Staff/TugasTable.php`, `PesanTable.php`
- `app/Livewire/Hrd/KaryawanTable.php`, `DivisiTable.php`
- `resources/views/livewire/staff/tugas-table.blade.php`, `pesan-table.blade.php`
- `resources/views/livewire/hrd/karyawan-table.blade.php`, `divisi-table.blade.php`
- `resources/views/component_hrd/karyawanModals.blade.php`, `divisiModal.blade.php`
- `tests/Feature/Queries/StaffTugasQueryTest.php`, `StaffPesanQueryTest.php`, `HrdKaryawanQueryTest.php`, `HrdDivisiQueryTest.php`
- `tests/Feature/Livewire/Staff/TugasTableTest.php`, `PesanTableTest.php`
- `tests/Feature/Livewire/Hrd/KaryawanTableTest.php`, `DivisiTableTest.php`
- `tests/Feature/StaffRoutesTest.php`, `tests/Feature/AllRolesPagesRenderTest.php`

**Modify**
- `app/Http/Controllers/Staff/StaffTugasController.php`, `app/Http/Controllers/Hrd/HrdKaryawanController.php`, `app/Http/Controllers/Hrd/HrdDivisiController.php` (use query classes)
- `routes/web.php` (staff auth guard)
- `resources/views/staff/tugas.blade.php`, `staff/pesan.blade.php`
- `resources/views/hrd/daftarKaryawan.blade.php`, `daftarDivisi.blade.php`, `manajemenDivisi.blade.php`
- `resources/views/component_hrd/tabelDivisi.blade.php` (modal → partial)
- `tests/Feature/HrdListFilteringTest.php`, `tests/Feature/HrdListUiTest.php`, `tests/Feature/HrdStaffCrudTest.php` (migrate list assertions)

**Delete**
- `resources/views/component_hrd/tabelKaryawan.blade.php` (→ Livewire + modals partial)
- `resources/views/component_kadiv/*` untouched; `component/tableTugas.blade.php` / `component/tablePesan.blade.php` **kept** (dashboard).

---

## Task 1: `StaffTugasQuery` + refactor `StaffTugasController::index`

**Files:** Create `app/Queries/StaffTugasQuery.php`; modify `app/Http/Controllers/Staff/StaffTugasController.php` (method `index`); test `tests/Feature/Queries/StaffTugasQueryTest.php`.

**Interfaces:**
- Produces: `StaffTugasQuery::forStaff(\App\Models\User2 $staff): self`; `apply(array $filters): \Illuminate\Database\Eloquent\Builder` (`sort`,`dir`,`status`,`q`).

- [ ] **Step 1: Write the failing test** — staff `budist` with tugas `Alpha`(baru) and `Gamma`(berjalan), future deadlines; assert `forStaff($s)->apply(['sort'=>'judul','dir'=>'asc'])->pluck('id_tugas')` = `['Gamma','Alpha']`... use ascending titles `Alpha`,`Gamma` so asc = `['T-2','T-1']`; `apply(['status'=>'baru'])`; and case-insensitive `apply(['q'=>'alph'])` with `PRAGMA case_sensitive_like = ON`; plus wildcard `apply(['q'=>'%_%'])->get()` must not throw.
- [ ] **Step 2: Run** `php artisan test --filter=StaffTugasQueryTest` → FAIL (`class not found`).
- [ ] **Step 3: Implement** — move `StaffTugasController::index` logic verbatim (`where('karyawan_id_karyawan', $staff->karyawan_id_karyawan)`, status whitelist → `statusEfektif`, `LOWER(judul_tugas) LIKE`, sort switch, default `orderBy('tanggal_update','desc')->orderBy('tanggal_dibuat','desc')`).
- [ ] **Step 4: Refactor the controller** to `StaffTugasQuery::forStaff($request->user())->apply($request->only(['sort','dir','status','q']))->get()`. Run `--filter=StaffTugasQueryTest` → PASS; `--filter=StaffTugasFilterTest` → PASS.
- [ ] **Step 5: Commit** `git commit -m "refactor(staff): extract StaffTugasQuery shared by API"`.

## Task 2: `StaffPesanQuery` (unified)

**Files:** Create `app/Queries/StaffPesanQuery.php`; test `tests/Feature/Queries/StaffPesanQueryTest.php`.

**Interfaces:**
- Produces: `StaffPesanQuery::forStaff(User2 $staff): self`; `unified(array $filters): \Illuminate\Support\Collection` returning items `['jenis','link_id','judul','pengirim','tanggal_raw','tanggal']`.

- [ ] **Step 1: Write the failing test** — one tugas with a `latestPesan` (thread) and one direct `Pesan` (`tugas_id_tugas` null) to the staff; assert `unified([])` has both (jenis `tugas` and `langsung`); `unified(['jenis'=>'langsung'])` only the direct; `unified(['q'=>'…'])` filters; `unified(['sort'=>'pengirim','dir'=>'asc'])` orders by sender.
- [ ] **Step 2: Run** `php artisan test --filter=StaffPesanQueryTest` → FAIL.
- [ ] **Step 3: Implement** — build the two collections as the current `component/tablePesan.blade.php` JS does (threads from `Tugas::with('latestPesan.pengirim')->withCount('pesans')->where('karyawan_id_karyawan', $staff->karyawan_id_karyawan)`, filtered to `latestPesan != null`; direct from `Pesan::with('pengirim.karyawan')->where('penerima_id_user',$staff->id_user)->whereNull('tugas_id_tugas')`), normalise, then filter (`q` over judul+pengirim, `jenis`) and sort in PHP.
- [ ] **Step 4: Run** → PASS. **Step 5: Commit** `git commit -m "refactor(staff): extract StaffPesanQuery unified list"`.

## Task 3: `HrdKaryawanQuery` + refactor `HrdKaryawanController::karyawanPage`

**Files:** Create `app/Queries/HrdKaryawanQuery.php`; modify `app/Http/Controllers/Hrd/HrdKaryawanController.php`; test `tests/Feature/Queries/HrdKaryawanQueryTest.php`.

**Interfaces:**
- Produces: `HrdKaryawanQuery::apply(array $filters): Builder` (`divisi`,`jabatan`,`status`,`sort`,`dir`,`q`).

- [ ] **Step 1: Write the failing test** — staff in two divisis; assert filters (`divisi`, `jabatan`, `status=aktif`/`belum`), case-insensitive `q`, and sorts (`id`,`nama`,`divisi`,`jabatan`).
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** — move `HrdKaryawanController::karyawanPage` (scope `with(['divisi','user.role'])`, `withCount('tugas')`, wheres, sort switch incl. the divisi subquery). **Step 4:** refactor `karyawanPage` to call it; run `--filter=HrdKaryawanQueryTest` and `--filter=HrdListFilteringTest` (page tests still pass at this stage). **Step 5: Commit** `refactor(hrd): extract HrdKaryawanQuery`.

## Task 4: `HrdDivisiQuery` + refactor `HrdDivisiController`

**Files:** Create `app/Queries/HrdDivisiQuery.php`; modify `app/Http/Controllers/Hrd/HrdDivisiController.php` (`divisiListQuery`/`listPage`/`manajemenPage`); test `tests/Feature/Queries/HrdDivisiQueryTest.php`.

**Interfaces:** `HrdDivisiQuery::apply(array $filters): Builder` (`status`,`sort`,`dir`,`q`).

- [ ] **Step 1: Write the failing test** — three divisis; filters (`status=aktif`/`nonaktif`, `q` kode/nama case-insensitive), sorts (`kode`,`nama`,`staff`).
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** (move `divisiListQuery`). **Step 4:** point `listPage`/`manajemenPage` at it; run `--filter=HrdDivisiQueryTest` + `--filter=HrdListFilteringTest` → PASS. **Step 5: Commit** `refactor(hrd): extract HrdDivisiQuery`.

## Task 5: Guard the staff web routes

**Files:** Modify `routes/web.php`; test `tests/Feature/StaffRoutesTest.php`.

- [ ] **Step 1: Write the failing test**

```php
public function test_staff_pages_require_auth(): void {
    $this->get('/tugas')->assertRedirect('/login');
    $this->get('/pesan')->assertRedirect('/login');
    $this->get('/')->assertRedirect('/login');
}
public function test_staff_pages_forbidden_for_non_staff(): void {
    $this->actingAs($hrd)->get('/tugas')->assertForbidden();
}
```

- [ ] **Step 2: Run** `php artisan test --filter=StaffRoutesTest` → FAIL.
- [ ] **Step 3: Implement** — wrap `/`, `/tugas`, `/pesan`, `/tugas/detail/{id}`, `/pesan/detail/{id}`, `/profile` in `Route::middleware(['auth','role:staff'])->group(...)` in `routes/web.php` (keep their route names).
- [ ] **Step 4: Run** `--filter=StaffRoutesTest` → PASS; full suite may need the staff page tests to authenticate (fix in Task 6/7).
- [ ] **Step 5: Commit** `git commit -m "fix(auth): guard the staff web routes with auth and role:staff"`.

## Task 6: Staff `TugasTable` component + wiring

**Files:** Create `app/Livewire/Staff/TugasTable.php`, `resources/views/livewire/staff/tugas-table.blade.php`; modify `resources/views/staff/tugas.blade.php`; migrate `tests/Feature/StaffTugasFilterTest.php` (UI parts).

**Interfaces:** Consumes `StaffTugasQuery::forStaff()->apply()`; Produces `App\Livewire\Staff\TugasTable` with `#[Url]` `$search`,`$sort`,`$dir`,`$status`; `sortBy`, `resetFilters`, `render()` paginates 10.

- [ ] **Step 1: Write the failing test**

```php
Livewire::actingAs($staff)->test(TugasTable::class)->set('search','alph')->assertSee('Alpha')->assertDontSee('Gamma');
Livewire::actingAs($staff)->test(TugasTable::class)->call('sortBy','judul')->assertSet('dir','asc')->call('sortBy','judul')->assertSet('dir','desc');
Livewire::actingAs($hrd)->test(TugasTable::class)->assertForbidden();
```

- [ ] **Step 2: Run** → FAIL (`ComponentNotFound`). **Step 3: Implement** the component (`mount()` guards session staff via a per-render resolver; single-root view with filter bar + sortable headers + rows `wire:key="tugas-{{ $row->id_tugas }}"` + `{{ $rows->links() }}` + empty state). **Step 4:** replace `@include('component.tableTugas')` with `<livewire:staff.tugas-table />`; run `--filter=TugasTableTest` → PASS. **Step 5: Commit** `feat(staff): Livewire TugasTable`.

## Task 7: Staff `PesanTable` component + wiring

**Files:** Create `app/Livewire/Staff/PesanTable.php`, `resources/views/livewire/staff/pesan-table.blade.php`; modify `resources/views/staff/pesan.blade.php`; migrate `tests/Feature/StaffTugasFilterTest.php` (Pesan part).

**Interfaces:** Consumes `StaffPesanQuery::forStaff()->unified()`; Produces `App\Livewire\Staff\PesanTable` `#[Url]` `$search`,`$jenis`,`$sort`,`$dir`; `render()` builds a `LengthAwarePaginator` (page size 10) from the unified collection.

- [ ] **Step 1: Write the failing test** — a thread + a direct message for the staff; assert both render; `set('jenis','langsung')->assertSee(<direct>)->assertDontSee(<thread>)`; `set('search', …)`; non-staff `assertForbidden()`.
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** (manual paginator: slice the unified collection by `($page-1)*10 .. *10`, `new \Illuminate\Pagination\LengthAwarePaginator($slice, $total, 10, $page, ['path' => request()->url(), 'query' => request()->query()])`). **Step 4:** wire `<livewire:staff.pesan-table />`; run `--filter=PesanTableTest` → PASS. **Step 5: Commit** `feat(staff): Livewire PesanTable`.

## Task 8: HRD `KaryawanTable` + modal split + wiring

**Files:** Create `app/Livewire/Hrd/KaryawanTable.php`, `resources/views/livewire/hrd/karyawan-table.blade.php`, `resources/views/component_hrd/karyawanModals.blade.php`; modify `resources/views/hrd/daftarKaryawan.blade.php`; delete `resources/views/component_hrd/tabelKaryawan.blade.php`; migrate tests.

**Interfaces:** Consumes `HrdKaryawanQuery::apply()`; Produces `App\Livewire\Hrd\KaryawanTable` `#[Url]` `$search`,`$divisi`,`$jabatan`,`$status`,`$sort`,`$dir`; `render()` paginates 6; rows include `data-staff-edit`/`data-staff-delete` + the data fields.

- [ ] **Step 1: Write the failing test** — filters (`divisi`,`jabatan`,`status`), search, sort, pagination; a row contains `data-staff-edit` and `data-id="…"`; non-HRD `assertForbidden()`.
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** the component + view (filter bar + chips + sortable headers + rows + filtered/plain empty state). **Step 4:** move the CRUD modal markup + `<script>` out of `tabelKaryawan` into `component_hrd/karyawanModals.blade.php`; wire `<livewire:hrd.karyawan-table />` + `@include('component_hrd.karyawanModals')`; delete `tabelKaryawan`. **Step 5:** migrate `HrdListFilteringTest`/`HrdListUiTest` Karyawan assertions to `Livewire::test(KaryawanTable::class)`; keep `HrdStaffCrudTest` (CRUD unchanged) but move its list-marker assertions here. **Step 6:** run `--filter=KaryawanTableTest` + `--filter=HrdStaffCrudTest` → PASS. **Step 7: Commit** `feat(hrd): Livewire KaryawanTable with CRUD modals kept`.

## Task 9: HRD `DivisiTable` + modal split + wiring

**Files:** Create `app/Livewire/Hrd/DivisiTable.php`, `resources/views/livewire/hrd/divisi-table.blade.php`, `resources/views/component_hrd/divisiModal.blade.php`; modify `hrd/daftarDivisi.blade.php`, `hrd/manajemenDivisi.blade.php`, `component_hrd/tabelDivisi.blade.php`; migrate tests.

**Interfaces:** Consumes `HrdDivisiQuery::apply()`; Produces `App\Livewire\Hrd\DivisiTable` `#[Url]` `$search`,`$status`,`$sort`,`$dir`; `mount(bool $manage = false)`; page size 5 (`manage=false`) / 10 (`manage=true`).

- [ ] **Step 1: Write the failing test** — sort/search/status; `DivisiTable::class` with `mount(manage: true)` shows the Tambah trigger + Hapus action; non-HRD forbidden.
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** the component + view. **Step 4:** extract the Tambah Divisi modal from `tabelDivisi` into `component_hrd/divisiModal.blade.php`; have `tabelDivisi` (dashboard) and `manajemenDivisi` include it; wire `<livewire:hrd.divisi-table />` / `<livewire:hrd.divisi-table manage />`. **Step 5:** migrate the Divisi assertions in `HrdListFilteringTest`/`HrdListUiTest` to `Livewire::test(DivisiTable::class)`. **Step 6:** run tests → PASS. **Step 7: Commit** `feat(hrd): Livewire DivisiTable`.

## Task 10: Page render tests + final verification

**Files:** Create `tests/Feature/AllRolesPagesRenderTest.php`.

- [ ] **Step 1: Write the test**

```php
public function test_staff_and_hrd_pages_render_livewire(): void {
    $this->actingAs($staff)->get('/tugas')->assertOk()->assertSee('id="staffTugasSearch"', false);
    $this->actingAs($staff)->get('/pesan')->assertOk()->assertSee('id="staffPesanSearch"', false);
    $this->actingAs($hrd)->get('/hrd/daftarKaryawan')->assertOk()->assertSee('id="hrdKaryawanSearch"', false);
    $this->actingAs($hrd)->get('/hrd/daftarDivisi')->assertOk()->assertSee('id="hrdDivisiSearch"', false);
}
public function test_tampered_filters_do_not_error(): void {
    $this->actingAs($staff)->get('/tugas?sort=DROP&dir=x&page=999')->assertOk();
    $this->actingAs($hrd)->get('/hrd/daftarKaryawan?sort=DROP&status=x&page=999')->assertOk();
}
```

- [ ] **Step 2: Run** `--filter=AllRolesPagesRenderTest` → PASS (after Tasks 5–9). **Step 3: Full suite** `php artisan test` → all green; `npm run build`; `php artisan view:cache` + lint compiled views. **Step 4: Commit** `test: all-roles page render + tampered-url`.

---

## Self-Review notes
- **Spec coverage:** query layer (1–4), staff auth (5), staff components (6–7), HRD components + modal split (8–9), render tests (10). All spec sections mapped.
- **Type consistency:** `forStaff`/`forDivision`/`apply`/`unified` names match the Interfaces blocks; component names match their views and page tags.
- **Review Focus:** items 1 (1–4,10), 2 (2,7), 3 (8,9), 4 (6–9), 5 (6,8) — each has an owning task test.
