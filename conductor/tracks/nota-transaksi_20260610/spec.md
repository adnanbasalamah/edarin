# Specification: Sistem Nota Transaksi Penjualan

## Overview
Convert the current per-product sales recording model into an invoice-based ("nota") transaction system. Each time a distributor submits sales data for a store, the system automatically groups all sold and returned products into a single Nota. The transaction history then displays a list of Notas instead of individual product entries, with a dedicated detail page for each Nota.

## Functional Requirements

### FR-1: Nota Creation
- When a distributor submits sales/returns data for a store, the system automatically creates a single Nota that groups all entries.
- Each Nota must include:
  - Store information (name, owner, address)
  - Distributor information (name)
  - Timestamp of submission
  - List of sold products (product name, quantity, price per unit, total value)
  - List of returned products (product name, quantity returned)
  - Total sales value (sum of all sold products)

### FR-2: Nota List (Transaction History)
- The transaction history screen displays a list of Notas instead of individual product sales.
- Each Nota entry in the list shows a summary: store name, date, total value, and number of items.
- Clicking a Nota navigates to a dedicated detail page.

### FR-3: Nota Detail Page
- A dedicated page that displays the full contents of a single Nota.
- Shows all sold products, returned products, store info, distributor info, and timestamp.
- Includes a back button to return to the Nota list.

### FR-4: Data Migration
- Existing sales data (before this feature) must be migrated into the Nota format.
- Records are grouped into virtual Notas based on store + date combination.
- Migration must preserve all historical data integrity.

## Non-Functional Requirements
- **Mobile-first:** Nota list and detail pages must be optimized for mobile (touch targets >=44px, responsive layout).
- **Performance:** Nota list should load within 2 seconds for up to 100 Notas.
- **Language:** All UI text in Bahasa Indonesia ("Nota", "Riwayat Transaksi", "Detail Nota").

## Acceptance Criteria
1. Distributor submits sales/returns data -> a single Nota is created for that store.
2. Transaction history page shows only Notas (not individual products).
3. Clicking a Nota opens a detail page showing all products sold and returned.
4. Previously migrated data appears correctly as Notas in the history.
5. The existing sales input form works unchanged; grouping happens automatically on submit.

## Out of Scope
- PDF export or printing of Notas
- Editing or deleting Notas after creation
- Admin-side Nota management (this track focuses on distributor-facing features)
- Email or share functionality for Notas
