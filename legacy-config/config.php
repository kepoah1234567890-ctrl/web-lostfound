<?php

/**
 * Application Global Configuration & Helpers
 * Lost & Found SMK Informatika Sumedang
 */

if (!function_exists('legacyEnv')) {
    function legacyEnv(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null || $value === ''
            ? $default
            : (string) $value;
    }
}

if (!class_exists('LegacyRedirectException', false)) {
    class LegacyRedirectException extends RuntimeException
    {
    }
}

if (!function_exists('legacyRedirect')) {
    function legacyRedirect(string $location): never
    {
        $target = preg_replace('/^Location:\s*/i', '', $location);

        throw new LegacyRedirectException($target ?? $location);
    }
}

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (legacyEnv('SESSION_DRIVER', 'file') === 'database') {
        require_once __DIR__ . '/legacy_database.php';

        session_set_save_handler(
            static function (string $savePath, string $sessionName): bool {
                return true;
            },
            static function (): bool {
                return true;
            },
            static function (string $id): string {
                $statement = Database::getConnection()->prepare(
                    'SELECT payload FROM sessions WHERE id = ?'
                );
                $statement->execute([$id]);

                return (string) ($statement->fetchColumn() ?: '');
            },
            static function (string $id, string $data): bool {
                $statement = Database::getConnection()->prepare(
                    'INSERT INTO sessions (id, payload, last_activity) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE payload = VALUES(payload), last_activity = VALUES(last_activity)'
                );

                return $statement->execute([$id, $data, time()]);
            },
            static function (string $id): bool {
                $statement = Database::getConnection()->prepare(
                    'DELETE FROM sessions WHERE id = ?'
                );

                return $statement->execute([$id]);
            },
            static function (int $maxLifetime): int|false {
                $statement = Database::getConnection()->prepare(
                    'DELETE FROM sessions WHERE last_activity < ?'
                );
                $statement->execute([time() - $maxLifetime]);

                return $statement->rowCount();
            }
        );
    }

    session_start();
}


/*
|--------------------------------------------------------------------------
| APPLICATION
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'Lost & Found');

define(
    'SCHOOL_NAME',
    'SMK Informatika Sumedang'
);

define(
    'APP_TAGLINE',
    'Temukan kembali barangmu dengan mudah.'
);

define(
    'APP_DESCRIPTION',
    'Platform resmi untuk membantu siswa menemukan kembali barang yang hilang dan mengelola barang yang ditemukan.'
);


/*
|--------------------------------------------------------------------------
| DIRECTORY PATHS
|--------------------------------------------------------------------------
*/

define(
    'ROOT_PATH',
    dirname(__DIR__)
);

define(
    'APP_PATH',
    ROOT_PATH . '/app'
);

define(
    'CONFIG_PATH',
    ROOT_PATH . '/legacy-config'
);

define(
    'PUBLIC_PATH',
    ROOT_PATH . '/public'
);

define(
    'UPLOAD_PATH',
    legacyEnv('UPLOAD_PATH', ROOT_PATH . '/uploads/barang')
);

require_once ROOT_PATH . '/legacy-config/media_storage.php';


/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
|
| Project:
| C:\laragon\www\web_lostfound
|
| Browser:
| http://localhost/web_lostfound
|
*/

$baseUrl = rtrim(legacyEnv('APP_URL', 'http://localhost/web_lostfound'), '/');

if (strtolower(legacyEnv('APP_ENV')) === 'production') {
    $baseUrl = preg_replace('/^http:\/\//i', 'https://', $baseUrl) ?? $baseUrl;
}

define('BASE_URL', $baseUrl);


/*
|--------------------------------------------------------------------------
| GOOGLE OAUTH
|--------------------------------------------------------------------------
|
| Web Client ID dan Secret dari Google Cloud Console.
|
*/

define(
    'GOOGLE_CLIENT_ID',
    legacyEnv('GOOGLE_CLIENT_ID')
);

