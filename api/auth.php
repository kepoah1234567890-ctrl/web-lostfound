<?php

declare(strict_types=1);

/**
 * Authentication API
 * Lost & Found SMK Informatika Sumedang
 *
 * Actions:
 * - login
 * - register
 * - logout
 * - me
 * - update_profile
 */

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__) . '/legacy-config/config.php';
require_once dirname(__DIR__) . '/app/models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
*/

function responseJson(
    bool $success,
    string $message,
    mixed $data = null,
    int $statusCode = 200
): never {
    http_response_code($statusCode);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Request Helpers
|--------------------------------------------------------------------------
*/

function getJsonInput(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (
        stripos(
            $contentType,
            'application/json'
        ) === false
    ) {
        return [];
    }

    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode(
        $raw,
        true
    );

    return is_array($decoded)
        ? $decoded
        : [];
}

function getRequestData(): array
{
    /*
     * Jika multipart/form-data:
     * data berada di $_POST.
     *
     * Jika application/json:
     * data berada di php://input.
     */
    if (!empty($_POST)) {
        return $_POST;
    }

    return getJsonInput();
}

function getAction(): string
{
    $action = $_GET['action'] ?? '';

    return strtolower(
        trim((string) $action)
    );
}

/*
|--------------------------------------------------------------------------
| User Response Helper
|--------------------------------------------------------------------------
*/

function normalizeUser(array $user): array
{
    return [
        'id' => isset($user['id'])
            ? (int) $user['id']
            : null,

        'nama' => $user['nama'] ?? '',

        'nis' => $user['nis'] ?? '',

        'email' => $user['email'] ?? '',

        'no_telepon' => $user['no_telepon'] ?? '',

        'avatar' => $user['avatar'] ?? null,

        'google_id' => $user['google_id'] ?? null,

        'password_set' => isset($user['password_set'])
            ? (int) $user['password_set']
            : 1,

        'kelas' => $user['kelas'] ?? '',

        'role' => $user['role'] ?? 'siswa',

        'created_at' => $user['created_at'] ?? null,

        'password_changed_at' =>
            $user['password_changed_at'] ?? null
    ];
}

/*
|--------------------------------------------------------------------------
| Validation Helpers
|--------------------------------------------------------------------------
*/

function validateEmail(string $email): bool
{
    return filter_var(
        trim($email),
        FILTER_VALIDATE_EMAIL
    ) !== false;
}

function validatePhone(string $phone): bool
{
    $phone = trim($phone);

    if ($phone === '') {
        return true;
    }

    /*
     * Nomor telepon maksimal 12 digit.
     */
    return preg_match(
        '/^\d{12}$/',
        $phone
    ) === 1;
}

function validateNis(string $nis): bool
{
    $nis = trim($nis);

    return $nis !== '';
}

/*
|--------------------------------------------------------------------------
| Avatar Upload
|--------------------------------------------------------------------------
*/

function getAvatarDirectory(): string
{
    $rootPath = dirname(__DIR__);

    return $rootPath . '/uploads/avatar';
}

function ensureAvatarDirectory(): bool
{
    $directory = getAvatarDirectory();

    if (is_dir($directory)) {
        return true;
    }

    return mkdir(
        $directory,
        0755,
        true
    );
}

function getAvatarRelativePath(
    string $filename
): string {
    return '/web_lostfound/uploads/avatar/' .
        rawurlencode($filename);
}

function isLocalAvatarFile(
    ?string $avatar
): bool {
    if ($avatar === null) {
        return false;
    }

    $avatar = trim($avatar);

    if ($avatar === '') {
        return false;
    }

    /*
     * Jangan menghapus URL eksternal.
     */
    if (
        str_starts_with(
            strtolower($avatar),
            'http://'
        )
        ||
        str_starts_with(
            strtolower($avatar),
            'https://'
        )
    ) {
        return false;
    }

    /*
     * Hanya nama file avatar.
     */
    return !str_contains(
        $avatar,
        '/'
    )
    &&
    !str_contains(
        $avatar,
        '\\'
    );
}

function deleteAvatarFile(
    ?string $avatar
): void {
    if (!isLocalAvatarFile($avatar)) {
        return;
    }

    $file = getAvatarDirectory() .
        DIRECTORY_SEPARATOR .
        basename((string) $avatar);

    if (
        is_file($file)
        &&
        is_writable($file)
    ) {
        @unlink($file);
    }
}

function uploadAvatar(
    int $userId,
    array $file
): array {
    if (
        !isset($file['error'])
        ||
        !isset($file['tmp_name'])
    ) {
        return [
            'success' => false,
            'message' => 'File foto tidak valid.'
        ];
    }

    if (
        $file['error'] !== UPLOAD_ERR_OK
    ) {
        $message = match (
            $file['error']
        ) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'Ukuran foto terlalu besar.',

            UPLOAD_ERR_PARTIAL =>
                'Upload foto tidak selesai.',

            UPLOAD_ERR_NO_FILE =>
                'Tidak ada foto yang dipilih.',

            default =>
                'Terjadi kesalahan saat upload foto.'
        };

        return [
            'success' => false,
            'message' => $message
        ];
    }

    $tmpName = $file['tmp_name'];

    if (
        !is_uploaded_file($tmpName)
    ) {
        return [
            'success' => false,
            'message' => 'File upload tidak valid.'
        ];
    }

    /*
     * Maksimal 2 MB.
     */
    $maxSize = 2 * 1024 * 1024;

    $fileSize = isset($file['size'])
        ? (int) $file['size']
        : 0;

    if ($fileSize <= 0) {
        return [
            'success' => false,
            'message' => 'Ukuran foto tidak valid.'
        ];
    }

    if ($fileSize > $maxSize) {
        return [
            'success' => false,
            'message' => 'Ukuran foto maksimal 2 MB.'
        ];
    }

    /*
     * Validasi MIME berdasarkan isi file.
     */
    $finfo = new finfo(
        FILEINFO_MIME_TYPE
    );

    $mimeType = $finfo->file(
        $tmpName,
        FILEINFO_MIME_TYPE
    );

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (
        !isset(
            $allowedMime[$mimeType]
        )
    ) {
        return [
            'success' => false,
            'message' =>
                'Format foto harus JPG, JPEG, PNG, atau WEBP.'
        ];
    }

    /*
     * Pastikan file memang gambar.
     */
    $imageInfo = @getimagesize(
        $tmpName
    );

    if ($imageInfo === false) {
        return [
            'success' => false,
            'message' => 'File bukan gambar yang valid.'
        ];
    }

    if (
        !ensureAvatarDirectory()
    ) {
        return [
            'success' => false,
            'message' =>
                'Folder upload foto tidak dapat dibuat.'
        ];
    }

    $extension =
        $allowedMime[$mimeType];

    /*
     * Nama file aman dan unik.
     */
    try {
        $random = bin2hex(
            random_bytes(8)
        );
    } catch (Throwable) {
        $random = uniqid();
    }

    $filename =
        'avatar_' .
        $userId .
        '_' .
        $random .
        '.' .
        $extension;

    $destination =
        getAvatarDirectory() .
        DIRECTORY_SEPARATOR .
        $filename;

    if (
        !move_uploaded_file(
            $tmpName,
            $destination
        )
    ) {
        return [
            'success' => false,
            'message' =>
                'Foto gagal disimpan ke server.'
        ];
    }

    try {
        storeUploadedMedia($filename, $destination);
    } catch (Throwable $exception) {
        if (is_file($destination)) {
            unlink($destination);
        }

        error_log('Failed to persist uploaded avatar: ' . $exception->getMessage());

        return [
            'success' => false,
            'message' =>
                'Foto gagal disimpan ke database. Silakan coba lagi.'
        ];
    }

    return [
        'success' => true,
        'filename' => $filename,
        'path' => $destination
    ];
}

