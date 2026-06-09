# Edarin API Documentation

## Base URL
```
http://domain-anda.com/api
```

## Authentication
Semua endpoint (kecuali login) membutuhkan JWT token di header:
```
Authorization: Bearer <token>
```

Token expired setelah **8 jam**. Refresh token via `POST /api/auth/refresh`.

---

## Roles & Access Control

| Role | Akses |
|------|-------|
| **admin** | Semua endpoint: products, stores, distributors, sales, reports |
| **distributor** | Sales input, stores, reports (data sendiri) |

Filter `role-check:admin` membatasi endpoint khusus admin.

---

## 1. Authentication

### Login
```
POST /api/auth/login
```
**Auth:** Tidak diperlukan

**Request Body (JSON):**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Response 200:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@edarin.com",
    "role": "admin"
  }
}
```

| Status | Arti |
|--------|------|
| 200 | Login berhasil |
| 400 | Validasi gagal (username/password kosong) |
| 401 | Invalid credentials |
| 403 | Akun dinonaktifkan |

---

### Refresh Token
```
POST /api/auth/refresh
```
**Auth:** JWT token (Bearer)

**Response 200:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIs..."
}
```

| Status | Arti |
|--------|------|
| 200 | Token baru berhasil |
| 401 | Token invalid/expired atau user tidak aktif |

---

## 2. Products

### List Products
```
GET /api/products
```
**Auth:** JWT (semua role)

**Response 200:**
```json
[
  {
    "id": 1,
    "name": "Teh Botol",
    "description": "Teh botol 250ml",
    "price": 5000,
    "unit": "pcs",
    "status": "active",
    "created_at": "2026-06-09 10:00:00",
    "updated_at": "2026-06-09 10:00:00"
  }
]
```

---

### Get Product by ID
```
GET /api/products/{id}
```
**Auth:** JWT (semua role)

**Response 200:** Object product tunggal

| Status | Arti |
|--------|------|
| 200 | OK |
| 404 | Product not found |

---

### Create Product
```
POST /api/products
```
**Auth:** JWT (admin only)

**Request Body (JSON):**
```json
{
  "name": "Teh Botol",
  "description": "Teh botol 250ml",
  "price": 5000,
  "unit": "pcs",
  "status": "active"
}
```

| Field | Validasi |
|-------|----------|
| name | Required, max 255 |
| price | Required, numeric, > 0 |
| unit | Required, max 50 |
| description | Optional |
| status | Optional, default: active |

**Response 201:**
```json
{
  "message": "Product created",
  "id": 1
}
```

---

### Update Product
```
PUT /api/products/{id}
```
**Auth:** JWT (admin only)

**Request Body (JSON):** Semua field opsional
```json
{
  "name": "Teh Botol Baru",
  "price": 6000
}
```

**Response 200:**
```json
{
  "message": "Product updated"
}
```

| Status | Arti |
|--------|------|
| 200 | OK |
| 400 | Validasi gagal |
| 404 | Product not found |

---

### Delete Product
```
DELETE /api/products/{id}
```
**Auth:** JWT (admin only)

**Response 200:**
```json
{
  "message": "Product deleted"
}
```

---

## 3. Stores

### List Stores
```
GET /api/stores
```
**Auth:** JWT (semua role)

**Response 200:**
```json
[
  {
    "id": 1,
    "name": "Toko Sembako Makmur",
    "owner": "Budi Santoso",
    "address": "Jl. Merdeka No. 123",
    "phone": "08123456789",
    "latitude": -6.208800,
    "longitude": 106.845600,
    "created_at": "2026-06-09 10:00:00",
    "updated_at": "2026-06-09 10:00:00"
  }
]
```

---

### Get Store by ID
```
GET /api/stores/{id}
```
**Auth:** JWT (semua role)

**Response 200:** Object store tunggal

---

### Create Store
```
POST /api/stores
```
**Auth:** JWT (semua role: admin & distributor)

**Request Body (JSON):**
```json
{
  "name": "Toko Sembako Makmur",
  "owner": "Budi Santoso",
  "address": "Jl. Merdeka No. 123",
  "phone": "08123456789",
  "latitude": -6.208800,
  "longitude": 106.845600
}
```

| Field | Validasi |
|-------|----------|
| name | Required, max 255 |
| owner | Required, max 255 |
| address | Required |
| phone | Required, max 20 |
| latitude | Optional, numeric |
| longitude | Optional, numeric |

**Response 201:**
```json
{
  "message": "Store created",
  "id": 1
}
```

---

### Update Store
```
PUT /api/stores/{id}
```
**Auth:** JWT (semua role: admin & distributor)

