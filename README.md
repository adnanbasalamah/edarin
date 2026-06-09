<p align="center">
  <img src="public/edarin-logo.png" alt="Edarin" height="80">
</p>

<h1 align="center">Edarin — Sistem Distribusi</h1>

<p align="center">
  A modern, offline-capable distribution management system built with <strong>CodeIgniter 4</strong>, <strong>Alpine.js</strong>, and <strong>Tailwind CSS</strong>.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/CodeIgniter-4.x-DD4814?logo=codeigniter&logoColor=white" alt="CI4">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?logo=alpine.js&logoColor=white" alt="Alpine">
  <img src="https://img.shields.io/badge/Tailwind-3.x-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JWT-Auth-000000?logo=jsonwebtokens&logoColor=white" alt="JWT">
  <img src="https://img.shields.io/badge/tests-37_passing-success" alt="Tests">
</p>

---

## ✨ Features

### 🔐 JWT Authentication
- Login with role-based redirect (admin → dashboard, distributor → sales)
- Auto-generated credentials for new sales persons
- 8-hour token expiry with refresh endpoint
- Full audit trail for all CRUD operations

### 📱 Offline-First Sales Input
- **IndexedDB** local storage for sales data
- Automatic background sync when reconnecting
- Unique `client_id` prevents duplicate entries
- Visual sync status indicators (pending / synced / failed)

### 📊 Rich Dashboards

| Admin | Distributor |
|-------|-------------|
| Total Sales (IDR) with trends | Total Revenue with % change |
| Active Sales Person count | Transaction count + progress bar |
| Total Stores | Average per order |
| Return Rate tracking | 7-day sales chart |
| Top Products ranking | Historical transactions list |
| High Return Store alerts | |

### 📋 Product, Store & Sales Person Management
- Full CRUD with search, edit, and audit logging
- GPS coordinate capture via Geolocation API
- Auto-generated passwords for distributors
- CSV report downloads

### 🛠️ One-Click Installer
- Web-based installer at `/install.php`
- System requirement checks
- Auto-creates database, tables, and admin account
- Self-destructs after installation

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | CodeIgniter 4 (PHP 8.0+) |
| Frontend | Alpine.js + Tailwind CSS (SPA) |
| Database | MySQL / MariaDB |
| Auth | JWT (HS256, firebase/php-jwt) |
| Storage | IndexedDB (offline sales) |
| Testing | PHPUnit 10 (37 tests) |

## 📦 Installation

### Quick Install (Web)

1. Upload all files to your web server
2. Open `http://your-domain.com/install.php`
3. Follow the 3-step wizard:
   - System check (PHP 8.0+, extensions, permissions)
   - Database credentials + admin account
   - Done — login at `/app.html`

### Manual Install (CLI)

```bash
git clone https://github.com/your-org/edarin.git
cd edarin
composer install
cp env .env
# Edit .env with your database credentials
php spark migrate
php spark db:seed App\\Database\\Seeds\\AdminSeeder
php spark serve
```

Then open `http://localhost:8080/app.html`

---

## 🧪 Tests

```bash
vendor/bin/phpunit
```

```
Tests: 37, Assertions: 78
├── Auth (login, refresh, validation)
├── Products CRUD
├── Stores CRUD
├── Distributors CRUD (auto-password, manual password)
├── Sales (create, duplicate idempotency, summary)
├── JWTAuth filter
├── JWT helper
├── Integration (full admin workflow)
└── Audit log verification
```

---

## 📖 API Reference

Full documentation: [Edarin_API.md](Edarin_API.md)

```
POST   /api/auth/login          Login
POST   /api/auth/refresh         Refresh token

GET    /api/products             List products
POST   /api/products             Create product  (admin)
PUT    /api/products/{id}        Update product  (admin)
DELETE /api/products/{id}        Delete product  (admin)

GET    /api/stores               List stores
POST   /api/stores               Create store
PUT    /api/stores/{id}          Update store
DELETE /api/stores/{id}          Delete store    (admin)

GET    /api/distributors         List distributors    (admin)
POST   /api/distributors         Create distributor   (admin)
PUT    /api/distributors/{id}    Update distributor   (admin)
DELETE /api/distributors/{id}    Deactivate distributor (admin)

GET    /api/sales                List sales
GET    /api/sales/summary        Today's sales summary
POST   /api/sales                Submit sale

GET    /api/reports              Report data
GET    /api/reports/stats        Distributor stats + chart
GET    /api/reports/download     Download CSV
GET    /api/reports/dashboard    Admin dashboard data

GET    /api/audit                Audit log    (admin)
```

---

## 🗄️ Database Schema

```
users       — id, username, email, password_hash, role, status
products    — id, name, description, price, unit, status
stores      — id, name, owner, address, phone, latitude, longitude
sales       — id, client_id, distributor_id, store_id, product_id,
              quantity, return_qty, sale_date, sync_status
audit_log   — id, user_id, action, entity_type, entity_id, details
migrations  — CI4 migration tracking
```

---

## 📁 Project Structure

```
edarin/
├── app/
│   ├── Config/         # Routes, filters, DB config
│   ├── Controllers/    # Auth, Products, Stores, Distributors, Sales, Reports, Audit
│   ├── Database/
│   │   ├── Migrations/ # Table schemas
│   │   └── Seeds/      # Admin seeder
│   ├── Filters/        # JWTAuth, RoleCheck
│   ├── Helpers/        # JWT generation/validation
│   └── Models/         # User, Product, Store, Sale, AuditLog
├── public/
│   ├── app.html        # SPA entry point
│   ├── install.php     # Web installer
│   ├── js/app.js       # Alpine.js application
│   ├── css/app.css     # Custom styles
│   └── edarin-logo.png
├── tests/
│   ├── controllers/    # Feature tests
│   └── unit/           # Unit tests
├── desain/             # Design system & mockups
├── conductor/          # Project planning & tracks
├── Edarin_API.md       # Full API documentation
└── README.md
```

---

## 🔒 Security

- Passwords hashed with **bcrypt**
- JWT tokens signed with **HS256**, expire in 8 hours
- All inputs validated & sanitized via CI4 validation
- Role-based access control (admin vs distributor)
- SQL injection prevention via CI4 Query Builder
- Installer self-destructs after successful setup
- `.env` excluded from version control

---

## 📝 License

MIT © 2026 Edarin
