<?php
/**
 * Aktivitas Model
 * Lost & Found SMK Informatika Sumedang
 *
 * Pusat pencatatan audit aktivitas sistem.
 */
require_once ROOT_PATH . '/legacy-config/legacy_database.php';

class Aktivitas
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Catat aktivitas tanpa memutus proses utama.
     * Semua informasi tambahan disimpan di metadata JSON.
     */
    public function record(array $data): int
    {
        $metadata = [];

        if (isset($data['metadata'])) {
            if (is_string($data['metadata'])) {
                $decoded = json_decode($data['metadata'], true);
                $metadata = is_array($decoded) ? $decoded : [];
            } elseif (is_array($data['metadata'])) {
                $metadata = $data['metadata'];
            }
        }

        if (isImpersonating()) {
            $admin = impersonationAdmin();
            $impersonatedUser = legacyAuth();

            if ($admin !== null && $impersonatedUser !== null) {
                $data['user_id'] = (int)$admin['id'];
                $metadata['actor_role'] = 'admin';
                $metadata['impersonated_user_id'] = (int)$impersonatedUser['id'];
                $metadata['impersonated_user_name'] = (string)($impersonatedUser['nama'] ?? '');
            }
        }

        $metadata['actor_role'] = $metadata['actor_role']
            ?? (legacyAuth()['role'] ?? 'siswa');

        if (!isset($metadata['channel'])) {
            $userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $metadata['channel'] = (
                str_contains($userAgent, 'android') ||
                str_contains($userAgent, 'iphone') ||
                str_contains($userAgent, 'ipad') ||
                str_contains($userAgent, 'mobile')
            ) ? 'mobile' : 'web';
        }

        $metadata['recorded_at'] = date('Y-m-d H:i:s');

        if (!isset($metadata['ip_address'])) {
            $metadata['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        if (!isset($metadata['user_agent'])) {
            $metadata['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }

        if (!isset($metadata['request_method'])) {
            $metadata['request_method'] = $_SERVER['REQUEST_METHOD'] ?? null;
        }

        if (!isset($metadata['request_uri'])) {
            $metadata['request_uri'] = $_SERVER['REQUEST_URI'] ?? null;
        }

        $stmt = $this->db->prepare("
            INSERT INTO aktivitas_user (
                user_id,
                target_user_id,
                laporan_hilang_id,
                barang_id,
                klaim_id,
                matching_id,
                aktivitas,
                deskripsi,
                metadata
            ) VALUES (
                :user_id,
                :target_user_id,
                :laporan_hilang_id,
                :barang_id,
                :klaim_id,
                :matching_id,
                :aktivitas,
                :deskripsi,
                :metadata
            )
        ");

        $stmt->execute([
            'user_id' => (int)($data['user_id'] ?? (legacyAuth()['id'] ?? 0)),
            'target_user_id' => !empty($data['target_user_id']) ? (int)$data['target_user_id'] : null,
            'laporan_hilang_id' => !empty($data['laporan_hilang_id']) ? (int)$data['laporan_hilang_id'] : null,
            'barang_id' => !empty($data['barang_id']) ? (int)$data['barang_id'] : null,
            'klaim_id' => !empty($data['klaim_id']) ? (int)$data['klaim_id'] : null,
            'matching_id' => !empty($data['matching_id']) ? (int)$data['matching_id'] : null,
            'aktivitas' => (string)($data['aktivitas'] ?? 'aktivitas'),
            'deskripsi' => $data['deskripsi'] ?? null,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Alias aman untuk pencatatan audit.
     * Jika logger gagal, proses utama aplikasi tetap berjalan.
     */
    public function recordSafe(array $data): ?int
    {
        try {
            return $this->record($data);
        } catch (Throwable $e) {
            error_log('[AKTIVITAS] ' . $e->getMessage());
            return null;
        }
    }

    public function getAccountSummaries(array $filters = []): array
    {
        $sql = "
            SELECT
                u.id AS user_id,
                u.nama AS user_nama,
                u.email AS user_email,
                u.kelas AS user_kelas,
                u.nis AS user_nis,
                u.role AS user_role,
                COUNT(a.id) AS activity_count,
                SUM(CASE
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = 'web'
                    THEN 1 ELSE 0
                END) AS web_count,
                SUM(CASE
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = 'mobile'
                    THEN 1 ELSE 0
                END) AS mobile_count,
                MAX(a.id) AS latest_activity_id
            FROM aktivitas_user a
            INNER JOIN users u ON a.user_id = u.id
            LEFT JOIN users tu ON a.target_user_id = tu.id
            LEFT JOIN barang b ON a.barang_id = b.id
            LEFT JOIN laporan_hilang lh ON a.laporan_hilang_id = lh.id
            WHERE 1=1
                            AND a.aktivitas NOT IN ('akses_web', 'web_request')
                            AND u.role = 'siswa'
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= "
                AND (
                    u.nama LIKE :search_user
                    OR u.email LIKE :search_email
                    OR u.kelas LIKE :search_class
                    OR u.nis LIKE :search_nis
                    OR tu.nama LIKE :search_target
                    OR a.aktivitas LIKE :search_activity
                    OR a.deskripsi LIKE :search_desc
                    OR b.nama_barang LIKE :search_barang
                    OR lh.nama_barang LIKE :search_laporan
                )
            ";
            $term = '%' . $filters['search'] . '%';
            foreach (['user', 'email', 'class', 'nis', 'target', 'activity', 'desc', 'barang', 'laporan'] as $key) {
                $params['search_' . $key] = $term;
            }
        }

        if (!empty($filters['aktivitas'])) {
            $sql .= ' AND a.aktivitas = :aktivitas';
            $params['aktivitas'] = $filters['aktivitas'];
        }

        if (!empty($filters['channel'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = :channel";
            $params['channel'] = $filters['channel'];
        }

        if (!empty($filters['actor_role'])) {
            $sql .= ' AND u.role = :actor_role';
            $params['actor_role'] = $filters['actor_role'];
        }

        $sql .= '
            GROUP BY u.id, u.nama, u.email, u.kelas, u.nis, u.role
            ORDER BY latest_activity_id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "
            SELECT
                a.*,
                u.nama AS user_nama,
                u.email AS user_email,
                u.no_telepon AS user_telepon,
                u.kelas AS user_kelas,
                u.nis AS user_nis,
                u.role AS user_role,
                tu.nama AS target_user_nama,
                tu.email AS target_user_email,
                tu.no_telepon AS target_user_telepon,
                tu.kelas AS target_user_kelas,
                tu.nis AS target_user_nis,
                tu.role AS target_user_role,
                b.nama_barang AS barang_nama,
                b.kategori AS barang_kategori,
                b.status AS barang_status,
                lh.nama_barang AS laporan_nama_barang,
                lh.status AS laporan_status,
                k.status AS klaim_status
            FROM aktivitas_user a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN users tu ON a.target_user_id = tu.id
            LEFT JOIN barang b ON a.barang_id = b.id
            LEFT JOIN laporan_hilang lh ON a.laporan_hilang_id = lh.id
            LEFT JOIN klaim k ON a.klaim_id = k.id
            WHERE 1=1
                            AND a.aktivitas NOT IN ('akses_web', 'web_request')
                            AND u.role = 'siswa'
        ";

        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= "
                AND (
                    u.nama LIKE :search
                    OR tu.nama LIKE :search_target
                    OR a.aktivitas LIKE :search_activity
                    OR a.deskripsi LIKE :search_desc
                    OR b.nama_barang LIKE :search_barang
                    OR lh.nama_barang LIKE :search_laporan
                )
            ";
            $term = '%' . $filters['search'] . '%';
            $params['search'] = $term;
            $params['search_target'] = $term;
            $params['search_activity'] = $term;
            $params['search_desc'] = $term;
            $params['search_barang'] = $term;
            $params['search_laporan'] = $term;
        }

        if (!empty($filters['aktivitas'])) {
            $sql .= " AND a.aktivitas = :aktivitas";
            $params['aktivitas'] = $filters['aktivitas'];
        }

        if (!empty($filters['channel'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = :channel";
            $params['channel'] = $filters['channel'];
        }

        if (!empty($filters['actor_role'])) {
            $sql .= " AND u.role = :actor_role";
            $params['actor_role'] = $filters['actor_role'];
        }

        $sql .= " ORDER BY a.id DESC LIMIT " . max(1, min(500, $limit)) . " OFFSET " . max(0, $offset);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM aktivitas_user a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN users tu ON a.target_user_id = tu.id
            LEFT JOIN barang b ON a.barang_id = b.id
            LEFT JOIN laporan_hilang lh ON a.laporan_hilang_id = lh.id
            WHERE 1=1
                            AND a.aktivitas NOT IN ('akses_web', 'web_request')
                            AND u.role = 'siswa'
        ";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= "
                AND (
                    u.nama LIKE :search
                    OR tu.nama LIKE :search_target
                    OR a.aktivitas LIKE :search_activity
                    OR a.deskripsi LIKE :search_desc
                    OR b.nama_barang LIKE :search_barang
                    OR lh.nama_barang LIKE :search_laporan
                )
            ";
            $term = '%' . $filters['search'] . '%';
            $params['search'] = $term;
            $params['search_target'] = $term;
            $params['search_activity'] = $term;
            $params['search_desc'] = $term;
            $params['search_barang'] = $term;
            $params['search_laporan'] = $term;
        }

        if (!empty($filters['aktivitas'])) {
            $sql .= " AND a.aktivitas = :aktivitas";
            $params['aktivitas'] = $filters['aktivitas'];
        }

        if (!empty($filters['channel'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = :channel";
            $params['channel'] = $filters['channel'];
        }

        if (!empty($filters['actor_role'])) {
            $sql .= " AND u.role = :actor_role";
            $params['actor_role'] = $filters['actor_role'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getActivityTypes(): array
    {
        $stmt = $this->db->query("
                        SELECT DISTINCT a.aktivitas
                        FROM aktivitas_user a
                        INNER JOIN users u ON u.id = a.user_id
                        WHERE a.aktivitas <> ''
                            AND a.aktivitas NOT IN ('akses_web', 'web_request')
                            AND u.role = 'siswa'
                        ORDER BY a.aktivitas ASC
        ");

        return array_values(
            array_filter(
                array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'aktivitas')
            )
        );
    }
}
