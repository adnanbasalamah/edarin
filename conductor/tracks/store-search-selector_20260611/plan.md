# Implementation Plan: Search-Based Store Selector on Sales Input

## Phase 1: Data Layer — Independent Search Logic

- [ ] Task: Write tests for sale store search filtering
    - [ ] Create test file verifying `saleFilteredStores` computed getter behavior
    - [ ] Test: returns all stores when search is empty
    - [ ] Test: filters stores by name (case-insensitive partial match)
    - [ ] Test: filters stores by owner name (case-insensitive partial match)
    - [ ] Test: does not filter by phone number
    - [ ] Test: `saleFilteredStores` is independent from `filteredStores` (Store view)
- [ ] Task: Implement `saleStoreSearch` data property and `saleFilteredStores` computed getter
    - [ ] Add `saleStoreSearch: ''` data property in `app.js`
    - [ ] Add `saleFilteredStores` computed getter filtering by `name` and `owner` only
    - [ ] Run tests and confirm all pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Data Layer' (Protocol in workflow.md)

## Phase 2: Search Input UI Component

- [ ] Task: Write test scenarios for search input component
    - [ ] Test: typing in search input shows filtered results
    - [ ] Test: clicking a result selects the store
    - [ ] Test: clear button (×) deselects the store
    - [ ] Test: clicking outside closes dropdown
    - [ ] Test: form validation shows error when no store selected
- [ ] Task: Replace `<select>` dropdown with search input and results dropdown
    - [ ] Replace `<select>` at `app.html:779-789` with search input + dropdown component
    - [ ] Add dropdown container with `x-show` for conditional display
    - [ ] Display search results using `saleFilteredStores` with format `name — owner`
    - [ ] Add clear button (×) to deselect store
    - [ ] Implement click-outside directive to close dropdown
    - [ ] Bind selected store to `saleForm.store_id`
- [ ] Task: Style the search component to match design system
    - [ ] Apply Tailwind classes matching existing design (rounded border, primary focus ring, min-h-[44px])
    - [ ] Add search icon in the input field
    - [ ] Style dropdown results with hover states
    - [ ] Style clear button (×)
- [ ] Task: Conductor - User Manual Verification 'Search Input UI' (Protocol in workflow.md)

## Phase 3: Keyboard Navigation & Accessibility

- [ ] Task: Write test scenarios for keyboard navigation
    - [ ] Test: ArrowDown highlights next result
    - [ ] Test: ArrowUp highlights previous result
    - [ ] Test: Enter selects highlighted result
    - [ ] Test: Escape closes dropdown
- [ ] Task: Implement keyboard navigation for search dropdown
    - [ ] Add `highlightedStoreIndex` data property
    - [ ] Handle ArrowDown/ArrowUp key events to navigate results
    - [ ] Handle Enter key to select highlighted result
    - [ ] Handle Escape key to close dropdown
    - [ ] Add `@keydown` handlers to search input
- [ ] Task: Add accessibility attributes
    - [ ] Add `role="combobox"` to search input
    - [ ] Add `aria-expanded`, `aria-activedescendant`, `aria-autocomplete` attributes
    - [ ] Add `role="listbox"` to dropdown container
    - [ ] Add `role="option"` to each result item
- [ ] Task: Conductor - User Manual Verification 'Keyboard Navigation & Accessibility' (Protocol in workflow.md)

## Phase 4: Mobile Optimization & Final Integration

- [ ] Task: Write test scenarios for mobile and integration
    - [ ] Test: touch targets are at least 44×44px
    - [ ] Test: dropdown is scrollable with max-height
    - [ ] Test: selected store persists when navigating away and back
    - [ ] Test: Store view search no longer affects sales input store list
- [ ] Task: Optimize for mobile
    - [ ] Set dropdown max-height with overflow-y scroll
    - [ ] Ensure all touch targets meet 44px minimum
    - [ ] Add proper `inputmode` attribute for mobile keyboard
    - [ ] Test on mobile viewport sizes
- [ ] Task: Verify session persistence and bug fix
    - [ ] Confirm `saleForm` persistence across route navigation in Alpine.js component
    - [ ] Verify `saleFilteredStores` is fully independent from `storeSearch` (Store view)
    - [ ] Verify dropdown is empty/closed when `saleStoreSearch` is empty and no store is selected
- [ ] Task: Conductor - User Manual Verification 'Mobile Optimization & Final Integration' (Protocol in workflow.md)