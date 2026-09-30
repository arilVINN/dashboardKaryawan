# Dokumentasi API Dashboard Karyawan

## General Information
- **Base URL:** `http://127.0.0.1:8000/api`
- **Global Response Format:**
  Semua response API dibungkus oleh Global Response Formatter dengan struktur:
  ```json
  {
      "success": true, // atau false jika error
      "code": 200,     // HTTP Status Code
      "message": "Pesan deskriptif",
      "data": { ... }  // Payload data (null jika error)
  }
  ```
- **Global Headers (Wajib untuk rute terproteksi):**
  - `Accept`: `application/json`
  - `Authorization`: `Bearer <token>`

---

## 🔑 1. Autentikasi (Public & User)

### 1.1 Login
- **Endpoint:** `POST /api/login`
- **Otorisasi:** Public (Tanpa Token)
- **Request Body (JSON):**
  ```json
  {
      "username": "darmawanhrd",
      "password": "pass123"
  }
  ```
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "access_token": "1|fvK9VQSEZElNXdJXIIot548p576BInjwiBYSHszw2248f512"
      }
  }
  ```

### 1.2 Logout
- **Endpoint:** `POST /api/logout`
- **Otorisasi:** Protected (`auth:sanctum`)
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Logout berhasil"
      }
  }
  ```

---

## 👨‍💻 2. Endpoint Staff (Role: `staff`)

### 2.1 Lihat Daftar Tugas Milik Staff
- **Endpoint:** `GET /api/staff/tugas`
- **Otorisasi:** Protected (`role:staff`)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Daftar tugas Anda",
          "data": [
              {
                  "id_tugas": "TG001",
                  "karyawan_id_karyawan": "KR001",
                  "judul_tugas": "Bikin API",
                  "deskripsi": "Buat backend.",
                  "file_pendukung": null,
                  "link_pendukung": null,
                  "deadline": "2026-10-01",
                  "progress": "0",
                  "status": "pending",
                  "tanggal_dibuat": "2026-09-28",
                  "tanggal_update": "2026-09-28"
              }
          ]
      }
  }
  ```

### 2.2 Detail Tugas Staff
- **Endpoint:** `GET /api/staff/tugas/{id}`
- **Otorisasi:** Protected (`role:staff`)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Detail tugas",
          "data": {
              "id_tugas": "TG001",
              "judul_tugas": "Bikin API",
              "deskripsi": "Buat backend.",
              "status": "pending",
              "submit_tugas": []
          }
      }
  }
  ```

### 2.3 Submit Hasil Tugas
- **Endpoint:** `POST /api/staff/tugas/{id}/submit`
- **Otorisasi:** Protected (`role:staff`)
- **Content-Type:** `multipart/form-data`
- **Request Body (form-data):**
  - `progress`: `100` (required, integer 0-100)
  - `catatan_karyawan`: `Pekerjaan selesai tepat waktu` (optional, string)
  - `link_submit`: `https://github.com/example/repo` (optional, string)
  - `file_hasil`: `[File]` (optional, file max 200MB)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Hasil tugas berhasil dikirim",
          "data": {
              "id_tugas": "TG001",
              "progress": 100,
              "status": "submitted"
          }
      }
  }
  ```

---

## 👔 3. Endpoint Kepala Divisi (Role: `kadiv`)

### 3.1 Daftar Staff Divisi
- **Endpoint:** `GET /api/kadiv/staff`
- **Otorisasi:** Protected (`role:kadiv`)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Berhasil mengambil data staff divisi",
          "data": [
              {
                  "id_karyawan": "KR001",
                  "nama": "Budi",
                  "jabatan": "Backend",
                  "user": {
                      "username": "budist",
                      "last_login_at": "2026-09-29 12:40:00"
                  }
              }
          ]
      }
  }
  ```