/*
|--------------------------------------------------------------------------
| Main API
|--------------------------------------------------------------------------
*/

try {
    $userModel = new User();

    $action = getAction();

    if ($action === '') {
        responseJson(
            false,
            'Action tidak ditemukan.',
            null,
            400
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    if ($action === 'login') {
        $data = getRequestData();

        $email = trim(
            (string) (
                $data['email'] ??
                ''
            )
        );

        $password = (string) (
            $data['password'] ??
            ''
        );

        if ($email === '') {
            responseJson(
                false,
                'Email wajib diisi.',
                null,
                422
            );
        }

        if (!validateEmail($email)) {
            responseJson(
                false,
                'Format email tidak valid.',
                null,
                422
            );
        }

        if ($password === '') {
            responseJson(
                false,
                'Password wajib diisi.',
                null,
                422
            );
        }

        $user = $userModel->findByEmail(
            $email
        );

        if ($user === null) {
            responseJson(
                false,
                'Email atau password salah.',
                null,
                401
            );
        }

        $storedPassword =
            (string) (
                $user['password'] ?? ''
            );

        if (
            $storedPassword === ''
            ||
            !password_verify(
                $password,
                $storedPassword
            )
        ) {
            responseJson(
                false,
                'Email atau password salah.',
                null,
                401
            );
        }

        session_regenerate_id(true);
        unset($_SESSION['impersonation']);

        $_SESSION['user_id'] =
            (int) $user['id'];

        $_SESSION['user_role'] =
            $user['role'] ?? 'siswa';

        $_SESSION['logged_in'] = true;

        $user = $userModel->findById(
            (int) $user['id']
        );

        if ($user === null) {
            responseJson(
                false,
                'Data pengguna tidak dapat dimuat.',
                null,
                500
            );
        }

        unset($user['password']);
        $_SESSION['user'] = $user;

        responseJson(
            true,
            'Login berhasil.',
            [
                'user' => normalizeUser(
                    $user ?? []
                )
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    if ($action === 'register') {
        $data = getRequestData();

        $nama = trim(
            (string) (
                $data['nama'] ??
                ''
            )
        );

        $nis = trim(
            (string) (
                $data['nis'] ??
                ''
            )
        );

        $email = trim(
            (string) (
                $data['email'] ??
                ''
            )
        );

        $noTelepon = trim(
            (string) (
                $data['no_telepon'] ??
                ''
            )
        );

        $kelas = trim(
            (string) (
                $data['kelas'] ??
                ''
            )
        );

        $password = (string) (
            $data['password'] ??
            ''
        );

        if ($nama === '') {
            responseJson(
                false,
                'Nama wajib diisi.',
                null,
                422
            );
        }

        if (!validateNis($nis)) {
            responseJson(
                false,
                'NIS wajib diisi.',
                null,
                422
            );
        }

        if (!validateEmail($email)) {
            responseJson(
                false,
                'Format email tidak valid.',
                null,
                422
            );
        }

        if (!validatePhone($noTelepon)) {
            responseJson(
                false,
                'Nomor telepon harus 12 digit.',
                null,
                422
            );
        }

        if ($password === '') {
            responseJson(
                false,
                'Password wajib diisi.',
                null,
                422
            );
        }

        if (strlen($password) < 8) {
            responseJson(
                false,
                'Password minimal 8 karakter.',
                null,
                422
            );
        }

        if (
            $userModel->findByEmail(
                $email
            ) !== null
        ) {
            responseJson(
                false,
                'Email sudah digunakan.',
                null,
                409
            );
        }

        if (
            $userModel->findByNis(
                $nis
            ) !== null
        ) {
            responseJson(
                false,
                'NIS sudah digunakan.',
                null,
                409
            );
        }

        $userId = $userModel->create([
            'nama' => $nama,
            'nis' => $nis,
            'email' => $email,
            'no_telepon' => $noTelepon,
            'google_id' => null,
            'avatar' => null,
            'password' => $password,
            'password_set' => 1,
            'kelas' => $kelas,
            'role' => 'siswa'
        ]);

        $user = $userModel->findById(
            $userId
        );

        responseJson(
            true,
            'Registrasi berhasil.',
            [
                'user' => normalizeUser(
                    $user ?? []
                )
            ],
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ME
    |--------------------------------------------------------------------------
    */

    if ($action === 'me') {
        $sessionUser = apiRequireLogin();
        $userId = (int) $sessionUser['id'];

        $user = $userModel->findById(
            $userId
        );

        if ($user === null) {
            responseJson(
                false,
                'User tidak ditemukan.',
                null,
                404
            );
        }

        responseJson(
            true,
            'Data user berhasil diambil.',
            [
                'user' => normalizeUser(
                    $user
                )
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'update_profile'
    ) {
        $data = getRequestData();
        $sessionUser = apiRequireLogin();
        $userId = (int) $sessionUser['id'];

        $existingUser =
            $userModel->findById(
                $userId
            );

        if ($existingUser === null) {
            responseJson(
                false,
                'User tidak ditemukan.',
                null,
                404
            );
        }

        $nama = trim(
            (string) (
                $data['nama'] ??
                ($existingUser['nama'] ?? '')
            )
        );

        $nis = trim(
            (string) (
                $data['nis'] ??
                ($existingUser['nis'] ?? '')
            )
        );

        $email = trim(
            (string) (
                $data['email'] ??
                ($existingUser['email'] ?? '')
            )
        );

        $noTelepon = trim(
            (string) (
                $data['no_telepon'] ??
                ($existingUser['no_telepon'] ?? '')
            )
        );

        $kelas = trim(
            (string) (
                $data['kelas'] ??
                ($existingUser['kelas'] ?? '')
            )
        );

        if ($nama === '') {
            responseJson(
                false,
                'Nama wajib diisi.',
                null,
                422
            );
        }

        if (!validateNis($nis)) {
            responseJson(
                false,
                'NIS wajib diisi.',
                null,
                422
            );
        }

        if (!validateEmail($email)) {
            responseJson(
                false,
                'Format email tidak valid.',
                null,
                422
            );
        }

        if (!validatePhone($noTelepon)) {
            responseJson(
                false,
                'Nomor telepon harus 12 digit.',
                null,
                422
            );
        }

        /*
         * Cek NIS milik user lain.
         */
        $nisOwner =
            $userModel->findByNisExceptId(
                $nis,
                $userId
            );

        if ($nisOwner !== null) {
            responseJson(
                false,
                'NIS sudah digunakan user lain.',
                null,
                409
            );
        }

        /*
         * Cek email milik user lain.
         */
        $emailOwner =
            $userModel->findByEmailExceptId(
                $email,
                $userId
            );

        if ($emailOwner !== null) {
            responseJson(
                false,
                'Email sudah digunakan user lain.',
                null,
                409
            );
        }

        $hasAvatar =
            isset($_FILES['avatar'])
            &&
            is_array(
                $_FILES['avatar']
            );

        $newAvatar = null;
        $oldAvatar = $existingUser['avatar'] ?? null;

        if ($hasAvatar) {
            $uploadResult =
                uploadAvatar(
                    $userId,
                    $_FILES['avatar']
                );

            if (
                !($uploadResult['success'] ?? false)
            ) {
                responseJson(
                    false,
                    (string) (
                        $uploadResult['message'] ??
                        'Upload avatar gagal.'
                    ),
                    null,
                    422
                );
            }

            $newAvatar =
                (string) (
                    $uploadResult['filename'] ??
                    ''
                );

            if ($newAvatar === '') {
                responseJson(
                    false,
                    'Nama file avatar tidak valid.',
                    null,
                    500
                );
            }

        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            if (!$userModel->updateProfile($userId, $nama, $nis, $email, $noTelepon, $kelas)) {
                throw new RuntimeException('Data profil gagal diperbarui.');
            }

            if ($newAvatar !== null && !$userModel->updateAvatar($userId, $newAvatar)) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($newAvatar !== null) {
                deleteAvatarFile($newAvatar);
            }

            responseJson(
                false,
                'Data profil gagal diperbarui.',
                null,
                500
            );
        }

        if ($newAvatar !== null && $oldAvatar !== null && $oldAvatar !== $newAvatar) {
            deleteAvatarFile((string)$oldAvatar);
        }

        /*
         * Ambil data terbaru dari DB.
         */
        $updatedUser =
            $userModel->findById(
                $userId
            );

        if ($updatedUser === null) {
            responseJson(
                false,
                'Profil berhasil diubah tetapi data user tidak dapat diambil.',
                null,
                500
            );
        }

        unset($updatedUser['password']);
        $_SESSION['user'] = $updatedUser;
        $_SESSION['user_role'] = $updatedUser['role'] ?? 'siswa';

        responseJson(
            true,
            'Profil berhasil diperbarui.',
            [
                'user' => normalizeUser(
                    $updatedUser
                )
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    if ($action === 'logout') {
        $_SESSION = [];

        if (
            ini_get(
                'session.use_cookies'
            )
        ) {
            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        responseJson(
            true,
            'Logout berhasil.',
            null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTION TIDAK DIKENAL
    |--------------------------------------------------------------------------
    */

    responseJson(
        false,
        'Action tidak dikenal.',
        null,
        404
    );

} catch (PDOException $e) {

    responseJson(
        false,
        'Terjadi kesalahan database.',
        null,
        500
    );

} catch (Throwable $e) {

    responseJson(
        false,
        'Terjadi kesalahan pada server.',
        null,
        500
    );
}