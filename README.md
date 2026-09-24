# 🏢 Employee Management Dashboard

Sistem informasi berbasis web untuk manajemen tugas, pelacakan progres pekerjaan, dan komunikasi internal perusahaan. Aplikasi ini dirancang menggunakan **Laravel** dan **PostgreSQL**, mendukung arsitektur multi-role yang saling terintegrasi.

## 🌟 Fitur Utama

Sistem ini memiliki 3 hak akses utama dengan fitur spesifik untuk masing-masing peran:

### 1. 🧑‍💻 Staff
* **Dashboard Analitik:** Widget statistik tugas (selesai, tertunda, belum selesai) beserta persentase progres.
* **Manajemen Tugas:** Menerima tugas, melihat detail lengkap (deskripsi, tenggat waktu, file pendukung), dan mengunggah hasil pekerjaan (file/link).
* **Pesan & Komunikasi:** Kotak masuk untuk pesan internal, melihat detail surat/pesan, dan membalas melalui sistem komentar (thread).
* **Notifikasi Real-time:** Pemberitahuan untuk tugas dan pesan baru.

### 2. 👨‍💼 Kepala Divisi (Kadiv)
* **Dashboard Divisi:** Pemantauan statistik tugas spesifik dalam divisinya (belum dikirim, menunggu persetujuan, disetujui).
* **Manajemen Staff:** Memantau daftar anggota divisi, aktivitas login terakhir, dan jumlah beban tugas per karyawan.
* **Delegasi Tugas:** Membuat tugas (assign) ke staff spesifik di divisinya, mengatur tenggat waktu via kalender, dan melampirkan file pendukung.
* **Approval Workflow:** Meninjau, mengedit, atau menghapus tugas yang sedang berjalan atau sudah disubmit oleh staff.
* **Persuratan:** Mengirim pesan/surat internal ke staff atau eskalasi pesan ke HRD dengan lampiran file/link.

### 3. 🏢 HRD
* **Dashboard Perusahaan:** Overview total jumlah divisi, total staff, dan persentase penyelesaian tugas di tingkat perusahaan.
* **Manajemen Divisi:** Menambahkan divisi baru, melihat metrik kinerja per divisi, dan memantau ketua serta anggota dari tiap divisi.
* **Monitoring Karyawan:** Akses untuk melihat detail beban kerja dan progres tugas setiap karyawan dari seluruh divisi.
* **Komunikasi Global:** Fitur perpesanan lintas divisi dan pengumuman tingkat perusahaan.

## 🛠️ Teknologi yang Digunakan

* **Backend:** Laravel (PHP)
* **Database:** PostgreSQL
* **Struktur Database:** Dilengkapi dengan sistem Indexing, Foreign Key constraints, dan Custom Enum Types untuk optimasi query kalkulasi dashboard.

## 🚀 Cara Instalasi & Menjalankan Project

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di komputer lokal:

### Prasyarat
* PHP >= 8.1
* Composer
* PostgreSQL (pastikan service berjalan)

### Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    divisi ||--o{ karyawan : "memiliki"
    divisi {
        VARCHAR2 id_divisi PK
        VARCHAR2 kode_divisi
        VARCHAR2 nama_divisi
        VARCHAR2 status_aktif
    }

    karyawan ||--o{ tugas : "ditugaskan"
    karyawan ||--o{ User : "memiliki akun"
    karyawan {
        VARCHAR2 id_karyawan PK
        VARCHAR2 nama
        VARCHAR2 jenis_kelamin
        DATE tanggal_lahir
        DATE tanggal_rekrut
        VARCHAR2 no_telepon
        VARCHAR2 email
        VARCHAR2 jabatan
        VARCHAR2 divisi_id_divisi FK
    }

    role ||--o{ User : "memiliki"
    role {
        VARCHAR2 id_role PK
        VARCHAR2 nama_role
    }

    User ||--o{ notifikasi : "menerima"
    User {
        VARCHAR2 id_user PK
        VARCHAR2 username
        VARCHAR2 password
        VARCHAR2 role_id_role FK
        VARCHAR2 karyawan_id_karyawan FK
    }

    notifikasi {
        VARCHAR2 id_notifikasi PK
        VARCHAR2 judul_notifikasi
        VARCHAR2 isi_notif
        DATE tanggal_notifikasi
        VARCHAR2 user_id_user FK
    }

    tugas ||--o{ pesan : "memiliki"
    tugas ||--o{ submit_tugas : "mempunyai"
    tugas {
        VARCHAR2 id_tugas PK
        VARCHAR2 karyawan_id_karyawan PK, FK
        VARCHAR2 judul_tugas
        VARCHAR2 deskripsi
        DATE deadline
        VARCHAR2 progress
        VARCHAR2 status
        DATE tanggal_dibuat
        DATE tanggal_update
    }

    pesan {
        VARCHAR2 id_pesan PK
        VARCHAR2 judul_pesan
        VARCHAR2 deskripsi
        DATE tanggal_pesan
        VARCHAR2 tugas_id_tugas FK
        VARCHAR2 tugas_karyawan_id_karyawan FK
    }

    submit_tugas {
        VARCHAR2 id_submit_tugas PK
        BLOB link_submit
        VARCHAR2 file_hasil
        VARCHAR2 catatan_karyawan
        VARCHAR2 catatan_revisi
        DATE tanggal_submit
        VARCHAR2 status_review
        VARCHAR2 tugas_id_tugas FK
        VARCHAR2 tugas_karyawan_id_karyawan FK
    }

