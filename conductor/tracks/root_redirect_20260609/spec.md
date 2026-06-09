# Specification: Redirect Root URL to public/app.html

## Overview
Saat ini `http://edarin.local/` menampilkan daftar file direktori. Pengguna harus langsung diarahkan ke halaman aplikasi `http://edarin.local/public/app.html` dengan HTTP 302 Temporary Redirect, tanpa melihat daftar file.

## Functional Requirements
1. Request ke root URL (`/`) mengembalikan HTTP 302 redirect ke `/public/app.html`
2. Daftar file direktori tidak lagi muncul saat akses root
3. Logic redirect via PHP `header()` di file index baru di document root
4. API endpoint (`/public/api/*`) tetap berfungsi normal — tidak terpengaruh redirect

## Acceptance Criteria
1. `curl -I http://edarin.local/` → `HTTP/1.1 302` + `Location: /public/app.html`
2. Browsing langsung ke domain menampilkan halaman login SPA
3. API login/CUD tetap berfungsi normal

## Out of Scope
- Mengubah .htaccess
- Mengubah routing CodeIgniter
