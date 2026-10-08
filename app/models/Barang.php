<?php
/**
 * Barang Model (Barang Ditemukan)
 * Lost & Found SMK Informatika Sumedang
 */

require_once ROOT_PATH . '/legacy-config/legacy_database.php';

class Barang
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * =========================================================
     * GET ALL BARANG
     * =========================================================
     */
    public function getAll(
        array $filters = [],
        ?int $limit = null,
        int $offset = 0
    ): array {

        $sql = "
            SELECT
                b.*,
                u.nama AS pelapor_nama,
                u.kelas AS pelapor_kelas
            FROM barang b
            LEFT JOIN users u
                ON b.ditemukan_oleh = u.id
            WHERE 1=1
        ";

        $params = [];

        /*
         * SEARCH
         * Gunakan placeholder berbeda agar aman untuk PDO.
         */
        if (!empty($filters['search'])) {

            $search = '%' . trim($filters['search']) . '%';

            $sql .= "
                AND (
                    b.nama_barang LIKE :search_nama
                    OR b.deskripsi LIKE :search_deskripsi
                    OR b.lokasi_ditemukan LIKE :search_lokasi
                    OR b.warna LIKE :search_warna
                )
            ";

            $params['search_nama'] = $search;
            $params['search_deskripsi'] = $search;
            $params['search_lokasi'] = $search;
            $params['search_warna'] = $search;
        }

        /*
         * KATEGORI
         */
        if (!empty($filters['kategori'])) {

            $sql .= "
                AND b.kategori = :kategori
            ";

            $params['kategori'] = $filters['kategori'];
        }

        /*
         * STATUS
         */
        if (!empty($filters['status'])) {

            $sql .= "
                AND b.status = :status
            ";

            $params['status'] = $filters['status'];
        }

        /*
         * DITEMUKAN OLEH
         */
        if (
            isset($filters['ditemukan_oleh'])
            && $filters['ditemukan_oleh'] !== ''
            && $filters['ditemukan_oleh'] !== null
        ) {

            $sql .= "
                AND b.ditemukan_oleh = :ditemukan_oleh
            ";

            $params['ditemukan_oleh'] =
                (int) $filters['ditemukan_oleh'];
        }

        /*
         * ORDER
         */
        $sql .= "
            ORDER BY b.id DESC
        ";

        /*
         * PAGINATION
         *
         * LIMIT dan OFFSET dimasukkan sebagai integer
         * supaya tidak perlu placeholder PDO.
         */
        if ($limit !== null) {

            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);

            $sql .= "
                LIMIT {$limit}
                OFFSET {$offset}
            ";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * =========================================================
     * COUNT BARANG
     * =========================================================
     */
    public function count(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM barang b
            WHERE 1=1
        ";

        $params = [];

        /*
         * SEARCH
         *
         * Jangan menggunakan :search berulang kali.
         */
        if (!empty($filters['search'])) {

            $search = '%' . trim($filters['search']) . '%';

            $sql .= "
                AND (
                    b.nama_barang LIKE :count_search_nama
                    OR b.deskripsi LIKE :count_search_deskripsi
                    OR b.lokasi_ditemukan LIKE :count_search_lokasi
                    OR b.warna LIKE :count_search_warna
                )
            ";

            $params['count_search_nama'] = $search;
            $params['count_search_deskripsi'] = $search;
            $params['count_search_lokasi'] = $search;
            $params['count_search_warna'] = $search;
        }

        /*
         * KATEGORI
         */
        if (!empty($filters['kategori'])) {

            $sql .= "
                AND b.kategori = :count_kategori
            ";

            $params['count_kategori'] =
                $filters['kategori'];
        }

        /*
         * STATUS
         */
        if (!empty($filters['status'])) {

            $sql .= "
                AND b.status = :count_status
            ";

            $params['count_status'] =
                $filters['status'];
        }

        /*
         * DITEMUKAN OLEH
         */
        if (
            isset($filters['ditemukan_oleh'])
            && $filters['ditemukan_oleh'] !== ''
            && $filters['ditemukan_oleh'] !== null
        ) {

            $sql .= "
                AND b.ditemukan_oleh = :count_ditemukan_oleh
            ";

            $params['count_ditemukan_oleh'] =
                (int) $filters['ditemukan_oleh'];
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }


    /**
     * =========================================================
     * GET BY ID
     * =========================================================
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT
                b.*,
                u.nama AS pelapor_nama,
                u.kelas AS pelapor_kelas,
                u.email AS pelapor_email,
                u.nis AS pelapor_nis
            FROM barang b
            LEFT JOIN users u
                ON b.ditemukan_oleh = u.id
            WHERE b.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    /**
     * =========================================================
     * CREATE
     * =========================================================
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO barang (
                nama_barang,
                kategori,
                warna,
                deskripsi,
                lokasi_ditemukan,
                tanggal_ditemukan,
                foto,
                status,
                ditemukan_oleh,
                created_at
            )
            VALUES (
                :nama_barang,
                :kategori,
                :warna,
                :deskripsi,
                :lokasi_ditemukan,
                :tanggal_ditemukan,
                :foto,
                :status,
                :ditemukan_oleh,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'nama_barang' =>
                $data['nama_barang'] ?? '',

            'kategori' =>
                $data['kategori'] ?? '',

            'warna' =>
                $data['warna'] ?? null,

            'deskripsi' =>
                $data['deskripsi'] ?? null,

            'lokasi_ditemukan' =>
                $data['lokasi_ditemukan'] ?? '',

            'tanggal_ditemukan' =>
                $data['tanggal_ditemukan'] ?? date('Y-m-d'),

            'foto' =>
                $data['foto'] ?? null,

            'status' =>
                $data['status'] ?? 'tersedia',

            'ditemukan_oleh' =>
                $data['ditemukan_oleh'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * =========================================================
     * UPDATE
     * =========================================================
     */
    public function update(
        int $id,
        array $data
    ): bool {

        $fields = [
            'nama_barang = :nama_barang',
            'kategori = :kategori',
            'warna = :warna',
            'deskripsi = :deskripsi',
            'lokasi_ditemukan = :lokasi_ditemukan',
            'tanggal_ditemukan = :tanggal_ditemukan',
        ];

        $params = [
            'id' =>
                $id,

            'nama_barang' =>
                $data['nama_barang'] ?? '',

            'kategori' =>
                $data['kategori'] ?? '',

            'warna' =>
                $data['warna'] ?? null,

            'deskripsi' =>
                $data['deskripsi'] ?? null,

            'lokasi_ditemukan' =>
                $data['lokasi_ditemukan'] ?? '',

            'tanggal_ditemukan' =>
                $data['tanggal_ditemukan'] ?? null,
        ];

        /*
         * STATUS
         */
        if (
            array_key_exists('status', $data)
            && $data['status'] !== null
        ) {

            $fields[] =
                'status = :status';

            $params['status'] =
                $data['status'];
        }

        /*
         * FOTO
         */
        if (
            array_key_exists('foto', $data)
            && $data['foto'] !== null
        ) {

            $fields[] =
                'foto = :foto';

            $params['foto'] =
                $data['foto'];
        }

        /*
         * DITEMUKAN OLEH
         */
        if (
            array_key_exists(
                'ditemukan_oleh',
                $data
            )
        ) {

            $fields[] =
                'ditemukan_oleh = :ditemukan_oleh';

            $params['ditemukan_oleh'] =
                $data['ditemukan_oleh'];
        }

        $sql =
            "UPDATE barang SET "
            . implode(', ', $fields)
            . " WHERE id = :id";

        $stmt =
            $this->db->prepare($sql);

        return $stmt->execute($params);
    }


    /**
     * =========================================================
     * UPDATE STATUS
     * =========================================================
     */
    public function updateStatus(
        int $id,
        string $status
    ): bool {

        $sql = "
            UPDATE barang
            SET status = :status
            WHERE id = :id
        ";

        $stmt =
            $this->db->prepare($sql);

        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }


    /**
     * =========================================================
     * DELETE
     * =========================================================
     */
    public function delete(int $id): bool
    {
        $item = $this->getById($id);

        if (!$item) {
            return false;
        }

        if (in_array($item['status'] ?? '', ['diklaim', 'dikembalikan'], true)) {
            throw new RuntimeException('Barang dengan klaim yang disetujui tidak dapat dihapus.');
        }

        $activeClaims = $this->db->prepare(
            "SELECT COUNT(*) FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui')"
        );
        $activeClaims->execute([$id]);

        if ((int)$activeClaims->fetchColumn() > 0) {
            throw new RuntimeException('Barang dengan klaim aktif tidak dapat dihapus.');
        }

        if (
            !empty($item['foto'])
            && defined('UPLOAD_PATH')
        ) {

            $filename = (string)$item['foto'];
            $uploadDirectory = realpath(UPLOAD_PATH);
            $fotoPath = realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);

            if (
                basename($filename) === $filename &&
                $uploadDirectory !== false &&
                $fotoPath !== false &&
                str_starts_with($fotoPath, $uploadDirectory . DIRECTORY_SEPARATOR) &&
                is_file($fotoPath)
            ) {
                @unlink($fotoPath);
            }
        }

        $stmt =
            $this->db->prepare(
                "DELETE FROM barang WHERE id = :id"
            );

        return $stmt->execute([
            'id' => $id
        ]);
    }


    /**
     * =========================================================
     * GET CATEGORIES
     * =========================================================
     */
    public function getCategories(): array
    {
        $sql = "
            SELECT DISTINCT kategori
            FROM barang
            WHERE kategori IS NOT NULL
            AND kategori != ''
            ORDER BY kategori ASC
        ";

        $stmt =
            $this->db->query($sql);

        return $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );
    }


    /**
     * =========================================================
     * GET STATS
     * =========================================================
     */
    public function getStats(): array
    {
        $sql = "
            SELECT

                COUNT(*) AS total_ditemukan,

                SUM(
                    CASE
                        WHEN status = 'tersedia'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_tersedia,

                SUM(
                    CASE
                        WHEN status = 'menunggu_klaim'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_menunggu_klaim,

                SUM(
                    CASE
                        WHEN status = 'diklaim'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_diklaim,

                SUM(
                    CASE
                        WHEN status = 'dikembalikan'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_dikembalikan

            FROM barang
        ";

        $stmt =
            $this->db->query($sql);

        $res =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' =>
                (int) (
                    $res['total_ditemukan']
                    ?? 0
                ),

            'tersedia' =>
                (int) (
                    $res['total_tersedia']
                    ?? 0
                ),

            'menunggu_klaim' =>
                (int) (
                    $res['total_menunggu_klaim']
                    ?? 0
                ),

            'diklaim' =>
                (int) (
                    $res['total_diklaim']
                    ?? 0
                ),

            'dikembalikan' =>
                (int) (
                    $res['total_dikembalikan']
                    ?? 0
                ),
        ];
    }
}