# Implementation Plan: Auto-Hide Mobile Sidebar Menu

## Phase 1: Fix Mobile Menu Behavior

- [ ] Task: Write failing test for mobile menu auto-hide
    - [ ] Manually verify mobile menu currently stays open after clicking nav item
    - [ ] Document the current behavior (menu covers screen after navigation)
- [ ] Task: Fix mobile nav items to auto-close menu
    - [ ] Edit app.html: ensure semua link navigasi di mobile sidebar menutup mobileMenuOpen
    - [ ] Edit app.html: pastikan bottom nav tetap berfungsi tanpa mengganggu
    - [ ] Verify overlay still closes on backdrop click
- [ ] Task: Verify fix on all user roles
    - [ ] Test sebagai admin di mobile viewport
    - [ ] Test sebagai distributor di mobile viewport
    - [ ] Pastikan tidak ada regresi di desktop view
- [ ] Task: Conductor - User Manual Verification 'Fix Mobile Menu Behavior' (Protocol in workflow.md)
