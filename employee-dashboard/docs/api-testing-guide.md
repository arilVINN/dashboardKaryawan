# API Testing Guide (Postman)

Panduan menguji seluruh endpoint API **Dashboard Karyawan** via Postman.
Semua endpoint (kecuali login) memakai **Sanctum Bearer token**.

---

## 1. Persiapan

```bash
cd employee-dashboard
php artisan migrate:fresh --seed
php artisan serve        # default http://127.0.0.1:8000
```

### Akun dari seeder

| Username | Password | Role | Divisi | Keterangan |
|---|---|---|---|---|
| `budist` | `pass123` | Staff | IT | Punya tugas `TG001` "Bikin API" |
| `revast` | `pass123` | Staff | IT | Punya tugas `TG002` "Desain UI Dashboard" |
| `andist` | `pass123` | Staff | HR | Punya tugas "Perbarui Data Karyawan" |
| `mayast` | `pass123` | Staff | HR | Punya tugas "Siapkan Orientasi Karyawan" |
| `tonokd` | `pass123` | Kadiv | IT | Kepala Divisi IT |
| `rinakadiv` | `pass123` | Kadiv | HR | Kepala Divisi HR |
| `darmawanhrd` | `pass123` | HRD | HR | |

### Postman setup

1. Buat collection, tambahkan **collection variables**:
   - `baseUrl` = `http://127.0.0.1:8000`
   - `token` = *(kosongkan, diisi otomatis saat login)*
2. Setiap request: header `Accept: application/json`.
3. Setelah login, isi header `Authorization: Bearer {{token}}`.

### Referensi status tugas

Lima status (hasil revisi UI/UX). Empat status pertama disimpan di kolom `tugas.status`; **`telat` tidak pernah disimpan** — diturunkan dari deadline.

| Status | Kapan muncul |
|---|---|
| `baru` | Tugas dibuat, belum dikerjakan |
| `berjalan` | Sedang dikerjakan, atau hasil revisi dari Kadiv |
| `menunggu di-acc` | Staff submit dengan `progress = 100` |
| `sudah di-acc` | Kadiv memberi ACC |
| `telat` | Deadline lewat **dan** status belum `sudah di-acc` (status turunan) |

Transisi:

- Buat tugas (kadiv) → `baru`
- Submit dengan progress `< 100` → `berjalan` · submit dengan progress `= 100` → `menunggu di-acc`
- Review kadiv `acc` → `sudah di-acc` · `revisi` → `berjalan`
- Deadline terlewat → status efektif `telat` (kecuali sudah `sudah di-acc`)

> Catatan: `status_review` pada `submit_tugas` (`submitted` / `acc` / `revisi`) adalah kolom terpisah untuk alur review pengumpulan — bukan status tugas.

---

## 2. Auth

### POST `/api/login` — Login

Body (`x-www-form-urlencoded` atau JSON):

```json
{ "username": "budist", "password": "pass123" }
```

Response `200`:

```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "role": "staff",
  "user": { "id_user": "US001", "username": "budist", "karyawan_id_karyawan": "KR001" }
}
```

> **Tips:** tambahkan test script di request ini agar token tersimpan otomatis:
> `pm.collectionVariables.set("token", pm.response.json().access_token);`

### POST `/api/logout` — Logout (butuh token)

Response `200`: `{ "message": "Logout berhasil" }`

---

## 3. Staff Endpoints

Semua butuh token dengan role **staff**.

### GET `/api/staff/tugas` — Daftar tugas saya

Response `200`:

```json
{ "message": "Daftar tugas Anda", "data": [ { "id_tugas": "TG001", "judul_tugas": "Bikin API", "status": "baru", "progress": "0" } ] }
```

### GET `/api/staff/tugas/{id}` — Detail tugas

- `200` → `{ "message": "Detail tugas", "data": { ...tugas + submitTugas } }`
- `404` → `{ "message": "Tugas tidak ditemukan" }` *(tugas milik staff lain)*

