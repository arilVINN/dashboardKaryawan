# API Kadiv — Dashboard, Pesan, dan Surat

Base URL: `/api/v1/kadiv`. Semua endpoint membutuhkan user terautentikasi dengan role `Kadiv` dan profil karyawan yang memiliki divisi.

## Dashboard

`GET /api/v1/kadiv/dashboard`

Mengembalikan metrik status tugas di divisi Kadiv dan tabel ringkas Staff. Lima metrik adalah bucket status tugas yang saling eksklusif (status efektif, termasuk turunan `telat`):

- `tugas_baru`: status `baru`, deadline belum lewat.
- `tugas_berjalan`: status `berjalan`, deadline belum lewat.
- `tugas_menunggu_di_acc`: status `menunggu di-acc`, deadline belum lewat.
- `tugas_sudah_di_acc`: status `sudah di-acc` (tanpa syarat deadline — tugas yang sudah di-ACC tidak pernah dihitung `telat`).
- `tugas_telat`: deadline sudah lewat dan status belum `sudah di-acc`.
- `persentase_penyelesaian`: `tugas_sudah_di_acc / seluruh_tugas_divisi * 100`.

Status `telat` adalah status turunan yang dihitung dari deadline — tidak pernah disimpan ke database. Lihat referensi status di `docs/api-testing-guide.md`.

Contoh respons `200`:

```json
{
  "data": {
    "divisi": { "id_divisi": "DIV-IT", "nama_divisi": "Teknologi" },
    "metrics": {
      "tugas_baru": 2,
      "tugas_berjalan": 3,
      "tugas_menunggu_di_acc": 2,
      "tugas_sudah_di_acc": 8,
      "tugas_telat": 1,
      "persentase_penyelesaian": 50
    },
    "staff": [
      {
        "id_karyawan": "KRY-01",
        "nama": "Budi",
        "jumlah_tugas_dikerjakan": 7,
        "terakhir_login": "2026-09-29T01:30:00.000000Z"
      }
    ]
  }
}
```

## Daftar pesan dan surat

`GET /api/v1/kadiv/pesan`

Query opsional:

- `tipe`: `pesan` atau `surat`.
- `arah`: `masuk` atau `keluar`.
- `per_page`: 1–100, default 15.

Respons berisi `data` dan metadata pagination (`current_page`, `last_page`, `per_page`, `total`). `arah` dihitung relatif terhadap Kadiv yang sedang login.

## Kirim pesan atau surat

`POST /api/v1/kadiv/pesan`

Gunakan `multipart/form-data` bila mengirim file. Field:

| Field | Aturan |
| --- | --- |
| `penerima_id_user` | Wajib; user Staff dalam divisi Kadiv atau user HRD |
| `tipe` | Wajib; `pesan` atau `surat` |
| `judul_pesan` | Wajib; maksimal 200 karakter |
| `deskripsi` | Wajib |
| `tugas_id_tugas` | Opsional; wajib berasal dari divisi Kadiv dan, untuk Staff, ditugaskan kepada penerima |
| `link_lampiran` | Opsional; URL maksimal 2048 karakter |
| `file_lampiran` | Opsional; PDF, DOC(X), XLS(X), JPG, JPEG, PNG; maksimal 5 MB |

Contoh tanpa file:

```json
{
  "penerima_id_user": "USR-HRD-01",
  "tipe": "surat",
  "judul_pesan": "Permintaan Rekrutmen",
  "deskripsi": "Mohon tindak lanjut kebutuhan personel.",
  "link_lampiran": "https://example.com/formasi"
}
```

Status respons utama: `201` berhasil, `401` belum login, `403` bukan Kadiv, dan `422` validasi atau penerima di luar lingkup.
