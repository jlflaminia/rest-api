<?php

/**
 * Handler: Products
 *
 * GET    /api/products                        — list all products (public)
 * GET    /api/products?category=Peripherals   — filter by category (public)
 * GET    /api/products/{id}                   — get single product (public)
 * POST   /api/products                        — create product (admin)
 * PATCH  /api/products/{id}                   — update product (admin)
 * DELETE /api/products/{id}                   — delete product (admin)
 */

function handleProducts(PDO $pdo, string $method, ?int $id, array $body): void
{
    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $product = $stmt->fetch();
                $product
                    ? respond(200, $product)
                    : respond(404, null, 'Product not found');
            } else {
                $category = $_GET['category'] ?? null;
                $search   = $_GET['search']   ?? null;

                $sql    = 'SELECT * FROM products WHERE 1=1';
                $params = [];

                if ($category) {
                    $sql           .= ' AND category = :category';
                    $params[':category'] = $category;
                }
                if ($search) {
                    $sql             .= ' AND name LIKE :search';
                    $params[':search'] = '%' . $search . '%';
                }
                $sql .= ' ORDER BY name';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                respond(200, $stmt->fetchAll());
            }
            break;

        /* ── POST ── */
        case 'POST':
            requireAdmin($pdo);

            $errors = [];
            if (empty($body['name']))           $errors[] = 'name is required';
            if (!isset($body['price']))          $errors[] = 'price is required';
            if (!isset($body['stock']))          $errors[] = 'stock is required';
            if ($errors) respond(400, null, implode(', ', $errors));

            if (!is_numeric($body['price']) || $body['price'] < 0) {
                respond(400, null, 'price must be a non-negative number');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO products (name, description, price, stock, category)
                 VALUES (:name, :description, :price, :stock, :category)'
            );
            $stmt->execute([
                ':name'        => trim($body['name']),
                ':description' => $body['description'] ?? null,
                ':price'       => round((float) $body['price'], 2),
                ':stock'       => (int) $body['stock'],
                ':category'    => $body['category'] ?? null,
            ]);
            respond(201, ['id' => (int) $pdo->lastInsertId()]);
            break;

        /* ── PATCH ── */
        case 'PATCH':
            requireAdmin($pdo);
            if (!$id) respond(400, null, 'Product ID required in URL');

            $allowed = ['name', 'description', 'price', 'stock', 'category'];
            $fields  = [];
            $params  = [':id' => $id];

            foreach ($allowed as $field) {
                if (array_key_exists($field, $body)) {
                    $fields[]            = "{$field} = :{$field}";
                    $params[":{$field}"] = $body[$field];
                }
            }

            if (empty($fields)) respond(400, null, 'No updatable fields provided');

            $pdo->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id')
                ->execute($params);
            respond(200, ['updated' => true]);
            break;

        /* ── DELETE ── */
        case 'DELETE':
            requireAdmin($pdo);
            if (!$id) respond(400, null, 'Product ID required in URL');

            try {
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $stmt->rowCount()
                    ? respond(200, ['deleted' => true])
                    : respond(404, null, 'Product not found');
            } catch (PDOException $e) {
                // FK constraint: product is referenced by order_items
                if ($e->getCode() === '23000') {
                    respond(409, null, 'Cannot delete — product is used in existing orders');
                }
                throw $e;
            }
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}
