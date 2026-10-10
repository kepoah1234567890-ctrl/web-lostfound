-- ==========================================================
-- Database Schema: Lost & Found SMK Informatika Sumedang
-- ==========================================================

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nis VARCHAR(30) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    no_telepon VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    kelas VARCHAR(50),
    role ENUM('siswa', 'admin') DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Barang (Barang Ditemukan)
CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    warna VARCHAR(50),
    deskripsi TEXT,
    lokasi_ditemukan VARCHAR(100) NOT NULL,
    tanggal_ditemukan DATE NOT NULL,
    foto VARCHAR(255),
    status ENUM(
        'tersedia',
        'menunggu_klaim',
        'diklaim',
        'dikembalikan'
    ) DEFAULT 'tersedia',
    ditemukan_oleh INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (ditemukan_oleh) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Laporan Hilang
CREATE TABLE IF NOT EXISTS laporan_hilang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    warna VARCHAR(50),
    deskripsi TEXT,
    lokasi_terakhir VARCHAR(100),
    tanggal_hilang DATE NOT NULL,
    foto VARCHAR(255),
    status ENUM(
        'menunggu',
        'diverifikasi',
        'ditemukan',
        'selesai'
    ) DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Klaim
CREATE TABLE IF NOT EXISTS klaim (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_id INT NOT NULL,
    user_id INT NOT NULL,
    ciri_barang TEXT NOT NULL,
    bukti_kepemilikan VARCHAR(255),
    status ENUM(
        'menunggu',
        'disetujui',
        'ditolak'
    ) DEFAULT 'menunggu',
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (barang_id) REFERENCES barang(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_id INT NOT NULL,
    klaim_id INT NOT NULL,
    admin_id INT NOT NULL,
    tanggal_dikembalikan DATETIME DEFAULT CURRENT_TIMESTAMP,
    catatan TEXT,

    FOREIGN KEY (barang_id) REFERENCES barang(id)
        ON DELETE CASCADE,

    FOREIGN KEY (klaim_id) REFERENCES klaim(id)
        ON DELETE CASCADE,

    FOREIGN KEY (admin_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gambar disimpan di MySQL agar tetap tersedia setelah deploy/instance Railway diganti.
CREATE TABLE IF NOT EXISTS uploaded_files (
    filename VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin PRIMARY KEY,
    mime_type VARCHAR(100) NOT NULL,
    content MEDIUMBLOB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
