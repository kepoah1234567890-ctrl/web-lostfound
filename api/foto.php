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

if (is_file($filePath)) {
    $mimeType = mime_content_type($filePath);
    $fileSize = filesize($filePath);
    $fileContents = null;
} else {
    $media = findUploadedMedia($filename);
    $mimeType = $media['mime_type'] ?? null;
    $fileContents = $media['content'] ?? null;
    $fileSize = is_string($fileContents) ? strlen($fileContents) : null;
}

if (!is_string($mimeType) || !is_int($fileSize)) {
    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Foto tidak ditemukan',
        'file' => $filename,
    ]);

    exit;
}

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
    $fileSize
);

header(
    'Cache-Control: public, max-age=86400'
);

if (is_string($fileContents)) {
    echo $fileContents;
} else {
    readfile($filePath);
}

exit;