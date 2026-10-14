# Security Regression Tests

File: `tests/Feature/SecurityRegressionTest.php` — 13 tests, 39 assertions.
Run: `php artisan test --filter=SecurityRegressionTest`.

Full suite on `testing/api-endpoints`: 46 passed (245 assertions).

Test data uses lowercase roles (`staff`, `kadiv`, `hrd`) matching `DatabaseSeeder`,
and dotless usernames — `POST /api/login` enforces `alpha_num:ascii`, so a dotted
account (even one that exists in the table) is rejected with `422`.

## A. HRD duplication guards

| Test | Request | Expected |
|---|---|---|
| rejects duplicate divisi kode | `POST /api/hrd/divisi` kode `IT` (exists) | `422`, `data.kode_divisi` |
| rejects duplicate divisi nama | same `nama_divisi`, other kode | `422`, `data.nama_divisi` |
| creates staff without email | `POST /api/hrd/staff` no `email` key | `201`, row in `users2` |
| rejects duplicate username | same payload twice | 1st `201`, 2nd `422`, `data.username` |

Note: `POST /api/hrd/staff` defaults `role_id_role` to the `staff` role
(case-insensitive lookup); if the role table has no `staff` entry the controller
returns `422` with a specific message (`HrdStaffController::store`), not `500`.
Validation errors render under `data` (custom handler in `bootstrap/app.php`),
so assertions use `assertJsonStructure(['data' => [...]])`.

## B. Upload and link rules

| Test | Request | Expected |
|---|---|---|
| rejects php upload | `file_lampiran: evil.php` | `422`, `data.file_lampiran` |
| accepts pdf upload | `file_lampiran: dokumen.pdf` | `201` and a stored path in `data.file_lampiran` |
| rejects javascript link (hrd) | `link_lampiran: javascript:alert(1)` | `422`, `data.link_lampiran` |
| rejects javascript link (submit) | `POST /api/staff/tugas/{id}/submit` with `link_submit: javascript:...` | `422`, `data.link_submit` |

Allowed: `mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png` (+`zip` on submit).
`link_*` must be `url|max:2048`. The `evil.php` rejection comes from the mime
allow-list (`mimes:...`), not the generic `file` rule.

## C. Login and throttling

| Test | Request | Expected |
|---|---|---|
| rejects wrong password | `POST /api/login` bad password for an existing user | `401 {"message":"Username atau password salah"}` |
| rejects unknown username | `POST /api/login` username that does not exist | same `401` + same message as above (no user-enumeration leak) |
| throttled after failures | 6× bad login in one minute | 1–5 `401`, 6th `429` (`gateway.throttle:5,1`) |
| rejects plaintext stored password | row whose `password` column is plaintext | `401`, no token issued |
| rejects dotted username | `john.doe` (account exists) | `422`, `data.username`, no token issued |

Login only authenticates against a real password hash (`password_get_info`
`algo` is neither `null` nor `0`); plaintext values are never compared with
`hash_equals` and never auto-upgraded, so a legacy/leaked plaintext column
cannot be used to log in.
