<?php

define('IS_API', true);

require_once dirname(__DIR__) . '/legacy-config/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode HTTP tidak didukung',
    ]);

    exit;
}

$filename = trim(
    $_GET['file'] ?? ''
);

if ($filename === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Nama file foto diperlukan',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Cegah path traversal
|--------------------------------------------------------------------------
*/
$filename = basename($filename);

$filePath =
    UPLOAD_PATH .
    DIRECTORY_SEPARATOR .
    $filename;

if (!is_file($filePath)) {
    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Foto tidak ditemukan',
        'file' => $filename,
    ]);

    exit;
}

$mimeType =
    mime_content_type($filePath);

$allowedMime = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
];

if (!in_array(
    $mimeType,
    $allowedMime,
    true
)) {
    http_response_code(415);

    echo json_encode([
        'success' => false,
        'message' => 'Format gambar tidak didukung',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Kirim gambar
|--------------------------------------------------------------------------
*/
header(
    'Content-Type: ' . $mimeType
);

header(
    'Content-Length: ' .
    filesize($filePath)
);

header(
    'Cache-Control: public, max-age=86400'
);

readfile($filePath);

exit;