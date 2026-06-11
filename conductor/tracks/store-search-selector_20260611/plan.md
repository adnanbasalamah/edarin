# Implementation Plan: Search-Based Store Selector on Sales Input

## Phase 1: Data Layer — Independent Search Logic [checkpoint: 58883e1]

- [x] Task: Write tests for sale store search filtering
    - [x] Create test file verifying `saleFilteredStores` computed getter behavior
    - [x] Test: returns all stores when search is empty
    - [x] Test: filters stores by name (case-insensitive partial match)
    - [x] Test: filters stores by owner name (case-insensitive partial match)
    - [x] Test: does not filter by phone number
    - [x] Test: `saleFilteredStores` is independent from `filteredStores` (Store view)
- [x] Task: Implement `saleStoreSearch` data property and `saleFilteredStores` computed getter
    - [x] Add `saleStoreSearch: ''` data property in `app.js`
    - [x] Add `saleFilteredStores` computed getter filtering by `name` and `owner` only
    - [x] Run tests and confirm all pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Data Layer' (Protocol in workflow.md)

## Phase 2: Search Input UI Component [checkpoint: 6b25d00]

- [x] Task: Write test scenarios for search input component
    - [x] Test: typing in search input shows filtered results
    - [x] Test: clicking a result selects the store
    - [x] Test: clear button (×) deselects the store
    - [x] Test: clicking outside closes dropdown
    - [x] Test: form validation shows error when no store selected
- [x] Task: Replace `<select>` dropdown with search input and results dropdown
    - [x] Replace `<select>` at `app.html:779-789` with search input + dropdown component
    - [x] Add dropdown container with `x-show` for conditional display
    - [x] Display search results using `saleFilteredStores` with format `name — owner`
    - [x] Add clear button (×) to deselect store
    - [x] Implement click-outside directive to close dropdown
    - [x] Bind selected store to `saleForm.store_id`
- [x] Task: Style the search component to match design system
    - [x] Apply Tailwind classes matching existing design (rounded border, primary focus ring, min-h-[44px])
    - [x] Add search icon in the input field
    - [x] Style dropdown results with hover states
    - [x] Style clear button (×)
- [x] Task: Conductor - User Manual Verification 'Search Input UI' (Protocol in workflow.md)

## Phase 3: Keyboard Navigation & Accessibility [checkpoint: 7f4228b]

- [x] Task: Write test scenarios for keyboard navigation
    - [x] Test: ArrowDown highlights next result
    - [x] Test: ArrowUp highlights previous result
    - [x] Test: Enter selects highlighted result
    - [x] Test: Escape closes dropdown
- [x] Task: Implement keyboard navigation for search dropdown
    - [x] Add `highlightedStoreIndex` data property
    - [x] Handle ArrowDown/ArrowUp key events to navigate results
    - [x] Handle Enter key to select highlighted result
    - [x] Handle Escape key to close dropdown
    - [x] Add `@keydown` handlers to search input
- [x] Task: Add accessibility attributes
    - [x] Add `role="combobox"` to search input
    - [x] Add `aria-expanded`, `aria-activedescendant`, `aria-autocomplete` attributes
    - [x] Add `role="listbox"` to dropdown container
    - [x] Add `role="option"` to each result item
- [x] Task: Conductor - User Manual Verification 'Keyboard Navigation & Accessibility' (Protocol in workflow.md)

## Phase 4: Mobile Optimization & Final Integration

- [x] Task: Write test scenarios for mobile and integration
    - [x] Test: touch targets are at least 44×44px
    - [x] Test: dropdown is scrollable with max-height
    - [x] Test: selected store persists when navigating away and back
    - [x] Test: Store view search no longer affects sales input store list
- [x] Task: Optimize for mobile
    - [x] Set dropdown max-height with overflow-y scroll
    - [x] Ensure all touch targets meet 44px minimum
    - [x] Add proper `inputmode` attribute for mobile keyboard
    - [x] Test on mobile viewport sizes
- [x] Task: Verify session persistence and bug fix
    - [x] Confirm `saleForm` persistence across route navigation in Alpine.js component
    - [x] Verify `saleFilteredStores` is fully independent from `storeSearch` (Store view)
    - [x] Verify dropdown is empty/closed when `saleStoreSearch` is empty and no store is selected
- [~] Task: Conductor - User Manual Verification 'Mobile Optimization & Final Integration' (Protocol in workflow.md)