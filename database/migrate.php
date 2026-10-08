<?php
/**
 * Safe database initializer.
 * Creates missing tables without deleting existing data or running seeds.
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

require_once dirname(__DIR__) . '/legacy-config/legacy_database.php';

echo "=== Lost & Found — Database Setup ===\n";

function runSqlFile(PDO $pdo, string $file, string $label): void {
    if (!file_exists($file)) {
        echo "- Lewati {$label}: file tidak ditemukan.\n";
        return;
    }
    $sql = trim(file_get_contents($file));
    if ($sql === '') {
        echo "- Lewati {$label}: kosong.\n";
        return;
    }
    $pdo->exec($sql);
    echo "✔ {$label} berhasil diproses.\n";
}

try {
    $pdo = Database::getConnection();
    $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

    if (!is_string($databaseName) || $databaseName === '') {
        throw new RuntimeException('Tidak ada database aktif pada koneksi MySQL.');
    }

    echo "Database target: {$databaseName}\n";

    runSqlFile($pdo, __DIR__ . '/schema.sql', 'schema dasar');
    runSqlFile($pdo, __DIR__ . '/auth_upgrade.sql', 'upgrade authentication');
    runSqlFile($pdo, __DIR__ . '/add_no_telepon_and_profile.sql', 'upgrade profil/nomor telepon');
    runSqlFile($pdo, __DIR__ . '/audit_aktivitas.sql', 'matching + audit aktivitas');

    echo "\n=== Selesai ===\n";
    echo "Data lama tidak di-TRUNCATE.\n";
    echo "Untuk data contoh/testing, jalankan seed.sql secara manual.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "✖ Gagal: " . $e->getMessage() . "\n");
    exit(1);
}
