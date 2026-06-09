# Implementation Plan: Redirect Root URL to public/app.html

## Phase 1: Root Redirect Implementation

- [ ] Task: Write test for redirect behavior
    - [ ] Write test verifying root URL (/) returns HTTP 302 redirect to /public/app.html
    - [ ] Write test verifying API endpoints still function after redirect
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Create index.php redirect in document root
    - [ ] Create `/var/www/edarin/index.php` with PHP header() redirect to /public/app.html
    - [ ] Verify directory listing no longer shows when accessing root
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md)
