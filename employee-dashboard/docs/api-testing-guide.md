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

## 2. Referensi Endpoint per Role

Semua path menggunakan base URL `{{baseUrl}}`. Kecuali `POST /api/login`, endpoint membutuhkan Bearer token. Endpoint terproteksi dibatasi 60 request per menit per user/IP. Login publik dibatasi 5 request per menit per IP terhadap brute-force.

### Publik dan autentikasi

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/login` | Publik | Login dan membuat token |
| POST | `/api/logout` | Semua role | Mencabut token aktif |

### Staff

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/staff/tugas` | Daftar tugas sendiri |
| GET | `/api/staff/tugas/{id}` | Detail tugas sendiri |
| POST | `/api/staff/tugas/{id}/submit` | Submit hasil/progress tugas |
| GET | `/api/staff/pesan` | Daftar pesan per tugas sendiri |
| GET | `/api/staff/pesan/{id_pesan}` | Detail satu pesan milik/diterima Staff |
| GET | `/api/staff/pesan?tugas_id_tugas={id_tugas}` | Riwayat pesan pada tugas sendiri |
| POST | `/api/staff/pesan/{id_pesan}/balas` | Membalas pesan, bukan surat |

### Kadiv

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/kadiv/dashboard` | Metrik divisi dan ringkasan staff |
| GET | `/api/kadiv/pesan` | Daftar pesan/surat masuk dan keluar |
| GET | `/api/kadiv/pesan/{id_pesan}` | Detail pesan dalam lingkup Kadiv/divisinya |
| POST | `/api/kadiv/pesan` | Kirim pesan/surat ke staff satu divisi atau HRD |
| POST | `/api/kadiv/pesan/{id_pesan}/balas` | Membalas pesan, bukan surat |
| GET | `/api/kadiv/staff` | Daftar staff divisi |
| GET | `/api/kadiv/staff/{id}` | Detail staff dan tugasnya |
| GET | `/api/kadiv/tugas` | Daftar tugas divisi |
| POST | `/api/kadiv/tugas` | Membuat tugas |
| POST | `/api/kadiv/tugas/{id}` | Alias untuk update tugas |
| PUT | `/api/kadiv/tugas/{id}` | Update tugas |
| DELETE | `/api/kadiv/tugas/{id}` | Hapus tugas beserta submission |
| POST | `/api/kadiv/tugas/{id}/review` | ACC atau minta revisi |

### HRD

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/hrd/dashboard` | Dashboard HRD dan metrik perusahaan |
| GET | `/api/hrd/divisi` | Daftar divisi |
| POST | `/api/hrd/divisi` | Membuat divisi |
| GET | `/api/hrd/divisi/{id}` | Detail divisi dan statistik |
| PUT/PATCH | `/api/hrd/divisi/{id}` | Mengubah kode, nama, atau status divisi |
| DELETE | `/api/hrd/divisi/{id}` | Menghapus divisi kosong |
| GET | `/api/hrd/pesan` | Daftar pesan/surat global yang masuk dan keluar |
| POST | `/api/hrd/pesan` | Kirim pesan/surat baru |
| GET | `/api/hrd/pesan/{id_pesan}` | Detail pesan yang dikirim/diterima HRD |
| POST | `/api/hrd/pesan/{id_pesan}/balas` | Membalas pesan, bukan surat |
| GET | `/api/hrd/staff` | Daftar seluruh karyawan |
| POST | `/api/hrd/staff` | Membuat karyawan dan akun login |
| GET | `/api/hrd/staff/{id}` | Detail karyawan dan tugasnya |
| PUT/PATCH | `/api/hrd/staff/{id}` | Mengubah profil, akun, dan role staff/kadiv |
| DELETE | `/api/hrd/staff/{id}` | Menghapus karyawan dan akun login |

## 3. Auth

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

> **Keamanan:** login memakai `Hash::check` saja (tanpa fallback plaintext) dan throttle `5/min/IP`. Brute-force berulang menghasilkan `429 Too Many Attempts`.

### POST `/api/logout` — Logout (butuh token)

Response `200`: `{ "message": "Logout berhasil" }`

---

