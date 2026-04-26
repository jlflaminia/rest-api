<?php
/**
 * Debug helper — DELETE THIS FILE before going to production!
 * Access via: http://localhost/php-webservice/debug.php
 */

header('Content-Type: application/json; charset=UTF-8');

$report = [];

// ── 1. PHP version ────────────────────────────────────────────────────────
$report['php_version']   = PHP_VERSION;
$report['php_ok']        = version_compare(PHP_VERSION, '8.0', '>=');

// ── 2. PDO & MySQL driver ─────────────────────────────────────────────────
$report['pdo_loaded']    = extension_loaded('pdo');
$report['pdo_mysql']     = extension_loaded('pdo_mysql');
$report['pdo_drivers']   = PDO::getAvailableDrivers();

// ── 3. .env file ──────────────────────────────────────────────────────────
$envPath = __DIR__ . '/.env';
$report['env_file_exists'] = file_exists($envPath);
$report['env_file_path']   = $envPath;

// Parse .env manually
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $env[$k] = $v;
    }
}

$report['env_loaded'] = $env;

// Mask password
if (isset($report['env_loaded']['DB_PASS'])) {
    $report['env_loaded']['DB_PASS'] = $report['env_loaded']['DB_PASS'] === '' ? '(empty)' : '(set)';
}

// ── 4. Attempt DB connection ──────────────────────────────────────────────
$host   = $env['DB_HOST'] ?? '127.0.0.1';
$port   = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_NAME'] ?? 'webservice_db';
$user   = $env['DB_USER'] ?? 'root';
$pass   = $env['DB_PASS'] ?? 'ev123sql$%^';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$report['dsn_used'] = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $report['db_connection'] = 'SUCCESS';

    // Check tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $report['tables_found'] = $tables;

    // Check row counts
    $counts = [];
    foreach (['users','products','orders','order_items','api_tokens'] as $t) {
        if (in_array($t, $tables)) {
            $counts[$t] = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        } else {
            $counts[$t] = 'TABLE MISSING';
        }
    }
    $report['row_counts'] = $counts;

} catch (PDOException $e) {
    $report['db_connection'] = 'FAILED';
    $report['db_error']      = $e->getMessage();
    $report['db_error_code'] = $e->getCode();

    // Common error hints
    $msg = $e->getMessage();
    if (str_contains($msg, 'Access denied')) {
        $report['hint'] = "Wrong DB_USER or DB_PASS in .env. Default XAMPP is user=root, pass=(empty).";
    } elseif (str_contains($msg, "Unknown database")) {
        $report['hint'] = "Database '{$dbname}' does not exist. Import database/webservice_db.sql first.";
    } elseif (str_contains($msg, 'Connection refused') || str_contains($msg, "Can't connect")) {
        $report['hint'] = "MySQL is not running. Start it in the XAMPP Control Panel.";
    } elseif (str_contains($msg, 'php_network_getaddresses')) {
        $report['hint'] = "Cannot resolve DB_HOST '{$host}'. Try changing DB_HOST to 127.0.0.1.";
    }
}

// ── 5. mod_rewrite ────────────────────────────────────────────────────────
$report['mod_rewrite'] = function_exists('apache_get_modules')
    ? (in_array('mod_rewrite', apache_get_modules()) ? 'enabled' : 'DISABLED — routing will not work!')
    : 'unknown (non-Apache or CLI)';

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
