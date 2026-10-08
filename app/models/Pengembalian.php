<?php
/**
 * Pengembalian Model
 * Lost & Found SMK Informatika Sumedang
 */

require_once ROOT_PATH . '/legacy-config/legacy_database.php';

class Pengembalian {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $sql = "
            SELECT p.*,
                   b.nama_barang, b.kategori AS barang_kategori, b.lokasi_ditemukan, b.foto AS barang_foto,
                   k.ciri_barang, k.user_id AS klaim_user_id,
                   u_siswa.nama AS siswa_nama, u_siswa.kelas AS siswa_kelas, u_siswa.nis AS siswa_nis,
                   u_siswa.no_telepon AS siswa_telepon, u_siswa.email AS siswa_email,
                   u_admin.nama AS admin_nama
            FROM pengembalian p
            JOIN barang b ON p.barang_id = b.id
            JOIN klaim k ON p.klaim_id = k.id
            JOIN users u_siswa ON k.user_id = u_siswa.id
            JOIN users u_admin ON p.admin_id = u_admin.id
            ORDER BY p.id DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql = "
            SELECT p.*,
                   b.nama_barang, b.kategori AS barang_kategori, b.lokasi_ditemukan, b.foto AS barang_foto,
                   k.ciri_barang, k.user_id AS klaim_user_id,
                   u_siswa.nama AS siswa_nama, u_siswa.kelas AS siswa_kelas, u_siswa.nis AS siswa_nis,
                   u_siswa.no_telepon AS siswa_telepon, u_siswa.email AS siswa_email,
                   u_admin.nama AS admin_nama
            FROM pengembalian p
            JOIN barang b ON p.barang_id = b.id
            JOIN klaim k ON p.klaim_id = k.id
            JOIN users u_siswa ON k.user_id = u_siswa.id
            JOIN users u_admin ON p.admin_id = u_admin.id
            WHERE p.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function existsByKlaimId(int $klaimId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM pengembalian WHERE klaim_id = :klaim_id");
        $stmt->execute(['klaim_id' => $klaimId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int {
        $sql = "
            INSERT INTO pengembalian (
                barang_id, klaim_id, admin_id, tanggal_dikembalikan, catatan
            ) VALUES (
                :barang_id, :klaim_id, :admin_id, :tanggal_dikembalikan, :catatan
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'barang_id'            => $data['barang_id'],
            'klaim_id'             => $data['klaim_id'],
            'admin_id'             => $data['admin_id'],
            'tanggal_dikembalikan' => $data['tanggal_dikembalikan'] ?? date('Y-m-d H:i:s'),
            'catatan'              => $data['catatan'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function countAll(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM pengembalian");
        return (int)$stmt->fetchColumn();
    }
}
