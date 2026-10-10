<?php
/**
 * Database Connection using PDO Singleton
 * Lost & Found SMK Informatika Sumedang
 */

class Database {
    private static ?PDO $instance = null;

    private static string $charset = 'utf8mb4';

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = self::connectionConfig();
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=" . self::$charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Return a friendly error message or JSON if in API
                if (defined('IS_API') && IS_API === true) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database connection error: ' . $e->getMessage(),
                        'data' => null
                    ]);
                    exit;
                }
                die("Koneksi Database Gagal: " . htmlspecialchars($e->getMessage()) . ". Pastikan MySQL XAMPP sudah berjalan dan database 'lost_found' sudah dibuat.");
            }
        }

        return self::$instance;
    }

    public static function getRawConnection(): PDO {
        $config = self::connectionConfig();
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=" . self::$charset;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        return new PDO($dsn, $config['username'], $config['password'], $options);
    }

    private static function environment(string $key, string $default): string {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null || $value === ''
            ? $default
            : (string) $value;
    }

    /**
     * @return array{host: string, port: string, database: string, username: string, password: string}
     */
    private static function connectionConfig(): array {
        $url = self::environment(
            'DB_URL',
            self::environment('MYSQL_URL', '')
        );

        if ($url !== '') {
            $parts = parse_url($url);
            if (
                !is_array($parts)
                || !in_array(strtolower($parts['scheme'] ?? ''), ['mysql', 'mariadb'], true)
                || empty($parts['host'])
                || empty($parts['path'])
            ) {
                throw new RuntimeException('DB_URL must be a valid MySQL connection URL.');
            }

            return [
                'host' => (string) $parts['host'],
                'port' => (string) ($parts['port'] ?? 3306),
                'database' => rawurldecode(ltrim((string) $parts['path'], '/')),
                'username' => rawurldecode((string) ($parts['user'] ?? 'root')),
                'password' => rawurldecode((string) ($parts['pass'] ?? '')),
            ];
        }

        return [
            'host' => self::environment('DB_HOST', self::environment('MYSQLHOST', '127.0.0.1')),
            'port' => self::environment('DB_PORT', self::environment('MYSQLPORT', '3306')),
            'database' => self::environment('DB_DATABASE', self::environment('MYSQLDATABASE', 'lost_found')),
            'username' => self::environment('DB_USERNAME', self::environment('MYSQLUSER', 'root')),
            'password' => self::environment('DB_PASSWORD', self::environment('MYSQLPASSWORD', '')),
        ];
    }
}