## 4. Staff Endpoints

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
| `progress` | integer | ❌ | 0–100, jika kosong memakai progress saat ini |
| `file_hasil` | file | ❌ | pdf/doc/docx/xls/xlsx/jpg/jpeg/png/zip, max 200 MB |
| `link_submit` | url | ❌ | harus URL `https://...` valid, max 2048 |
| `catatan_karyawan` | string | ❌ | tag HTML dihapus via `strip_tags` |

Response `200`: `{ "message": "Hasil tugas berhasil dikirim", "data": { ...tugas } }`

Error:
- `403` → tugas sudah di-ACC (`status = sudah di-acc`)
- `404` → tugas bukan milik Anda

### GET `/api/staff/pesan` — Daftar pesan per tugas

Response berisi ringkasan tugas di `data` serta pesan langsung masuk (pesan tanpa `tugas_id_tugas`) di `pesan_langsung`. Pesan yang dikaitkan dengan tugas tetap tampil pada ringkasan tugas:

```json
{
  "data": [
    {
      "id_tugas": "TG001",
      "judul_tugas": "Bikin API",
      "jumlah_pesan": 2,
      "pesan_terakhir": {
        "id_pesan": "PSN001",
        "deskripsi": "Mohon kirim update progres."
      }
    }
  ],
  "pesan_langsung": [
    {
      "id_pesan": "PSNC3227046477",
      "tipe": "pesan",
      "arah": "masuk",
      "judul_pesan": "Pembaruan tugas",
      "deskripsi": "Mohon kirim update progres.",
      "tanggal_pesan": "2026-09-30",
      "pengirim": {
        "id_user": "US003",
        "username": "tonokd",
        "nama": "Pak Tono"
      },
      "lampiran": { "link": null, "file": null },
      "tugas": null,
      "created_at": "2026-09-30T06:03:26.000000Z"
    }
  ]
}
```

### GET `/api/staff/pesan/{id_pesan}` — Detail satu pesan

Staff hanya dapat membuka pesan yang dikirim/diterima olehnya atau pesan pada tugas miliknya. Contoh: `GET /api/staff/pesan/PSNC3227046477`.

Response `200` berisi detail pesan, pengirim, penerima, lampiran, tugas (jika ada), daftar `balasan`, dan `can_reply`. `can_reply` bernilai `false` untuk surat. Pesan di luar akses Staff menghasilkan `404`.

### POST `/api/staff/pesan/{id_pesan}/balas` — Balas pesan

Body JSON: `{ "deskripsi": "Progres sudah 80 persen." }`. Balasan tersimpan sebagai pesan baru dan ditautkan ke pesan asal. Hanya tipe `pesan` yang bisa dibalas; tipe `surat` menghasilkan `422`.

Untuk membuka seluruh thread tugas, gunakan query pada endpoint daftar: `GET /api/staff/pesan?tugas_id_tugas=TG001`.

Staff tidak dapat membuat pesan baru. Pesan hanya dapat dikirim sebagai balasan terhadap pesan bertipe `pesan` melalui endpoint balas di atas.

---

## 5. Kadiv Endpoints

Semua butuh token dengan role **kadiv**.

### GET `/api/kadiv/dashboard` — Metrik divisi

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

### GET `/api/kadiv/pesan` — Daftar pesan/surat

Query params (opsional): `tipe` (`pesan`|`surat`), `arah` (`masuk`|`keluar`), `per_page` (1–100).

Response `200`:

```json
{
  "data": [ { "id_pesan": "PSN...", "tipe": "surat", "arah": "keluar", "judul_pesan": "...", "pengirim": {}, "penerima": {} } ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

### GET `/api/kadiv/pesan/{id_pesan}` — Detail pesan

Contoh: `GET /api/kadiv/pesan/PSNC3227046477`. Kadiv dapat membuka pesan yang dikirim/diterima olehnya atau pesan terkait tugas di divisinya. Response `200` berisi `balasan` dan `can_reply`; pesan di luar lingkup menghasilkan `404`.

### POST `/api/kadiv/pesan` — Kirim pesan/surat

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

### POST `/api/kadiv/pesan/{id_pesan}/balas` — Balas pesan

Body JSON: `{ "deskripsi": "Terima kasih atas informasinya." }`. Balasan dikirim ke lawan bicara pada pesan asal. Surat ditolak dengan `422`.

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

### PUT atau POST `/api/kadiv/tugas/{id}` — Edit tugas

Body JSON (semua opsional, kirim yang diubah). `POST` adalah alias untuk client yang memakai POST ketika upload file. Field yang dapat diubah: `judul_tugas`, `deskripsi`, `deadline`, `karyawan_id_karyawan`, `file_pendukung`, dan `link_pendukung`.

```json
{ "judul_tugas": "Judul baru", "deadline": "2026-10-20" }
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

