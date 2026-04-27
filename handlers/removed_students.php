<?php

/**
 * Handler: Removed Students (Archive/Trash)
 *
 * GET    /api/removed-students        — list removed students      (admin only)
 * GET    /api/removed-students/{id} — get single removed      (admin only)
 * DELETE /api/removed-students/{id} — permanently delete    (admin only)
 */

function handleRemovedStudents(PDO $pdo, string $method, ?int $id, array $body): void
{
    requireAdmin($pdo);

    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare('SELECT * FROM removed_students WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $student = $stmt->fetch();
                $student
                    ? respond(200, $student)
                    : respond(404, null, 'Removed student not found');
            } else {
                $stmt = $pdo->query('SELECT * FROM removed_students ORDER BY removed_at DESC');
                respond(200, $stmt->fetchAll());
            }
            break;

        /* ── DELETE (permanent delete) ── */
        case 'DELETE':
            if (!$id) respond(400, null, 'ID required in URL');

            $stmt = $pdo->prepare('DELETE FROM removed_students WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $stmt->rowCount()
                ? respond(200, ['deleted' => true])
                : respond(404, null, 'Removed student not found');
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}