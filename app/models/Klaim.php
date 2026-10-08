<?php
/**
 * Klaim Model
 * Lost & Found SMK Informatika Sumedang
 */

require_once ROOT_PATH . '/legacy-config/legacy_database.php';

class Klaim {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(array $filters = [], ?int $limit = null, int $offset = 0): array {
        $sql = "
            SELECT k.*, 
                   b.nama_barang, b.kategori AS barang_kategori, b.lokasi_ditemukan, b.foto AS barang_foto, b.status AS barang_status,
                   u.nama AS pengklaim_nama, u.kelas AS pengklaim_kelas, u.nis AS pengklaim_nis,
                   u.no_telepon AS pengklaim_telepon, u.email AS pengklaim_email
            FROM klaim k
            JOIN barang b ON k.barang_id = b.id
            JOIN users u ON k.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['barang_id'])) {
            $sql .= " AND k.barang_id = :barang_id";
            $params['barang_id'] = $filters['barang_id'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND k.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND k.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.nama_barang LIKE :search OR u.nama LIKE :search OR k.ciri_barang LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY k.id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int {
        $sql = "
            SELECT COUNT(*)
            FROM klaim k
            JOIN barang b ON k.barang_id = b.id
            JOIN users u ON k.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['barang_id'])) {
            $sql .= " AND k.barang_id = :barang_id";
            $params['barang_id'] = $filters['barang_id'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND k.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND k.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.nama_barang LIKE :search OR u.nama LIKE :search OR k.ciri_barang LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getById(int $id): ?array {
        $sql = "
            SELECT k.*, 
                   b.nama_barang, b.kategori AS barang_kategori, b.lokasi_ditemukan, b.tanggal_ditemukan, b.foto AS barang_foto, b.status AS barang_status,
                   u.nama AS pengklaim_nama, u.kelas AS pengklaim_kelas, u.nis AS pengklaim_nis,
                   u.no_telepon AS pengklaim_telepon, u.email AS pengklaim_email
            FROM klaim k
            JOIN barang b ON k.barang_id = b.id
            JOIN users u ON k.user_id = u.id
            WHERE k.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByUserId(int $userId): array {
        return $this->getAll(['user_id' => $userId]);
    }

    public function getByBarangId(int $barangId): array {
        return $this->getAll(['barang_id' => $barangId]);
    }

    public function hasUserClaimed(int $barangId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM klaim WHERE barang_id = :barang_id AND user_id = :user_id");
        $stmt->execute(['barang_id' => $barangId, 'user_id' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int {
        $sql = "
            INSERT INTO klaim (
                barang_id, user_id, ciri_barang, bukti_kepemilikan,
                status, catatan_admin, created_at
            ) VALUES (
                :barang_id, :user_id, :ciri_barang, :bukti_kepemilikan,
                :status, :catatan_admin, NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'barang_id'         => $data['barang_id'],
            'user_id'           => $data['user_id'],
            'ciri_barang'       => $data['ciri_barang'],
            'bukti_kepemilikan' => $data['bukti_kepemilikan'] ?? null,
            'status'            => $data['status'] ?? 'menunggu',
            'catatan_admin'     => $data['catatan_admin'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?string $catatan_admin = null): bool {
        $sql = "UPDATE klaim SET status = :status";
        $params = ['status' => $status, 'id' => $id];

        if ($catatan_admin !== null) {
            $sql .= ", catatan_admin = :catatan_admin";
            $params['catatan_admin'] = $catatan_admin;
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $item = $this->getById($id);
        if ($item && !empty($item['bukti_kepemilikan'])) {
            $filename = (string)$item['bukti_kepemilikan'];
            $uploadDirectory = realpath(UPLOAD_PATH);
            $filePath = realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);

            if (
                basename($filename) === $filename &&
                $uploadDirectory !== false &&
                $filePath !== false &&
                str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR) &&
                is_file($filePath)
            ) {
                @unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM klaim WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getStats(): array {
        $sql = "
            SELECT
                COUNT(*) AS total_klaim,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS total_menunggu,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) AS total_disetujui,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS total_ditolak
            FROM klaim
        ";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch();
        return [
            'total'     => (int)($res['total_klaim'] ?? 0),
            'menunggu'  => (int)($res['total_menunggu'] ?? 0),
            'disetujui' => (int)($res['total_disetujui'] ?? 0),
            'ditolak'   => (int)($res['total_ditolak'] ?? 0),
        ];
    }
}
