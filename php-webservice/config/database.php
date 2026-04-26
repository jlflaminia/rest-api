<?php

/**
 * Database Configuration
 * Loads credentials from .env and returns a singleton PDO instance.
 */

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Load .env manually (no Composer required for XAMPP convenience)
        self::loadEnv(__DIR__ . '/../.env');

        $host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port    = $_ENV['DB_PORT'] ?? '3306';
        $dbname  = $_ENV['DB_NAME'] ?? 'webservice_db';
        $user    = $_ENV['DB_USER'] ?? 'root';
        $pass    = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            http_response_code(503);

            // Show real error in development — change APP_ENV=production to hide it
            $isDev   = ($_ENV['APP_ENV'] ?? 'development') === 'development';
            $message = $isDev
                ? 'DB Error: ' . $e->getMessage() . ' | DSN: ' . $dsn . ' | User: ' . $user
                : 'Database unavailable';

            echo json_encode(['status' => 'error', 'message' => $message]);
            exit;
        }

        return self::$instance;
    }

    /**
     * Minimal .env parser — no Composer dependency needed.
     */
    private static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value]   = array_map('trim', explode('=', $line, 2));
            $_ENV[$key]      = $value;
            putenv("{$key}={$value}");
        }
    }
}