## 6. HRD Endpoints

Semua butuh token dengan role **hrd**.

### GET `/api/hrd/dashboard` — Dashboard HRD & Metrik Perusahaan

Response `200`:

```json
{
  "message": "Berhasil mengambil data dashboard HRD",
  "data": {
    "metrics": {
      "total_staff": 7,
      "total_divisi": 2,
      "tugas_keseluruhan": 4,
      "tugas_selesai": 1,
      "persentase_tugas_selesai": "25%",
      "total_pesan_perusahaan": 15
    },
    "widgets": {
      "divisi_terbesar": [
        { "nama_divisi": "Information Technology", "total_staff": 4 },
        { "nama_divisi": "Human Resources", "total_staff": 3 }
      ]
    }
  }
}
```

### GET `/api/hrd/pesan` — Daftar Pusat Pesan

Response `200`:

```json
{
  "message": "Berhasil mengambil pusat pesan",
  "data": {
    "metrics": {
      "total": 5,
      "belum_dibaca": 2,
      "selesai": 3
    },
    "list_pesan": [
      {
        "id_pesan": "PSN...",
        "tipe": "surat",
        "judul_pesan": "Surat Edaran",
        "status": "belum_dibaca",
        "pengirim": {},
        "penerima": {}
      }
    ]
  }
}
```

### POST `/api/hrd/pesan` — Kirim Pesan/Surat Baru

Body (form-data atau JSON):

```json
{
  "penerima_id_user": "US003",
  "tipe": "surat",
  "judul_pesan": "Pemberitahuan Audit",
  "deskripsi": "Berikut terlampir dokumen yang perlu dipersiapkan.",
  "link_lampiran": "https://drive.google.com/..."
}
```
*Gunakan `form-data` dengan key `file_lampiran` jika ingin mengunggah file (pdf/doc/docx/xls/xlsx/jpg/jpeg/png, maks 20MB). `link_lampiran` harus URL valid (`url|max:2048`), `javascript:`/`data:` ditolak 422. `judul_pesan` disanitasi via `strip_tags`.*

- `201` → `{ "message": "Pesan/Surat berhasil dikirim", "data": { ...pesan } }`

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

### PUT/PATCH `/api/hrd/divisi/{id}` — Edit divisi

Body JSON parsial: `kode_divisi`, `nama_divisi`, dan/atau `status_aktif`. Respons `200` berisi divisi terbaru; `404` jika divisi tidak ditemukan dan `422` jika validasi gagal atau kode/nama sudah dipakai.

### DELETE `/api/hrd/divisi/{id}` — Hapus divisi

