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

   git clone [https://github.com/username-kamu/nama-repo-kamu.git](https://github.com/username-kamu/nama-repo-kamu.git)
   cd nama-repo-kamu
