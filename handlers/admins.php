<?php

/**
 * Handler: Admins (formerly Users)
 *
 * GET    /api/admins         — list all admins       (admin only)
 * GET    /api/admins/{id}    — get single admin      (admin only)
 * POST   /api/admins        — create admin         (admin only)
 * PATCH  /api/admins/{id}   — update admin fields  (admin only)
 * DELETE /api/admins/{id}    — delete admin         (admin only)
 */

function handleAdmins(PDO $pdo, string $method, ?int $id, array $body): void
{
    requireAdmin($pdo);

    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare(
                    'SELECT id, username, email, full_name, role, avatar, created_at, updated_at
                       FROM admins
                      WHERE id = :id'
                );
                $stmt->execute([':id' => $id]);
                $admin = $stmt->fetch();
                $admin
                    ? respond(200, $admin)
                    : respond(404, null, 'Admin not found');
            } else {
                $stmt = $pdo->query(
                    'SELECT id, username, email, full_name, role, created_at FROM admins ORDER BY id'
                );
                respond(200, $stmt->fetchAll());
            }
            break;

        /* ── POST ── */
        case 'POST':
            $errors = [];
            if (empty($body['username']))   $errors[] = 'username is required';
            if (empty($body['email']))      $errors[] = 'email is required';
            if (empty($body['password']))   $errors[] = 'password is required';
            if (empty($body['full_name'])) $errors[] = 'full_name is required';
            if ($errors) respond(400, null, implode(', ', $errors));

            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                respond(400, null, 'Invalid email address');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO admins (username, email, full_name, password_hash, role, avatar)
                 VALUES (:username, :email, :full_name, :password_hash, :role, :avatar)'
            );

            try {
                $stmt->execute([
                    ':username'    => trim($body['username']),
                    ':email'      => strtolower(trim($body['email'])),
                    ':full_name'  => trim($body['full_name']),
                    ':password_hash' => password_hash($body['password'], PASSWORD_BCRYPT),
                    ':role'      => in_array($body['role'] ?? '', ['admin', 'superadmin'])
                                    ? $body['role'] : 'admin',
                    ':avatar'    => $body['avatar'] ?? null,
                ]);
                respond(201, ['id' => (int) $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    respond(409, null, 'Username or email already registered');
                }
                throw $e;
            }
            break;

        /* ── PATCH ── */
        case 'PATCH':
            if (!$id) respond(400, null, 'Admin ID required in URL');

            $allowed = ['username', 'email', 'full_name', 'role', 'avatar'];
            $fields  = [];
            $params  = [':id' => $id];

            foreach ($allowed as $field) {
                if (isset($body[$field])) {
                    $fields[]            = "{$field} = :{$field}";
                    $params[":{$field}"] = trim($body[$field]);
                }
            }

            if (empty($fields)) respond(400, null, 'No updatable fields provided');

            $sql = 'UPDATE admins SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $pdo->prepare($sql)->execute($params);
            respond(200, ['updated' => true]);
            break;

        /* ── DELETE ── */
        case 'DELETE':
            if (!$id) respond(400, null, 'Admin ID required in URL');

            $stmt = $pdo->prepare('DELETE FROM admins WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $stmt->rowCount()
                ? respond(200, ['deleted' => true])
                : respond(404, null, 'Admin not found');
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}