### 3.2 Detail Staff Divisi + Tugas
- **Endpoint:** `GET /api/kadiv/staff/{id_karyawan}`
- **Otorisasi:** Protected (`role:kadiv`)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Berhasil mengambil detail staff",
          "data": {
              "id_karyawan": "KR001",
              "nama": "Budi",
              "tugas": [ ... ]
          }
      }
  }
  ```

### 3.3 Lihat Seluruh Tugas Divisi
- **Endpoint:** `GET /api/kadiv/tugas`
- **Otorisasi:** Protected (`role:kadiv`)

### 3.4 Buat Tugas Baru
- **Endpoint:** `POST /api/kadiv/tugas`
- **Otorisasi:** Protected (`role:kadiv`)
- **Content-Type:** `multipart/form-data` atau `application/json`
- **Request Body:**
  ```json
  {
      "karyawan_id_karyawan": "KR001",
      "judul_tugas": "Implementasi Payment Gateway",
      "deskripsi": "Integrasi dengan Midtrans API.",
      "deadline": "2026-10-20",
      "link_pendukung": "https://figma.com/file/xyz"
  }
  ```

### 3.5 Edit Tugas
- **Endpoint:** `PUT /api/kadiv/tugas/{id_tugas}` (atau `POST` dengan field `_method: PUT`)
- **Otorisasi:** Protected (`role:kadiv`)

### 3.6 Hapus Tugas
- **Endpoint:** `DELETE /api/kadiv/tugas/{id_tugas}`
- **Otorisasi:** Protected (`role:kadiv`)

### 3.7 Review Tugas (ACC / Revisi)
- **Endpoint:** `POST /api/kadiv/tugas/{id_tugas}/review`
- **Otorisasi:** Protected (`role:kadiv`)
- **Request Body (JSON):**
  ```json
  {
      "status_review": "acc", // pilihan: "acc" atau "revisi"
      "catatan_revisi": "Pekerjaan sangat baik!"
  }
  ```
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Review berhasil disimpan",
          "data": {
              "tugas": { "id_tugas": "TG001", "status": "selesai" },
              "submit": { "status_review": "acc" }
          }
      }
  }
  ```

---

## 🏢 4. Endpoint HRD (Role: `hrd`)

### 4.1 Daftar Seluruh Divisi
- **Endpoint:** `GET /api/hrd/divisi`
- **Otorisasi:** Protected (`role:hrd`)

### 4.2 Tambah Divisi Baru
- **Endpoint:** `POST /api/hrd/divisi`
- **Otorisasi:** Protected (`role:hrd`)
- **Request Body (JSON):**
  ```json
  {
      "kode_divisi": "FN01",
      "nama_divisi": "Finance",
      "status_aktif": "Aktif"
  }
  ```
- **Response Error Duplicate (409 Conflict):**
  ```json
  {
      "success": false,
      "code": 409,
      "message": "Divisi dengan nama \"Finance\" sudah ada",
      "data": null
  }
  ```

### 4.3 Detail Divisi (+ Statistik)
- **Endpoint:** `GET /api/hrd/divisi/{id_divisi}`
- **Otorisasi:** Protected (`role:hrd`)
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "code": 200,
      "message": "OK",
      "data": {
          "message": "Berhasil mengambil detail divisi",
          "data": {
              "divisi": { ... },
              "ketua_divisi": "Pak Tono",
              "total_staff": 3,
              "total_tugas": 2,
              "tugas_selesai": 1,
              "persentase_selesai": "50%"
          }
      }
  }
  ```

### 4.4 Daftar Seluruh Staff Perusahaan
- **Endpoint:** `GET /api/hrd/staff`
- **Otorisasi:** Protected (`role:hrd`)

### 4.5 Tambah Staff Baru + Buatkan Akun
- **Endpoint:** `POST /api/hrd/staff`
- **Otorisasi:** Protected (`role:hrd`)
- **Request Body (JSON):**
  ```json
  {
      "nama": "Dewi Kurnia",
      "jenis_kelamin": "Perempuan",
      "tanggal_lahir": "2000-05-10",
      "no_telepon": "08199887766",
      "email": "dewi@mail.com",
      "jabatan": "Staff Keuangan",
      "divisi_id_divisi": "DV001",
      "username": "dewist",
      "password": "pass123"
  }
  ```
- **Response Error Divisi Tidak Ditemukan (404 Not Found):**
  ```json
  {
      "success": false,
      "code": 404,
      "message": "Divisi dengan ID \"DV999\" tidak ditemukan. Silakan buat divisi terlebih dahulu.",
      "data": null
  }
  ```

### 4.6 Detail Staff & Rincian Tugas
- **Endpoint:** `GET /api/hrd/staff/{id_karyawan}`
- **Otorisasi:** Protected (`role:hrd`)
