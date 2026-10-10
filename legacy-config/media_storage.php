<?php

require_once __DIR__ . '/legacy_database.php';

function storeUploadedMedia(string $filename, string $filePath): void
{
    if (
        $filename === ''
        || basename($filename) !== $filename
        || !is_file($filePath)
        || !is_readable($filePath)
    ) {
        throw new RuntimeException('File media tidak valid untuk disimpan.');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file(
        $filePath,
        FILEINFO_MIME_TYPE
    );
    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    if (
        !is_string($mimeType)
        || !in_array($mimeType, $allowedMimeTypes, true)
        || @getimagesize($filePath) === false
    ) {
        throw new RuntimeException('File media bukan gambar yang didukung.');
    }

    $contents = file_get_contents($filePath);
    if ($contents === false || $contents === '') {
        throw new RuntimeException('File media gagal dibaca.');
    }

    $statement = Database::getConnection()->prepare(
        'INSERT INTO uploaded_files (filename, mime_type, content, created_at, updated_at)
         VALUES (:filename, :mime_type, :content, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
         ON DUPLICATE KEY UPDATE
            mime_type = VALUES(mime_type),
            content = VALUES(content),
            updated_at = CURRENT_TIMESTAMP'
    );
    $statement->bindValue(':filename', $filename, PDO::PARAM_STR);
    $statement->bindValue(':mime_type', $mimeType, PDO::PARAM_STR);
    $statement->bindValue(':content', $contents, PDO::PARAM_LOB);
    $statement->execute();
}

function findUploadedMedia(string $filename): ?array
{
    if ($filename === '' || basename($filename) !== $filename) {
        return null;
    }

    $statement = Database::getConnection()->prepare(
        'SELECT mime_type, content FROM uploaded_files WHERE filename = :filename LIMIT 1'
    );
    $statement->execute(['filename' => $filename]);
    $media = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$media) {
        return null;
    }

    $contents = $media['content'];
    if (is_resource($contents)) {
        $contents = stream_get_contents($contents);
    }

    if (!is_string($contents)) {
        throw new RuntimeException('Data gambar di database tidak dapat dibaca.');
    }

    return [
        'mime_type' => (string) $media['mime_type'],
        'content' => $contents,
    ];
}

function uploadedMediaExists(string $filename): bool
{
    if ($filename === '' || basename($filename) !== $filename) {
        return false;
    }

    $statement = Database::getConnection()->prepare(
        'SELECT 1 FROM uploaded_files WHERE filename = :filename LIMIT 1'
    );
    $statement->execute(['filename' => $filename]);

    return $statement->fetchColumn() !== false;
}
