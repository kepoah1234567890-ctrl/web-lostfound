<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ReplaceRailwayDatabase extends Command
{
    protected $signature = 'lostfound:copy-local-to-railway
        {--dry-run : Check the Railway connection and count local data without writing}
        {--replace : Replace Railway application-table contents with the local copy}';

    protected $description = 'Copy local Lost & Found tables and uploaded photos to Railway MySQL';

    private const TABLES = [
        'users',
        'barang',
        'laporan_hilang',
        'klaim',
        'pengembalian',
        'matching_barang',
        'aktivitas_user',
        'password_reset_tokens',
        'uploaded_files',
    ];

    public function handle(): int
    {
        $url = env('RAILWAY_DB_URL');
        if (!is_string($url) || $url === '') {
            $this->error('RAILWAY_DB_URL belum disetel. Jangan ubah koneksi DB lokal.');

            return self::FAILURE;
        }

        try {
            $sourceHost = (string) config(
                'database.connections.' . config('database.default') . '.host'
            );
            if (!in_array(strtolower($sourceHost), ['127.0.0.1', 'localhost', '::1'], true)) {
                throw new RuntimeException(
                    'Database sumber bukan MySQL lokal. Pulihkan DB_HOST/DB_DATABASE lokal sebelum menyalin.'
                );
            }
            if ((string) config(
                'database.connections.' . config('database.default') . '.database'
            ) !== 'lost_found') {
                throw new RuntimeException(
                    'Database sumber harus bernama lost_found untuk mencegah penyalinan database lokal yang keliru.'
                );
            }
            if ($this->option('dry-run') && $this->option('replace')) {
                throw new RuntimeException('Gunakan hanya salah satu opsi: --dry-run atau --replace.');
            }

            $targetConfig = $this->targetConfig($url);
            config(['database.connections.railway' => $targetConfig]);
            DB::purge('railway');

            $source = DB::connection();
            $target = DB::connection('railway');
            $target->getPdo();

            $this->info(sprintf(
                'Koneksi Railway berhasil: %s/%s.',
                $targetConfig['host'],
                $targetConfig['database']
            ));

            $counts = [];
            foreach (self::TABLES as $table) {
                if ($source->getSchemaBuilder()->hasTable($table)) {
                    $counts[$table] = $source->table($table)->count();
                }
            }

            $this->table(
                ['Tabel lokal', 'Jumlah baris'],
                array_map(
                    static fn (string $table, int $count): array => [$table, $count],
                    array_keys($counts),
                    array_values($counts)
                )
            );

            if ($this->option('dry-run')) {
                Artisan::call('lostfound:sync-uploads', ['--dry-run' => true]);
                $this->output->write(Artisan::output());
                $this->info('Dry-run selesai; database Railway tidak diubah.');

                return self::SUCCESS;
            }

            if (!$this->option('replace')) {
                $this->error('Operasi tulis memerlukan --replace agar penggantian isi Railway eksplisit.');

                return self::FAILURE;
            }

            if (!$this->confirm(
                'Ini akan mengganti isi tabel aplikasi Railway dengan salinan lokal. Lanjutkan?'
            )) {
                $this->warn('Dibatalkan; database Railway tidak diubah.');

                return self::SUCCESS;
            }

            $this->initializeSchema($target);
            $this->replaceRows($source, $target, array_keys($counts));

            $mediaExitCode = Artisan::call('lostfound:sync-uploads', [
                '--connection' => 'railway',
            ]);
            $this->output->write(Artisan::output());

            if ($mediaExitCode !== self::SUCCESS) {
                $this->error('Tabel berhasil disalin, tetapi sinkronisasi file foto gagal.');

                return self::FAILURE;
            }

            $this->info('Selesai: tabel, data lokal, dan foto lokal sudah disalin ke Railway.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Penyalinan gagal: ' . $exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge('railway');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function targetConfig(string $url): array
    {
        $parts = parse_url($url);
        if (
            !is_array($parts)
            || !in_array(strtolower($parts['scheme'] ?? ''), ['mysql', 'mariadb'], true)
            || empty($parts['host'])
            || empty($parts['path'])
        ) {
            throw new RuntimeException('RAILWAY_DB_URL bukan URL koneksi MySQL yang valid.');
        }

        if (str_ends_with(strtolower((string) $parts['host']), '.railway.internal')) {
            throw new RuntimeException(
                'Host .railway.internal hanya bisa diakses dari Railway. Gunakan host TCP Proxy untuk migrasi dari komputer lokal.'
            );
        }

        return array_merge(config('database.connections.mysql'), [
            'url' => null,
            'host' => (string) $parts['host'],
            'port' => (string) ($parts['port'] ?? 3306),
            'database' => rawurldecode(ltrim((string) $parts['path'], '/')),
            'username' => rawurldecode((string) ($parts['user'] ?? 'root')),
            'password' => rawurldecode((string) ($parts['pass'] ?? '')),
        ]);
    }

    private function initializeSchema(\Illuminate\Database\Connection $target): void
    {
        foreach ([
            'schema.sql',
            'auth_upgrade.sql',
            'add_no_telepon_and_profile.sql',
            'audit_aktivitas.sql',
        ] as $filename) {
            $path = database_path($filename);
            $sql = file_get_contents($path);
            if ($sql === false) {
                throw new RuntimeException("Gagal membaca schema {$filename}.");
            }

            $target->getPdo()->exec($sql);
        }
    }

    /**
     * @param list<string> $tables
     */
    private function replaceRows(
        \Illuminate\Database\Connection $source,
        \Illuminate\Database\Connection $target,
        array $tables
    ): void {
        $this->assertCompatibleSchemas($source, $target, $tables);

        $pdo = $target->getPdo();
        $target->transaction(function () use ($source, $target, $tables, $pdo): void {
            foreach (array_reverse(self::TABLES) as $table) {
                if ($target->getSchemaBuilder()->hasTable($table)) {
                    $target->table($table)->delete();
                }
            }

            foreach ($tables as $table) {
                $columns = $source->getSchemaBuilder()->getColumnListing($table);
                $quotedColumns = implode(
                    ', ',
                    array_map(static fn (string $column): string => '`' . $column . '`', $columns)
                );
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $statement = $pdo->prepare(
                    'INSERT INTO `' . $table . '` (' . $quotedColumns . ') VALUES (' . $placeholders . ')'
                );

                foreach ($source->table($table)->orderBy($columns[0])->cursor() as $row) {
                    $values = (array) $row;
                    foreach ($columns as $index => $column) {
                        $value = $values[$column];
                        $parameterType = $value === null
                            ? \PDO::PARAM_NULL
                            : ($table === 'uploaded_files' && $column === 'content'
                                ? \PDO::PARAM_LOB
                                : (is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR));
                        $statement->bindValue($index + 1, $value, $parameterType);
                    }
                    $statement->execute();
                }
            }
        });
    }

    /**
     * @param list<string> $tables
     */
    private function assertCompatibleSchemas(
        \Illuminate\Database\Connection $source,
        \Illuminate\Database\Connection $target,
        array $tables
    ): void {
        foreach ($tables as $table) {
            if (!$target->getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException("Tabel tujuan {$table} tidak tersedia setelah inisialisasi schema.");
            }

            $sourceColumns = $source->getSchemaBuilder()->getColumnListing($table);
            $targetColumns = $target->getSchemaBuilder()->getColumnListing($table);
            $missingColumns = array_diff($sourceColumns, $targetColumns);

            if ($missingColumns !== []) {
                throw new RuntimeException(sprintf(
                    'Tabel tujuan %s belum kompatibel; kolom belum ada: %s.',
                    $table,
                    implode(', ', $missingColumns)
                ));
            }
        }
    }
}
