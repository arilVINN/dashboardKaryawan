# Integration Investigation Report — `employee-dashboard` (office system)

> Scope: read-only static inspection of the repository. No files were modified, no dependencies installed, no migrations run, no database connections made. Credentials and secret values are deliberately excluded. Findings are labelled **Verified**, **Inference**, or **Unknown**.

---

## 1. Executive Summary

**Verified facts:**
- Laravel **12** application on **PHP ≥ 8.2** (`composer.json:12,14`), with **Livewire 4.4**, **Sanctum 4.3**, **Filament 5.0** declared, plus standard dev tooling (Pint, Pail, Sail, PHPUnit).
- Dual-mode architecture: **session-cookie auth for Blade web routes** and **Sanctum Bearer-token auth for a JSON API** under `/api`. One login action (`AuthController::login`) serves both, distinguishing via `$request->is('api/*')` (`app/Http/Controllers/AuthController.php:25`).
- The frontend is **Blade + Livewire + vanilla JS (axios/fetch) over Vite + Tailwind 4**. Livewire components back the HRD/Kadiv/Direksi tables (`app/Livewire/`); the staff side is largely Blade views backfilled by axios calls to `/api/*` endpoints. **No React, Vue, or Inertia.**
- Non-default data model: the authenticated user is **`App\Models\User2`** (table `users2`, string PK `id_user`, `config/auth.php:69-73`), linked 1:1 to `karyawans` (employees) and `roles`. Roles are custom (`staff`, `kadiv`, `hrd`, `direksi`/`pemimpin` super-admin), enforced by three custom middlewares — not Laravel policies or a permission package.
- **`DB_CONNECTION=pgsql` in the actual `.env`** (values redacted; only the key name reported). `.env.example` still defaults to sqlite, so PostgreSQL is an environment choice, not a code-level assumption — but there is one confirmed PostgreSQL-only SQL fragment in application code (see §4).
- **No deployment infrastructure exists**: no Dockerfile, no Compose file, no CI workflows, no nginx config. `laravel/sail` is only a dev dependency.

**Reasonable inference:** this is an internship-stage system with two parallel API surfaces (web-session-driven JS calls and a token API), and mixed Indonesian/English code comments. It appears to have started as a UI prototype (`routes/web.php:144-160` still contains a dummy session-based login route `/login-proses` writing hardcoded user data) and was later retrofitted with real authentication.

**Unknown:** the actual hosting environment, and whether the psychometric assessment application exists yet, on what stack, hosted where.

---

## 2. Authentication Findings

### Verified — how login works
- `POST /login` → `AuthController::login` (`routes/web.php:20-23`), throttled through the alias `gateway.throttle:5,1`, which is simply `ThrottleRequests` re-aliased (`bootstrap/app.php:26`).
- Manual credential check against `User2` (`app/Http/Controllers/AuthController.php:27-51`): rejects any stored password that is not a real hash (plaintext is never compared), checks `karyawan.status_aktif` only after a correct password, updates `last_login_at`, then calls **`Auth::login($user)` + `session()->regenerate()`** (`AuthController.php:70-73`). The session cookie is the web auth state; sessions are DB-backed (`SESSION_DRIVER=database`, key name only, no values reported).
- For `api/*` requests the same controller issues a **Sanctum plain-text Bearer token** (`AuthController.php:75-88`).

### Verified — guards and providers (`config/auth.php`)
- Guards: `web` (session) and `sanctum` (`config/auth.php:40-50`). Default guard is `web`.
- Provider `users` → **eloquent `App\Models\User2`** (`config/auth.php:69-73`). The default Laravel `User.php` model exists but is not wired into auth.

### Verified — roles and access control
- **No policies, no Gate definitions, no permission package.** All restrictions flow through middleware aliases registered in `bootstrap/app.php:23-28`:
  - `role` → `App\Http\Middleware\CheckRole` — variadic role names, case-insensitive, with super-admin inheritance via `User2::hasAnyRole()` (`app/Models/User2.php:73-83`). `direksi`/`pemimpin` satisfy every `hrd` and `kadiv` gate.
  - `deny.superadmin` → `App\Http\Middleware\DenySuperAdmin` — makes direksi read-only on mutation endpoints.
  - `EnsureStaffRole` — strict staff-only check (`app/Http/Middleware/EnsureStaffRole.php`).
