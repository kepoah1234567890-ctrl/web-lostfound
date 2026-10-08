# 🏫 Lost & Found - SMK Informatika Sumedang

Aplikasi Web **Lost & Found (Barang Hilang & Ditemukan)** resmi untuk lingkungan **SMK Informatika Sumedang**. Aplikasi berjalan di atas **Laravel 13** dan **MySQL**. Controller, tampilan, API, aset, serta alur aplikasi lama dipertahankan melalui rute kompatibilitas agar tampilan dan fungsi yang sudah ada tidak berubah. Antarmuka menggunakan Pastel UI yang responsif.

---

## 📋 Daftar Isi

1. [Teknologi & Fitur](#1-teknologi--fitur)
2. [Persyaratan Sistem (Requirements)](#2-persyaratan-sistem-requirements)
3. [Panduan Instalasi XAMPP](#3-panduan-instalasi-xampp)
4. [Pembuatan Database & Import SQL](#4-pembuatan-database--import-sql)
5. [Konfigurasi Database](#5-konfigurasi-database)
6. [Cara Menjalankan Website](#6-cara-menjalankan-website)
7. [Akun Pengguna untuk Pengujian (Dummy Data)](#7-akun-pengguna-untuk-pengujian-dummy-data)
8. [Struktur Folder & Penjelasan MVC](#8-struktur-folder--penjelasan-mvc)
9. [Dokumentasi REST API](#9-dokumentasi-rest-api)
10. [Contoh Request & Response REST API](#10-contoh-request--response-rest-api)
11. [Keamanan & Validasi](#11-keamanan--validasi)
12. [Panduan Hosting Railway](#12-panduan-hosting-railway)

---

## 1. Teknologi & Fitur

### Teknologi
- **Framework**: Laravel 13 (PHP 8.3+)
- **Database**: MySQL dengan Laravel database config dan PDO untuk modul kompatibilitas lama
- **Frontend**: HTML5, CSS3 kustom (Pastel Theme), JavaScript Vanilla, Bootstrap 5.3 & Bootstrap Icons
- **Arsitektur**: Laravel front controller dengan rute kompatibilitas untuk MVC lama
- **Autentikasi**: PHP Session + `password_hash()` & `password_verify()`
- **API**: REST API Native dengan output format JSON standar

### Fitur Utama

#### Role Siswa:
- 🔍 **Katalog & Pencarian**: Menjelajahi barang yang ditemukan, mencari berdasarkan kata kunci, filter kategori, dan filter status.
- 📄 **Detail Barang**: Melihat detail spesifik barang, foto, lokasi, dan tanggal ditemukan.
- 📢 **Laporkan Barang Hilang**: Siswa dapat membuat laporan kehilangan barang miliknya.
- 🎁 **Laporkan Barang Ditemukan**: Siswa dapat mengunggah barang yang mereka temukan di sekolah (otomatis masuk ke status `tersedia`).
- ✋ **Klaim Barang ("Ini Barang Saya")**: Mengajukan klaim dengan mengisi ciri-ciri unik dan mengunggah foto bukti kepemilikan.
- 📊 **Riwayat & Status**: Memantau status laporan kehilangan dan perkembangan persetujuan klaim secara realtime.

#### Role Admin:
- 📊 **Dashboard & Statistik**: Ringkasan total barang temuan, barang tersedia, laporan hilang, klaim menunggu, dan barang yang sudah dikembalikan.
- 📦 **Manajemen Barang (CRUD)**: Menambah, melihat, mengedit, memperbarui status, dan menghapus barang temuan.
- 📋 **Verifikasi Laporan Hilang**: Mengelola status laporan kehilangan siswa (`menunggu`, `diverifikasi`, `ditemukan`, `selesai`).
- 🛡️ **Verifikasi Klaim**: Menyetujui atau menolak klaim kepemilikan dari siswa serta memberikan catatan resmi.
- 🤝 **Serah Terima & Pengembalian**: Mencatat proses penyerahan barang yang disetujui kepada pemilik sah dan mengarsipkan riwayat pengembalian.

---

## 2. Persyaratan Sistem (Requirements)

- **Sistem Operasi**: Windows 10/11, Linux, atau macOS
- **Web Server**: Apache (XAMPP / WampServer / Laragon) atau PHP Built-in Server
- **PHP**: Versi 8.3 atau yang lebih baru
- **Composer**: Versi 2
- **Ekstensi PHP**: `pdo_mysql`, `mbstring`, `fileinfo`, `session`
- **Database**: MySQL 5.7+ atau MariaDB 10.4+
- **Web Browser**: Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari

---

## 3. Panduan Instalasi XAMPP

1. Unduh installer XAMPP dengan PHP 8+ dari situs resmi: [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Jalankan installer dan ikuti instruksi hingga selesai.
3. Buka **XAMPP Control Panel**.
4. Klik tombol **Start** pada modul **Apache** dan **MySQL** hingga indikator berwarna hijau.

---

## 4. Pembuatan Database & Import SQL

Proyek ini telah menyediakan dua skrip SQL:
- `database/schema.sql` (Struktur tabel DDL)
- `database/seed.sql` (Data dummy realistis)
- `database/migrate.php` (Skrip otomatis untuk membuat dan mengisi database)

### Cara 1: Menggunakan Skrip Otomatis CLI (Paling Cepat)
Buka terminal PowerShell / Command Prompt dan jalankan:
```bash
C:\xampp\php\php.exe database/migrate.php
```

### Cara 2: Menggunakan phpMyAdmin
1. Buka browser dan akses `http://localhost/phpmyadmin/`.
2. Klik menu **Databases**, buat database baru bernama `lost_found` (Collation: `utf8mb4_unicode_ci`), lalu klik **Create**.
3. Pilih database `lost_found` di panel kiri.
4. Klik tab **Import** di menu atas.
5. Klik **Choose File**, pilih file `database/schema.sql`, lalu klik tombol **Import** di bagian bawah.
6. Ulangi langkah Import untuk file `database/seed.sql`.

---

## 5. Konfigurasi Database

Pengaturan koneksi lokal terdapat di `.env` (buat dari `.env.example`):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lost_found
DB_USERNAME=root
DB_PASSWORD=
```

Laravel menggunakan `config/database.php`; model PDO lama membaca variabel koneksi yang sama melalui `legacy-config/legacy_database.php`.

---

## 6. Cara Menjalankan Website

Letakkan folder proyek di dalam direktori `htdocs` XAMPP:
`C:\xampp\htdocs\web_lostfound\`

### Opsi A: Melalui XAMPP Apache
1. Pastikan Apache dan MySQL aktif.
2. Jalankan `composer install`.
3. Salin `.env.example` menjadi `.env`, isi koneksi database `lost_found`, lalu jalankan `php artisan key:generate`.
4. Untuk virtual host Laravel, arahkan document root ke `C:\xampp\htdocs\web_lostfound\public`, atur `APP_URL=http://localhost` di `.env`, lalu buka `http://localhost/`.
5. Jika memakai URL subfolder Laragon (`http://localhost/web_lostfound/`), atur `APP_URL=http://localhost/web_lostfound` di `.env` dan gunakan rewrite `.htaccess` proyek.

### Opsi B: Melalui PHP Built-in Server
Buka terminal di folder proyek dan jalankan:
```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8000
```
Dengan `.env.example` tanpa perubahan, buka langsung `http://127.0.0.1:8000/`. Jangan tambahkan `/web_lostfound`. Jangan jalankan `php artisan migrate` untuk database lama; gunakan database MySQL `lost_found` yang sudah ada.

---

## 7. Akun Pengguna untuk Pengujian (Dummy Data)

### Akun Administrator
| Role | Email | Password | NIS / Identitas | Keterangan |
|---|---|---|---|---|
| **Admin** | `admin.lostfound@gmail.com` | `admin1234` | `ADM001` | Akses penuh dashboard admin & verifikasi |

### Akun Siswa (Dapat Digunakan Langsung)
| No | Nama Siswa | Email | Password | NIS | Kelas |
|---|---|---|---|---|---|
| 1 | Budi Santoso | `budi.santoso@gmail.com` | `user123` | `22231001` | XII RPL 1 |
| 2 | Siti Rahmawati | `siti.rahmawati@gmail.com` | `user123` | `22231002` | XI TKJ 2 |
| 3 | Ahmad Fauzan | `ahmad.fauzan@gmail.com` | `user123` | `22231003` | X DKV 1 |
| 4 | Dewi Lestari | `dewi.lestari@gmail.com` | `user123` | `22231004` | XII RPL 2 |
| 5 | Rizky Pratama | `rizky.pratama@gmail.com` | `user123` | `22231005` | XI TKJ 1 |

> Siswa baru juga dapat mendaftar langsung melalui menu **Daftar** di website.

---

## 8. Struktur Folder & Penjelasan MVC

```text
web_lostfound/
│
├── app/
│   ├── controllers/            # Controller: Menangani logika alur, validasi, dan response
│   │   ├── AuthController.php      # Login, registrasi, logout
│   │   ├── BarangController.php    # Katalog barang, detail, tambah, edit, hapus
│   │   ├── LaporanController.php   # Lapor hilang, lapor temuan, riwayat pelaporan
│   │   ├── KlaimController.php     # Pengajuan klaim dan pemantauan riwayat klaim siswa
│   │   └── AdminController.php     # Dashboard statistik, verifikasi laporan/klaim, serah terima
│   │
│   ├── models/                 # Model: Menangani interaksi & kueri database (PDO)
│   │   ├── User.php                # Kueri tabel users & otentikasi
│   │   ├── Barang.php              # Kueri CRUD barang ditemukan & statistik
│   │   ├── LaporanHilang.php       # Kueri laporan kehilangan barang
│   │   ├── Klaim.php               # Kueri klaim barang
│   │   └── Pengembalian.php        # Kueri pencatatan serah terima barang
│   │
│   └── views/                  # View: Tampilan antarmuka HTML & Pastel UI
│       ├── layouts/                # Template header, navbar, footer, 404
│       ├── home/                   # Halaman beranda utama
│       ├── auth/                   # Halaman login & register
│       ├── barang/                 # Tampilan katalog, detail, create, edit barang
│       ├── laporan/                # Tampilan form lapor hilang/temuan & riwayat
│       ├── klaim/                  # Form klaim & daftar riwayat klaim siswa
│       └── admin/                  # Halaman dashboard & pengelolaan admin
│
├── config/
│   └── database.php            # Konfigurasi koneksi Laravel/MySQL
├── legacy-config/
│   ├── legacy_database.php     # PDO Singleton untuk modul lama
│   └── config.php              # Helper global, path, session, format tanggal, & render view
│
├── bootstrap/                  # Bootstrap Laravel 13
├── routes/
│   └── legacy.php              # Rute kompatibilitas halaman, API, aset, dan upload
├── legacy/
│   └── front-controller.php    # Router lama yang dijalankan melalui Laravel
├── public/                     # Document root publik
│   ├── index.php               # Front controller Laravel 13
│   ├── .htaccess               # URL Rewrite Apache
│   └── assets/
│       ├── css/style.css       # Styling custom warna pastel, shadow, responsive
│       ├── js/app.js           # Preview gambar, konfirmasi modal, auto-dismiss alert
│       └── images/             # Gambar pendukung & SVG placeholder
│
├── api/                        # REST API Endpoints (JSON Native)
│   ├── auth.php                # API login, register, logout, profil me
│   ├── barang.php              # API CRUD barang ditemukan
│   ├── laporan.php             # API CRUD laporan kehilangan
│   ├── klaim.php               # API CRUD pengajuan & verifikasi klaim
│   └── users.php               # API daftar & detail pengguna
│
├── database/                   # Skrip basis data
│   ├── schema.sql              # Struktur tabel DDL
│   ├── seed.sql                # Data dummy awal
│   └── migrate.php             # Runner migrasi otomatis
│
├── uploads/
│   └── barang/                 # Direktori penyimpanan foto barang & bukti kepemilikan
│
├── index.php                   # Root bootstrap redirector
├── .htaccess                   # Root htaccess URL rewriting
└── README.md                   # Dokumentasi lengkap
```

---

## 9. Dokumentasi REST API

Format response API selalu berupa JSON terstruktur:

**Format Berhasil:**
```json
{
    "success": true,
    "message": "Pesan keberhasilan",
    "data": []
}
```

**Format Gagal:**
```json
{
    "success": false,
    "message": "Pesan kesalahan",
    "data": null
}
```

### Ringkasan Endpoint

| No | Modul | Method | Endpoint | Keterangan |
|---|---|---|---|---|
| 1 | **Auth** | `POST` | `/api/auth.php?action=login` | Login user & set session |
| 2 | **Auth** | `POST` | `/api/auth.php?action=register` | Registrasi akun siswa baru |
| 3 | **Auth** | `POST` | `/api/auth.php?action=logout` | Logout sesi user |
| 4 | **Auth** | `GET` | `/api/auth.php?action=me` | Ambil data user yang sedang login |
| 5 | **Barang** | `GET` | `/api/barang.php` | Mengambil seluruh daftar barang temuan |
| 6 | **Barang** | `GET` | `/api/barang.php?id={id}` | Mengambil detail spesifik barang |
| 7 | **Barang** | `POST` | `/api/barang.php` | Menambah data barang temuan baru |
| 8 | **Barang** | `PUT` | `/api/barang.php?id={id}` | Memperbarui data barang |
| 9 | **Barang** | `DELETE` | `/api/barang.php?id={id}` | Menghapus data barang |
| 10 | **Laporan** | `GET` | `/api/laporan.php` | Mengambil daftar laporan barang hilang |
| 11 | **Laporan** | `GET` | `/api/laporan.php?id={id}` | Mengambil detail laporan barang hilang |
| 12 | **Laporan** | `POST` | `/api/laporan.php` | Membuat laporan kehilangan baru |
| 13 | **Laporan** | `PUT` | `/api/laporan.php?id={id}` | Memperbarui data/status laporan hilang |
| 14 | **Laporan** | `DELETE` | `/api/laporan.php?id={id}` | Menghapus laporan hilang |
| 15 | **Klaim** | `GET` | `/api/klaim.php` | Mengambil daftar pengajuan klaim |
| 16 | **Klaim** | `POST` | `/api/klaim.php` | Mengajukan klaim kepemilikan barang |
| 17 | **Klaim** | `PUT` | `/api/klaim.php?id={id}` | Memverifikasi status klaim (`disetujui`/`ditolak`) |
| 18 | **Klaim** | `DELETE` | `/api/klaim.php?id={id}` | Menghapus / membatalkan klaim |
| 19 | **Users** | `GET` | `/api/users.php` | Mengambil daftar pengguna |
| 20 | **Users** | `GET` | `/api/users.php?id={id}` | Mengambil profil pengguna tertentu |

---

## 10. Contoh Request & Response REST API

### 1. Ambil Semua Barang Ditemukan
**Request:**
```http
GET http://localhost/web_lostfound/api/barang.php HTTP/1.1
```
**Response (200 OK):**
```json
{
    "success": true,
    "message": "Daftar barang berhasil diambil",
    "data": [
        {
            "id": 1,
            "nama_barang": "Tas Ransel Hitam Eiger",
            "kategori": "Tas",
            "warna": "Hitam",
            "deskripsi": "Tas ransel merk Eiger warna hitam, ada gantungan kunci robot.",
            "lokasi_ditemukan": "Lab Komputer RPL 2",
            "tanggal_ditemukan": "2026-09-08",
            "foto": "sample_tas.jpg",
            "status": "tersedia",
            "pelapor_nama": "Budi Santoso"
        }
    ]
}
```

### 2. Tambah Barang Baru (POST)
**Request:**
```http
POST http://localhost/web_lostfound/api/barang.php HTTP/1.1
Content-Type: application/json

{
    "nama_barang": "Tumbler Biru Tupperware",
    "kategori": "Botol Minum",
    "warna": "Biru",
    "deskripsi": "Tumbler dengan stiker SMK Informatika",
    "lokasi_ditemukan": "Kantin Sekolah Meja No. 5",
    "tanggal_ditemukan": "2026-09-10"
}
```
**Response (201 Created):**
```json
{
    "success": true,
    "message": "Barang berhasil ditambahkan",
    "data": {
        "id": 6,
        "nama_barang": "Tumbler Biru Tupperware",
        "kategori": "Botol Minum",
        "warna": "Biru",
        "deskripsi": "Tumbler dengan stiker SMK Informatika",
        "lokasi_ditemukan": "Kantin Sekolah Meja No. 5",
        "tanggal_ditemukan": "2026-09-10",
        "status": "tersedia"
    }
}
```

### 3. Ajukan Klaim Barang (POST)
**Request:**
```http
POST http://localhost/web_lostfound/api/klaim.php HTTP/1.1
Content-Type: application/json

{
    "barang_id": 1,
    "user_id": 2,
    "ciri_barang": "Terdapat gantungan kunci robot besi di resleting depan dan di dalam saku ada buku catatan kecil."
}
```
**Response (201 Created):**
```json
{
    "success": true,
    "message": "Klaim berhasil diajukan",
    "data": {
        "id": 3,
        "barang_id": 1,
        "user_id": 2,
        "ciri_barang": "Terdapat gantungan kunci robot besi di resleting depan...",
        "status": "menunggu"
    }
}
```

### 4. Verifikasi Klaim oleh Admin (PUT)
**Request:**
```http
PUT http://localhost/web_lostfound/api/klaim.php?id=3 HTTP/1.1
Content-Type: application/json

{
    "status": "disetujui",
    "catatan_admin": "Ciri barang sangat cocok. Silakan ambil di ruang Tata Usaha."
}
```
**Response (200 OK):**
```json
{
    "success": true,
    "message": "Status klaim berhasil diperbarui",
    "data": {
        "id": 3,
        "status": "disetujui",
        "catatan_admin": "Ciri barang sangat cocok. Silakan ambil di ruang Tata Usaha."
    }
}
```

---

## 11. Keamanan & Validasi

Aplikasi ini menerapkan standar keamanan terbaik untuk PHP Native:
- **Prepared Statements (PDO)**: Mencegah SQL Injection pada semua query insert, select, update, dan delete.
- **Password Hashing**: Menggunakan algoritma Bcrypt bawaan PHP (`password_hash` & `password_verify`).
- **Session Security**: Session fixation dicegah dengan `session_regenerate_id(true)` saat login berhasil.
- **Role-Based Authorization**: Proteksi halaman admin sehingga siswa biasa tidak dapat mengakses panel admin (`requireAdmin()`).
- **CSRF Protection**: Semua aksi `POST` pada halaman web utama memerlukan token sesi yang valid; logout juga menggunakan `POST`.
- **File Upload Validation**: Memeriksa ekstensi dan MIME gambar (`jpg`, `jpeg`, `png`, `webp`), memastikan file benar-benar gambar, dan membatasi ukuran maksimal 2MB.
- **XSS Protection**: Sanitasi output data menggunakan fungsi `htmlspecialchars()` (`e()`) sebelum dirender di HTML.

---

## 🎨 Palet Warna Pastel SMK Informatika Sumedang

- **Pastel Blue**: `#DCEBFA`
- **Pastel Purple**: `#E8DDF5`
- **Pastel Green**: `#DDF3E4`
- **Pastel Yellow**: `#FFF1C9`
- **Pastel Pink**: `#F9DDE5`
- **Putih**: `#FFFFFF`
- **Dark Navy**: `#26354A`

---

*Dikembangkan untuk SMK Informatika Sumedang.*

---

## 12. Panduan Hosting Railway

Panduan langkah demi langkah untuk menerbitkan aplikasi dan menghubungkan MySQL Railway tersedia di [RAILWAY_DEPLOY.md](RAILWAY_DEPLOY.md). Panduan tersebut mencakup impor database `lost_found`, environment variables, volume permanen untuk foto, deploy, dan tes setelah deploy.