define(
    'GOOGLE_CLIENT_SECRET',
    legacyEnv('GOOGLE_CLIENT_SECRET')
);

define(
    'GOOGLE_REDIRECT_URI',
    BASE_URL . '/google_login.php'
);


/*
|--------------------------------------------------------------------------
| UPLOAD DIRECTORY
|--------------------------------------------------------------------------
*/

if (!is_dir(UPLOAD_PATH)) {
    mkdir(
        UPLOAD_PATH,
        0777,
        true
    );
}


/*
|--------------------------------------------------------------------------
| URL HELPER
|--------------------------------------------------------------------------
*/

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            return BASE_URL;
        }

        return BASE_URL . '/' . $path;
    }
}


/*
|--------------------------------------------------------------------------
| ASSET URL
|--------------------------------------------------------------------------
*/

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return BASE_URL . '/public/assets/' . $path;
    }
}


/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE URL
|--------------------------------------------------------------------------
*/

function uploadUrl(?string $filename): string
{
    if ($filename === null || trim($filename) === '') {
        return BASE_URL . '/public/assets/images/default-item.svg';
    }

    $filename = trim($filename);
    if (basename($filename) !== $filename) {
        return BASE_URL . '/public/assets/images/default-item.svg';
    }

    static $mediaAvailability = [];
    $localPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename;
    $available = is_file($localPath);

    if (!$available) {
        $mediaAvailability[$filename] ??= uploadedMediaExists($filename);
        $available = $mediaAvailability[$filename];
    }

    if ($available) {
        return BASE_URL
            . '/uploads/barang/'
            . rawurlencode($filename);
    }

    return BASE_URL
        . '/public/assets/images/default-item.svg';
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

function setFlash(
    string $type,
    string $message
): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}


function getFlash(): ?array
{
    if (
        !isset(
            $_SESSION['flash']
        )
    ) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset(
        $_SESSION['flash']
    );

    return $flash;
}


/*
|--------------------------------------------------------------------------
| AUTH & SESSION
|--------------------------------------------------------------------------
*/

function legacyAuth(): ?array
{
    return $_SESSION['user'] ?? null;
}


function isLoggedIn(): bool
{
    return (
        isset($_SESSION['user'])
        && !empty($_SESSION['user']['id'])
    );
}

function impersonationAdmin(): ?array
{
    $admin = $_SESSION['impersonation']['admin_user'] ?? null;

    return is_array($admin) ? $admin : null;
}


function isImpersonating(): bool
{
    $user = legacyAuth();
    $admin = impersonationAdmin();
    $targetUserId = (int)($_SESSION['impersonation']['target_user_id'] ?? 0);

    return is_array($user)
        && is_array($admin)
        && ($admin['role'] ?? '') === 'admin'
        && $targetUserId > 0
        && (int)($user['id'] ?? 0) === $targetUserId;
}


function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['_csrf_token'];
}


function isValidCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && hash_equals((string)$_SESSION['_csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . '">';
}




function isAdmin(): bool
{
    return (
        isLoggedIn()
    && !isImpersonating()
        && isset($_SESSION['user']['role'])
        && $_SESSION['user']['role'] === 'admin'
    );
}


function isSiswa(): bool
{
    return (
        isLoggedIn()
        && isset($_SESSION['user']['role'])
        && $_SESSION['user']['role'] === 'siswa'
    );
}


function apiRequireLogin(): array
{
    $user = legacyAuth();

    if (!is_array($user) || empty($user['id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login untuk melanjutkan.',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $user;
}


function apiRequireAdmin(): array
{
    $user = apiRequireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Akses hanya untuk administrator.',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $user;
}


function requireLogin(): void
{
    if (!isLoggedIn()) {

        setFlash(
            'warning',
            'Silakan masuk ke akun Anda terlebih dahulu.'
        );

        header(
            'Location: ' . url('login')
        );

        exit;
    }
}


function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {

        setFlash(
            'danger',
            'Akses ditolak. Anda tidak memiliki izin mengakses halaman ini.'
        );

        header(
            'Location: ' . url('')
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| VIEW RENDER
|--------------------------------------------------------------------------
*/

function renderView(
    string $viewPath,
    array $data = [],
    bool $withLayout = true
): void {

    extract($data);

    if (str_starts_with($viewPath, 'admin/')) {
        renderAdminView($viewPath, $data);
        return;
    }

    if ($withLayout) {

        require_once APP_PATH
            . '/views/layouts/header.php';

        require_once APP_PATH
            . '/views/layouts/navbar.php';

        require_once APP_PATH
            . "/views/{$viewPath}.php";

        require_once APP_PATH
            . '/views/layouts/footer.php';

    } else {

        require_once APP_PATH
            . "/views/{$viewPath}.php";
    }
}



/*
|--------------------------------------------------------------------------
| ADMIN VIEW LAYOUT
|--------------------------------------------------------------------------
| Semua halaman admin memakai shell terpisah dari navbar publik.
*/
function renderAdminPrintView(string $viewPath, array $data = []): void
{
    extract($data);
    require_once APP_PATH . '/views/layouts/admin_print_layout.php';
}


function renderAdminView(string $viewPath, array $data = []): void
{
    $data['bodyClass'] = 'admin-page';
    $data['viewPath'] = $viewPath;
    extract($data);

    require_once APP_PATH . '/views/layouts/admin_layout.php';
}

/*
|--------------------------------------------------------------------------
| FILE UPLOAD
|--------------------------------------------------------------------------
*/

function handleFileUpload(
    array $fileInput,
    ?string $oldFilename = null
): ?string {

    if (!isset($fileInput['error']) || !is_int($fileInput['error'])) {
        throw new Exception('Data unggahan foto tidak valid.');
    }

    if ($fileInput['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran foto melebihi batas server.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran foto melebihi batas formulir.',
            UPLOAD_ERR_PARTIAL => 'Foto tidak terunggah dengan lengkap.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menyimpan foto.',
            UPLOAD_ERR_EXTENSION => 'Ekstensi PHP menghentikan upload foto.',
        ];

        throw new Exception($uploadErrors[$fileInput['error']] ?? 'Gagal mengunggah file foto.');
    }

    $allowedTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];
    $maxSize = 2 * 1024 * 1024;
    $fileTmpPath = $fileInput['tmp_name'] ?? '';
    $fileName = $fileInput['name'] ?? '';
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!isset($allowedTypes[$fileExtension])) {
        throw new Exception(
            'Format file tidak didukung. '
            . 'Harap upload gambar JPG, JPEG, PNG, atau WEBP.'
        );
    }

    if (!is_string($fileTmpPath) || !is_uploaded_file($fileTmpPath)) {
        throw new Exception('File foto tidak valid atau tidak dapat dibaca.');
    }

    $fileSize = filesize($fileTmpPath);
    if ($fileSize === false) {
        throw new Exception('Ukuran file foto tidak dapat diperiksa.');
    }

    if ($fileSize > $maxSize) {
        throw new Exception(
            'Ukuran file terlalu besar. '
            . 'Maksimal ukuran gambar adalah 2MB.'
        );
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($fileTmpPath, FILEINFO_MIME_TYPE);
    if ($mimeType !== $allowedTypes[$fileExtension] || @getimagesize($fileTmpPath) === false) {
        throw new Exception('Isi file tidak sesuai dengan format gambar yang dipilih.');
    }

    $newFileName = uniqid('item_', true) . '.' . $fileExtension;
    $destination = UPLOAD_PATH . DIRECTORY_SEPARATOR . $newFileName;

    if (
        !move_uploaded_file(
            $fileTmpPath,
            $destination
        )
    ) {

        throw new Exception(
            'Gagal mengunggah file foto.'
        );
    }

    try {
        storeUploadedMedia($newFileName, $destination);
    } catch (Throwable $exception) {
        if (is_file($destination)) {
            unlink($destination);
        }

        error_log('Failed to persist uploaded media: ' . $exception->getMessage());
        throw new RuntimeException(
            'Foto gagal disimpan ke database. Silakan coba lagi atau hubungi administrator.',
            0,
            $exception
        );
    }

    if ($oldFilename !== null && trim($oldFilename) !== '') {
        $oldFilename = trim($oldFilename);
        $uploadDirectory = realpath(UPLOAD_PATH);
        $oldFilePath = realpath(
            UPLOAD_PATH . DIRECTORY_SEPARATOR . $oldFilename
        );

        if (
            basename($oldFilename) === $oldFilename &&
            $uploadDirectory !== false &&
            $oldFilePath !== false &&
            str_starts_with($oldFilePath, $uploadDirectory . DIRECTORY_SEPARATOR) &&
            is_file($oldFilePath)
        ) {
            @unlink($oldFilePath);
        }
    }


    return $newFileName;
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {
    function e(?string $string): string
    {
        return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
    }
}


/*
|--------------------------------------------------------------------------
| DATE FORMAT INDONESIA
|--------------------------------------------------------------------------
*/

function formatTanggalIndo(
    ?string $dateStr,
    bool $withTime = false
): string {

    if (!$dateStr) {
        return '-';
    }


    $time =
        strtotime($dateStr);


    if (!$time) {
        return '-';
    }


    $bulanIndo = [

        1 => 'Januari',

        2 => 'Februari',

        3 => 'Maret',

        4 => 'April',

        5 => 'Mei',

        6 => 'Juni',

        7 => 'Juli',

        8 => 'Agustus',

        9 => 'September',

        10 => 'Oktober',

        11 => 'November',

        12 => 'Desember',
    ];


    $hari =
        date(
            'd',
            $time
        );


    $bulan =
        $bulanIndo[
            (int)date(
                'm',
                $time
            )
        ];


    $tahun =
        date(
            'Y',
            $time
        );


    $formatted =
        $hari
        . ' '
        . $bulan
        . ' '
        . $tahun;


    if ($withTime) {

        $formatted .=
            ' '
            . date(
                'H:i',
                $time
            )
            . ' WIB';
    }


    return $formatted;
}


/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

function getStatusBadge(
    string $status
): string {

    $statusMap = [

        'tersedia' => [
            'label' => 'Tersedia',
            'class' => 'badge-pastel-green',
        ],

        'menunggu_klaim' => [
            'label' => 'Menunggu Klaim',
            'class' => 'badge-pastel-yellow',
        ],

        'diklaim' => [
            'label' => 'Diklaim',
            'class' => 'badge-pastel-purple',
        ],

        'dikembalikan' => [
            'label' => 'Dikembalikan',
            'class' => 'badge-pastel-blue',
        ],

        'menunggu' => [
            'label' => 'Menunggu',
            'class' => 'badge-pastel-yellow',
        ],

        'diverifikasi' => [
            'label' => 'Diverifikasi',
            'class' => 'badge-pastel-blue',
        ],

        'ditemukan' => [
            'label' => 'Ditemukan',
            'class' => 'badge-pastel-green',
        ],

        'selesai' => [
            'label' => 'Selesai',
            'class' => 'badge-pastel-purple',
        ],

        'disetujui' => [
            'label' => 'Disetujui',
            'class' => 'badge-pastel-green',
        ],

        'ditolak' => [
            'label' => 'Ditolak',
            'class' => 'badge-pastel-pink',
        ],
    ];


    $item =
        $statusMap[$status]
        ?? [
            'label' => ucfirst($status),
            'class' => 'badge-pastel-gray',
        ];


    return
        '<span class="badge '
        . e($item['class'])
        . '">'
        . e($item['label'])
        . '</span>';
}