- Super-admin constants: `User2::SUPERADMIN_ROLES` and `SUPERADMIN_INHERITED_ROLES` (`app/Models/User2.php:42-48`).
- **Authorization is modelled as role inheritance, not per-permission grants.** Any new integration section must respect this model; "who may see candidate results" is then a simple choice of which `role:` list the new routes carry.

### Verified — API authentication is real, not just installed
- `routes/api.php` is registered (`bootstrap/app.php:10`) and **all** protected routes sit inside `'auth:sanctum'` + `gateway.throttle:60,1` groups with `role:*` sub-groups (`routes/api.php:19-99`). Sanctum's `personal_access_tokens` migration exists (`database/migrations/2026_09_25_063625_create_personal_access_tokens_table.php`).
- Notable quirk: **Blade pages mint and inject Sanctum tokens server-side** so browser JS can call the API, e.g. `routes/web.php:40-44` and `routes/web.php:125-129` (`$user->createToken('web_token')->plainTextToken` passed into a view). Session-to-token bridging is therefore an established pattern in this codebase.
- CSRF is **excluded** for `login`, `api/login`, and `staff/pesan/send` (`bootstrap/app.php:17-21`).

### Installed vs. demonstrably implemented

| Package | Installed | Demonstrably used |
| --- | --- | --- |
| `laravel/sanctum` | Yes | Yes (guard, API routes, token issuance) |
| `livewire/livewire` | Yes | Yes (`app/Livewire/*`, `<livewire:...>` tags in views) |
| `filament/filament` | Yes | **No** — no panel provider, no config, no usage in `app/` |
| `laravel/sail` | Yes (dev) | **No** — no `docker-compose.yml` |
| Passport / JWT | No | No |

---

## 3. API and Integration Findings

### Verified — routes
- `routes/web.php:20-235` — login/logout, session-authenticated role groups (`auth` + `role:staff|kadiv|direksi,pemimpin|hrd` + throttle aliases such as `throttle:umum`, `throttle:pencarian`, `throttle:submit-form`), plus the prototype remnant `/login-proses` that writes hardcoded session data (`routes/web.php:144-160`).
- `routes/api.php:16-99` — public token-login, then `auth:sanctum` groups for staff/kadiv/hrd covering dashboards, tasks, messages, profiles, division and staff management. **There are currently no cross-application, server-to-server, or webhook routes.**

### Verified — existing integration patterns
- **No outbound HTTP client usage anywhere**: no `Http::` facade calls, no Guzzle usage in `app/`, no `app/Services/` directory, no service classes wrapping external APIs. Conclusion: *the office system does not yet call any other system.*
- The only integration patterns are **inbound**: Sanctum token auth (documented via `docs/api-testing-guide.md`, `docs/kadiv-api.md`, `docs/Employee-Dashboard.postman_collection.json`) and the server-minted-token-into-Blade pattern described in §2.
- `ApiResponseFormatter` middleware plus a custom exception renderer produce a uniform JSON envelope `{success, code, message, data}` for `api/*` (`bootstrap/app.php:31-64`) — a useful contract baseline for any new integration endpoints.

### Verified — where navigation can be extended
- Per-role sidebars are Blade partials included by each role's pages:
  - Staff: `resources/views/component/sidebar.blade.php`
  - HRD: `resources/views/component_hrd/sidebar.blade.php` — the `<nav>` block at **lines 24-57** lists Dashboard / Karyawan / Manajemen Divisi / Pesan as plain `<a href="{{ url(...) }}">` items. An "Assessment Results" link is a one-line addition here.
  - Super-admin / direksi: `resources/views/component_direksi/sidebar.blade.php`
- Note `resources/views/component_hrd/sidebar.blade.php:1-2`: when the user is super-admin, the **direksi** sidebar renders instead of the HRD one. A link intended for both audiences must be added to both variants.

---

## 4. Database Findings

### Verified — driver
- `.env` sets `DB_CONNECTION=pgsql` (all credential values withheld). `.env.example` shows `DB_CONNECTION=sqlite` (`.env.example:23`), so PostgreSQL is the *environment's* choice rather than a code-level assumption.
- `config/database.php` is stock Laravel. Sessions, cache, and queue all use database-backed stores (key names only: `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`).

