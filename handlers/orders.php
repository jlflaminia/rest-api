<?php

/**
 * Handler: Orders
 *
 * GET    /api/orders          — list orders with items (auth required)
 * GET    /api/orders/{id}     — get single order with items (auth required)
 * POST   /api/orders          — place new order (auth required)
 * PATCH  /api/orders/{id}     — update order status (admin only)
 */

function handleOrders(PDO $pdo, string $method, ?int $id, array $body): void
{
    $currentUser = requireAuth($pdo);

    switch ($method) {

        /* ── GET ── */
        case 'GET':
            if ($id) {
                // Single order with full item breakdown
                $sql = '
                    SELECT o.id AS order_id, o.status, o.total, o.created_at,
                           u.id AS user_id, u.name AS customer, u.email,
                           oi.id AS item_id, oi.quantity, oi.unit_price,
                           p.id AS product_id, p.name AS product_name, p.category
                    FROM   orders o
                    JOIN   users       u  ON u.id  = o.user_id
                    JOIN   order_items oi ON oi.order_id   = o.id
                    JOIN   products    p  ON p.id  = oi.product_id
                    WHERE  o.id = :id
                ';
                // Non-admins can only see their own orders
                if ($currentUser['role'] !== 'admin') {
                    $sql .= ' AND o.user_id = :uid';
                }

                $params = [':id' => $id];
                if ($currentUser['role'] !== 'admin') {
                    $params[':uid'] = (int) $currentUser['user_id'];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();

                if (empty($rows)) {
                    respond(404, null, 'Order not found');
                }
                respond(200, groupOrderRows($rows)[0]);
            } else {
                // All orders (admin sees all; user sees own)
                $sql = '
                    SELECT o.id AS order_id, o.status, o.total, o.created_at,
                           u.id AS user_id, u.name AS customer, u.email,
                           oi.id AS item_id, oi.quantity, oi.unit_price,
                           p.id AS product_id, p.name AS product_name, p.category
                    FROM   orders o
                    JOIN   users       u  ON u.id  = o.user_id
                    JOIN   order_items oi ON oi.order_id   = o.id
                    JOIN   products    p  ON p.id  = oi.product_id
                ';

                $params = [];
                if ($currentUser['role'] !== 'admin') {
                    $sql           .= ' WHERE o.user_id = :uid';
                    $params[':uid'] = (int) $currentUser['user_id'];
                }
                $sql .= ' ORDER BY o.id DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                respond(200, groupOrderRows($stmt->fetchAll()));
            }
            break;

        /* ── POST ── */
        case 'POST':
            // Expected body: { "items": [{ "product_id": 1, "quantity": 2 }, ...] }
            if (empty($body['items']) || !is_array($body['items'])) {
                respond(400, null, 'items[] array is required');
            }

            $pdo->beginTransaction();
            try {
                $total   = 0.0;
                $userId  = (int) $currentUser['user_id'];

                // Validate products and calculate total from real DB prices
                $lineItems = [];
                foreach ($body['items'] as $item) {
                    if (empty($item['product_id']) || empty($item['quantity'])) {
                        respond(400, null, 'Each item needs product_id and quantity');
                    }
                    $qty = (int) $item['quantity'];
                    if ($qty < 1) respond(400, null, 'Quantity must be at least 1');

                    $s = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = :id');
                    $s->execute([':id' => (int) $item['product_id']]);
                    $product = $s->fetch();

                    if (!$product) {
                        respond(404, null, "Product ID {$item['product_id']} not found");
                    }
                    if ($product['stock'] < $qty) {
                        respond(409, null, "Insufficient stock for '{$product['name']}'");
                    }

                    $lineItems[] = [
                        'product_id' => (int) $product['id'],
                        'quantity'   => $qty,
                        'unit_price' => (float) $product['price'],
                    ];
                    $total += (float) $product['price'] * $qty;
                }

                // Insert order header
                $s = $pdo->prepare(
                    'INSERT INTO orders (user_id, total) VALUES (:uid, :total)'
                );
                $s->execute([':uid' => $userId, ':total' => round($total, 2)]);
                $orderId = (int) $pdo->lastInsertId();

                // Insert line items & decrement stock
                $insItem = $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                     VALUES (:oid, :pid, :qty, :price)'
                );
                $updStock = $pdo->prepare(
                    'UPDATE products SET stock = stock - :qty WHERE id = :id'
                );

                foreach ($lineItems as $li) {
                    $insItem->execute([
                        ':oid'   => $orderId,
                        ':pid'   => $li['product_id'],
                        ':qty'   => $li['quantity'],
                        ':price' => $li['unit_price'],
                    ]);
                    $updStock->execute([
                        ':qty' => $li['quantity'],
                        ':id'  => $li['product_id'],
                    ]);
                }

                $pdo->commit();
                respond(201, [
                    'order_id' => $orderId,
                    'total'    => round($total, 2),
                    'status'   => 'pending',
                ]);

            } catch (\Throwable $e) {
                $pdo->rollBack();
                error_log('[Orders] Transaction failed: ' . $e->getMessage());
                respond(500, null, 'Order could not be placed. Please try again.');
            }
            break;

        /* ── PATCH (admin: update status) ── */
        case 'PATCH':
            requireAdmin($pdo);
            if (!$id) respond(400, null, 'Order ID required in URL');

            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (empty($body['status']) || !in_array($body['status'], $validStatuses)) {
                respond(400, null, 'Valid status required: ' . implode(', ', $validStatuses));
            }

            $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $stmt->execute([':status' => $body['status'], ':id' => $id]);
            $stmt->rowCount()
                ? respond(200, ['updated' => true, 'status' => $body['status']])
                : respond(404, null, 'Order not found');
            break;

        default:
            respond(405, null, 'Method not allowed');
    }
}

/**
 * Transforms flat JOIN rows into nested order objects.
 */
function groupOrderRows(array $rows): array
{
    $orders = [];
    foreach ($rows as $r) {
        $oid = $r['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'id'         => (int) $r['order_id'],
                'customer'   => $r['customer'],
                'email'      => $r['email'],
                'status'     => $r['status'],
                'total'      => (float) $r['total'],
                'created_at' => $r['created_at'],
                'items'      => [],
            ];
        }
        $orders[$oid]['items'][] = [
            'item_id'      => (int) $r['item_id'],
            'product_id'   => (int) $r['product_id'],
            'product_name' => $r['product_name'],
            'category'     => $r['category'],
            'quantity'     => (int) $r['quantity'],
            'unit_price'   => (float) $r['unit_price'],
            'subtotal'     => round((float) $r['unit_price'] * (int) $r['quantity'], 2),
        ];
    }
    return array_values($orders);
}
