# Implementation Plan: Sistem Nota Transaksi Penjualan

## Phase 1: Database Schema & Models

- [x] Task: Create notas table migration (250db38)
    - [x] Create migration file `CreateNotasTable` with fields: id, client_id, distributor_id, store_id, note_date (DATE), total_value (DECIMAL), sync_status, timestamps
    - [x] Run migration to verify table creation
- [x] Task: Create nota_items table migration
    - [x] Create migration file `CreateNotaItemsTable` with fields: id, nota_id (FK), product_id (FK), quantity, return_qty, price (DECIMAL), timestamps
    - [x] Run migration to verify table creation
- [~] Task: Write tests for NotaModel
    - [x] Write unit tests for NotaModel (create, read, validation rules)
    - [x] Confirm tests fail (Red phase)
- [x] Task: Implement NotaModel
    - [x] Create NotaModel.php with allowedFields, validationRules, relationships (store, distributor, items)
    - [x] Run tests to confirm they pass (Green phase)
- [ ] Task: Write tests for NotaItemModel
    - [ ] Write unit tests for NotaItemModel (create, read, validation)
    - [ ] Confirm tests fail (Red phase)
- [ ] Task: Implement NotaItemModel
    - [ ] Create NotaItemModel.php with allowedFields, validationRules, relationships (nota, product)
    - [ ] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Database Schema & Models' (Protocol in workflow.md)

## Phase 2: Backend API - Nota Endpoints

- [ ] Task: Write tests for Notas controller
    - [ ] Write controller tests for `GET api/notas` (index)
    - [ ] Write controller tests for `GET api/notas/(:num)` (show)
    - [ ] Confirm all tests fail (Red phase)
- [ ] Task: Implement Notas controller (index & show)
    - [ ] Create Notas.php controller with index() (list notas for authenticated user, with store+items joins)
    - [ ] Implement show() (single nota with all items, store, distributor details)
    - [ ] Add routes: GET api/notas, GET api/notas/(:num) with jwt-auth filter
    - [ ] Run tests to confirm they pass (Green phase)
- [ ] Task: Write tests for Sales controller modification
    - [ ] Write test for create(): verify a Nota + NotaItems are created alongside the sales records
    - [ ] Write test for index(): verify response returns notas instead of individual sales
    - [ ] Confirm tests fail (Red phase)
- [ ] Task: Modify Sales controller to integrate Nota creation
    - [ ] Update create() to group entries by store and generate a Nota with NotaItems
    - [ ] Modify index() to return notas list instead of raw sales entries
    - [ ] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Backend API - Nota Endpoints' (Protocol in workflow.md)

## Phase 3: Data Migration

- [ ] Task: Write tests for data migration
    - [ ] Write test to verify existing sales are grouped into Notas by store+date
    - [ ] Write test to verify NotaItems are created from existing sales records
    - [ ] Confirm tests fail (Red phase)
- [ ] Task: Implement data migration
    - [ ] Create migration script that groups existing sales records by (store_id, sale_date) into Notas
    - [ ] Create NotaItems from each sale record's quantity, return_qty, product_id, and price
    - [ ] Calculate and store total_value on each Nota
    - [ ] Run migration and confirm tests pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Data Migration' (Protocol in workflow.md)

## Phase 4: Frontend Integration & Polish

- [ ] Task: Update distributor transaction history view
    - [ ] Create Nota list page showing summary cards (store name, date, total value, item count)
    - [ ] Wire up API calls to fetch notas list
    - [ ] Implement loading/empty/error states
- [ ] Task: Create Nota detail page
    - [ ] Display full nota info: store details, distributor, timestamp
    - [ ] Show sold products table (name, qty, price, total)
    - [ ] Show returned products table (name, qty returned)
    - [ ] Add back button navigation to nota list
- [ ] Task: Verify mobile responsiveness
    - [ ] Test nota list on mobile viewport (< 480px)
    - [ ] Test nota detail on mobile viewport
    - [ ] Ensure touch targets >= 44px
- [ ] Task: Conductor - User Manual Verification 'Frontend Integration & Polish' (Protocol in workflow.md)
