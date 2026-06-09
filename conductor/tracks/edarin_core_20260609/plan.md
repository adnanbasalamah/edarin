# Implementation Plan: Build Core Edarin Distribution System

## Phase 1: Project Setup & CodeIgniter 4 Backend Foundation [checkpoint: 1771553]

- [x] Task: Initialize CodeIgniter 4 project with Composer
    - [x] Run `composer create-project codeigniter4/appstarter`
    - [x] Configure `.env` with database credentials
    - [x] Set up MariaDB/MySQL database and run initial migration
    - [x] Configure base URL and environment settings
- [x] Task: Set up database schema and models
    - [x] Create migration for `users` table
    - [x] Create migration for `products` table
    - [x] Create migration for `stores` table
    - [x] Create migration for `sales` table
    - [x] Create migration for `audit_log` table
    - [x] Create corresponding model classes with validation rules
- [x] Task: Set up JWT authentication system [59c7dd3]
    - [x] Install and configure `firebase/php-jwt` library
    - [x] Create Auth controller with login and token refresh endpoints
    - [x] Implement JWT middleware/filter for protected routes
    - [x] Create role-based authorization helper
    - [x] Write unit tests for auth endpoints
- [x] Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md)

## Phase 2: Admin API — Products, Stores, and Distributors Management [checkpoint: 9d79be2]

- [x] Task: Build Products CRUD API
    - [x] Create Products controller with index, create, update, delete endpoints
    - [x] Implement request validation and sanitization
    - [x] Add audit logging for all mutations
    - [x] Write unit tests for products API
- [x] Task: Build Stores CRUD API
    - [x] Create Stores controller with index, create, update, delete endpoints
    - [x] Implement coordinate storage support (latitude/longitude)
    - [x] Add audit logging for all mutations
    - [x] Write unit tests for stores API
- [x] Task: Build Distributors CRUD API (admin-only)
    - [x] Create Distributors controller with index, create, update, deactivate endpoints
    - [x] Implement auto-generated credentials for new distributors
    - [x] Add audit logging for all mutations
    - [x] Write unit tests for distributors API
- [ ] Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md)

## Phase 3: Alpine.js SPA — Frontend Foundation & Authentication

- [x] Task: Set up frontend project structure
    - [x] Create HTML entry point with Alpine.js and Tailwind CSS via CDN
    - [x] Set up routing logic (hash-based SPA routing)
    - [x] Create shared layout components (navbar, sidebar, loading states)
    - [x] Implement responsive design: mobile-first for distributor, desktop for admin
- [x] Task: Build login page
    - [x] Create login form with username and password fields
    - [x] Implement JWT token storage in localStorage
    - [x] Add role-based redirect (admin → dashboard, distributor → sales input)
    - [x] Handle error states (invalid credentials, network error)
    - [x] Add auto-logout on token expiry
    - [x] Ensure all touch targets are 44x44px minimum
- [x] Task: Build offline detection and notification system
    - [x] Implement network status detection (online/offline event listeners)
    - [x] Create offline status indicator component
    - [x] Show notification: "Data akan dikirim otomatis saat koneksi tersedia"
    - [x] Queue failed API requests for retry when online
    - [x] Write tests for offline detection behavior
- [ ] Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md)

## Phase 4: Offline-Capable Sales Input for Distributors

- [x] Task: Implement IndexedDB local storage layer
    - [x] Create IndexedDB wrapper for sales data CRUD
    - [x] Store pending sales with unique client-generated IDs (UUID/timestamp)
    - [x] Implement data conflict prevention using client_id + timestamp
    - [x] Create sync status tracking (pending/synced/failed)
- [x] Task: Build distributor sales input page
    - [x] Select store from dropdown or add new store
    - [x] Display product list with quantity and return quantity inputs
    - [x] Save sales data to IndexedDB when offline
    - [x] Show visual sync status per entry
    - [x] Implement background sync on connection restore
    - [x] Write unit tests for offline storage and sync logic
- [x] Task: Build sales submission API endpoint
    - [x] Create Sales controller with POST endpoint accepting client_id
    - [x] Implement idempotency check using client_id to prevent duplicates
    - [x] Store sales records with distributor association
    - [x] Write unit tests for sales submission
- [ ] Task: Conductor - User Manual Verification 'Phase 4' (Protocol in workflow.md)

## Phase 5: Reports, Dashboards & Downloads

- [x] Task: Build admin dashboard
    - [x] Create overview page with summary statistics (total stores, products, distributors, today's sales)
    - [x] Display recent sales activity table
    - [x] Add date range filter
- [x] Task: Build distributor reports page
    - [x] Show personal sales history table with search and filter
    - [x] Implement date range filter (daily/weekly/monthly)
    - [x] Create download functionality (CSV format)
    - [x] Ensure mobile-friendly table with horizontal scroll and frozen first column
- [x] Task: Build distributor store management page
    - [x] Show list of stores with search
    - [x] Allow adding new store with coordinate capture
    - [x] Allow editing existing store data
- [ ] Task: Conductor - User Manual Verification 'Phase 5' (Protocol in workflow.md)

## Phase 6: Integration, Testing & Deployment Preparation

- [x] Task: End-to-end integration testing
    - [x] Test complete admin workflow: login → manage products → manage stores → manage distributors
    - [x] Test complete distributor workflow: login → input sales offline → sync when online → view reports
    - [x] Verify offline→online sync with multiple concurrent distributor data
    - [x] Verify audit trail entries on all CRUD operations
    - [x] Test JWT expiry and refresh flow
- [x] Task: Polish and finalize
    - [x] Ensure all UI text is in Bahasa Indonesia
    - [x] Verify accessibility (touch targets, contrast, labels)
    - [x] Add loading skeletons and error states throughout
    - [x] Performance optimization (bundle size, API response times)
- [ ] Task: Conductor - User Manual Verification 'Phase 6' (Protocol in workflow.md)

## Phase: Review Fixes
- [x] Task: Apply review suggestions [c2273af]
