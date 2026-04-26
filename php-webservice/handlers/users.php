<?php

/**
 * Handler: Users
 *
 * GET    /api/users          — list all users        (admin only)
 * GET    /api/users/{id}     — get single user       (admin only)
 * POST   /api/users          — create user           (admin only)
 * PATCH  /api/users/{id}     — update user fields    (admin only)
 * DELETE /api/users/{id}     — delete user           (admin only)
 */

function handleUsers(PDO $pdo, string $method, ?int $id, array $body): void
{
    // All user management requires admin role
    requireAdmin($pdo);

    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare(
                    'SELECT id, name, email, role, created_at, updated_at
                       FROM users
                      WHERE id = :id'
                );
                $stmt->execute([':id' => $id]);
                $user = $stmt->fetch();
                $user
                    ? respond(200, $user)
                    : respond(404, null, 'User not found');
            } else {
                $stmt = $pdo->query(
                    'SELECT id, name, email, role, created_at FROM users ORDER BY id'
                );
                respond(200, $stmt->fetchAll());
            }
            break;

        /* ── POST ── */
        case 'POST':
            $errors = [];
            if (empty($body['name']))     $errors[] = 'name is required';
            if (empty($body['email']))    $errors[] = 'email is required';
            if (empty($body['password'])) $errors[] = 'password is required';
            if ($errors) respond(400, null, implode(', ', $errors));

            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                respond(400, null, 'Invalid email address');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password, role)
                 VALUES (:name, :email, :password, :role)'
            );

            try {
                $stmt->execute([
                    ':name'     => trim($body['name']),
                    ':email'    => strtolower(trim($body['email'])),
                    ':password' => password_hash($body['password'], PASSWORD_BCRYPT),
                    ':role'     => in_array($body['role'] ?? '', ['admin', 'user'])
                                    ? $body['role'] : 'user',
                ]);
                respond(201, ['id' => (int) $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    respond(409, null, 'Email already registered');
                }
                throw $e;
            }
            break;

        /* ── PATCH ── */
        case 'PATCH':
            if (!$id) respond(400, null, 'User ID required in URL');

            $allowed = ['name', 'email', 'role']; // whitelist — no direct password patch here
            $fields  = [];
            $params  = [':id' => $id];

            foreach ($allowed as $field) {
                if (isset($body[$field])) {
                    $fields[]          = "{$field} = :{$field}";
                    $params[":{$field}"] = trim($body[$field]);
                }
            }

            if (empty($fields)) respond(400, null, 'No updatable fields provided');

            $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $pdo->prepare($sql)->execute($params);
            respond(200, ['updated' => true]);
            break;

        /* ── DELETE ── */
        case 'DELETE':
            if (!$id) respond(400, null, 'User ID required in URL');

            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $stmt->rowCount()
                ? respond(200, ['deleted' => true])
                : respond(404, null, 'User not found');
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}
