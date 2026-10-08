
<?php

/**
 * API Users
 * Lost & Found SMK Informatika Sumedang
 */

// =====================================================
// LOAD CONFIG
// =====================================================
// Jangan define ROOT_PATH di sini.
// ROOT_PATH sudah didefinisikan oleh config.php.
require_once dirname(__DIR__) . '/legacy-config/config.php';

// =====================================================
// LOAD MODEL
// =====================================================
require_once ROOT_PATH . '/app/models/User.php';

// =====================================================
// SESSION
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// HEADER
// =====================================================
header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// =====================================================
// OPTIONS / PREFLIGHT
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =====================================================
// RESPONSE JSON
// =====================================================
function responseJson(
    bool $success,
    string $message,
    mixed $data = null,
    int $status = 200
): never {
    http_response_code($status);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// =====================================================
// REQUEST METHOD
// =====================================================
$method = $_SERVER['REQUEST_METHOD'];

// =====================================================
// REQUEST INPUT
// =====================================================
$rawInput = file_get_contents('php://input');

$input = json_decode($rawInput, true);

if (!is_array($input)) {
    $input = $_POST;
}

if (!is_array($input)) {
    $input = [];
}

// =====================================================
// ACTION
// =====================================================
$action = $_GET['action'] ?? $input['action'] ?? 'me';

// =====================================================
// USER MODEL
// =====================================================
try {
    $userModel = new User();
} catch (Throwable $e) {
    responseJson(
        false,
        'Gagal menghubungkan model user',
        null,
        500
    );
}

// =====================================================
// CEK SESSION LOGIN
// =====================================================
$sessionUser = $_SESSION['user'] ?? null;

if (
    !is_array($sessionUser) ||
    empty($sessionUser['id'])
) {
    responseJson(
        false,
        'Belum login',
        null,
        401
    );
}

$clientPlatform = strtolower(trim((string)($_SERVER['HTTP_X_CLIENT_PLATFORM'] ?? '')));
if ($clientPlatform === 'mobile' && ($sessionUser['role'] ?? '') === 'admin' && !isImpersonating()) {
    responseJson(
        false,
        'Akun admin hanya dapat mengakses user melalui website.',
        null,
        403
    );
}

$userId = (int) $sessionUser['id'];

// =====================================================
// GET PROFILE
// GET /api/users.php?action=me
// =====================================================
if (
    $method === 'GET' &&
    $action === 'me'
) {

    try {

        $user = $userModel->findById($userId);

        if (!$user) {
            responseJson(
                false,
                'User tidak ditemukan',
                null,
                404
            );
        }

        // Jangan kirim password
        unset($user['password']);

        responseJson(
            true,
            'Data profile berhasil diambil',
            $user,
            200
        );

    } catch (Throwable $e) {

        responseJson(
            false,
            'Gagal mengambil data profile',
            null,
            500
        );
    }
}

// =====================================================
// UPDATE PROFILE
// Email + Nomor Telepon
//
// NIS, nama, kelas, dan role TIDAK boleh diubah
// melalui endpoint profile user.
// =====================================================
if (
    in_array($method, ['POST', 'PUT'], true) &&
    $action === 'update_profile'
) {

    // -------------------------------------------------
    // Ambil data
    // -------------------------------------------------
    $email = trim((string) ($input['email'] ?? ''));

    $noTelepon = trim(
        (string) ($input['no_telepon'] ?? '')
    );

    // -------------------------------------------------
    // Validasi email
    // -------------------------------------------------
    if ($email === '') {

        responseJson(
            false,
            'Email wajib diisi',
            null,
            400
        );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        responseJson(
            false,
            'Format email tidak valid',
            null,
            400
        );
    }

    // -------------------------------------------------
    // Validasi nomor telepon
    // -------------------------------------------------
    if ($noTelepon !== '') {

        $cleanPhone = preg_replace(
            '/[^0-9+]/',
            '',
            $noTelepon
        );

        if (
            strlen($cleanPhone) < 10 ||
            strlen($cleanPhone) > 15
        ) {

            responseJson(
                false,
                'Nomor telepon harus 10-15 digit',
                null,
                400
            );
        }

        $noTelepon = $cleanPhone;
    }

    try {

        // -------------------------------------------------
        // Cek email milik user lain
        // -------------------------------------------------
        $existingUser = $userModel->findByEmailExceptId(
            $email,
            $userId
        );

        if ($existingUser) {

            responseJson(
                false,
                'Email sudah digunakan oleh user lain',
                null,
                409
            );
        }

        // -------------------------------------------------
        // Update database
        // -------------------------------------------------
        $updated = $userModel->updateContact(
            $userId,
            $email,
            $noTelepon
        );

        if (!$updated) {

            responseJson(
                false,
                'Tidak ada perubahan atau profile gagal diperbarui',
                null,
                400
            );
        }

        // -------------------------------------------------
        // Update session
        // -------------------------------------------------
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['no_telepon'] = $noTelepon;

        // -------------------------------------------------
        // Ambil data terbaru
        // -------------------------------------------------
        $user = $userModel->findById($userId);

        if (!$user) {

            responseJson(
                false,
                'Profile berhasil diperbarui tetapi data user tidak ditemukan',
                null,
                404
            );
        }

        unset($user['password']);

        responseJson(
            true,
            'Profile berhasil diperbarui',
            $user,
            200
        );

    } catch (Throwable $e) {

        responseJson(
            false,
            'Gagal memperbarui profile',
            null,
            500
        );
    }
}

// =====================================================
// ADMIN LIST USER
// GET /api/users.php?action=admin
//
// Hanya admin yang boleh menggunakan endpoint ini.
// =====================================================
if (
    $method === 'GET' &&
    $action === 'admin'
) {

    // -------------------------------------------------
    // Cek role
    // -------------------------------------------------
    apiRequireAdmin();

    try {

        $users = $userModel->getAll();

        if (!is_array($users)) {
            $users = [];
        }

        // Jangan kirim password
        foreach ($users as &$user) {

            if (is_array($user)) {
                unset($user['password']);
            }
        }

        unset($user);

        responseJson(
            true,
            'Data user berhasil diambil',
            $users,
            200
        );

    } catch (Throwable $e) {

        responseJson(
            false,
            'Gagal mengambil data user',
            null,
            500
        );
    }
}

// =====================================================
// ACTION TIDAK DITEMUKAN
// =====================================================
responseJson(
    false,
    'Action tidak ditemukan',
    [
        'action' => $action,
        'method' => $method
    ],
    404
);
