<?php

/**
 * PHP Web Services — Main Router
 * ================================
 * ACLC API - Student Information Management System
 *
 * Endpoints:
 *   POST   /api/auth/login
 *   POST   /api/auth/logout
 *   GET    /api/admins
 *   GET    /api/admins/{id}
 *   POST   /api/admins
 *   PATCH  /api/admins/{id}
 *   DELETE /api/admins/{id}
 *   GET    /api/bse-students
 *   GET    /api/bse-students/{id}
 *   POST   /api/bse-students
 *   PATCH  /api/bse-students/{id}
 *   DELETE /api/bse-students/{id}
 *   GET    /api/bsis-students
 *   GET    /api/bsis-students/{id}
 *   POST   /api/bsis-students
 *   PATCH  /api/bsis-students/{id}
 *   DELETE /api/bsis-students/{id}
 *   GET    /api/removed-students
 *   GET    /api/removed-students/{id}
 *   DELETE /api/removed-students/{id}
 */

declare(strict_types=1);

// ── Global headers ─────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Autoload helpers ────────────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/http.php';

// ── Parse request ───────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$rawUri = $_SERVER['REQUEST_URI'];

// Strip query string and leading slash, then remove script sub-folder prefix
$path   = trim(parse_url($rawUri, PHP_URL_PATH), '/');

// Remove the project folder prefix so routes work regardless of subfolder name
$parts = explode('/', $path);
$apiIndex = array_search('api', $parts);
if ($apiIndex === false) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Not found']);
    exit;
}
$parts = array_slice($parts, $apiIndex); // ['api', 'resource', 'id?']

$segment  = $parts[1] ?? '';     // resource name
$subSeg   = $parts[2] ?? '';     // optional sub-resource (e.g. "login")
$idRaw    = is_numeric($parts[2] ?? '') ? $parts[2] : null;
$id       = $idRaw !== null ? (int) $idRaw : null;

// Raw request body (JSON)
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Database connection ──────────────────────────────────────────────────────
$pdo = Database::connect();

// ── Route dispatch ───────────────────────────────────────────────────────────
switch ($segment) {

    case 'auth':
        require_once __DIR__ . '/handlers/auth.php';
        match ($subSeg) {
            'login'  => handleAuthLogin($pdo, $body),
            'logout' => handleAuthLogout($pdo),
            default  => respond(404, null, 'Auth route not found'),
        };
        break;

    case 'admins':
        require_once __DIR__ . '/handlers/admins.php';
        handleAdmins($pdo, $method, $id, $body);
        break;

    case 'bse-students':
        require_once __DIR__ . '/handlers/bse_students.php';
        handleBseStudents($pdo, $method, $id, $body);
        break;

    case 'bsis-students':
        require_once __DIR__ . '/handlers/bsis_students.php';
        handleBsisStudents($pdo, $method, $id, $body);
        break;

    case 'removed-students':
        require_once __DIR__ . '/handlers/removed_students.php';
        handleRemovedStudents($pdo, $method, $id, $body);
        break;

    default:
        respond(404, null, "Unknown resource: '{$segment}'");
}