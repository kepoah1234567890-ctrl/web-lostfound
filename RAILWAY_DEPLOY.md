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
3. Pastikan versi PHP build dan runtime minimal **8.3**, sesuai `composer.json`. Lock Composer proyek ini ditargetkan ke PHP 8.3 agar tidak memasang komponen Symfony 8 yang membutuhkan PHP 8.4.
4. Biarkan Railway mendeteksi PHP melalui `composer.json`. [railway.json](railway.json) sudah mengatur perintah server Laravel dan health check `/up`.
5. Pada layanan aplikasi, buat public domain melalui **Settings → Networking → Generate Domain**.

Jika mengatur build command sendiri, gunakan `composer install --no-dev --optimize-autoloader --no-interaction`. Jangan gunakan `--ignore-platform-reqs`; opsi itu melewati pemeriksaan versi dan dapat memasang dependency yang tidak kompatibel dengan PHP runtime.

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
Pastikan `APP_URL` memakai `https://`. Aplikasi juga memaksa HTTPS untuk URL Laravel dan helper aset pada environment `production`, supaya stylesheet, gambar, dan formulir tidak diblokir sebagai mixed content.

Gunakan database yang disediakan layanan MySQL Railway melalui `${{MySQL.MYSQLDATABASE}}` untuk `DB_DATABASE`. Railway biasanya membatasi user ke database tersebut; tidak perlu membuat database bernama `lost_found`.

Untuk fitur yang memakai Google OAuth atau SMTP, masukkan kredensialnya sebagai Railway Variables sesuai konfigurasi. Jangan gunakan kembali kredensial yang pernah ditulis langsung di source code; buat/rotasi secret baru di penyedia terkait.

## 4. Impor database `lost_found`

Siapkan struktur database aplikasi di database MySQL Railway. Cara yang disarankan adalah menjalankan initializer aman dari terminal lokal lewat TCP Proxy Railway. Di layanan MySQL, aktifkan koneksi TCP publik sementara melalui pengaturan networking, lalu ambil host TCP Proxy, port, nama database, username, dan password dari Railway Variables.

Di PowerShell pada folder proyek, isi kredensial dengan prompt tersembunyi lalu jalankan initializer. Gunakan nama host dan port **TCP Proxy** untuk koneksi dari komputer lokal:

```powershell
$env:DB_CONNECTION = "mysql"
$env:DB_HOST = "<HOST_TCP_PROXY>"
$env:DB_PORT = "<PORT_TCP_PROXY>"
$env:DB_DATABASE = "<MYSQLDATABASE>"
$env:DB_USERNAME = "<MYSQLUSER>"
$securePassword = Read-Host "Railway MySQL password" -AsSecureString
$env:DB_PASSWORD = [System.Net.NetworkCredential]::new("", $securePassword).Password
php database/migrate.php
$env:DB_PASSWORD = $null
```

Initializer membuat tabel aplikasi yang belum ada dan tidak menghapus tabel atau data yang sudah ada. Jalankan dari root proyek. Setelah berhasil, nonaktifkan TCP Proxy publik jika tidak diperlukan. Alternatifnya, setelah commit yang berisi migration tersedia, jalankan `php artisan migrate --force` dari shell layanan aplikasi Railway.

Jangan jalankan `php artisan migrate:fresh --seed` pada Railway atau database berisi data penting. Perintah tersebut menghapus semua tabel sebelum membangunnya kembali, dan seeder hanya untuk data contoh/testing.

Jika perlu memindahkan data dari database lokal, gunakan Artisan command `lostfound:import-data` dari komputer lokal melalui TCP Proxy Railway. File `.sql` dibaca dari komputer lokal, sedangkan koneksi Artisan diarahkan ke database Railway; file berisi data pribadi tidak perlu dimasukkan ke GitHub atau diunggah ke image aplikasi. Gunakan file **data-only** yang kompatibel dengan schema aplikasi, seperti `E:\lost_found_railway.sql` yang dibuat dari backup lama. Jangan langsung gunakan full dump `E:\lost_found.sql`, karena file itu berisi schema lama.

Jalankan migration dahulu, lalu set koneksi proxy di PowerShell dari root proyek. Eusian host, port, database, jeung username tina TCP Proxy/Variables Railway:

