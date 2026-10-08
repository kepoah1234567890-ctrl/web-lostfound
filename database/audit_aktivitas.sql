-- ============================================================
-- AUDIT & MATCHING LOST & FOUND
-- Jalankan pada database aplikasi yang sedang aktif.
-- ============================================================

CREATE TABLE IF NOT EXISTS matching_barang (
    id INT NOT NULL AUTO_INCREMENT,
    laporan_hilang_id INT NOT NULL,
    barang_ditemukan_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,
    label VARCHAR(100) DEFAULT NULL,
    reasons TEXT DEFAULT NULL,
    status ENUM('ditemukan','dihubungi','diklaim','selesai','ditolak') NOT NULL DEFAULT 'ditemukan',
    dibuat_oleh INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_matching (laporan_hilang_id, barang_ditemukan_id),
    KEY idx_matching_laporan_hilang (laporan_hilang_id),
    KEY idx_matching_barang (barang_ditemukan_id),
    KEY idx_matching_dibuat_oleh (dibuat_oleh),
    CONSTRAINT fk_matching_laporan
        FOREIGN KEY (laporan_hilang_id) REFERENCES laporan_hilang(id) ON DELETE CASCADE,
    CONSTRAINT fk_matching_barang
        FOREIGN KEY (barang_ditemukan_id) REFERENCES barang(id) ON DELETE CASCADE,
    CONSTRAINT fk_matching_user
        FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS aktivitas_user (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    target_user_id INT DEFAULT NULL,
    laporan_hilang_id INT DEFAULT NULL,
    barang_id INT DEFAULT NULL,
    klaim_id INT DEFAULT NULL,
    matching_id INT DEFAULT NULL,
    aktivitas VARCHAR(100) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_aktivitas_user_id (user_id),
    KEY idx_aktivitas_target_user_id (target_user_id),
    KEY idx_aktivitas_laporan_hilang_id (laporan_hilang_id),
    KEY idx_aktivitas_barang_id (barang_id),
    KEY idx_aktivitas_klaim_id (klaim_id),
    KEY idx_aktivitas_matching_id (matching_id),
    KEY idx_aktivitas_created_at (created_at),
    CONSTRAINT fk_aktivitas_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_aktivitas_target_user
        FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_aktivitas_laporan
        FOREIGN KEY (laporan_hilang_id) REFERENCES laporan_hilang(id) ON DELETE SET NULL,
    CONSTRAINT fk_aktivitas_barang
        FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE SET NULL,
    CONSTRAINT fk_aktivitas_klaim
        FOREIGN KEY (klaim_id) REFERENCES klaim(id) ON DELETE SET NULL,
    CONSTRAINT fk_aktivitas_matching
        FOREIGN KEY (matching_id) REFERENCES matching_barang(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
