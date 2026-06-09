# Track Specification: Core Edarin Distribution System

## Overview
Build the foundational Edarin distribution management system enabling admin management of products, stores, and distributors, and empowering field distributors to submit sales data with offline resilience.

## Functional Requirements

### Authentication & Role Management
- JWT-based login for admin and distributor roles
- Role-based redirect (admin → dashboard, distributor → sales input)
- Admin can create, edit, and deactivate distributor accounts
- All passwords hashed with bcrypt; tokens expire after 8 hours

### Offline-Capable Frontend
- Alpine.js SPA with Tailwind CSS responsive design
- IndexedDB local storage for offline sales data
- Network connection detection (online/offline status indicator)
- Automatic background sync when connection restored
- Unique client-generated IDs prevent data conflicts on multi-device sync
- User notification that data will be sent automatically

### Admin Features
- Dashboard with overview of distribution data
- Product management (CRUD)
- Store management (CRUD)
- Distributor management (CRUD, account creation)
- View all sales data across all distributors
- Filter reports by date, distributor, store

### Distributor Features
- Login via mobile-optimized interface
- Input store data (name, owner, address, phone, coordinates)
- Input sales quantities per product per store
- Input return quantities per product per store
- View personal sales history
- Download reports (daily/weekly/monthly)

### Data Integrity
- No duplicate entries on reconnection
- Audit trail for all CRUD operations
- Input validation and sanitization on all endpoints
- SQL injection and XSS prevention

## Non-Functional Requirements
- Mobile-first UI for distributors; desktop-optimized for admin
- Pages loadable within 2 seconds on 4G
- Works offline for data entry; syncs when online
- Touch targets minimum 44x44px
- Bahasa Indonesia UI text

## API Endpoints
- `POST /api/auth/login` — Login
- `POST /api/auth/refresh` — Refresh token
- `GET /api/products` — List products
- `POST /api/products` — Create product (admin)
- `PUT /api/products/{id}` — Update product (admin)
- `DELETE /api/products/{id}` — Delete product (admin)
- `GET /api/stores` — List stores
- `POST /api/stores` — Create store
- `PUT /api/stores/{id}` — Update store
- `DELETE /api/stores/{id}` — Delete store (admin)
- `GET /api/distributors` — List distributors (admin)
- `POST /api/distributors` — Create distributor (admin)
- `PUT /api/distributors/{id}` — Update distributor (admin)
- `DELETE /api/distributors/{id}` — Deactivate distributor (admin)
- `POST /api/sales` — Submit sales data (distributor)
- `GET /api/sales` — Get sales data (filtered by role)
- `GET /api/reports/download` — Download report (CSV/PDF)