### POST `/api/staff/tugas/{id}/submit` — Kumpulkan tugas

Body (`form-data`, karena bisa upload file):

| Key | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `progress` | integer | ✅ | 0–100 |
| `file_hasil` | file | ❌ | max 200 MB |
| `link_submit` | string | ❌ | |
| `catatan_karyawan` | string | ❌ | |

Response `200`: `{ "message": "Hasil tugas berhasil dikirim", "data": { ...tugas } }`

Error:
- `403` → tugas sudah di-ACC (`status = sudah di-acc`)
- `404` → tugas bukan milik Anda

---

## 4. Kadiv Endpoints

Semua butuh token dengan role **kadiv**.

### GET `/api/v1/kadiv/dashboard` — Metrik divisi

Response `200`:

```json
{
  "data": {
    "divisi": { "id_divisi": "DV001", "nama_divisi": "Information Technology" },
    "metrics": {
      "tugas_baru": 1,
      "tugas_berjalan": 1,
      "tugas_menunggu_di_acc": 1,
      "tugas_sudah_di_acc": 2,
      "tugas_telat": 1,
      "persentase_penyelesaian": 33.33
    },
    "staff": [ { "id_karyawan": "KR001", "nama": "Budi", "jumlah_tugas_dikerjakan": 1, "terakhir_login": null } ]
  }
}
```

### GET `/api/v1/kadiv/pesan` — Daftar pesan/surat

Query params (opsional): `tipe` (`pesan`|`surat`), `arah` (`masuk`|`keluar`), `per_page` (1–100).

Response `200`:

