# Implementation Plan: Sistem Nota Transaksi Penjualan

## Phase 1: Database Schema & Models [checkpoint: ce6c050]

- [x] Task: Create notas table migration (250db38)
    - [x] Create migration file `CreateNotasTable` with fields: id, client_id, distributor_id, store_id, note_date (DATE), total_value (DECIMAL), sync_status, timestamps
    - [x] Run migration to verify table creation
- [x] Task: Create nota_items table migration
    - [x] Create migration file `CreateNotaItemsTable` with fields: id, nota_id (FK), product_id (FK), quantity, return_qty, price (DECIMAL), timestamps
    - [x] Run migration to verify table creation
- [~] Task: Write tests for NotaModel
    - [x] Write unit tests for NotaModel (create, read, validation rules)
    - [x] Confirm tests fail (Red phase)
- [x] Task: Implement NotaModel (0337122)
    - [x] Create NotaModel.php with allowedFields, validationRules, relationships (store, distributor, items)
    - [x] Run tests to confirm they pass (Green phase)
- [~] Task: Write tests for NotaItemModel
    - [x] Write unit tests for NotaItemModel (create, read, validation)
    - [x] Confirm tests fail (Red phase)
- [x] Task: Implement NotaItemModel (8ee0ab0)
    - [x] Create NotaItemModel.php with allowedFields, validationRules, relationships (nota, product)
    - [x] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Database Schema & Models' (Protocol in workflow.md)

## Phase 2: Backend API - Nota Endpoints [checkpoint: 846d9aa]

- [~] Task: Write tests for Notas controller
    - [x] Write controller tests for `GET api/notas` (index)
    - [x] Write controller tests for `GET api/notas/(:num)` (show)
    - [x] Confirm all tests fail (Red phase)
- [x] Task: Implement Notas controller (index & show) (09f6b33)
    - [x] Create Notas.php controller with index() (list notas for authenticated user, with store+items joins)
    - [x] Implement show() (single nota with all items, store, distributor details)
    - [x] Add routes: GET api/notas, GET api/notas/(:num) with jwt-auth filter
    - [x] Run tests to confirm they pass (Green phase)
- [~] Task: Write tests for Sales controller modification
    - [x] Write test for create(): verify a Nota + NotaItems are created alongside the sales records
    - [x] Write test for index(): verify response returns notas instead of individual sales
    - [x] Confirm tests fail (Red phase)
- [x] Task: Modify Sales controller to integrate Nota creation (96d87c4)
    - [x] Update create() to group entries by store and generate a Nota with NotaItems
    - [x] Modify index() to return notas list instead of raw sales entries
    - [x] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Backend API - Nota Endpoints' (Protocol in workflow.md)

## Phase 3: Data Migration [checkpoint: 0e071da]

- [~] Task: Write tests for data migration
    - [x] Write test to verify existing sales are grouped into Notas by store+date
    - [x] Write test to verify NotaItems are created from existing sales records
    - [x] Confirm tests fail (Red phase)
- [x] Task: Implement data migration (77d5f8b)
    - [x] Create migration script that groups existing sales records by (store_id, sale_date) into Notas
    - [x] Create NotaItems from each sale record's quantity, return_qty, product_id, and price
    - [x] Calculate and store total_value on each Nota
    - [x] Run migration and confirm tests pass (Green phase)
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