### Verified — schema and models
Custom, string-keyed, FK-restrictive schema (see `database/migrations/`):
- `roles`: string PK `id_role(20)`, `nama_role` (`2026_09_25_025720_create_roles_table.php`).
- `users2`: string PK `id_user(20)`, `username`, hashed password, `role_id_role` FK → roles, `karyawan_id_karyawan` FK (unique 1:1) → karyawans, `rememberToken`, later `last_login_at` (`2026_09_25_025906_create_users2_table.php`, `2026_09_29_000002_add_last_login_at_to_users2_table.php`).
- `karyawans`: string PK `id_karyawan(20)`, identity/contact fields, `email` unique, `jabatan`, `divisi_id_divisi` FK → `divisis` with `onDelete('restrict')` (`2026_09_25_025821_create_karyawans_table.php`); later additions include photo, address, and `status_aktif`.
- Supporting tables: `divisis`, `tugas`, `submit_tugas`, `pesans`, `notifikasis`, plus Laravel-standard `users` (not used by auth), `sessions`, `cache`, `jobs`, `personal_access_tokens`.
- `User2` relationships (`app/Models/User2.php:85-116`): `belongsTo(Role)`, `belongsTo(Karyawan)`, `hasMany(Notifikasi)`, and `hasMany(Pesan)` for both sent and received messages.
- A dedicated migration converts `sessions.user_id` to a string (`database/migrations/2026_10_08_000000_change_sessions_user_id_to_string.php`), required because the default sessions table assumes an integer user key while `User2` uses a string PK. This confirms a hand-patched standard stack rather than a vanilla install.

### PostgreSQL-specific behaviour
- **Confirmed PostgreSQL-only SQL** (`NULLS LAST` is PostgreSQL syntax): `app/Livewire/Kadiv/TugasTable.php:138` —
  `orderByRaw("CAST(SUBSTRING(id_tugas, 3) AS INTEGER) DESC NULLS LAST")`.
- Other raw queries are portable but lock in case-insensitive role lookups: `whereRaw('LOWER(nama_role) = ?', ...)` in `app/Http/Controllers/Hrd/HrdStaffController.php:73,75,229`, `app/Http/Controllers/Staff/PesanController.php:267`, `app/Http/Controllers/Kadiv/KadivStaffController.php:27,52`, `app/Livewire/Kadiv/PesanTable.php:163-165`, `app/Livewire/Kadiv/TugasTable.php:114`. Several run alongside `lockForUpdate()` (pessimistic row locking), which assumes a single shared live database session.
- **No schema object inherently requires PostgreSQL** (no JSONB, arrays, or PG-specific column types). But the presence of PG-only SQL means the office system is effectively pinned to PostgreSQL unless that query is rewritten.

### Verified — seeding
`database/seeders/DatabaseSeeder.php` exists. Documentation references demo accounts with known passwords; treat any real deployment as requiring different credentials. (No credentials are reproduced in this report.)

---

## 5. Deployment Findings

### Verified — what exists
Effectively nothing in-repo: no `Dockerfile`, no `docker-compose.yml`, no `.github/workflows/`, no CI/CD scripts, no nginx/Apache config, no deploy scripts, and no hosting documentation.
What does exist: a `composer run setup` script chain (`composer.json:41-48`), a `composer run dev` concurrently-based local runner (`composer.json:49-52`), the health endpoint `/up` (`bootstrap/app.php:12`), and API-testing docs under `docs/`.

### Unknown — must not be assumed
- Actual hosting target (bare VM, shared hosting, VPS, container platform).
- Public domain(s) and whether TLS/HTTPS termination exists. Note `trustProxies('*')` (`bootstrap/app.php:15`), which **suggests** deployment behind a proxy, but which one is unknown.
- Whether a production PostgreSQL server exists — `.env` proves a pgsql connection string was configured locally, not where it points or whether it is reachable from another host.
- Whether the office system and the assessment app can reach each other over a network (same host, private LAN, or public HTTPS).
- Where a server-to-server token would be stored and rotated (no vault or secret-manager configuration exists).
- The actual production PHP runtime (composer requires ≥ 8.2; the deployed runtime is unverifiable from the repository).
- Horizontal scaling / multiple app instances (matters for sessions, cache, and any token caching).