```json
{
  "data": [ { "id_pesan": "PSN...", "tipe": "surat", "arah": "keluar", "judul_pesan": "...", "pengirim": {}, "penerima": {} } ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

### POST `/api/v1/kadiv/pesan` — Kirim pesan/surat

Body JSON:

```json
{
  "penerima_id_user": "US007",
  "tipe": "surat",
  "judul_pesan": "Permintaan Rekrutmen",
  "deskripsi": "Mohon tindak lanjut.",
  "tugas_id_tugas": "TG001",
  "link_lampiran": "https://example.com/dokumen"
}
```

- `201` → `{ "message": "Surat berhasil dikirim.", "data": { ... } }`
- `422` → penerima harus Staff satu divisi atau HRD
- File lampiran: gunakan `form-data` dengan key `file_lampiran` (pdf/doc/docx/xls/xlsx/jpg/jpeg/png, max 5 MB)

### GET `/api/kadiv/tugas` — Semua tugas divisi

Response `200`: `{ "message": "Berhasil mengambil daftar tugas divisi", "data": [...] }`

### POST `/api/kadiv/tugas` — Buat tugas baru

Body JSON:

```json
{
  "karyawan_id_karyawan": "KR001",
  "judul_tugas": "Bikin API",
  "deskripsi": "Buat backend.",
  "deadline": "2026-10-15",
  "file_pendukung": null,
  "link_pendukung": null
}
```

- `201` → `{ "message": "Tugas berhasil dibuat", "data": { ...tugas } }`
- `403` → karyawan beda divisi
- `422` → validasi gagal

### PUT `/api/kadiv/tugas/{id}` — Edit tugas

Body JSON (semua opsional, kirim yang diubah):

```json
{ "judul_tugas": "Judul baru", "deadline": "2026-10-20", "progress": "50" }
```

- `200` → `{ "message": "Tugas berhasil diubah", "data": { ...tugas } }`
- `404` → tugas tidak ditemukan / di luar wewenang

### DELETE `/api/kadiv/tugas/{id}` — Hapus tugas

- `200` → `{ "message": "Tugas berhasil dihapus" }`
- `404` → tidak ditemukan / di luar wewenang

### POST `/api/kadiv/tugas/{id}/review` — ACC / Revisi

Body JSON:

```json
{ "status_review": "acc", "catatan_revisi": null }
```

`status_review`: `acc` (tugas → `sudah di-acc`) atau `revisi` (tugas → `berjalan`).

- `200` → `{ "message": "Review berhasil disimpan", "data": { "tugas": {}, "submit": {} } }`
- `400` → belum ada file yang dikumpulkan staff
- `403` → tugas sudah di-ACC sebelumnya
- `404` → tidak ditemukan / di luar wewenang

### GET `/api/kadiv/staff` — Daftar staff divisi

Response `200`: `{ "message": "Berhasil mengambil data staff divisi", "data": [...] }`

### GET `/api/kadiv/staff/{id}` — Detail staff

- `200` → `{ "message": "Berhasil mengambil detail staff", "data": { ... } }`
- `404` → staff bukan bagian dari divisi Anda

---

## 5. HRD Endpoints

Semua butuh token dengan role **hrd**.

### GET `/api/hrd/divisi` — Daftar divisi

Response `200`: `{ "message": "Berhasil mengambil daftar divisi", "data": [...] }`

### POST `/api/hrd/divisi` — Tambah divisi

Body JSON:

```json
{ "kode_divisi": "FN01", "nama_divisi": "Finance", "status_aktif": "Aktif" }
```

- `201` → `{ "message": "Divisi berhasil ditambahkan", "data": { ...divisi } }`
- `409` → nama divisi sudah ada
- `422` → validasi gagal

### GET `/api/hrd/divisi/{id}` — Detail divisi

Response `200`:

```json
{
  "message": "Berhasil mengambil detail divisi",
  "data": {
    "divisi": { ... },
    "ketua_divisi": "Pak Tono",
    "total_staff": 4,
    "total_tugas": 4,
    "tugas_selesai": 1,
    "persentase_selesai": "25%"
  }
}
```

> `tugas_selesai` menghitung tugas dengan `status = sudah di-acc`.

### GET `/api/hrd/staff` — Daftar seluruh staff

Response `200`: `{ "message": "Berhasil mengambil daftar seluruh staff", "data": [...] }`

### POST `/api/hrd/staff` — Tambah staff + akun login

Body JSON:

```json
{
  "nama": "John Doe",
  "jenis_kelamin": "Laki-laki",
  "tanggal_lahir": "1995-05-05",
  "tanggal_rekrut": "2026-01-15",
  "no_telepon": "081234567890",
  "email": "john@mail.com",
  "jabatan": "Backend Developer",
  "divisi_id_divisi": "DV001",
  "username": "johndoe",
  "password": "rahasia123"
}
```

- `200` → `{ "message": "Staff dan akun login berhasil dibuat" }`
- `404` → divisi tidak ditemukan
- `422` → validasi gagal / username sudah dipakai

### GET `/api/hrd/staff/{id}` — Detail staff

- `200` → `{ "message": "Berhasil mengambil detail staff", "data": { ...karyawan } }`
- `404` → staff tidak ditemukan

---

## 6. Negative Tests (Keamanan)

Pastikan proteksi bekerja:

| Skenario | Request | Expected |
|---|---|---|
| Tanpa token | `GET /api/staff/tugas` (header Authorization dihapus) | `401` `{ "success": false, "code": 401, "message": "Unauthenticated", "data": null }` |
| Token role salah | Login sebagai `budist` (staff), lalu `GET /api/v1/kadiv/dashboard` | `403` `{ "message": "Forbidden. Akses ditolak." }` |
| Staff lihat tugas orang lain | `budist` → `GET /api/staff/tugas/TG002` | `404` |
| Kadiv buat tugas untuk divisi lain | `tonokd` → `POST /api/kadiv/tugas` dengan karyawan HR | `403` |
| Review tanpa submission | `POST /api/kadiv/tugas/TG001/review` sebelum staff submit | `400` |

---

## 7. Catatan: Endpoint Web `/staff/*`

Endpoint `/staff/dashboard`, `/staff/pesan`, `/staff/notifikasi` (di `routes/web.php`)
memakai **session auth** (untuk frontend Blade/Vue), bukan token. Untuk pengujian API
via Postman, gunakan endpoint `/api/*` di atas.
