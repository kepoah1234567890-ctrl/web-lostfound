<?php

/**
 * API AKTIVITAS USER
 * Lost & Found SMK Informatika Sumedang
 *
 * Fungsi:
 * - Mencatat aktivitas User <-> User
 * - Mengambil aktivitas milik user
 * - Admin mengambil seluruh aktivitas
 * - Filter berdasarkan user, target user, barang, laporan, klaim, matching
 *
 * Endpoint:
 *
 * POST
 * /api/aktivitas.php
 *
 * GET USER
 * /api/aktivitas.php?user_id=5
 *
 * GET ADMIN
 * /api/aktivitas.php?admin=1
 *
 * GET DETAIL
 * /api/aktivitas.php?id=1
 */

define('IS_API', true);
require_once dirname(__DIR__) . '/legacy-config/config.php';
require_once dirname(__DIR__) . '/legacy-config/legacy_database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$currentUser = apiRequireLogin();
$isAdmin = isAdmin();
$pdo = Database::getConnection();

/*
|--------------------------------------------------------------------------
| HELPER RESPONSE
|--------------------------------------------------------------------------
*/

function responseJson(
    bool $success,
    string $message,
    $data = null,
    int $statusCode = 200
): void {
    http_response_code($statusCode);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| INPUT JSON
|--------------------------------------------------------------------------
*/

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');

    if (!$raw) {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        return [];
    }

    return $data;
}

/*
|--------------------------------------------------------------------------
| VALIDASI INTEGER
|--------------------------------------------------------------------------
*/

function nullableInt($value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    if (!is_numeric($value)) {
        return null;
    }

    return (int) $value;
}

/*
|--------------------------------------------------------------------------
| CEK USER
|--------------------------------------------------------------------------
*/

function userExists(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$userId]);

    return (bool) $stmt->fetchColumn();
}

/*
|--------------------------------------------------------------------------
| CEK ADMIN
|--------------------------------------------------------------------------
*/

function adminExists(PDO $pdo, int $adminId): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE id = ?
           AND role = 'admin'
         LIMIT 1"
    );

    $stmt->execute([$adminId]);

    return (bool) $stmt->fetchColumn();
}

/*
|--------------------------------------------------------------------------
| CEK REFERENSI
|--------------------------------------------------------------------------
*/

function laporanExists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM laporan_hilang
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);

    return (bool) $stmt->fetchColumn();
}

function barangExists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM barang
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);

    return (bool) $stmt->fetchColumn();
}

function klaimExists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM klaim
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);

    return (bool) $stmt->fetchColumn();
}

function matchingExists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "SELECT id
         FROM matching_barang
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);

    return (bool) $stmt->fetchColumn();
}

