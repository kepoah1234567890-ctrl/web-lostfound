<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SyncUploadedMedia extends Command
{
    protected $signature = 'lostfound:sync-uploads {--dry-run : Validate and count local images without writing to the database} {--connection= : Database connection to use}';

    protected $description = 'Copy existing local uploaded images to the configured MySQL database';

    private const MAX_FILE_SIZE = (16 * 1024 * 1024) - 1;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public function handle(): int
    {
        $files = $this->localMediaFiles();
        if ($files === []) {
            $this->warn('Tidak ada foto lokal yang ditemukan pada uploads/barang atau uploads/avatar.');

            return self::SUCCESS;
        }

        $validated = [];
        $totalBytes = 0;

        try {
            foreach ($files as $file) {
                $media = $this->readMedia($file);
                $validated[] = $media;
                $totalBytes += strlen($media['content']);
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->line(sprintf(
            'Foto valid: %d, ukuran total: %.2f MB.',
            count($validated),
            $totalBytes / 1024 / 1024
        ));

        if ($this->option('dry-run')) {
            $this->info('Dry-run selesai; database tidak diubah.');

            return self::SUCCESS;
        }

        try {
            $connection = DB::connection($this->option('connection'));
            $connection->table('uploaded_files');

            foreach ($validated as $media) {
                $connection->table('uploaded_files')->updateOrInsert(
                    ['filename' => $media['filename']],
                    [
                        'mime_type' => $media['mime_type'],
                        'content' => $media['content'],
                        'updated_at' => now(),
                    ]
                );
            }
        } catch (Throwable $exception) {
            $this->error('Sinkronisasi foto gagal: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '%d foto berhasil disimpan di database; data aplikasi lain tidak diubah.',
            count($validated)
        ));

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function localMediaFiles(): array
    {
        $files = [];

        foreach (['barang', 'avatar'] as $directory) {
            $path = base_path('uploads/' . $directory);
            if (!is_dir($path)) {
                continue;
            }

            foreach (new \DirectoryIterator($path) as $file) {
                if (
                    $file->isFile()
                    && !$file->isDot()
                    && !str_starts_with($file->getFilename(), '.')
                ) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    private function readMedia(string $path): array
    {
        $filename = basename($path);
        if (basename($filename) !== $filename || str_contains($filename, '\\')) {
            throw new RuntimeException('Nama salah satu file foto tidak valid.');
        }

        $size = filesize($path);
        if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
            throw new RuntimeException("Ukuran foto {$filename} tidak valid untuk MEDIUMBLOB.");
        }

        try {
            $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file(
                $path,
                FILEINFO_MIME_TYPE
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "Tipe foto {$filename} gagal diperiksa: " . $exception->getMessage(),
                0,
                $exception
            );
        }
        if (
            !is_string($mimeType)
            || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)
            || @getimagesize($path) === false
        ) {
            throw new RuntimeException("File {$filename} bukan gambar yang didukung.");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Foto {$filename} gagal dibaca.");
        }

        return [
            'filename' => $filename,
            'mime_type' => $mimeType,
            'content' => $content,
        ];
    }
}
