<?php

/**
 * Helpers: Authentication
 * Token-based auth backed by the api_tokens MySQL table.
 */

/**
 * Require a valid Bearer token. Returns the authenticated admin row.
 * Calls respond(401) and exits if authentication fails.
 */
function requireAuth(PDO $pdo): array
{
    $headers = getallheaders();

    // Header name may be capitalised differently depending on server
    $auth = $headers['Authorization']
         ?? $headers['authorization']
         ?? $_SERVER['HTTP_AUTHORIZATION']
         ?? '';

    if (!str_starts_with($auth, 'Bearer ')) {
        respond(401, null, 'Missing or malformed Authorization header');
    }

    $rawToken = substr($auth, 7);

    $stmt = $pdo->prepare(
        'SELECT t.user_id, a.username, a.full_name, a.email, a.role
           FROM api_tokens t
           JOIN admins a ON a.id = t.user_id
          WHERE t.token      = :token
            AND t.expires_at > NOW()
          LIMIT 1'
    );
    $stmt->execute([':token' => $rawToken]);
    $admin = $stmt->fetch();

    if (!$admin) {
        respond(401, null, 'Invalid or expired token');
    }

    return $admin;
}

/**
 * Require an authenticated admin user.
 */
function requireAdmin(PDO $pdo): array
{
    $admin = requireAuth($pdo);
    if ($admin['role'] !== 'admin' && $admin['role'] !== 'superadmin') {
        respond(403, null, 'Admin access required');
    }
    return $admin;
}