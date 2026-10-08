-- ==========================================================
-- Seed Data: Lost & Found SMK Informatika Sumedang
-- Password admin: admin1234
-- Password siswa: user123
-- ==========================================================

USE lost_found;

-- Disable FK checks during seed
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pengembalian;
TRUNCATE TABLE klaim;
TRUNCATE TABLE laporan_hilang;
TRUNCATE TABLE barang;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert Users (1 Admin & 5 Siswa)
INSERT INTO users (id, nama, nis, email, password, kelas, role, created_at) VALUES
(1, 'Administrator Lost & Found', 'ADM001', 'admin.lostfound@gmail.com', '$2y$10$F/Al1hSVefjLDpuHoY2TyOvBcgutrYZBPHgdzM2BRR11SsXLJMiQu', 'Staf Kesiswaan', 'admin', NOW()),
(2, 'Budi Santoso', '22231001', 'budi.santoso@gmail.com', '$2y$10$ZI5XHyNbmJQkfbI3fc1CbO6wGRwB6ijcZ6HRR1fNSYdIDvXWRsi2K', 'XII RPL 1', 'siswa', NOW()),
(3, 'Siti Rahmawati', '22231002', 'siti.rahmawati@gmail.com', '$2y$10$ZI5XHyNbmJQkfbI3fc1CbO6wGRwB6ijcZ6HRR1fNSYdIDvXWRsi2K', 'XI TKJ 2', 'siswa', NOW()),
(4, 'Ahmad Fauzan', '22231003', 'ahmad.fauzan@gmail.com', '$2y$10$ZI5XHyNbmJQkfbI3fc1CbO6wGRwB6ijcZ6HRR1fNSYdIDvXWRsi2K', 'X DKV 1', 'siswa', NOW()),
(5, 'Dewi Lestari', '22231004', 'dewi.lestari@gmail.com', '$2y$10$ZI5XHyNbmJQkfbI3fc1CbO6wGRwB6ijcZ6HRR1fNSYdIDvXWRsi2K', 'XII RPL 2', 'siswa', NOW()),
(6, 'Rizky Pratama', '22231005', 'rizky.pratama@gmail.com', '$2y$10$ZI5XHyNbmJQkfbI3fc1CbO6wGRwB6ijcZ6HRR1fNSYdIDvXWRsi2K', 'XI TKJ 1', 'siswa', NOW());

-- 2. Insert Barang (Barang Ditemukan)
INSERT INTO barang (id, nama_barang, kategori, warna, deskripsi, lokasi_ditemukan, tanggal_ditemukan, foto, status, ditemukan_oleh, created_at) VALUES
(1, 'Tas Ransel Hitam Eiger', 'Tas', 'Hitam', 'Tas ransel merk Eiger warna hitam, ada gantungan kunci robot di resleting depan.', 'Lab Komputer RPL 2', '2026-09-08', 'sample_tas.jpg', 'tersedia', 2, NOW()),
(2, 'Tumbler Biru Tupperware', 'Botol Minum', 'Biru', 'Botol minum Tupperware warna biru ukuran 750ml dengan stiker logo SMK Informatika.', 'Kantin Sekolah Meja No. 5', '2026-09-07', 'sample_tumbler.jpg', 'dikembalikan', 4, NOW()),
(3, 'Jaket Hoodie Abu-abu Uniqlo', 'Pakaian', 'Abu-abu', 'Jaket hoodie warna abu-abu ukuran L, tertinggal di bangku pinggir lapangan basket.', 'Lapangan Basket', '2026-09-09', 'sample_jaket.jpg', 'diklaim', 5, NOW()),
(4, 'Kotak Pensil Marvel Merah', 'Alat Tulis', 'Merah', 'Kotak pensil bergambar Avengers, berisi pulpen pilot, tipe-x, dan penggaris besi 30cm.', 'Ruang Perpustakaan Lantai 1', '2026-09-10', 'sample_kotakpensil.jpg', 'tersedia', 3, NOW()),
(5, 'Kunci Motor Honda Beat', 'Kunci', 'Hitam', 'Kunci motor Honda Beat beserta gantungan kunci karakter anime One Piece (Luffy).', 'Parkiran Sepeda Motor Siswa', '2026-09-09', 'sample_kunci.jpg', 'menunggu_klaim', 6, NOW());

-- 3. Insert Laporan Hilang
INSERT INTO laporan_hilang (id, user_id, nama_barang, kategori, warna, deskripsi, lokasi_terakhir, tanggal_hilang, foto, status, created_at) VALUES
(1, 2, 'Dompet Coklat Kulit', 'Dompet', 'Coklat', 'Dompet lipat kulit warna coklat merk Baellerry, berisi kartu pelajar dan uang saku.', 'Kantin Sekolah', '2026-09-09', NULL, 'menunggu', NOW()),
(2, 4, 'Kalkulator Casio fx-991EX', 'Elektronik', 'Hitam', 'Kalkulator scientific Casio fx-991EX warna hitam dengan nama Fauzan di stiker belakang.', 'Lab Fisika / Kelas X DKV 1', '2026-09-08', NULL, 'diverifikasi', NOW()),
(3, 5, 'Jam Tangan Hitam Casio', 'Aksesoris', 'Hitam', 'Jam tangan Casio digital tali karet hitam, ada goresan tipis di layar kaca.', 'Toilet Gedung B Lantai 2', '2026-09-07', NULL, 'ditemukan', NOW());

-- 4. Insert Klaim
INSERT INTO klaim (id, barang_id, user_id, ciri_barang, bukti_kepemilikan, status, catatan_admin, created_at) VALUES
(1, 3, 4, 'Jaket Hoodie abu-abu Uniqlo ukuran L, ada sedikit sobekan jahitan di saku dalam sebelah kanan dan tali hoodie warna putih.', NULL, 'disetujui', 'Ciri-ciri dan identitas cocok. Silakan ambil di ruang TU Kesiswaan.', NOW()),
(2, 5, 6, 'Kunci kontak motor Honda Beat karbu, gantungan akrilik Monkey D. Luffy dan stiker plat nomor D.', NULL, 'menunggu', NULL, NOW());

-- 5. Insert Pengembalian
INSERT INTO pengembalian (id, barang_id, klaim_id, admin_id, tanggal_dikembalikan, catatan) VALUES
(1, 2, 1, 1, '2026-09-09 14:30:00', 'Barang Tumbler Biru telah diserahkan langsung kepada pemilik (Siti Rahmawati) di ruang kesiswaan setelah verifikasi identitas.');
