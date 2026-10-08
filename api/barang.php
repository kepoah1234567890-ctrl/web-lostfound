<?php
/**
 * API Barang - Lost & Found
 * PHP Native MVC + PDO
 *
 * Mendukung:
 * 1. Barang ditemukan
 * 2. Barang hilang
 * 3. Detail barang
 * 4. Data pemilik/pelapor
 * 5. Nomor telepon pemilik/pelapor
 * 6. POST tambah barang ditemukan
 * 7. POST update barang + foto
 * 8. PUT/PATCH update barang
 * 9. DELETE barang
 * 10. Matching barang hilang <-> barang ditemukan
 */

define('IS_API', true);

require_once dirname(__DIR__) . '/legacy-config/config.php';
require_once APP_PATH . '/models/Barang.php';
require_once APP_PATH . '/models/LaporanHilang.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$apiUser = apiRequireLogin();
$isAdmin = ($apiUser['role'] ?? '') === 'admin';

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

function apiResponse(
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
            'data' => $data,
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| STRING HELPER UNTUK MATCHING
|--------------------------------------------------------------------------
*/

function normalizeMatchText(?string $text): string
{
    $text = strtolower(trim((string) $text));

    if ($text === '') {
        return '';
    }

    $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim($text);
}

function matchWords(?string $text): array
{
    $text = normalizeMatchText($text);

    if ($text === '') {
        return [];
    }

    $words = preg_split('/\s+/', $text);

    $stopWords = [
        'dan',
        'yang',
        'di',
        'ke',
        'dari',
        'pada',
        'ini',
        'itu',
        'ada',
        'saya',
        'punya',
        'milik',
        'barang',
        'nya',
    ];

    $result = [];

    foreach ($words as $word) {
        $word = trim($word);

        if ($word === '') {
            continue;
        }

        if (in_array($word, $stopWords, true)) {
            continue;
        }

        if (mb_strlen($word) < 2) {
            continue;
        }

        $result[] = $word;
    }

    return array_values(array_unique($result));
}

/*
|--------------------------------------------------------------------------
| HITUNG MATCHING
|--------------------------------------------------------------------------
|
| Bobot:
|
| Nama       = 40
| Kategori   = 25
| Warna      = 15
| Deskripsi  = 10
| Lokasi     = 10
|
|--------------------------------------------------------------------------
*/

function calculateMatchScore(
    array $lost,
    array $found
): array {
    $score = 0;
    $reasons = [];

    /*
    |--------------------------------------------------------------------------
    | NAMA BARANG
    |--------------------------------------------------------------------------
    */

    $lostName = normalizeMatchText(
        $lost['nama_barang'] ?? ''
    );

    $foundName = normalizeMatchText(
        $found['nama_barang'] ?? ''
    );

    if (
        $lostName !== '' &&
        $foundName !== ''
    ) {
        if ($lostName === $foundName) {

            $score += 40;

            $reasons[] =
                'Nama barang sama persis';

        } elseif (
            str_contains($lostName, $foundName) ||
            str_contains($foundName, $lostName)
        ) {

            $score += 30;

            $reasons[] =
                'Nama barang mirip';
        } else {

            $lostWords =
                matchWords($lostName);

            $foundWords =
                matchWords($foundName);

            $sameWords =
                array_intersect(
                    $lostWords,
                    $foundWords
                );

            if (count($sameWords) > 0) {

                $score += 20;

                $reasons[] =
                    'Sebagian nama barang cocok';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    $lostCategory =
        normalizeMatchText(
            $lost['kategori'] ?? ''
        );

    $foundCategory =
        normalizeMatchText(
            $found['kategori'] ?? ''
        );

    if (
        $lostCategory !== '' &&
        $foundCategory !== ''
    ) {

        if (
            $lostCategory ===
            $foundCategory
        ) {

            $score += 25;

            $reasons[] =
                'Kategori sama';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | WARNA
    |--------------------------------------------------------------------------
    */

    $lostColor =
        normalizeMatchText(
            $lost['warna'] ?? ''
        );

    $foundColor =
        normalizeMatchText(
            $found['warna'] ?? ''
        );

    if (
        $lostColor !== '' &&
        $foundColor !== ''
    ) {

        if (
            $lostColor ===
            $foundColor
        ) {

            $score += 15;

            $reasons[] =
                'Warna sama';
        } elseif (
            str_contains(
                $lostColor,
                $foundColor
            ) ||
            str_contains(
                $foundColor,
                $lostColor
            )
        ) {

            $score += 10;

            $reasons[] =
                'Warna mirip';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESKRIPSI
    |--------------------------------------------------------------------------
    */

    $lostDescription =
        matchWords(
            $lost['deskripsi'] ?? ''
        );

    $foundDescription =
        matchWords(
            $found['deskripsi'] ?? ''
        );

    if (
        count($lostDescription) > 0 &&
        count($foundDescription) > 0
    ) {

        $sameDescriptionWords =
            array_intersect(
                $lostDescription,
                $foundDescription
            );

        if (
            count($sameDescriptionWords) >= 3
        ) {

            $score += 10;

            $reasons[] =
                'Deskripsi memiliki beberapa ciri yang sama';

        } elseif (
            count($sameDescriptionWords) >= 1
        ) {

            $score += 5;

            $reasons[] =
                'Ada ciri barang yang sama';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOKASI
    |--------------------------------------------------------------------------
    */

    $lostLocation =
        normalizeMatchText(
            $lost['lokasi_terakhir'] ?? ''
        );

    $foundLocation =
        normalizeMatchText(
            $found['lokasi_ditemukan'] ?? ''
        );

    if (
        $lostLocation !== '' &&
        $foundLocation !== ''
    ) {

        if (
            $lostLocation ===
            $foundLocation
        ) {

            $score += 10;

            $reasons[] =
                'Lokasi sama';

        } elseif (
            str_contains(
                $lostLocation,
                $foundLocation
            ) ||
            str_contains(
                $foundLocation,
                $lostLocation
            )
        ) {

            $score += 7;

            $reasons[] =
                'Lokasi mirip';
        } else {

            $lostLocationWords =
                matchWords($lostLocation);

            $foundLocationWords =
                matchWords($foundLocation);

            $sameLocationWords =
                array_intersect(
                    $lostLocationWords,
                    $foundLocationWords
                );

            if (
                count($sameLocationWords) > 0
            ) {

                $score += 5;

                $reasons[] =
                    'Ada bagian lokasi yang sama';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BATAS SKOR
    |--------------------------------------------------------------------------
    */

    if ($score > 100) {
        $score = 100;
    }

    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */

    if ($score >= 75) {

        $label =
            'Sangat mungkin cocok';

    } elseif ($score >= 50) {

        $label =
            'Kemungkinan cocok';

    } elseif ($score >= 30) {

        $label =
            'Kemungkinan kecil';

    } else {

        $label =
            'Kurang cocok';
    }

    return [
        'score' => $score,
        'label' => $label,
        'reasons' => array_values(
            array_unique($reasons)
        ),
    ];
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA KONTAK USER
|--------------------------------------------------------------------------
*/

function getUserContact(
    PDO $pdo,
    int $userId
): array {

    if ($userId <= 0) {

        return [
            'user_id' => null,
            'user_nama' => '',
            'user_kelas' => '',
            'user_email' => '',
            'user_nis' => '',
            'user_telepon' => '',
        ];
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            kelas,
            email,
            nis,
            no_telepon AS telepon
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $user =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );

    if (!$user) {

        return [
            'user_id' => $userId,
            'user_nama' => '',
            'user_kelas' => '',
            'user_email' => '',
            'user_nis' => '',
            'user_telepon' => '',
        ];
    }

    return [
        'user_id' =>
            (int) ($user['id'] ?? $userId),

        'user_nama' =>
            $user['nama'] ?? '',

        'user_kelas' =>
            $user['kelas'] ?? '',

        'user_email' =>
            $user['email'] ?? '',

        'user_nis' =>
            $user['nis'] ?? '',

        'user_telepon' =>
            $user['telepon'] ?? '',
    ];
}

/*
|--------------------------------------------------------------------------
| TAMBAHKAN KONTAK BARANG DITEMUKAN
|--------------------------------------------------------------------------
*/

function attachFoundUserContact(
    PDO $pdo,
    array $barang
): array {

    $userId =
        (int) (
            $barang['ditemukan_oleh'] ?? 0
        );

    $contact =
        getUserContact(
            $pdo,
            $userId
        );

    $barang['penemu_id'] =
        $contact['user_id'];

    $barang['penemu_nama'] =
        $contact['user_nama'];

    $barang['penemu_kelas'] =
        $contact['user_kelas'];

    $barang['penemu_email'] =
        $contact['user_email'];

    $barang['penemu_nis'] =
        $contact['user_nis'];

    $barang['penemu_telepon'] =
        $contact['user_telepon'];

    $barang['pelapor_nama'] =
        $contact['user_nama'];

    $barang['pelapor_kelas'] =
        $contact['user_kelas'];

    $barang['pelapor_email'] =
        $contact['user_email'];

    $barang['pelapor_nis'] =
        $contact['user_nis'];

    $barang['pelapor_telepon'] =
        $contact['user_telepon'];

    return $barang;
}

/*
|--------------------------------------------------------------------------
| TAMBAHKAN KONTAK LAPORAN HILANG
|--------------------------------------------------------------------------
*/

function attachLostUserContact(
    PDO $pdo,
    array $laporan
): array {

    $userId =
        (int) (
            $laporan['user_id'] ?? 0
        );

    $contact =
        getUserContact(
            $pdo,
            $userId
        );

    $laporan['pelapor_id'] =
        $contact['user_id'];

    $laporan['pelapor_nama'] =
        $contact['user_nama'];

    $laporan['pelapor_kelas'] =
        $contact['user_kelas'];

    $laporan['pelapor_email'] =
        $contact['user_email'];

    $laporan['pelapor_nis'] =
        $contact['user_nis'];

    $laporan['pelapor_telepon'] =
        $contact['user_telepon'];

    return $laporan;
}

/*
|--------------------------------------------------------------------------
| AMBIL LAPORAN HILANG USER
|--------------------------------------------------------------------------
*/

function getLostReportsByUser(
    PDO $pdo,
    int $userId,
    ?int $laporanId = null
): array {

    $sql = "
        SELECT
            lh.id,
            lh.user_id,
            lh.nama_barang,
            lh.kategori,
            lh.warna,
            lh.deskripsi,
            lh.lokasi_terakhir,
            lh.tanggal_hilang,
            lh.foto,
            lh.status,
            lh.created_at,
            lh.updated_at
        FROM laporan_hilang lh
        WHERE lh.user_id = ?
    ";

    $params = [$userId];

    if ($laporanId !== null && $laporanId > 0) {

        $sql .= "
            AND lh.id = ?
        ";

        $params[] = $laporanId;
    }

    $sql .= "
        ORDER BY lh.created_at DESC
    ";

    $stmt =
        $pdo->prepare($sql);

    $stmt->execute($params);

    $data =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

    return is_array($data)
        ? $data
        : [];
}

/*
|--------------------------------------------------------------------------
| MATCHING BARANG
|--------------------------------------------------------------------------
*/

function findMatchingItems(
    PDO $pdo,
    int $userId,
    ?int $laporanId = null
): array {

    $lostReports =
        getLostReportsByUser(
            $pdo,
            $userId,
            $laporanId
        );

    if (
        count($lostReports) === 0
    ) {

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil barang ditemukan yang masih tersedia
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("
            SELECT
                b.*
            FROM barang b
            WHERE
                (
                    b.status = 'tersedia'
                    OR b.status = 'menunggu_klaim'
                )
                AND b.ditemukan_oleh <> ?
            ORDER BY b.created_at DESC
        ");

    $stmt->execute([
        $userId
    ]);

    $foundItems =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

    if (
        !is_array($foundItems)
    ) {

        $foundItems = [];
    }

    $matches = [];

    foreach (
        $lostReports as $lost
    ) {

        foreach (
            $foundItems as $found
        ) {

            $match =
                calculateMatchScore(
                    $lost,
                    $found
                );

            /*
            |--------------------------------------------------------------------------
            | Hanya tampilkan minimal skor 30
            |--------------------------------------------------------------------------
            */

            if (
                $match['score'] < 30
            ) {
                continue;
            }

            $found =
                attachFoundUserContact(
                    $pdo,
                    $found
                );

            $matches[] = [

                'laporan_hilang_id' =>
                    (int) (
                        $lost['id'] ?? 0
                    ),

                'barang_ditemukan_id' =>
                    (int) (
                        $found['id'] ?? 0
                    ),

                'score' =>
                    $match['score'],

                'label' =>
                    $match['label'],

                'reasons' =>
                    $match['reasons'],

                'laporan_hilang' => [
                    'id' =>
                        (int) (
                            $lost['id'] ?? 0
                        ),

                    'user_id' =>
                        (int) (
                            $lost['user_id'] ?? 0
                        ),

                    'nama_barang' =>
                        $lost['nama_barang'] ?? '',

                    'kategori' =>
                        $lost['kategori'] ?? '',

                    'warna' =>
                        $lost['warna'] ?? '',

                    'deskripsi' =>
                        $lost['deskripsi'] ?? '',

                    'lokasi_terakhir' =>
                        $lost['lokasi_terakhir'] ?? '',

                    'tanggal_hilang' =>
                        $lost['tanggal_hilang'] ?? '',

                    'foto' =>
                        $lost['foto'] ?? null,

                    'status' =>
                        $lost['status'] ?? '',
                ],

                'barang_ditemukan' => $found,
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Urutkan skor tertinggi
    |--------------------------------------------------------------------------
    */

    usort(
        $matches,
        function (
            $a,
            $b
        ) {

            return
                ($b['score'] ?? 0)
                <=>
                ($a['score'] ?? 0);
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Batasi maksimal 50 hasil
    |--------------------------------------------------------------------------
    */

    return array_slice(
        $matches,
        0,
        50
    );
}

try {

    $method =
        $_SERVER['REQUEST_METHOD'];

    $barangModel =
        new Barang();

    $pdo =
        Database::getConnection();

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    if ($method === 'GET') {

        $id =
            isset($_GET['id'])
                ? (int) $_GET['id']
                : 0;

        $requestedUserId =
            isset($_GET['user_id'])
                ? (int) $_GET['user_id']
                : 0;
        $userId = 0;

        if ($requestedUserId > 0) {
            if (!$isAdmin && $requestedUserId !== (int) $apiUser['id']) {
                apiResponse(false, 'Akses data barang ditolak.', null, 403);
            }
            $userId = $requestedUserId;
        }

        $search =
            trim(
                $_GET['search'] ?? ''
            );

        $action =
            strtolower(
                trim(
                    $_GET['action'] ?? ''
                )
            );

        /*
        |--------------------------------------------------------------------------
        | MATCHING
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | barang.php?action=matching&user_id=5
        |
        | Atau:
        |
        | barang.php?action=matching&user_id=5&laporan_id=3
        |
        |--------------------------------------------------------------------------
        */

        if (
            $action === 'matching' ||
            $action === 'match'
        ) {

            if ($userId <= 0 && !$isAdmin) {
                $userId = (int) $apiUser['id'];
            }

            if ($userId <= 0) {

                apiResponse(
                    false,
                    'User ID wajib diisi untuk mencari barang yang cocok.',
                    null,
                    400
                );
            }

            $laporanId =
                isset($_GET['laporan_id'])
                    ? (int) $_GET['laporan_id']
                    : null;

            $matches =
                findMatchingItems(
                    $pdo,
                    $userId,
                    $laporanId
                );

            apiResponse(
                true,
                count($matches) > 0
                    ? 'Kemungkinan barang ditemukan berhasil dicari.'
                    : 'Belum ditemukan barang yang memiliki kecocokan.',
                [
                    'total' =>
                        count($matches),

                    'matches' =>
                        $matches,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DETAIL BARANG
        |--------------------------------------------------------------------------
        */

        if ($id > 0) {

            /*
            |--------------------------------------------------------------------------
            | CEK BARANG DITEMUKAN
            |--------------------------------------------------------------------------
            */

            $barang =
                $barangModel->getById(
                    $id
                );

            if ($barang) {

                $barang['jenis_data'] =
                    'ditemukan';

                $barang =
                    attachFoundUserContact(
                        $pdo,
                        $barang
                    );

                if (
                    !isset(
                        $barang['lokasi_ditemukan']
                    )
                ) {

                    $barang['lokasi_ditemukan'] =
                        $barang['lokasi'] ??
                        '';
                }

                if (
                    !isset(
                        $barang['tanggal_ditemukan']
                    )
                ) {

                    $barang['tanggal_ditemukan'] =
                        $barang['tanggal'] ??
                        '';
                }

                apiResponse(
                    true,
                    'Detail barang berhasil diambil',
                    $barang
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CEK LAPORAN HILANG
            |--------------------------------------------------------------------------
            */

            $stmt =
                $pdo->prepare("
                    SELECT
                        lh.*,

                        u.nama AS pelapor_nama,
                        u.kelas AS pelapor_kelas,
                        u.email AS pelapor_email,
                        u.nis AS pelapor_nis,
                        u.no_telepon AS pelapor_telepon

                    FROM laporan_hilang lh

                    LEFT JOIN users u
                        ON u.id = lh.user_id

                    WHERE lh.id = ?

                    LIMIT 1
                ");

            $stmt->execute([
                $id
            ]);

            $laporan =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            if ($laporan) {
                if (!$isAdmin && (int)$laporan['user_id'] !== (int)$apiUser['id']) {
                    apiResponse(false, 'Akses laporan ditolak.', null, 403);
                }

                $laporan['jenis_data'] =
                    'hilang';

                $laporan['lokasi_ditemukan'] =
                    $laporan['lokasi_terakhir'] ??
                    '';

                $laporan['tanggal_ditemukan'] =
                    $laporan['tanggal_hilang'] ??
                    '';

                if (
                    !isset(
                        $laporan['pelapor_telepon']
                    )
                ) {

                    $laporan =
                        attachLostUserContact(
                            $pdo,
                            $laporan
                        );
                }

                if (
                    !isset($laporan['foto']) ||
                    trim(
                        (string)
                        $laporan['foto']
                    ) === ''
                ) {

                    $laporan['foto'] =
                        null;
                }

                apiResponse(
                    true,
                    'Detail laporan hilang berhasil diambil',
                    $laporan
                );
            }

            apiResponse(
                false,
                'Data barang tidak ditemukan',
                null,
                404
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LIST BARANG DITEMUKAN
        |--------------------------------------------------------------------------
        */

        $filters = [];

        if ($search !== '') {

            $filters['search'] =
                $search;
        }

        if ($userId > 0) {

            $filters['ditemukan_oleh'] =
                $userId;
        }

        $barangList =
            $barangModel->getAll(
                $filters
            );

        if (!is_array($barangList)) {

            $barangList = [];
        }

        foreach (
            $barangList as &$barang
        ) {

            $barang['jenis_data'] =
                'ditemukan';

            $barang =
                attachFoundUserContact(
                    $pdo,
                    $barang
                );
        }

        unset($barang);

        /*
        |--------------------------------------------------------------------------
        | USER_ID DIPAKAI
        |--------------------------------------------------------------------------
        */

        if ($userId > 0) {

            apiResponse(
                true,
                'Barang milik user berhasil diambil',
                $barangList
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LIST LAPORAN HILANG
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT
                lh.id,
                lh.user_id,
                lh.nama_barang,
                lh.kategori,
                lh.warna,
                lh.deskripsi,
                lh.lokasi_terakhir,
                lh.tanggal_hilang,
                lh.foto,
                lh.status,
                lh.created_at,
                lh.updated_at,

                u.nama AS pelapor_nama,
                u.kelas AS pelapor_kelas,
                u.email AS pelapor_email,
                u.nis AS pelapor_nis,
                u.no_telepon AS pelapor_telepon

            FROM laporan_hilang lh

            LEFT JOIN users u
                ON u.id = lh.user_id
        ";

        $params = [];

        if ($search !== '') {

            $sql .= "
                WHERE
                    lh.nama_barang LIKE ?
                    OR lh.kategori LIKE ?
                    OR lh.warna LIKE ?
                    OR lh.deskripsi LIKE ?
                    OR lh.lokasi_terakhir LIKE ?
                    OR u.nama LIKE ?
                    OR u.kelas LIKE ?
            ";

            $keyword =
                '%' .
                $search .
                '%';

            $params = [
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
            ];
        }

        $sql .= "
            ORDER BY
                lh.created_at DESC
        ";

        $stmt =
            $pdo->prepare(
                $sql
            );

        $stmt->execute(
            $params
        );

        $laporanList =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        foreach (
            $laporanList as &$laporan
        ) {

            $laporan['jenis_data'] =
                'hilang';

            $laporan['lokasi_ditemukan'] =
                $laporan['lokasi_terakhir'] ??
                '';

            $laporan['tanggal_ditemukan'] =
                $laporan['tanggal_hilang'] ??
                '';

            if (
                !isset(
                    $laporan['pelapor_telepon']
                )
            ) {

                $laporan =
                    attachLostUserContact(
                        $pdo,
                        $laporan
                    );
            }

            if (
                !isset($laporan['foto']) ||
                trim(
                    (string)
                    $laporan['foto']
                ) === ''
            ) {

                $laporan['foto'] =
                    null;
            }
        }

        unset($laporan);

        /*
        |--------------------------------------------------------------------------
        | GABUNG
        |--------------------------------------------------------------------------
        */

        $allData =
            array_merge(
                $barangList,
                $laporanList
            );

        /*
        |--------------------------------------------------------------------------
        | URUTKAN TERBARU
        |--------------------------------------------------------------------------
        */

        usort(
            $allData,
            function (
                $a,
                $b
            ) {

                $dateA =
                    $a['created_at'] ??
                    '';

                $dateB =
                    $b['created_at'] ??
                    '';

                return strcmp(
                    $dateB,
                    $dateA
                );
            }
        );

        apiResponse(
            true,
            'Daftar barang berhasil diambil',
            $allData
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POST
    |--------------------------------------------------------------------------
    */

    if ($method === 'POST') {

        $action =
            $_GET['action'] ??
            'create';

        /*
        |--------------------------------------------------------------------------
        | UPDATE BARANG
        |--------------------------------------------------------------------------
        */

        if (
            $action === 'update'
        ) {

            $id =
                isset($_GET['id'])
                    ? (int) $_GET['id']
                    : 0;

            $userId = (int) $apiUser['id'];

            if ($id <= 0) {

                apiResponse(
                    false,
                    'ID barang wajib diisi',
                    null,
                    400
                );
            }

            if ($userId <= 0) {

                apiResponse(
                    false,
                    'User ID wajib diisi',
                    null,
                    400
                );
            }

            $barang =
                $barangModel->getById(
                    $id
                );

            if (!$barang) {

                apiResponse(
                    false,
                    'Barang tidak ditemukan',
                    null,
                    404
                );
            }

            if (
                (int) (
                    $barang[
                        'ditemukan_oleh'
                    ] ?? 0
                ) !== $userId
            ) {

                apiResponse(
                    false,
                    'Kamu tidak memiliki izin mengubah barang ini.',
                    null,
                    403
                );
            }

            $input = [

                'nama_barang' =>
                    trim(
                        $_POST[
                            'nama_barang'
                        ] ?? ''
                    ),

                'kategori' =>
                    trim(
                        $_POST[
                            'kategori'
                        ] ?? ''
                    ),

                'warna' =>
                    trim(
                        $_POST[
                            'warna'
                        ] ?? ''
                    ),

                'deskripsi' =>
                    trim(
                        $_POST[
                            'deskripsi'
                        ] ?? ''
                    ),

                'lokasi_ditemukan' =>
                    trim(
                        $_POST[
                            'lokasi_ditemukan'
                        ] ?? ''
                    ),

                'tanggal_ditemukan' =>
                    trim(
                        $_POST[
                            'tanggal_ditemukan'
                        ] ?? ''
                    ),

                'status' =>
                    $barang[
                        'status'
                    ] ?? 'tersedia',
            ];

            if (
                $input[
                    'nama_barang'
                ] === '' ||
                $input[
                    'kategori'
                ] === '' ||
                $input[
                    'lokasi_ditemukan'
                ] === ''
            ) {

                apiResponse(
                    false,
                    'Nama barang, kategori, dan lokasi ditemukan wajib diisi.',
                    null,
                    422
                );
            }

            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !==
                    UPLOAD_ERR_NO_FILE
            ) {

                $newFoto =
                    handleFileUpload(
                        $_FILES['foto'],
                        $barang['foto'] ??
                            null
                    );

                $input['foto'] =
                    $newFoto;
            }

            $result =
                $barangModel->update(
                    $id,
                    $input
                );

            if (!$result) {

                apiResponse(
                    false,
                    'Gagal memperbarui barang.',
                    null,
                    500
                );
            }

            $updated =
                $barangModel->getById(
                    $id
                );

            if ($updated) {

                $updated[
                    'jenis_data'
                ] = 'ditemukan';

                $updated =
                    attachFoundUserContact(
                        $pdo,
                        $updated
                    );
            }

            apiResponse(
                true,
                'Barang berhasil diperbarui.',
                $updated
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE BARANG DITEMUKAN
        |--------------------------------------------------------------------------
        */

        $userId = (int) $apiUser['id'];

        if ($userId <= 0) {

            apiResponse(
                false,
                'User ID wajib diisi.',
                null,
                400
            );
        }

        $input = [

            'nama_barang' =>
                trim(
                    $_POST[
                        'nama_barang'
                    ] ?? ''
                ),

            'kategori' =>
                trim(
                    $_POST[
                        'kategori'
                    ] ?? ''
                ),

            'warna' =>
                trim(
                    $_POST[
                        'warna'
                    ] ?? ''
                ),

            'deskripsi' =>
                trim(
                    $_POST[
                        'deskripsi'
                    ] ?? ''
                ),

            'lokasi_ditemukan' =>
                trim(
                    $_POST[
                        'lokasi_ditemukan'
                    ] ?? ''
                ),

            'tanggal_ditemukan' =>
                trim(
                    $_POST[
                        'tanggal_ditemukan'
                    ] ??
                    date('Y-m-d')
                ),

            'status' =>
                'tersedia',

            'ditemukan_oleh' =>
                $userId,

            'foto' =>
                null,
        ];

        if (
            $input[
                'nama_barang'
            ] === '' ||
            $input[
                'kategori'
            ] === '' ||
            $input[
                'lokasi_ditemukan'
            ] === ''
        ) {

            apiResponse(
                false,
                'Nama barang, kategori, dan lokasi ditemukan wajib diisi.',
                null,
                422
            );
        }

        if (
            isset($_FILES['foto']) &&
            $_FILES['foto']['error'] !==
                UPLOAD_ERR_NO_FILE
        ) {

            $input['foto'] =
                handleFileUpload(
                    $_FILES['foto']
                );
        }

        $newId =
            $barangModel->create(
                $input
            );

        $created =
            $barangModel->getById(
                $newId
            );

        if ($created) {

            $created[
                'jenis_data'
            ] = 'ditemukan';

            $created =
                attachFoundUserContact(
                    $pdo,
                    $created
                );
        }

        apiResponse(
            true,
            'Barang berhasil ditambahkan.',
            $created,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PUT / PATCH
    |--------------------------------------------------------------------------
    */

    if (
        $method === 'PUT' ||
        $method === 'PATCH'
    ) {

        $id =
            isset($_GET['id'])
                ? (int) $_GET['id']
                : 0;

        if ($id <= 0) {

            apiResponse(
                false,
                'ID barang wajib diisi.',
                null,
                400
            );
        }

        $input =
            json_decode(
                file_get_contents(
                    'php://input'
                ),
                true
            );

        if (!is_array($input)) {

            $input = [];
        }

        $userId = (int) $apiUser['id'];

        if ($userId <= 0) {

            apiResponse(
                false,
                'User ID wajib diisi.',
                null,
                400
            );
        }

        $barang =
            $barangModel->getById(
                $id
            );

        if (!$barang) {

            apiResponse(
                false,
                'Barang tidak ditemukan.',
                null,
                404
            );
        }

        if (
            (int) (
                $barang[
                    'ditemukan_oleh'
                ] ?? 0
            ) !== $userId
        ) {

            apiResponse(
                false,
                'Kamu tidak memiliki izin mengubah barang ini.',
                null,
                403
            );
        }

        $allowedFields = [
            'nama_barang',
            'kategori',
            'warna',
            'deskripsi',
            'lokasi_ditemukan',
            'tanggal_ditemukan',
        ];
        $updateData = array_intersect_key(
            $input,
            array_flip($allowedFields)
        );

        if ($updateData === []) {
            apiResponse(
                false,
                'Tidak ada field barang yang dapat diperbarui.',
                null,
                422
            );
        }

        $updateData = array_merge(
            [
                'nama_barang' => $barang['nama_barang'],
                'kategori' => $barang['kategori'],
                'warna' => $barang['warna'] ?? null,
                'deskripsi' => $barang['deskripsi'] ?? null,
                'lokasi_ditemukan' => $barang['lokasi_ditemukan'],
                'tanggal_ditemukan' => $barang['tanggal_ditemukan'],
            ],
            $updateData
        );

        $result =
            $barangModel->update(
                $id,
                $updateData
            );

        $updated =
            $barangModel->getById(
                $id
            );

        if ($updated) {

            $updated[
                'jenis_data'
            ] = 'ditemukan';

            $updated =
                attachFoundUserContact(
                    $pdo,
                    $updated
                );
        }

        apiResponse(
            true,
            'Barang berhasil diperbarui.',
            $updated
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    if ($method === 'DELETE') {

        $id =
            isset($_GET['id'])
                ? (int) $_GET['id']
                : 0;

        $userId = (int) $apiUser['id'];

        if ($id <= 0) {

            apiResponse(
                false,
                'ID barang wajib diisi.',
                null,
                400
            );
        }

        if ($userId <= 0) {

            apiResponse(
                false,
                'User ID wajib diisi.',
                null,
                400
            );
        }

        $barang =
            $barangModel->getById(
                $id
            );

        if (!$barang) {

            apiResponse(
                false,
                'Barang tidak ditemukan.',
                null,
                404
            );
        }

        if (
            (int) (
                $barang[
                    'ditemukan_oleh'
                ] ?? 0
            ) !== $userId
        ) {

            apiResponse(
                false,
                'Kamu tidak memiliki izin menghapus barang ini.',
                null,
                403
            );
        }

        $activeClaims = $pdo->prepare(
            "SELECT id FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui') LIMIT 1"
        );
        $activeClaims->execute([$id]);

        if (
            in_array($barang['status'], ['diklaim', 'dikembalikan'], true) ||
            $activeClaims->fetchColumn()
        ) {
            apiResponse(
                false,
                'Barang dengan klaim aktif tidak dapat dihapus.',
                null,
                409
            );
        }

        $result =
            $barangModel->delete(
                $id
            );

        apiResponse(
            true,
            'Barang berhasil dihapus.',
            $result
        );
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD TIDAK DIIZINKAN
    |--------------------------------------------------------------------------
    */

    apiResponse(
        false,
        'Method HTTP tidak diizinkan.',
        null,
        405
    );

} catch (Throwable $e) {

    apiResponse(
        false,
        'Terjadi kesalahan server: ' .
        $e->getMessage(),
        null,
        500
    );
}