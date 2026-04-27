<?php

/**
 * Handler: BSIS Students
 *
 * GET    /api/bsis-students          — list all BSIS students      (admin only)
 * GET    /api/bsis-students/{id}    — get single student       (admin only)
 * POST   /api/bsis-students         — create student         (admin only)
 * PATCH  /api/bsis-students/{id}    — update student fields (admin only)
 * DELETE /api/bsis-students/{id}    — delete student         (admin only)
 */

function handleBsisStudents(PDO $pdo, string $method, ?int $id, array $body): void
{
    requireAdmin($pdo);

    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare('SELECT * FROM bsis_students WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $student = $stmt->fetch();
                $student
                    ? respond(200, $student)
                    : respond(404, null, 'Student not found');
            } else {
                $search  = $_GET['search']   ?? null;
                $year    = $_GET['year']     ?? null;

                $sql    = 'SELECT * FROM bsis_students WHERE 1=1';
                $params = [];

                if ($search) {
                    $sql           .= ' AND (name LIKE :search OR student_id LIKE :search)';
                    $params[':search'] = '%' . $search . '%';
                }
                if ($year) {
                    $sql         .= ' AND year_level = :year';
                    $params[':year'] = $year;
                }
                $sql .= ' ORDER BY name';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                respond(200, $stmt->fetchAll());
            }
            break;

        /* ── POST ── */
        case 'POST':
            $errors = [];
            if (empty($body['student_id'])) $errors[] = 'student_id is required';
            if (empty($body['name']))     $errors[] = 'name is required';
            if ($errors) respond(400, null, implode(', ', $errors));

            $stmt = $pdo->prepare(
                'INSERT INTO bsis_students (student_id, name, program, year_level, gmail)
                 VALUES (:student_id, :name, :program, :year_level, :gmail)'
            );

            try {
                $stmt->execute([
                    ':student_id' => trim($body['student_id']),
                    ':name'     => trim($body['name']),
                    ':program'  => $body['program'] ?? 'BSIS',
                    ':year_level' => $body['year_level'] ?? '',
                    ':gmail'    => $body['gmail'] ?? '',
                ]);
                respond(201, ['id' => (int) $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    respond(409, null, 'Student ID already exists');
                }
                throw $e;
            }
            break;

        /* ── PATCH ── */
        case 'PATCH':
            if (!$id) respond(400, null, 'Student ID required in URL');

            $allowed = [
                'student_id', 'name', 'program', 'year_level', 'gmail',
                'downpayment_date', 'prelim_date', 'midterm_date', 'prefinal_date', 'final_date', 'total_balance_date',
                'downpayment_paid_amount', 'prelim_paid_amount', 'midterm_paid_amount', 'prefinal_paid_amount', 'final_paid_amount', 'total_balance_paid_amount'
            ];
            $fields  = [];
            $params  = [':id' => $id];

            foreach ($allowed as $field) {
                if (array_key_exists($field, $body)) {
                    $fields[]            = "{$field} = :{$field}";
                    $params[":{$field}"] = $body[$field];
                }
            }

            if (empty($fields)) respond(400, null, 'No updatable fields provided');

            $sql = 'UPDATE bsis_students SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $pdo->prepare($sql)->execute($params);
            respond(200, ['updated' => true]);
            break;

        /* ── DELETE ── */
        case 'DELETE':
            if (!$id) respond(400, null, 'Student ID required in URL');

            $stmt = $pdo->prepare('DELETE FROM bsis_students WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $stmt->rowCount()
                ? respond(200, ['deleted' => true])
                : respond(404, null, 'Student not found');
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}