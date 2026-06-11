# Specification: Search-Based Store Selector on Sales Input

## Overview
Replace the current `<select>` dropdown for store selection on the sales input (Input Penjualan) page with a search-based selector component. The new selector will allow distributors to type and search for stores by name or owner name, displaying results in an autocomplete-style dropdown. The selected store will be remembered within the session. This matches the intended design from the mockup (searchable select with search icon) and provides a consistent search experience with the Store view.

## Functional Requirements

### FR-1: Search Input Component
- Replace the existing `<select>` dropdown at `app.html:779-789` with a text input field that supports real-time search.
- The input should display the placeholder "Cari atau pilih toko..." when empty.
- As the user types, a dropdown list of matching stores should appear below the input.
- Clicking/tapping outside the dropdown should close it.

### FR-2: Search Matching Behavior
- Search should be case-insensitive.
- Matching should be performed against store `name` and `owner` fields only (not phone).
- Partial matches should be supported (e.g., typing "ber" matches "Toko Berkah Makmur" and any owner name containing "ber").

### FR-3: Result Display
- Each result in the dropdown should display: `{store name} — {owner name}`.
- Results should update in real-time as the user types.
- When the search input is empty, the dropdown should show all stores.

### FR-4: Store Selection
- Clicking/tapping a store from the dropdown selects it.
- The selected store should be visually indicated in the input field (showing store name).
- A clear button (×) should allow the user to deselect the current store and search again.
- The selected store's `id` is stored in `saleForm.store_id` for form submission.

### FR-5: Session Persistence
- The last selected store should be remembered when the user navigates away from the sales input page and returns within the same session.
- This uses the existing `saleForm` data persistence approach within the Alpine.js component.

### FR-6: Validation
- Form submission should still validate that a store has been selected before allowing submission.
- The existing validation message "Silakan pilih toko." should be preserved.

### FR-7: Mobile Optimization
- Touch targets must be at least 44×44px per product guidelines.
- The dropdown should be scrollable with a reasonable max-height on mobile.
- Input should trigger the mobile keyboard appropriately.

### FR-8: Fix Existing Bug
- The current `<select>` uses `filteredStores` which is tied to `storeSearch` shared with the Store view. The new implementation should use a separate, independent search property for the sales input store search.

## Non-Functional Requirements

### NFR-1: Performance
- Search results should appear within 100ms of typing (client-side filtering).

### NFR-2: Accessibility
- The search input must have a visible label ("Pilih Toko").
- Keyboard navigation should be supported (arrow keys, Enter to select, Escape to close).

### NFR-3: Consistency
- Visual styling should match the existing design system: rounded border, primary color focus ring, minimum 44px height.

### NFR-4: Offline Resilience
- Since all stores are loaded client-side, the search works offline once stores are fetched.

## Acceptance Criteria

1. Given the sales input page, when the user types in the store search field, then matching stores are displayed in a dropdown filtered by store name and owner name.
2. Given a list of search results, when the user clicks a store, then that store is selected and displayed in the input field.
3. Given a selected store, when the user clicks the clear button, then the selection is cleared and the search input is re-enabled.
4. Given the user has selected a store and navigated away, when they return to the sales input page, then the previously selected store is still shown.
5. Given the user submits the sales form without selecting a store, then the error message "Silakan pilih toko." is displayed.
6. Given the user typed a search in the Store view, when they navigate to the sales input page, then all stores are shown (not filtered by the Store view search).
7. Given a mobile device, all touch targets are at least 44×44px and the dropdown scrolls smoothly.

## Out of Scope

- Server-side search/pagination (current client-side approach is sufficient for MVP data volume).
- Multi-store selection (single store per sales entry).
- Sorting or filtering search results by distance or other criteria.
- Search by phone number (only name and owner).