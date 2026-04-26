# PHP Web Services — XAMPP Project

A complete REST API built with PHP and MySQL, designed for the **PHP Web Services** interactive lesson.

---

## 📁 Folder Structure

```
php-webservice/
├── .env                    ← DB credentials (edit this!)
├── .env.example            ← Template for .env
├── .htaccess               ← Apache URL rewriting
├── index.php               ← Main router / front controller
├── README.md
│
├── config/
│   └── database.php        ← PDO singleton connection
│
├── database/
│   └── webservice_db.sql   ← Full DB schema + sample data
│
├── handlers/
│   ├── auth.php            ← POST /api/auth/login|logout
│   ├── users.php           ← CRUD /api/users
│   ├── products.php        ← CRUD /api/products
│   └── orders.php          ← CRUD /api/orders
│
├── helpers/
│   ├── response.php        ← respond() helper
│   ├── auth.php            ← requireAuth() / requireAdmin()
│   └── http.php            ← HttpClient (cURL wrapper)
│
└── public/
    └── test-client.html    ← Browser-based API test UI
```

---

## 🚀 Setup in XAMPP (Step by Step)

### 1. Copy to htdocs
```
Copy the entire `php-webservice/` folder into:
  Windows : C:\xampp\htdocs\
  macOS   : /Applications/XAMPP/htdocs/
  Linux   : /opt/lampp/htdocs/
```

### 2. Import the database
Open **phpMyAdmin** → `http://localhost/phpmyadmin`
- Click **Import**
- Choose `database/webservice_db.sql`
- Click **Go**

Or via terminal:
```bash
mysql -u root -p < database/webservice_db.sql
```

### 3. Configure .env
Edit `.env` in the project root:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=webservice_db
DB_USER=root
DB_PASS=          ← leave blank for default XAMPP
```

### 4. Enable mod_rewrite (if needed)
In `httpd.conf`, ensure:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
AllowOverride All
```

### 5. Test it!
Open the test client in your browser:
```
http://localhost/php-webservice/public/test-client.html
```

---

## 📡 API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/auth/login | — | Login, returns Bearer token |
| POST | /api/auth/logout | ✓ | Revoke current token |
| GET | /api/products | — | List all products |
| GET | /api/products?category=X | — | Filter by category |
| GET | /api/products/{id} | — | Get single product |
| POST | /api/products | Admin | Create product |
| PATCH | /api/products/{id} | Admin | Update product |
| DELETE | /api/products/{id} | Admin | Delete product |
| GET | /api/users | Admin | List all users |
| GET | /api/users/{id} | Admin | Get single user |
| POST | /api/users | Admin | Create user |
| PATCH | /api/users/{id} | Admin | Update user |
| DELETE | /api/users/{id} | Admin | Delete user |
| GET | /api/orders | ✓ | List orders |
| GET | /api/orders/{id} | ✓ | Get single order |
| POST | /api/orders | ✓ | Place new order |
| PATCH | /api/orders/{id} | Admin | Update order status |

---

## 🔐 Authentication

Use the **Login** endpoint to get a token, then include it in all authenticated requests:

```
Authorization: Bearer your-token-here
```

### Sample credentials (from seed data):
| Email | Password | Role |
|-------|----------|------|
| alice@example.com | password123 | admin |
| bob@example.com   | password123 | user  |

---

## 🧪 Quick Test (cURL)

```bash
# 1. Login
curl -X POST http://localhost/php-webservice/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password123"}'

# 2. Get products (public)
curl http://localhost/php-webservice/api/products

# 3. Place an order (replace TOKEN)
curl -X POST http://localhost/php-webservice/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

---

## 🗄️ Database Schema

```
users ──< api_tokens
users ──< orders ──< order_items >── products
```

---

## ⚙️ Key Concepts Demonstrated

- **PDO Prepared Statements** — SQL injection prevention
- **bcrypt** — Secure password hashing with `password_hash()` / `password_verify()`
- **Transactions** — `beginTransaction()` / `commit()` / `rollBack()` in order placement
- **Bearer Token Auth** — Token stored in `api_tokens` table with expiry
- **CORS Headers** — Cross-origin support with OPTIONS preflight handling
- **HTTP Status Codes** — Correct use of 200, 201, 204, 400, 401, 403, 404, 409, 500
- **Stock Decrement** — Inventory updated atomically within the order transaction
"# rest-api" 