**Request Body (JSON):** Semua field opsional
```json
{
  "name": "Nama Toko Baru",
  "owner": "Pemilik Baru",
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response 200:**
```json
{
  "message": "Store updated"
}
```

---

### Delete Store
```
DELETE /api/stores/{id}
```
**Auth:** JWT (admin only)

**Response 200:**
```json
{
  "message": "Store deleted"
}
```

---

## 4. Distributors (Sales Person)

### List Distributors
```
GET /api/distributors
```
**Auth:** JWT (admin only)

**Response 200:**
```json
[
  {
    "id": 2,
    "username": "distributor_jakarta",
    "email": "distro@test.com",
    "role": "distributor",
    "status": "active",
    "created_at": "2026-06-09 10:00:00",
    "updated_at": "2026-06-09 10:00:00"
  }
]
```

---

### Get Distributor by ID
```
GET /api/distributors/{id}
```
**Auth:** JWT (admin only)

**Response 200:** Object distributor tunggal

---

### Create Distributor
```
POST /api/distributors
```
**Auth:** JWT (admin only)

**Request Body (JSON):**
```json
{
  "username": "distributor_jakarta",
  "email": "distro@test.com",
  "password": "rahasia123"
}
```

| Field | Validasi |
|-------|----------|
| username | Required, min 3, max 100, unique |
| email | Required, valid email, unique |
| password | Optional, min 6. Jika kosong: auto-generate 8-char hex |

**Response 201:**
```json
{
  "message": "Distributor created",
  "id": 2,
  "username": "distributor_jakarta",
  "password": "5e11e999"
}
```

> Password dikembalikan dalam response. Catat dan berikan ke sales person.

---

### Update Distributor
```
PUT /api/distributors/{id}
```
**Auth:** JWT (admin only)

**Request Body (JSON):** Semua field opsional
```json
{
  "username": "new_username",
  "email": "new@email.com",
  "password": "newpassword"
}
```

**Response 200:**
```json
{
  "message": "Distributor updated"
}
```

---

### Deactivate Distributor
```
DELETE /api/distributors/{id}
```
**Auth:** JWT (admin only)

> Tidak menghapus data, hanya set `status = inactive`.

**Response 200:**
```json
{
  "message": "Distributor deactivated"
}
```

---

## 5. Sales

### List Sales
```
GET /api/sales
```
**Auth:** JWT (semua role)

- **Admin:** Melihat semua sales
- **Distributor:** Hanya melihat sales milik sendiri

**Query Parameters:**

| Param | Contoh | Keterangan |
|-------|--------|------------|
| date_from | 2026-06-01 | Filter tanggal mulai |
| date_to | 2026-06-09 | Filter tanggal akhir |

**Response 200:**
```json
[
  {
    "id": 1,
    "client_id": "sale_1717910000_abc123",
    "distributor_id": 2,
    "store_id": 1,
    "product_id": 3,
    "quantity": 10,
    "return_qty": 2,
    "sale_date": "2026-06-09",
    "sync_status": "synced",
    "store_name": "Toko Sembako Makmur",
    "product_name": "Teh Botol",
    "created_at": "2026-06-09 10:00:00",
    "updated_at": "2026-06-09 10:00:00"
  }
]
```

---

### Sales Summary
```
GET /api/sales/summary
```
**Auth:** JWT (semua role)

Mengembalikan jumlah penjualan hari ini.

**Response 200:**
```json
{
  "today_sales": 15
}
```

---

### Create Sale
```
POST /api/sales
```
**Auth:** JWT (semua role)

**Request Body (JSON):**
```json
{
  "client_id": "sale_1717910000_abc123",
  "store_id": 1,
  "product_id": 3,
  "quantity": 10,
  "return_qty": 2,
  "sale_date": "2026-06-09"
}
```

| Field | Validasi |
|-------|----------|
| client_id | Required, unique (idempotency) |
| store_id | Required, numeric |
| product_id | Required, numeric |
| quantity | Required, numeric, >= 0 |
| return_qty | Optional, numeric, >= 0 |
| sale_date | Required, format Y-m-d |

**Response 201:**
```json
{
  "message": "Sale created",
  "id": 1
}
```

**Response 409 (Duplicate):**
```json
{
  "message": "Sale already exists",
  "id": 1
}
```

> `client_id` mencegah duplikasi data saat sync offline. Gunakan ID unik (UUID/timestamp).

---

## 6. Reports

### List Reports
```
GET /api/reports
```
**Auth:** JWT (semua role)

Sama seperti List Sales tetapi dengan field tambahan `product_price`.

**Query Parameters:**

| Param | Contoh | Keterangan |
|-------|--------|------------|
| date_from | 2026-06-01 | Filter tanggal mulai |
| date_to | 2026-06-09 | Filter tanggal akhir |

**Response 200:**
```json
[
  {
    "id": 1,
    "sale_date": "2026-06-09",
    "store_name": "Toko Sembako Makmur",
    "product_name": "Teh Botol",
    "product_price": 5000,
    "quantity": 10,
    "return_qty": 2
  }
]
```

---

### Download Report CSV
```
GET /api/reports/download
```
**Auth:** JWT (semua role)

**Query Parameters:** Sama seperti List Reports.

**Response:** File CSV dengan header:
```
Tanggal, Toko, Product, Harga, Jual, Retur, Total
```

> BOM UTF-8 ditambahkan untuk kompatibilitas Excel.

---

## Response Codes Summary

| Status | Arti |
|--------|------|
| 200 | OK |
| 201 | Created |
| 400 | Validation failed |
| 401 | Unauthorized / token invalid |
| 403 | Forbidden / role tidak diizinkan |
| 404 | Resource not found |
| 409 | Conflict / duplicate client_id |

---

## Database Schema

```
users       — username, email, password_hash, role, status
products    — name, description, price, unit, status
stores      — name, owner, address, phone, latitude, longitude
sales       — client_id, distributor_id, store_id, product_id, quantity, return_qty, sale_date, sync_status
audit_log   — user_id, action, entity_type, entity_id, details
```

---

## Deployment

1. Upload semua file ke hosting
2. Buka `/install.php` untuk instalasi otomatis
3. Hapus `install.php` setelah selesai
4. Login melalui `/app.html`

## Tech Stack
- **Backend:** CodeIgniter 4 (PHP)
- **Frontend:** Alpine.js + Tailwind CSS (SPA)
- **Database:** MySQL / MariaDB
- **Auth:** JWT (HS256, 8 jam expiry)
