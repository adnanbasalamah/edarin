# Implementation Plan: Build Core Edarin Distribution System

## Phase 1: Project Setup & CodeIgniter 4 Backend Foundation

- [ ] Task: Initialize CodeIgniter 4 project with Composer
    - [ ] Run `composer create-project codeigniter4/appstarter`
    - [ ] Configure `.env` with database credentials
    - [ ] Set up MariaDB/MySQL database and run initial migration
    - [ ] Configure base URL and environment settings
- [ ] Task: Set up database schema and models
    - [ ] Create migration for `users` table (id, username, email, password_hash, role, status, created_at, updated_at)
    - [ ] Create migration for `products` table (id, name, description, price, unit, status, created_at, updated_at)
    - [ ] Create migration for `stores` table (id, name, owner, address, phone, latitude, longitude, created_at, updated_at)
    - [ ] Create migration for `sales` table (id, client_id, distributor_id, store_id, product_id, quantity, return_qty, sale_date, sync_status, created_at, updated_at)
    - [ ] Create migration for `audit_log` table (id, user_id, action, entity_type, entity_id, details, created_at)
    - [ ] Create corresponding model classes with validation rules
- [ ] Task: Set up JWT authentication system
    - [ ] Install and configure `firebase/php-jwt` library
    - [ ] Create Auth controller with login and token refresh endpoints
    - [ ] Implement JWT middleware/filter for protected routes
    - [ ] Create role-based authorization helper
    - [ ] Write unit tests for auth endpoints
- [ ] Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md)

## Phase 2: Admin API — Products, Stores, and Distributors Management

- [ ] Task: Build Products CRUD API
    - [ ] Create Products controller with index, create, update, delete endpoints
    - [ ] Implement request validation and sanitization
    - [ ] Add audit logging for all mutations
    - [ ] Write unit tests for products API
- [ ] Task: Build Stores CRUD API
    - [ ] Create Stores controller with index, create, update, delete endpoints
    - [ ] Implement coordinate storage support (latitude/longitude)
    - [ ] Add audit logging for all mutations
    - [ ] Write unit tests for stores API
- [ ] Task: Build Distributors CRUD API (admin-only)
    - [ ] Create Distributors controller with index, create, update, deactivate endpoints
    - [ ] Implement auto-generated credentials for new distributors
    - [ ] Add audit logging for all mutations
    - [ ] Write unit tests for distributors API
- [ ] Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md)

## Phase 3: Alpine.js SPA — Frontend Foundation & Authentication

- [ ] Task: Set up frontend project structure
    - [ ] Create HTML entry point with Alpine.js and Tailwind CSS via CDN
    - [ ] Set up routing logic (hash-based SPA routing)
    - [ ] Create shared layout components (navbar, sidebar, loading states)
    - [ ] Implement responsive design: mobile-first for distributor, desktop for admin
- [ ] Task: Build login page
    - [ ] Create login form with username and password fields
    - [ ] Implement JWT token storage in localStorage
    - [ ] Add role-based redirect (admin → dashboard, distributor → sales input)
    - [ ] Handle error states (invalid credentials, network error)
    - [ ] Add auto-logout on token expiry
    - [ ] Ensure all touch targets are 44x44px minimum
- [ ] Task: Build offline detection and notification system
    - [ ] Implement network status detection (online/offline event listeners)
    - [ ] Create offline status indicator component
    - [ ] Show notification: "Data akan dikirim otomatis saat koneksi tersedia"
    - [ ] Queue failed API requests for retry when online
    - [ ] Write tests for offline detection behavior
- [ ] Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md)

## Phase 4: Offline-Capable Sales Input for Distributors

- [ ] Task: Implement IndexedDB local storage layer
    - [ ] Create IndexedDB wrapper for sales data CRUD
    - [ ] Store pending sales with unique client-generated IDs (UUID/timestamp)
    - [ ] Implement data conflict prevention using client_id + timestamp
    - [ ] Create sync status tracking (pending/synced/failed)
- [ ] Task: Build distributor sales input page
    - [ ] Select store from dropdown or add new store
    - [ ] Display product list with quantity and return quantity inputs
    - [ ] Save sales data to IndexedDB when offline
    - [ ] Show visual sync status per entry
    - [ ] Implement background sync on connection restore
    - [ ] Write unit tests for offline storage and sync logic
- [ ] Task: Build sales submission API endpoint
    - [ ] Create Sales controller with POST endpoint accepting client_id
    - [ ] Implement idempotency check using client_id to prevent duplicates
    - [ ] Store sales records with distributor association
    - [ ] Write unit tests for sales submission
- [ ] Task: Conductor - User Manual Verification 'Phase 4' (Protocol in workflow.md)

## Phase 5: Reports, Dashboards & Downloads

- [ ] Task: Build admin dashboard
    - [ ] Create overview page with summary statistics (total stores, products, distributors, today's sales)
    - [ ] Display recent sales activity table
    - [ ] Add date range filter
- [ ] Task: Build distributor reports page
    - [ ] Show personal sales history table with search and filter
    - [ ] Implement date range filter (daily/weekly/monthly)
    - [ ] Create download functionality (CSV format)
    - [ ] Ensure mobile-friendly table with horizontal scroll and frozen first column
- [ ] Task: Build distributor store management page
    - [ ] Show list of stores with search
    - [ ] Allow adding new store with coordinate capture
    - [ ] Allow editing existing store data
- [ ] Task: Conductor - User Manual Verification 'Phase 5' (Protocol in workflow.md)

## Phase 6: Integration, Testing & Deployment Preparation

- [ ] Task: End-to-end integration testing
    - [ ] Test complete admin workflow: login → manage products → manage stores → manage distributors
    - [ ] Test complete distributor workflow: login → input sales offline → sync when online → view reports
    - [ ] Verify offline→online sync with multiple concurrent distributor data
    - [ ] Verify audit trail entries on all CRUD operations
    - [ ] Test JWT expiry and refresh flow
- [ ] Task: Polish and finalize
    - [ ] Ensure all UI text is in Bahasa Indonesia
    - [ ] Verify accessibility (touch targets, contrast, labels)
    - [ ] Add loading skeletons and error states throughout
    - [ ] Performance optimization (bundle size, API response times)
- [ ] Task: Conductor - User Manual Verification 'Phase 6' (Protocol in workflow.md)