Respons `200` jika berhasil, `404` jika tidak ditemukan, atau `409` jika divisi masih memiliki karyawan.

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
  "jabatan": "Backend Developer",
  "divisi_id_divisi": "DV001",
  "username": "johndoe",
  "password": "rahasia123"
}
```

- `201` → `{ "message": "Staff dan akun login berhasil dibuat", "data": { "karyawan": {}, "user": {} } }`
- `404` → divisi tidak ditemukan
- `422` → validasi gagal / username sudah dipakai

### GET `/api/hrd/staff/{id}` — Detail staff

- `200` → `{ "message": "Berhasil mengambil detail staff", "data": { ...karyawan } }`
- `404` → staff tidak ditemukan

### PUT/PATCH `/api/hrd/staff/{id}` — Edit staff

Body JSON parsial. Field profil yang dapat diubah: `nama`, `jenis_kelamin`, `tanggal_lahir`, `tanggal_rekrut`, `no_telepon`, `jabatan`, dan `divisi_id_divisi`. Field akun opsional: `username`, `password`, serta `role_id_role`. Role yang diizinkan hanya ID role dengan nama `kadiv` atau `staff`.

- `200` → data staff terbaru
- `404` → staff tidak ditemukan
- `409` → akun login tidak ditemukan
- `422` → validasi gagal atau role selain kadiv/staff

### DELETE `/api/hrd/staff/{id}` — Hapus staff

Menghapus profil dan akun login dalam satu transaksi. Respons `200` jika berhasil, `404` jika staff tidak ditemukan, atau `409` jika tugas/pesan terkait masih membatasi penghapusan.

### GET `/api/hrd/pesan/{id_pesan}` — Detail pesan

Contoh: `GET /api/hrd/pesan/PSNC3227046477`. HRD hanya dapat membuka pesan yang dikirim atau diterima oleh akunnya. Response `200` berisi detail pesan, pengirim, penerima, lampiran, tugas bila ada, `balasan`, dan `can_reply`.

### POST `/api/hrd/pesan/{id_pesan}/balas` — Balas pesan

Body JSON: `{ "deskripsi": "Data akan dikirim hari ini." }`. Balasan dikirim kepada pengirim pesan asal. Surat ditolak dengan `422`.

---

## 7. Negative Tests (Keamanan)

Pastikan proteksi bekerja:

| Skenario | Request | Expected |
|---|---|---|
| Tanpa token | `GET /api/staff/tugas` (header Authorization dihapus) | `401` `{ "success": false, "code": 401, "message": "Unauthenticated", "data": null }` |
| Token role salah | Login sebagai `budist` (staff), lalu `GET /api/kadiv/dashboard` | `403` `{ "message": "Forbidden. Akses ditolak." }` |
| Staff lihat tugas orang lain | `budist` → `GET /api/staff/tugas/TG002` | `404` |
| Kadiv buat tugas untuk divisi lain | `tonokd` → `POST /api/kadiv/tugas` dengan karyawan HR | `403` |
| Review tanpa submission | `POST /api/kadiv/tugas/TG001/review` sebelum staff submit | `400` |

---

## 8. Keamanan (Security)

Aturan validasi keamanan yang diterapkan di semua endpoint:

- **SQL injection:** seluruh query memakai Eloquent binding. Dua `whereRaw` tersisa memakai binding `?` (`LOWER(nama_role) = ?`). `tugas_id_tugas` divalidasi `exists:tugas,id_tugas`.
- **Brute-force:** `POST /api/login` throttle `5/min/IP` → `429`. Endpoint auth memakai `gateway.throttle:60,1`.
- **Password:** hanya `Hash::check`. Tidak ada fallback plaintext.
- **Upload:** `file_lampiran`/`file_pendukung` → `mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png` (submit tambah `zip`). `.php/.phtml/.svg` ditolak `422`.
- **Link/SSRF:** `link_*` wajib `url|max:2048`. `javascript:`/`data:` ditolak `422`. Tidak ada fetch server-side.
- **Error disclosure:** 5xx mengembalikan `Server Error` generik tanpa `getMessage()`/SQL. Detail hanya di log via `report($e)`.
- **XSS:** `judul_*` via `strip_tags`. `deskripsi` disimpan mentah — frontend wajib escape (`textContent`, jangan `v-html`/`innerHTML`).
- **IDOR:** `show/update/destroy` di-scope ke `karyawan_id`/`divisi_id` milik user. Thread `balasan` mengembalikan full thread setelah satu pesan terotorisasi.

### Uji keamanan via Postman

| Skenario | Request | Expected |
|---|---|---|
| Brute-force login | `POST /api/login` 6× cepat `{"username":"x","password":"y"}` | ke-6 `429 Too Many Attempts` |
| Upload webshell | HRD login → `POST /api/hrd/pesan` form-data + file `evil.php` sebagai `file_lampiran` | `422` error `file_lampiran` |
| Upload valid | file `.pdf` yang sama | `201` |
| Link jahat | `link_lampiran=javascript:alert(1)` | `422` |
| Link valid | `link_lampiran=https://example.com/x` | `201` |
| SQLi query | `GET /api/staff/pesan?tugas_id_tugas=' OR '1'='1` | `422`, bukan dump data |
| Error generik | `POST /api/hrd/staff` username duplikat | `500 {"message":"Gagal menambahkan staff. Silakan coba lagi."}` tanpa teks SQL |

---

## 9. Catatan Route Web (bukan API)

API pesan Staff hanya tersedia di `/api/staff/pesan` dan memakai Sanctum Bearer token. Route `/staff/dashboard` dan `/staff/notifikasi` merupakan route web/aplikasi, bukan API. Halaman Blade pesan berada di `GET /pesan`; halaman tersebut berbeda dari endpoint JSON `/api/staff/pesan`.