---

## 6. Recommended Integration Architecture

### Option A — Direct database sharing
The assessment app writes candidate/result tables directly into the office system's PostgreSQL database; HR reads them via Eloquent/Livewire.

**Pros**
- No HTTP surface, no token issuance, lowest moving-part count for the anticipated 20-100 candidates per round.
- Both applications are Laravel; the office system already handles non-default tables (`users2`, `karyawans`).

**Cons**
- Couples two applications' schema lifecycles in one database.
- Both apps ship Laravel-standard tables (`users`, `cache`, `sessions`, `jobs`, `personal_access_tokens`) that collide unless one app uses a table prefix or a separate schema.
- Migration releases become interdependent; credential and performance tuning become shared concerns.
- The PG-only query at `app/Livewire/Kadiv/TugasTable.php:138` already constrains database portability.

**Feasibility:** practical only if both apps can be provisioned on the same PostgreSQL server with distinct schemas or table prefixes. This is **not verified as available**.

### Option B — Server-to-server API (recommended)
The assessment app exposes a REST API for finalized results; the office system calls it from a server-side controller or service class.

- **Suitable auth mechanism:** a long-lived **Sanctum personal access token** minted in the assessment app and stored in the office system's environment, sent as `Authorization: Bearer`. Rationale:
  1. Sanctum is already the established pattern here (`AuthController::createToken`, `auth:sanctum` guard config).
  2. Volume (≤ 100 candidates per round) is trivial; a token-authenticated JSON API is more than sufficient.
  3. HR never re-authenticates: staff authentication stays entirely inside the office system and the **server**, not the browser, holds the credential.
- **Why not OAuth2 client-credentials (Passport):** it solves problems this project does not have (third-party clients, per-client scopes, token rotation at scale) and adds setup and operational cost with no current requirement. Revisit only if multiple consuming systems appear.
- **Precedent already in the codebase:** server-side token issuance, CSRF exclusions, and normalized JSON error handling (`bootstrap/app.php:31-64`) mean token-based integration needs no framework work.

**Pros**
- Clean ownership boundary: the assessment app owns candidate data and scoring; the office system owns staff identity and HR workflows.
- Independent deploy and migration cycles; the assessment app can scale for public traffic without touching internal HR systems.
- Retryable, observable, and testable in isolation.

**Cons**
- Requires a network path, TLS, a secret, and an availability contract between the two apps.
- Needs a small amount of caching/error-handling work in the office system so an assessment outage does not break the HR dashboard.

### Option C — Full SSO (shared session or redirect login)
**Not recommended for the MVP.** Neither application references a shared session store or an SSO provider, and the user populations differ by design (internal staff vs. public candidates). "HR must not log in again" is already satisfied by Option B, because authentication never leaves the office system.

### Recommendation
Adopt **Option B** for the MVP:

1. The assessment app exposes a small read API for finalized results (candidate identity, assessment metadata, scores, completion timestamps), authenticated with a Sanctum personal access token.
2. The office system calls it server-side from a service class and renders results in a new HRD section reached from `resources/views/component_hrd/sidebar.blade.php:24-57` (and the direksi sidebar if super-admins should see it).
3. New routes sit behind the existing `auth` + `role:hrd` convention (optionally `role:hrd,kadiv`) so `deny.superadmin` semantics stay consistent.
4. **Database:** keep **separate databases on the same PostgreSQL server** if that server is available. The office system must not share its `users2`/`karyawans` schema with the assessment app. Only fall back to a shared database with table prefixes if infrastructure constraints (for example, a single-database hosting tier) force it.
5. Candidate-originated tokens are never accepted by the office system; the only cross-system credential is the server-side service token.

---

## 7. Evidence Index

Paths are relative to `employee-dashboard/`.

