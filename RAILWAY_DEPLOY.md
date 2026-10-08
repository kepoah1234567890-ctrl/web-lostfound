# Hosting di Railway

Panduan ini men-deploy aplikasi Laravel 13, menghubungkannya ke MySQL, dan menjaga foto upload tetap tersimpan. File `.env`, kredensial database, serta dump database tidak boleh dimasukkan ke Git.

## 1. Siapkan kode dan cadangan database

1. Pastikan proyek sudah ada di repository GitHub yang dapat diakses Railway.
2. Periksa PHP lokal dengan `php -v` (minimal 8.3) dan jalankan `composer install`.
3. Buat dump terbaru dari MySQL lokal sebelum mulai. Di Windows, jalankan dari folder proyek (sesuaikan lokasi `mysqldump.exe` bila tidak ada di `PATH`):

   ```powershell
   mysqldump -u root -p --single-transaction --routines --triggers lost_found > lost_found_backup.sql
   ```

   Jika database memakai password kosong di Laragon, hilangkan opsi `-p`. Simpan dump ini dengan aman dan jangan commit ke Git.
4. Foto yang ada saat ini berada di folder `uploads/`. Buat salinan folder tersebut; foto lama tidak otomatis ikut masuk ke Railway Volume yang masih kosong.

## 2. Buat layanan Railway

1. Buat project baru di Railway dan tambahkan layanan **MySQL**.
2. Tambahkan layanan aplikasi dari repository GitHub yang berisi proyek ini.
3. Biarkan Railway mendeteksi PHP melalui `composer.json`. [railway.json](railway.json) sudah mengatur perintah server Laravel dan health check `/up`.
4. Pada layanan aplikasi, buat public domain melalui **Settings → Networking → Generate Domain**.

## 3. Isi variables aplikasi

Di **Variables** pada layanan aplikasi, tambahkan:

| Variable | Nilai |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` |
| `APP_KEY` | Hasil `php artisan key:generate --show` yang dijalankan lokal |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `APP_LOCALE` | `id` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `lost_found` jika database itu tersedia; jika memakai database bawaan layanan MySQL, gunakan `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `CACHE_STORE` | `file` |
| `SESSION_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `UPLOAD_PATH` | `/app/uploads/barang` |

Ganti `MySQL` pada referensi variable dengan nama layanan MySQL Railway yang sebenarnya. Jangan menaruh nilai rahasia di file konfigurasi atau Git.

Untuk menggunakan nama database `lost_found`, pastikan database tersebut sudah dibuat pada MySQL Railway sebelum impor. Jika akun database hanya diberi akses ke database bawaan, gunakan nilai `MYSQLDATABASE` sebagai `DB_DATABASE` (database itu tetap dapat diisi dengan tabel aplikasi Lost & Found).

Untuk fitur yang memakai Google OAuth atau SMTP, masukkan kredensialnya sebagai Railway Variables sesuai konfigurasi. Jangan gunakan kembali kredensial yang pernah ditulis langsung di source code; buat/rotasi secret baru di penyedia terkait.

## 4. Impor database `lost_found`

Impor dump lokal terbaru ke database MySQL Railway sebelum membuka aplikasi. Di layanan MySQL, aktifkan koneksi TCP publik sementara melalui pengaturan networking, lalu ambil host, port, nama database, username, dan password dari Railway Variables. Dari komputer lokal, jalankan:

```powershell
mysql --host=<HOST_RAILWAY> --port=<PORT_RAILWAY> --user=<USERNAME> --password <NAMA_DATABASE> < lost_found_backup.sql
```

Masukkan password ketika diminta. Jika dump belum menyertakan struktur/data yang ingin dipakai, `database/lost_found_current_export.sql` dapat menjadi sumber awal; impor hanya ke database target yang kosong. Jangan jalankan dump awal berulang kali pada database berisi data karena dapat menggandakan data.

Periksa setidaknya tabel `users`, `barang`, `laporan_hilang`, `klaim`, `pengembalian`, dan `aktivitas` (jika ada pada dump yang digunakan). Aplikasi lama memakai skema database tersebut secara langsung; jangan jalankan `php artisan migrate` sebagai pengganti impor skema.

Setelah impor, nonaktifkan lagi TCP publik jika tidak diperlukan.

## 5. Simpan foto secara permanen

1. Tambahkan **Volume** pada layanan aplikasi Railway.
2. Atur mount path ke `/app/uploads`. Variable `UPLOAD_PATH=/app/uploads/barang` harus mengarah ke folder barang di dalam volume tersebut.
3. Setelah volume aktif, salin isi cadangan `uploads/` lokal ke dalam volume, dengan struktur yang sama (`barang/` dan `avatar/`). Volume baru mengosongkan/menutupi folder pada image deploy, sehingga foto lama perlu disalin sekali.
4. Pastikan proses PHP bisa membaca dan menulis volume. Foto baru akan disimpan di sana dan dilayani melalui rute upload Laravel.

Tanpa volume, upload yang dibuat aplikasi dapat hilang saat Railway mengganti instance atau melakukan deploy ulang.

## 6. Deploy dan tes

1. Simpan perubahan dan push ke branch repository yang dipilih Railway. Build menjalankan instalasi Composer; start command menjalankan `php artisan serve --host=0.0.0.0 --port=$PORT`.
2. Tunggu deployment sukses, lalu cek log build dan deploy.
3. Buka `https://<domain-railway>/up`; health check seharusnya merespons sukses.
4. Buka homepage, login, daftar barang, gambar barang, halaman laporan/klaim, dan dashboard admin.
5. Tes API yang dipakai, misalnya `/api/barang.php`, lalu buat satu upload percobaan dan redeploy untuk memastikan foto di volume tetap ada.
6. Jika koneksi gagal, cocokkan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` aplikasi dengan variable layanan MySQL. Jangan menampilkan password di issue atau log publik.

Untuk perubahan kode berikutnya, commit dan push ke GitHub; Railway akan membangun deployment baru. Backup database MySQL dan isi Volume secara berkala.
