# HRD Pesan Participant-Only Scope Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restrict HRD message visibility to participant-only (penerima OR pengirim) across lists, detail pages, APIs, and metrics.

**Architecture:** Constrain `HrdPesanQuery` at its base so the Livewire component inherits the scope with no view changes; add participant gates to HRD `detailPage` and API `show()` mirroring Staff's `show`; scope both metrics computations. Staff/Kadiv code and `balas` untouched.

**Tech Stack:** Laravel 12, Livewire v4.4, PHPUnit feature tests.

**Spec:** `docs/superpowers/specs/2026-10-08-hrd-pesan-filter-sort-search-design.md` (Amendment 2026-10-09)

## Global Constraints

- Participant = `pengirim_id_user` OR `penerima_id_user` equals the viewer; third-party messages invisible everywhere for HRD.
- Non-participant detail/API access returns 404 with the same message as unknown ID (`Pesan tidak ditemukan.`) — no existence leak.
- Kadiv's same-division-task exception, `balas` behaviour, and JSON response shapes unchanged.
- `Lainnya` pill stays as defensive rendering; full suite green before each commit.

## Review Focus

- A reply (`balasan`) addressed to me whose root thread is third-party: visible (it is itself addressed to me) — Task 1 pins.
- Legacy rows with NULL sender: excluded from lists, never error — Task 1 pins.
- Cross-HRD invisibility: HRD-A cannot list or open HRD-B's messages — Tasks 1 and 2 pin.
- Metrics agree with the list: `totalPesan` equals the unfiltered participant count — Task 2 pins.
- Tampered `arah` on the constrained base still falls back safely — Task 1 pins (existing invalid-input test now runs against the constrained base).

---

### Task 1: Participant-constrain `HrdPesanQuery` + query tests

**Files:**
- Modify: `app/Queries/HrdPesanQuery.php`
- Test: `tests/Feature/Queries/HrdPesanQueryTest.php`

**Interfaces:**
- Consumes: `HrdPesanQuery::forHrd(User2 $hrd): self`, `apply(array $filters): Builder` (unchanged signatures).
- Produces: constrained `apply()` — Task 2 inherits it with no changes.

- [ ] **Step 1: Rewrite the failing tests** — add a second HRD account (`hrddua`) with a `P-X` thread between `hrddua` and staff; convert the existing staff→staff `P-3` fixture role into the exclusion assertions: `apply([])` returns `[P-2, P-1]` only (newest first, no `P-3`/`P-X`); every existing filter/search/sort test asserts the same exclusions; add `test_excludes_other_hrds_messages` (`P-X` absent for `hrduser`); add `test_reply_addressed_to_me_is_visible` (a `balasan` with `penerima` = me on a third-party root is returned); add `test_null_sender_row_is_excluded` (a pesan with null `pengirim_id_user` never appears, no error).
- [ ] **Step 2: Run** `php artisan test --filter=HrdPesanQueryTest` → FAIL (third-party rows still returned).
- [ ] **Step 3: Implement** the participant constraint at the base of `apply()`: `where(pengirim == hrd OR penerima == hrd)`, before all filters.
- [ ] **Step 4: Run** `php artisan test --filter=HrdPesanQueryTest` → PASS.
- [ ] **Step 5: Commit** `git add app/Queries/HrdPesanQuery.php tests/Feature/Queries/HrdPesanQueryTest.php && git commit -m "fix(hrd): scope HrdPesanQuery to participants"`

### Task 2: Gate details/APIs, scope metrics, migrate component tests

**Files:**
- Modify: `app/Http/Controllers/Hrd/HrdPesanController.php` (`detailPage`, `show`, `page` metrics, `index` metrics), `tests/Feature/Livewire/Hrd/PesanTableTest.php` (third-party row gone; add 404 tests).
- Test: same component test file + new assertions (no new files).

**Interfaces:**
- Consumes: constrained `HrdPesanQuery::forHrd()->apply()` from Task 1; Staff `show()` 404 pattern as reference.
- Produces: participant-gated HRD message surfaces (nothing downstream consumes further).

- [ ] **Step 1: Write the failing tests** — in `PesanTableTest`: remove `Koordinasi Shift` from render assertions (assert `assertDontSee`), drop the `lainnya`-pill render assertion to a null-sender defensive case or remove it; add `test_detail_page_404s_for_non_participants` (`GET /hrd/detailPesan/P-X` as `hrduser` → 404); add `test_api_show_404s_for_non_participants` (`GET /api/hrd/pesan/P-X` → 404); add `test_metrics_count_only_participant_messages` (`totalPesan` on `/hrd/pesan` equals the participant count).
- [ ] **Step 2: Run** `php artisan test --filter="PesanTableTest|HrdPesanQueryTest"` → FAIL.
- [ ] **Step 3: Implement** — `detailPage`: participant check → `abort(404)`; `show()`: participant check → 404 JSON (same message); `page()` metrics via the constrained query; `index()`: add the participant `where` before fetching (metrics then agree automatically).
- [ ] **Step 4: Run** focused filters → PASS, then full `php artisan test` → green; `vendor/bin/pint --test` on touched PHP files.
- [ ] **Step 5: Commit** `feat(hrd): gate message details and metrics to participants`