| Claim | Evidence |
| --- | --- |
| Laravel 12 / PHP 8.2 / Sanctum / Livewire / Filament requirements | `composer.json:11-27` |
| Web + API routing, middleware aliases, CSRF exclusions, trusted proxies | `bootstrap/app.php:7-28`, `bootstrap/app.php:15` |
| Uniform JSON error envelope for `api/*` | `bootstrap/app.php:31-64` |
| Session + Sanctum guards; custom provider model `User2` | `config/auth.php:40-79` |
| Role table + super-admin inheritance rules | `app/Models/User2.php:42-83`, `app/Models/User2.php:85-116` |
| Login flow, bcrypt-only check, session login, API token issuance | `app/Http/Controllers/AuthController.php:18-102` |
| Sanctum-protected API surface | `routes/api.php:19-99` |
| Server-minted token injected into Blade (integration precedent) | `routes/web.php:40-44`, `routes/web.php:125-129` |
| Prototype dummy login route | `routes/web.php:144-160` |
| Role middleware implementations | `app/Http/Middleware/CheckRole.php`, `app/Http/Middleware/EnsureStaffRole.php`, `app/Http/Middleware/DenySuperAdmin.php` |
| HRD sidebar navigation insertion point | `resources/views/component_hrd/sidebar.blade.php:1-57` |
| Livewire usage in views | `resources/views/hrd/daftarDivisi.blade.php:19`, `resources/views/hrd/daftarKaryawan.blade.php:33`, `resources/views/kadiv/tugas.blade.php:22`, `resources/views/direksi/tugas.blade.php:31` |
| pgsql driver configured in the environment | `.env` key `DB_CONNECTION=pgsql` (values withheld) |
| Example env still defaults to sqlite | `.env.example:23` |
| Custom string-keyed schema and FK restrictions | `database/migrations/2026_09_25_025720_create_roles_table.php`, `..._025821_create_karyawans_table.php`, `..._025906_create_users2_table.php` |
| Sessions table patched for string user keys | `database/migrations/2026_10_08_000000_change_sessions_user_id_to_string.php` |
| PostgreSQL-only SQL (`NULLS LAST`) | `app/Livewire/Kadiv/TugasTable.php:138` |
| Case-insensitive raw role queries and row locking | `app/Http/Controllers/Hrd/HrdStaffController.php:73,75,229`; `app/Livewire/Kadiv/TugasTable.php:114,138` |
| Sanctum personal access tokens table | `database/migrations/2026_09_25_063625_create_personal_access_tokens_table.php` |
| No outbound HTTP client or service layer | Repository search for `Http::` and `app/Services/` returned nothing |
| No deployment infrastructure in repo | Repository tree: no Dockerfile, Compose file, CI workflow, or web-server config |
| Existing API/testing documentation | `docs/api-testing-guide.md`, `docs/kadiv-api.md`, `docs/Employee-Dashboard.postman_collection.json`, `docs/security-tests.md` |
| Seeder exists | `database/seeders/DatabaseSeeder.php` |

---

## 8. Open Questions

Only the unresolved items that materially affect the design:

1. **Where do both applications actually run?** Domain(s), TLS termination, network proximity (same host, private LAN, public internet), and who has server access. This determines whether the server-to-server call is a same-host loopback, a private-network request, or a public HTTPS call.
2. **Is a production PostgreSQL server confirmed, and can the assessment app have its own database on it?** The repository only proves a `pgsql` connection string was configured at some point; it does not prove a production server exists or is reachable.
3. **What is the current state of the assessment application?** Does it exist as a codebase, does it already have an API contract, and how are candidate records and results identified (for example, an assessment-run or candidate external ID) so the office system can reconcile them with requisitions? There are no outbound integrations here to build on.
4. **What is the data-ownership and PII policy for candidate records?** Whether candidate profiles may be duplicated into the office database or must remain solely in the assessment database, who approves that, and what retention period applies.
5. **Where can a service token be stored and rotated in production?** This decides between a long-lived Sanctum token in an env file versus a short-lived or rotating credential. No secret-management mechanism exists in the repository.
6. **Should kadiv and direksi see candidate assessment results, or only HRD?** A business decision that maps directly onto which `role:` middleware list the new routes carry.
7. **What is the expected assessment lifecycle?** Whether HR triggers assessment invitations from the office system (one-time links) or the assessment app manages candidate intake independently. The MVP recommendation assumes HR only *reads* finalized results; any write-back or invitation flow expands the API contract and the security surface.