/*
|--------------------------------------------------------------------------
| POST
| CATAT AKTIVITAS USER
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = getJsonInput();

    $userId = (int) $currentUser['id'];
    $impersonationAdmin = isImpersonating() ? impersonationAdmin() : null;
    $auditUserId = $impersonationAdmin !== null
        ? (int)$impersonationAdmin['id']
        : $userId;

    $targetUserId = nullableInt(
        $input['target_user_id'] ?? null
    );

    $laporanHilangId = nullableInt(
        $input['laporan_hilang_id'] ?? null
    );

    $barangId = nullableInt(
        $input['barang_id'] ?? null
    );

    $klaimId = nullableInt(
        $input['klaim_id'] ?? null
    );

    $matchingId = nullableInt(
        $input['matching_id'] ?? null
    );

    $aktivitas = trim(
        (string) ($input['aktivitas'] ?? '')
    );

    $deskripsi = trim(
        (string) ($input['deskripsi'] ?? '')
    );

    $metadata = $input['metadata'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | VALIDASI AKTIVITAS
    |--------------------------------------------------------------------------
    */

    if ($aktivitas === '') {
        responseJson(
            false,
            'aktivitas wajib diisi.',
            null,
            422
        );
    }

    if (mb_strlen($aktivitas) > 100) {
        responseJson(
            false,
            'aktivitas maksimal 100 karakter.',
            null,
            422
        );
    }

    $activityTypes = [
        'admin_memverifikasi_klaim',
        'admin_mengedit_barang',
        'admin_mengedit_user',
        'admin_menghapus_barang',
        'admin_menghapus_klaim',
        'admin_menghapus_laporan',
        'admin_menghubungi_pemilik',
        'admin_mengubah_status_barang',
        'admin_mengubah_status_laporan',
        'edit_profil',
        'login',
        'melihat_daftar_barang',
        'melihat_detail_barang',
        'membuat_laporan_hilang',
        'membuka_aplikasi',
        'membuka_beranda',
        'membuka_form_klaim',
        'melaporkan_barang_ditemukan',
        'mencari_barang',
        'mengajukan_klaim',
        'membatalkan_klaim',
        'menghapus_laporan_hilang',
        'menghubungi_user',
        'register',
        'serah_terima_barang',
        'logout',
    ];
    $adminActivityTypes = [
        'admin_memverifikasi_klaim',
        'admin_mengedit_barang',
        'admin_mengedit_user',
        'admin_menghapus_barang',
        'admin_menghapus_klaim',
        'admin_menghapus_laporan',
        'admin_menghubungi_pemilik',
        'admin_mengubah_status_barang',
        'admin_mengubah_status_laporan',
        'serah_terima_barang',
    ];

    if (!in_array($aktivitas, $activityTypes, true)) {
        responseJson(
            false,
            'Jenis aktivitas tidak dikenal.',
            null,
            422
        );
    }

    if (!$isAdmin && in_array($aktivitas, $adminActivityTypes, true)) {
        responseJson(
            false,
            'Aktivitas ini hanya dapat dicatat oleh Admin.',
            null,
            403
        );
    }

    if (
        !$isAdmin &&
        $aktivitas === 'menghubungi_user' &&
        ($targetUserId === null || $barangId === null)
    ) {
        responseJson(
            false,
            'Aktivitas kontak harus menyertakan pemilik dan barang.',
            null,
            422
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI TARGET USER
    |--------------------------------------------------------------------------
    */

    if ($targetUserId !== null) {

        if ($targetUserId <= 0) {
            responseJson(
                false,
                'target_user_id tidak valid.',
                null,
                422
            );
        }

        if (!userExists($pdo, $targetUserId)) {
            responseJson(
                false,
                'Target user tidak ditemukan.',
                null,
                404
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI LAPORAN
    |--------------------------------------------------------------------------
    */

    if ($laporanHilangId !== null) {

        if ($laporanHilangId <= 0) {
            responseJson(
                false,
                'laporan_hilang_id tidak valid.',
                null,
                422
            );
        }

        if (!laporanExists($pdo, $laporanHilangId)) {
            responseJson(
                false,
                'Laporan hilang tidak ditemukan.',
                null,
                404
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI BARANG
    |--------------------------------------------------------------------------
    */

    if ($barangId !== null) {

        if ($barangId <= 0) {
            responseJson(
                false,
                'barang_id tidak valid.',
                null,
                422
            );
        }

        if (!barangExists($pdo, $barangId)) {
            responseJson(
                false,
                'Barang tidak ditemukan.',
                null,
                404
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI KLAIM
    |--------------------------------------------------------------------------
    */

    if ($klaimId !== null) {

        if ($klaimId <= 0) {
            responseJson(
                false,
                'klaim_id tidak valid.',
                null,
                422
            );
        }

        if (!klaimExists($pdo, $klaimId)) {
            responseJson(
                false,
                'Klaim tidak ditemukan.',
                null,
                404
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI MATCHING
    |--------------------------------------------------------------------------
    */

    if ($matchingId !== null) {

        if ($matchingId <= 0) {
            responseJson(
                false,
                'matching_id tidak valid.',
                null,
                422
            );
        }

        if (!matchingExists($pdo, $matchingId)) {
            responseJson(
                false,
                'Data matching tidak ditemukan.',
                null,
                404
            );
        }
    }

    if (!$isAdmin && $targetUserId !== null) {
        if ($aktivitas !== 'menghubungi_user' || $barangId === null) {
            responseJson(
                false,
                'Target aktivitas tidak sesuai dengan tindakan yang diizinkan.',
                null,
                403
            );
        }

        $ownerStmt = $pdo->prepare(
            'SELECT ditemukan_oleh FROM barang WHERE id = ? LIMIT 1'
        );
        $ownerStmt->execute([$barangId]);

        if ((int)$ownerStmt->fetchColumn() !== $targetUserId) {
            responseJson(
                false,
                'Target bukan pemilik barang yang dimaksud.',
                null,
                403
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | METADATA JSON
    |--------------------------------------------------------------------------
    */

    if ($metadata === null) {
        $metadata = [];
    } elseif (is_string($metadata)) {
        $decoded = json_decode($metadata, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            responseJson(
                false,
                'metadata harus berupa JSON object yang valid.',
                null,
                422
            );
        }

        $metadata = $decoded;
    }

    if (!is_array($metadata)) {
        responseJson(
            false,
            'Format metadata tidak valid.',
            null,
            422
        );
    }

    $metadata['actor_role'] = $impersonationAdmin !== null
        ? 'admin'
        : ($currentUser['role'] ?? 'siswa');

    if ($impersonationAdmin !== null) {
        $metadata['impersonated_user_id'] = $userId;
        $metadata['impersonated_user_name'] = (string)($currentUser['nama'] ?? '');
    }

    if (!isset($metadata['channel'])) {
        $userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $metadata['channel'] = (
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'iphone') ||
            str_contains($userAgent, 'ipad') ||
            str_contains($userAgent, 'mobile')
        ) ? 'mobile' : 'web';
    }

    $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE);

    if ($metadataJson === false) {
        responseJson(
            false,
            'Metadata tidak dapat diproses.',
            null,
            422
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    try {

        $sql = "
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
            )
            VALUES (
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
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(
            [
                ':user_id'            => $auditUserId,
                ':target_user_id'     => $targetUserId,
                ':laporan_hilang_id'  => $laporanHilangId,
                ':barang_id'          => $barangId,
                ':klaim_id'           => $klaimId,
                ':matching_id'        => $matchingId,
                ':aktivitas'          => $aktivitas,
                ':deskripsi'          => $deskripsi !== ''
                    ? $deskripsi
                    : null,
                ':metadata'           => $metadataJson,
            ]
        );

        $id = (int) $pdo->lastInsertId();

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA YANG BARU DIBUAT
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare(
            "SELECT
                a.*,

                u.nama AS user_nama,
                u.email AS user_email,
                u.no_telepon AS user_telepon,
                u.kelas AS user_kelas,
                u.nis AS user_nis,

                tu.nama AS target_user_nama,
                tu.email AS target_user_email,
                tu.no_telepon AS target_user_telepon,
                tu.kelas AS target_user_kelas,
                tu.nis AS target_user_nis

             FROM aktivitas_user a

             INNER JOIN users u
                ON u.id = a.user_id

             LEFT JOIN users tu
                ON tu.id = a.target_user_id

                         WHERE a.id = ?
                             AND u.role = 'siswa'
                             AND a.aktivitas NOT IN ('akses_web', 'web_request')

             LIMIT 1"
        );

        $stmt->execute([$id]);

        $data = $stmt->fetch();

        responseJson(
            true,
            'Aktivitas berhasil dicatat.',
            $data,
            201
        );

    } catch (PDOException $e) {

        responseJson(
            false,
            'Gagal mencatat aktivitas.',
            [
                'error' => $e->getMessage(),
            ],
            500
        );
    }
}

/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = nullableInt(
        $_GET['id'] ?? null
    );

    $userId = nullableInt(
        $_GET['user_id'] ?? null
    );

    $targetUserId = nullableInt(
        $_GET['target_user_id'] ?? null
    );

    $laporanHilangId = nullableInt(
        $_GET['laporan_hilang_id'] ?? null
    );

    $barangId = nullableInt(
        $_GET['barang_id'] ?? null
    );

    $klaimId = nullableInt(
        $_GET['klaim_id'] ?? null
    );

    $matchingId = nullableInt(
        $_GET['matching_id'] ?? null
    );

    $aktivitas = trim(
        (string) ($_GET['aktivitas'] ?? '')
    );

    $channel = trim(
        (string) ($_GET['channel'] ?? '')
    );

    $search = trim(
        (string) ($_GET['search'] ?? '')
    );

    $limit = nullableInt(
        $_GET['limit'] ?? 50
    );

    $offset = nullableInt(
        $_GET['offset'] ?? 0
    );

    if ($limit === null || $limit <= 0) {
        $limit = 50;
    }

    if ($limit > 200) {
        $limit = 200;
    }

    if ($offset === null || $offset < 0) {
        $offset = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL BY ID
    |--------------------------------------------------------------------------
    */

    if ($id !== null) {

        if ($id <= 0) {
            responseJson(
                false,
                'ID aktivitas tidak valid.',
                null,
                422
            );
        }

        $stmt = $pdo->prepare(
            "SELECT
                a.*,

                u.nama AS user_nama,
                u.email AS user_email,
                u.no_telepon AS user_telepon,
                u.kelas AS user_kelas,
                u.nis AS user_nis,

                tu.nama AS target_user_nama,
                tu.email AS target_user_email,
                tu.no_telepon AS target_user_telepon,
                tu.kelas AS target_user_kelas,
                tu.nis AS target_user_nis

             FROM aktivitas_user a

             INNER JOIN users u
                ON u.id = a.user_id

             LEFT JOIN users tu
                ON tu.id = a.target_user_id

             WHERE a.id = ?

             LIMIT 1"
        );

        $stmt->execute([$id]);

        $data = $stmt->fetch();

        if (!$data) {
            responseJson(
                false,
                'Aktivitas tidak ditemukan.',
                null,
                404
            );
        }

        if (!$isAdmin && (int)$data['user_id'] !== (int)$currentUser['id']) {
            responseJson(
                false,
                'Akses aktivitas ditolak.',
                null,
                403
            );
        }

        responseJson(
            true,
            'Detail aktivitas berhasil diambil.',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY LIST
    |--------------------------------------------------------------------------
    */

    $where = [];
    $params = [];
    $where[] = "a.aktivitas NOT IN ('akses_web', 'web_request')";
    $where[] = "EXISTS (SELECT 1 FROM users activity_actor WHERE activity_actor.id = a.user_id AND activity_actor.role = 'siswa')";

    if (!$isAdmin) {
        $where[] = 'a.user_id = :session_actor_id';
        $params[':session_actor_id'] = (int)$currentUser['id'];
    } elseif ($userId === null) {
        responseJson(false, 'Admin harus memilih akun siswa untuk melihat riwayat.', null, 422);
    }

    if ($userId !== null) {

        if ($userId <= 0) {
            responseJson(
                false,
                'user_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.user_id = :user_id';
        $params[':user_id'] = $userId;
    }

    if ($targetUserId !== null) {

        if ($targetUserId <= 0) {
            responseJson(
                false,
                'target_user_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.target_user_id = :target_user_id';
        $params[':target_user_id'] = $targetUserId;
    }

    if ($laporanHilangId !== null) {

        if ($laporanHilangId <= 0) {
            responseJson(
                false,
                'laporan_hilang_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.laporan_hilang_id = :laporan_hilang_id';
        $params[':laporan_hilang_id'] = $laporanHilangId;
    }

    if ($barangId !== null) {

        if ($barangId <= 0) {
            responseJson(
                false,
                'barang_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.barang_id = :barang_id';
        $params[':barang_id'] = $barangId;
    }

    if ($klaimId !== null) {

        if ($klaimId <= 0) {
            responseJson(
                false,
                'klaim_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.klaim_id = :klaim_id';
        $params[':klaim_id'] = $klaimId;
    }

    if ($matchingId !== null) {

        if ($matchingId <= 0) {
            responseJson(
                false,
                'matching_id tidak valid.',
                null,
                422
            );
        }

        $where[] = 'a.matching_id = :matching_id';
        $params[':matching_id'] = $matchingId;
    }

    if ($aktivitas !== '') {

        $where[] = 'a.aktivitas = :aktivitas';
        $params[':aktivitas'] = $aktivitas;
    }

    if ($channel !== '') {
        $where[] = "JSON_UNQUOTE(JSON_EXTRACT(COALESCE(a.metadata, '{}'), '$.channel')) = :channel";
        $params[':channel'] = $channel;
    }

    if ($search !== '') {
        $where[] = "(
            a.aktivitas LIKE :search_activity
            OR a.deskripsi LIKE :search_description
            OR EXISTS (SELECT 1 FROM users su WHERE su.id = a.user_id AND (su.nama LIKE :search_user OR su.email LIKE :search_email OR su.kelas LIKE :search_class OR su.nis LIKE :search_nis))
            OR EXISTS (SELECT 1 FROM users tu_search WHERE tu_search.id = a.target_user_id AND tu_search.nama LIKE :search_target)
            OR EXISTS (SELECT 1 FROM barang b_search WHERE b_search.id = a.barang_id AND b_search.nama_barang LIKE :search_barang)
            OR EXISTS (SELECT 1 FROM laporan_hilang lh_search WHERE lh_search.id = a.laporan_hilang_id AND lh_search.nama_barang LIKE :search_laporan)
        )";
        $term = '%' . $search . '%';
        foreach (['activity', 'description', 'user', 'email', 'class', 'nis', 'target', 'barang', 'laporan'] as $key) {
            $params[':search_' . $key] = $term;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD WHERE
    |--------------------------------------------------------------------------
    */

    $whereSql = '';

    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    $countSql = "
        SELECT COUNT(*)
        FROM aktivitas_user a
        {$whereSql}
    ";

    $countStmt = $pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue(
            $key,
            $value,
            is_int($value)
                ? PDO::PARAM_INT
                : PDO::PARAM_STR
        );
    }

    $countStmt->execute();

    $total = (int) $countStmt->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            a.*,

            u.nama AS user_nama,
            u.email AS user_email,
            u.no_telepon AS user_telepon,
            u.kelas AS user_kelas,
            u.nis AS user_nis,

            tu.nama AS target_user_nama,
            tu.email AS target_user_email,
            tu.no_telepon AS target_user_telepon,
            tu.kelas AS target_user_kelas,
            tu.nis AS target_user_nis,

            lh.nama_barang AS laporan_nama_barang,
            lh.kategori AS laporan_kategori,
            lh.status AS laporan_status,

            b.nama_barang AS barang_nama,
            b.kategori AS barang_kategori,
            b.status AS barang_status,

            k.status AS klaim_status

        FROM aktivitas_user a

        INNER JOIN users u
            ON u.id = a.user_id

        LEFT JOIN users tu
            ON tu.id = a.target_user_id

        LEFT JOIN laporan_hilang lh
            ON lh.id = a.laporan_hilang_id

        LEFT JOIN barang b
            ON b.id = a.barang_id

        LEFT JOIN klaim k
            ON k.id = a.klaim_id

        {$whereSql}

        ORDER BY a.created_at DESC

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(
            $key,
            $value,
            is_int($value)
                ? PDO::PARAM_INT
                : PDO::PARAM_STR
        );
    }

    $stmt->execute();

    $data = $stmt->fetchAll();

    responseJson(
        true,
        'Daftar aktivitas berhasil diambil.',
        [
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'data'   => $data,
        ]
    );
}

/*
|--------------------------------------------------------------------------
| METHOD TIDAK DIDUKUNG
|--------------------------------------------------------------------------
*/

responseJson(
    false,
    'Method tidak diizinkan.',
    null,
    405
);