```powershell
$env:DB_CONNECTION = "mysql"
$env:DB_HOST = "<HOST_TCP_PROXY>"
$env:DB_PORT = "<PORT_TCP_PROXY>"
$env:DB_DATABASE = "<MYSQLDATABASE>"
$env:DB_USERNAME = "<MYSQLUSER>"
$securePassword = Read-Host "Railway MySQL password" -AsSecureString
$env:DB_PASSWORD = [System.Net.NetworkCredential]::new("", $securePassword).Password

php artisan migrate --force
php artisan lostfound:import-data "E:\lost_found_railway.sql"
```

Command bakal mariksa jumlah data heula. Lamun database tujuan teu kosong, ulah diteruskeun lamun can nyieun backup. Pikeun ngahaja ngaganti data aplikasi Railway ku data tina file, tambahkeun `--replace`; paréntah bakal nembongkeun jumlah data ayeuna jeung ménta konfirmasi. Data dihapus jeung diimpor dina hiji transaksi, sarta bakal dibatalkeun lamun import gagal.

```powershell
php artisan lostfound:import-data "E:\lost_found_railway.sql" --replace
Remove-Item Env:DB_PASSWORD
```

Ulah ngajalankeun import ti Railway service Console lamun file-na ngan aya di komputer lokal. Jalankeun command ti terminal lokal kalayan TCP Proxy aktif. Sanggeus réngsé, nonaktifkeun TCP Proxy publik lamun teu diperlukeun.

Periksa setidaknya tabel `users`, `barang`, `laporan_hilang`, `klaim`, `pengembalian`, dan `aktivitas` (jika ada pada dump yang digunakan). Aplikasi lama memakai skema database tersebut secara langsung; jangan jalankan `php artisan migrate` sebagai pengganti impor skema.

Setelah impor, nonaktifkan lagi TCP publik jika tidak diperlukan.

## 5. Simpan foto secara permanen

Foto baru disimpan di folder upload lokal aplikasi **dan** tabel MySQL `uploaded_files`. Jika file lokal hilang setelah Railway mengganti instance atau deploy ulang, aplikasi mengambil salinan permanen dari MySQL. `railway.json` menjalankan initializer skema yang idempoten sebelum server mulai; initializer tidak menghapus data aplikasi.

Untuk menyalin foto yang sudah ada dari komputer lokal ke database Railway:

1. Buat backup database dan rotasi password database jika pernah dibagikan melalui chat, tiket, atau source code.
2. Aktifkan TCP Proxy MySQL sementara. Dari root project, arahkan environment `DB_HOST`, `DB_PORT`, `DB_DATABASE`, dan `DB_USERNAME` ke kredensial proxy; masukkan password memakai prompt tersembunyi seperti contoh pada bagian impor database.
3. Pastikan foto lokal lolos validasi tanpa mengubah database:

   ```powershell
   php artisan lostfound:sync-uploads --dry-run
   ```

4. Jalankan sinkronisasi. Perintah ini hanya menambah atau memperbarui isi gambar berdasarkan nama file; tidak menghapus foto maupun data aplikasi:

   ```powershell
   php artisan lostfound:sync-uploads
   ```

5. Hapus environment password dari terminal dan nonaktifkan TCP Proxy jika tidak diperlukan. Jangan menyimpan kredensial di Git atau memasukkannya langsung ke perintah terminal.

Foto satu file dibatasi sampai 16 MB oleh tipe `MEDIUMBLOB`; batas upload aplikasi tetap 2 MB. Volume Railway tidak lagi diperlukan untuk mempertahankan foto, meskipun boleh dipakai sebagai cache file lokal.

## 6. Deploy dan tes

1. Simpan perubahan dan push ke branch repository yang dipilih Railway. Build menjalankan instalasi Composer; start command menjalankan `php artisan serve --host=0.0.0.0 --port=$PORT`.
2. Tunggu deployment sukses, lalu cek log build dan deploy.
3. Buka `https://<domain-railway>/up`; health check seharusnya merespons sukses.
4. Buka homepage, login, daftar barang, gambar barang, halaman laporan/klaim, dan dashboard admin.
5. Tes API yang dipakai, misalnya `/api/barang.php`, lalu buat satu upload percobaan dan redeploy untuk memastikan foto di volume tetap ada.
6. Jika koneksi gagal, cocokkan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` aplikasi dengan variable layanan MySQL. Jangan menampilkan password di issue atau log publik.

Untuk perubahan kode berikutnya, commit dan push ke GitHub; Railway akan membangun deployment baru. Backup database MySQL dan isi Volume secara berkala.
