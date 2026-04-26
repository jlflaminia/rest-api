-- ============================================================
--  PHP Web Services — Sample Database
--  Database : webservice_db
--  Import   : mysql -u root -p < webservice_db.sql
--             OR via phpMyAdmin > Import
-- ============================================================

CREATE DATABASE IF NOT EXISTS webservice_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webservice_db;

-- ------------------------------------------------------------
--  Table: users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS api_tokens;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)    NOT NULL,
    email      VARCHAR(150)    NOT NULL,
    password   VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table: products
-- ------------------------------------------------------------
CREATE TABLE products (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name        VARCHAR(150)     NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    stock       INT UNSIGNED     NOT NULL DEFAULT 0,
    category    VARCHAR(80),
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table: orders
-- ------------------------------------------------------------
CREATE TABLE orders (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED    NOT NULL,
    status     ENUM('pending','processing','shipped','delivered','cancelled')
               NOT NULL DEFAULT 'pending',
    total      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table: order_items
-- ------------------------------------------------------------
CREATE TABLE order_items (
    id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    order_id   INT UNSIGNED   NOT NULL,
    product_id INT UNSIGNED   NOT NULL,
    quantity   INT UNSIGNED   NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_order_id   (order_id),
    INDEX idx_product_id (product_id),
    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table: api_tokens
-- ------------------------------------------------------------
CREATE TABLE api_tokens (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED  NOT NULL,
    token      VARCHAR(64)   NOT NULL,
    expires_at TIMESTAMP     NOT NULL,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SAMPLE DATA
-- ============================================================

-- Passwords are bcrypt hashes of "password123"
INSERT INTO users (name, email, password, role) VALUES
    ('Alice Reyes',   'alice@example.com',  '$2y$12$K8VfFqH3xW7VpLq9ZlY.neM8T6lAbMzNQ3nVQ1kLPVBi3dJz4r7o6', 'admin'),
    ('Bob Santos',    'bob@example.com',    '$2y$12$K8VfFqH3xW7VpLq9ZlY.neM8T6lAbMzNQ3nVQ1kLPVBi3dJz4r7o6', 'user'),
    ('Carol Mendoza', 'carol@example.com',  '$2y$12$K8VfFqH3xW7VpLq9ZlY.neM8T6lAbMzNQ3nVQ1kLPVBi3dJz4r7o6', 'user'),
    ('Dan Cruz',      'dan@example.com',    '$2y$12$K8VfFqH3xW7VpLq9ZlY.neM8T6lAbMzNQ3nVQ1kLPVBi3dJz4r7o6', 'user'),
    ('Eve Lim',       'eve@example.com',    '$2y$12$K8VfFqH3xW7VpLq9ZlY.neM8T6lAbMzNQ3nVQ1kLPVBi3dJz4r7o6', 'user');

INSERT INTO products (name, description, price, stock, category) VALUES
    ('Wireless Mouse',      'Ergonomic 2.4GHz wireless mouse',             699.00,  50, 'Peripherals'),
    ('Mechanical Keyboard', 'TKL mechanical keyboard with blue switches', 1299.00,  30, 'Peripherals'),
    ('USB-C Hub',           '7-in-1 USB-C hub with HDMI and SD card',      899.00,  75, 'Accessories'),
    ('Webcam 1080p',        'Full HD webcam with built-in microphone',    1599.00,  20, 'Peripherals'),
    ('Laptop Stand',        'Adjustable aluminium laptop stand',           549.00, 100, 'Accessories'),
    ('HDMI Cable 2m',       'High-speed HDMI 2.0 cable, 2 meters',        199.00, 200, 'Cables'),
    ('SSD 1TB',             'NVMe M.2 SSD 1TB, 3500MB/s read',           3499.00,  15, 'Storage'),
    ('RAM 16GB DDR4',       '16GB DDR4 3200MHz desktop RAM',             1899.00,  25, 'Memory');

INSERT INTO orders (user_id, status, total) VALUES
    (2, 'delivered',  1898.00),
    (2, 'processing', 3499.00),
    (3, 'pending',    2198.00),
    (4, 'shipped',    4798.00),
    (5, 'cancelled',   199.00);

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
    (1, 1, 1,  699.00),
    (1, 5, 2,  549.00),
    (2, 7, 1, 3499.00),
    (3, 2, 1, 1299.00),
    (3, 1, 1,  699.00),
    (3, 6, 1,  199.00),
    (4, 8, 1, 1899.00),
    (4, 4, 1, 1599.00),
    (4, 3, 1,  899.00),
    (4, 6, 2,  199.00),
    (5, 6, 1,  199.00);

-- Sample tokens (expire 1 year from import)
INSERT INTO api_tokens (user_id, token, expires_at) VALUES
    (1, 'aa6564061ea6f51b13a3501acee50fa3f72284a50e6c74f4526329523f0f0486', DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    (2, '9e4117c39b8af6bbd1521de183b9c2cb53692a0d08c2dcc661658747cb370c68', DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- ============================================================
--  View: v_order_summary
-- ============================================================
CREATE OR REPLACE VIEW v_order_summary AS
SELECT
    o.id          AS order_id,
    u.name        AS customer,
    u.email       AS customer_email,
    o.status,
    o.total,
    COUNT(oi.id)  AS item_count,
    o.created_at
FROM orders o
JOIN users       u  ON u.id = o.user_id
JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id, u.name, u.email, o.status, o.total, o.created_at;

-- ============================================================
--  END OF SCRIPT
-- ============================================================
