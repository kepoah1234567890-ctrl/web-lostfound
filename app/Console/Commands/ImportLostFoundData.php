<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportLostFoundData extends Command
{
    protected $signature = 'lostfound:import-data
        {file : Path to a data-only SQL export compatible with the current schema}
        {--replace : Replace existing application data after confirmation}';

    protected $description = 'Import a compatible Lost & Found data-only SQL export';

    private const INSERT_ORDER = [
        'users',
        'barang',
        'laporan_hilang',
        'klaim',
        'matching_barang',
        'pengembalian',
        'aktivitas_user',
    ];

    private const DELETE_ORDER = [
        'aktivitas_user',
        'matching_barang',
        'pengembalian',
        'klaim',
        'laporan_hilang',
        'barang',
        'users',
    ];

    private const REQUIRED_TABLES = [
        'users',
        'barang',
        'laporan_hilang',
        'klaim',
        'pengembalian',
        'matching_barang',
        'aktivitas_user',
        'password_reset_tokens',
    ];

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!is_file($file) || !is_readable($file)) {
            $this->error("File teu kapanggih atawa teu bisa dibaca: {$file}");

            return self::FAILURE;
        }

        $size = filesize($file);
        if ($size === false || $size > 100 * 1024 * 1024) {
            $this->error('Ukuran file SQL teu valid atawa leuwih ti 100 MB.');

            return self::FAILURE;
        }

        try {
            $inserts = $this->readInserts($file);
            $this->ensureSchemaIsReady();
            $counts = $this->currentCounts();
        } catch (Throwable $exception) {
            $this->error('Teu bisa mariksa file/schema: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $hasExistingData = array_sum($counts) > 0;
        if ($hasExistingData && !$this->option('replace')) {
            $this->error('Database tujuan geus aya eusina. Pikeun ngaganti sakabéh data aplikasi, tambahkeun pilihan --replace.');
            $this->line('Jumlah ayeuna: ' . $this->formatCounts($counts));

            return self::FAILURE;
        }

        if ($this->option('replace')) {
            $this->warn('--replace bakal ngahapus data aplikasi ayeuna, kaasup akun, barang, laporan, klaim, pengembalian, aktivitas, jeung token reset password.');
            $this->line('Jumlah ayeuna: ' . $this->formatCounts($counts));

            if (!$this->confirm('Geus nyieun backup jeung yakin rék neruskeun?', false)) {
                $this->info('Import dibatalkeun; data teu dirobah.');

                return self::SUCCESS;
            }
        }

        $connection = DB::connection();

        try {
            $connection->beginTransaction();

            if ($this->option('replace')) {
                foreach (self::DELETE_ORDER as $table) {
                    $connection->table($table)->delete();
                }
                $connection->table('password_reset_tokens')->delete();
            }

            foreach (self::INSERT_ORDER as $table) {
                foreach ($inserts[$table] as $statement) {
                    $connection->unprepared($statement);
                }
            }

            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            $this->error('Import gagal; transaksi geus dibatalkeun: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Import hasil. Jumlah data ayeuna: ' . $this->formatCounts($this->currentCounts()));

        return self::SUCCESS;
    }

    /**
     * @return array<string, list<string>>
     */
    private function readInserts(string $file): array
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException('Eusi file teu bisa dibaca.');
        }

        $inserts = array_fill_keys(self::INSERT_ORDER, []);
        $allowedControls = [
            'SET FOREIGN_KEY_CHECKS=0',
            'SET FOREIGN_KEY_CHECKS=1',
            'START TRANSACTION',
            'COMMIT',
        ];

        foreach ($this->splitStatements($sql) as $statement) {
            $control = strtoupper(preg_replace('/\s+/', '', $statement));
            if (in_array($control, array_map(
                static fn (string $allowed): string => strtoupper(preg_replace('/\s+/', '', $allowed)),
                $allowedControls,
            ), true)) {
                continue;
            }

            if (!preg_match('/^INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\(/i', $statement, $matches)) {
                throw new RuntimeException('File kudu mangrupa data-only SQL; aya statement salian ti INSERT tabel aplikasi.');
            }

            $table = strtolower($matches[1]);
            if (!array_key_exists($table, $inserts)) {
                throw new RuntimeException("Tabel `{$table}` teu diidinan dina file import.");
            }

            $inserts[$table][] = $statement;
        }

        if (array_sum(array_map('count', $inserts)) === 0) {
            throw new RuntimeException('Teu aya INSERT data dina file SQL.');
        }

        return $inserts;
    }

    /**
     * Split SQL at semicolons outside strings and remove SQL comments.
     *
     * @return list<string>
     */
    private function splitStatements(string $sql): array
    {
        $statements = [];
        $statement = '';
        $quote = null;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $character = $sql[$index];
            $next = $sql[$index + 1] ?? '';

            if ($quote !== null) {
                $statement .= $character;

                if (($quote === "'" || $quote === '"') && $character === '\\' && $index + 1 < $length) {
                    $statement .= $sql[++$index];
                    continue;
                }

                if ($character === $quote) {
                    if ($next === $quote) {
                        $statement .= $sql[++$index];
                    } else {
                        $quote = null;
                    }
                }

                continue;
            }

            if (($character === '-' && $next === '-' && ctype_space($sql[$index + 2] ?? '')) || $character === '#') {
                while ($index < $length && $sql[$index] !== "\n") {
                    $index++;
                }
                $statement .= ' ';
                continue;
            }

            if ($character === '/' && $next === '*') {
                $end = strpos($sql, '*/', $index + 2);
                if ($end === false) {
                    throw new RuntimeException('Komentar SQL teu ditutup.');
                }
                $statement .= ' ';
                $index = $end + 1;
                continue;
            }

            if ($character === "'" || $character === '"' || $character === '`') {
                $quote = $character;
                $statement .= $character;
                continue;
            }

            if ($character === ';') {
                if (trim($statement) !== '') {
                    $statements[] = trim($statement);
                }
                $statement = '';
                continue;
            }

            $statement .= $character;
        }

        if ($quote !== null) {
            throw new RuntimeException('String atawa ngaran kolom dina SQL teu ditutup.');
        }

        if (trim($statement) !== '') {
            $statements[] = trim($statement);
        }

        return $statements;
    }

    private function ensureSchemaIsReady(): void
    {
        foreach (self::REQUIRED_TABLES as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException("Tabel `{$table}` can aya. Jalankeun php artisan migrate --force heula.");
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function currentCounts(): array
    {
        $counts = [];

        foreach (self::REQUIRED_TABLES as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    /**
     * @param array<string, int> $counts
     */
    private function formatCounts(array $counts): string
    {
        return implode(', ', array_map(
            static fn (string $table, int $count): string => "{$table}={$count}",
            array_keys($counts),
            array_values($counts),
        ));
    }
}
