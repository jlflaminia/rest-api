<?php

/**
 * Helpers: Authentication
 * Token-based auth backed by the api_tokens MySQL table.
 */

/**
 * Require a valid Bearer token. Returns the authenticated user row.
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
        'SELECT t.user_id, u.name, u.email, u.role
           FROM api_tokens t
           JOIN users u ON u.id = t.user_id
          WHERE t.token      = :token
            AND t.expires_at > NOW()
          LIMIT 1'
    );
    $stmt->execute([':token' => $rawToken]);
    $user = $stmt->fetch();

    if (!$user) {
        respond(401, null, 'Invalid or expired token');
    }

    return $user;
}

/**
 * Require an authenticated admin user.
 */
function requireAdmin(PDO $pdo): array
{
    $user = requireAuth($pdo);
    if ($user['role'] !== 'admin') {
        respond(403, null, 'Admin access required');
    }
    return $user;
}
