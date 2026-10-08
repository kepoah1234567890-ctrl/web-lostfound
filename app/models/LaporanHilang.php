<?php
/**
 * LaporanHilang Model
 * Lost & Found SMK Informatika Sumedang
 */

require_once ROOT_PATH . '/legacy-config/legacy_database.php';

class LaporanHilang {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(array $filters = [], ?int $limit = null, int $offset = 0): array {
        $sql = "
            SELECT l.*, u.nama AS pelapor_nama, u.kelas AS pelapor_kelas, u.email AS pelapor_email, u.no_telepon AS pelapor_telepon, u.nis AS pelapor_nis
            FROM laporan_hilang l
            JOIN users u ON l.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (l.nama_barang LIKE :search OR l.deskripsi LIKE :search OR l.lokasi_terakhir LIKE :search OR l.warna LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND l.kategori = :kategori";
            $params['kategori'] = $filters['kategori'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.tanggal_hilang) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.tanggal_hilang) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY l.id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM laporan_hilang l WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (l.nama_barang LIKE :search OR l.deskripsi LIKE :search OR l.lokasi_terakhir LIKE :search OR l.warna LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND l.kategori = :kategori";
            $params['kategori'] = $filters['kategori'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.tanggal_hilang) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.tanggal_hilang) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getById(int $id): ?array {
        $sql = "
            SELECT l.*, u.nama AS pelapor_nama, u.kelas AS pelapor_kelas, u.email AS pelapor_email, u.no_telepon AS pelapor_telepon, u.nis AS pelapor_nis
            FROM laporan_hilang l
            JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getPotentialMatchesForFound(array $barang): array
    {
        $nama = trim((string)($barang['nama_barang'] ?? ''));
        $kategori = trim((string)($barang['kategori'] ?? ''));
        $warna = trim((string)($barang['warna'] ?? ''));

        if ($nama === '' || $kategori === '') {
            return [];
        }

        $sql = "
            SELECT
                l.*,
                u.nama AS pelapor_nama,
                u.kelas AS pelapor_kelas,
                u.email AS pelapor_email,
                u.no_telepon AS pelapor_telepon,
                u.nis AS pelapor_nis
            FROM laporan_hilang l
            JOIN users u ON l.user_id = u.id
            WHERE l.status <> 'selesai'
              AND LOWER(l.kategori) = LOWER(:kategori)
              AND (
                    LOWER(l.nama_barang) LIKE LOWER(:nama1)
                    OR LOWER(l.deskripsi) LIKE LOWER(:nama2)
                    OR (:warna <> '' AND LOWER(l.warna) LIKE LOWER(:warna1))
                  )
            ORDER BY l.id DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'kategori' => $kategori,
            'nama1' => '%' . $nama . '%',
            'nama2' => '%' . $nama . '%',
            'warna' => $warna,
            'warna1' => '%' . $warna . '%',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserId(int $userId): array {
        return $this->getAll(['user_id' => $userId]);
    }

    public function create(array $data): int {
        $sql = "
            INSERT INTO laporan_hilang (
                user_id, nama_barang, kategori, warna, deskripsi,
                lokasi_terakhir, tanggal_hilang, foto, status, created_at
            ) VALUES (
                :user_id, :nama_barang, :kategori, :warna, :deskripsi,
                :lokasi_terakhir, :tanggal_hilang, :foto, :status, NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'         => $data['user_id'],
            'nama_barang'     => $data['nama_barang'],
            'kategori'        => $data['kategori'],
            'warna'           => $data['warna'] ?? null,
            'deskripsi'       => $data['deskripsi'] ?? null,
            'lokasi_terakhir' => $data['lokasi_terakhir'] ?? null,
            'tanggal_hilang'  => $data['tanggal_hilang'],
            'foto'            => $data['foto'] ?? null,
            'status'          => $data['status'] ?? 'menunggu',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [
            'nama_barang = :nama_barang',
            'kategori = :kategori',
            'warna = :warna',
            'deskripsi = :deskripsi',
            'lokasi_terakhir = :lokasi_terakhir',
            'tanggal_hilang = :tanggal_hilang',
        ];

        $params = [
            'id'              => $id,
            'nama_barang'     => $data['nama_barang'],
            'kategori'        => $data['kategori'],
            'warna'           => $data['warna'] ?? null,
            'deskripsi'       => $data['deskripsi'] ?? null,
            'lokasi_terakhir' => $data['lokasi_terakhir'] ?? null,
            'tanggal_hilang'  => $data['tanggal_hilang'],
        ];

        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }

        if (array_key_exists('foto', $data) && $data['foto'] !== null) {
            $fields[] = 'foto = :foto';
            $params['foto'] = $data['foto'];
        }

        $sql = "UPDATE laporan_hilang SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE laporan_hilang SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function delete(int $id): bool {
        $item = $this->getById($id);
        if ($item && !empty($item['foto'])) {
            $filename = (string)$item['foto'];
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

        $stmt = $this->db->prepare("DELETE FROM laporan_hilang WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT DISTINCT kategori FROM laporan_hilang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStats(): array {
        $sql = "
            SELECT
                COUNT(*) AS total_hilang,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS total_menunggu,
                SUM(CASE WHEN status = 'diverifikasi' THEN 1 ELSE 0 END) AS total_diverifikasi,
                SUM(CASE WHEN status = 'ditemukan' THEN 1 ELSE 0 END) AS total_ditemukan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS total_selesai
            FROM laporan_hilang
        ";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch();
        return [
            'total'        => (int)($res['total_hilang'] ?? 0),
            'menunggu'     => (int)($res['total_menunggu'] ?? 0),
            'diverifikasi' => (int)($res['total_diverifikasi'] ?? 0),
            'ditemukan'    => (int)($res['total_ditemukan'] ?? 0),
            'selesai'      => (int)($res['total_selesai'] ?? 0),
        ];
    }
}
