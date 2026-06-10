# Implementation Plan: Universal Web Installer

## Phase 1: Core Installer

- [x] Task: Create installer file structure
    - [x] Buat `public/install.php` sebagai single-file installer
    - [x] Styling dengan Tailwind CDN (tanpa dependency)
    - [x] Header dengan logo Edarin dan judul "Instalasi"
- [x] Task: Implement system requirements check
    - [x] Cek PHP version >= 8.2
    - [x] Cek extensions: mysqli, gd, json, mbstring, fileinfo
    - [x] Cek writable/ dapat ditulis
    - [x] Deteksi web server (Apache/Nginx)
    - [x] Tampilkan checklist hijau/merah
- [x] Task: Implement installer form
    - [x] Form database: host, port, user, password, nama db
    - [x] Form admin: username, email, password
    - [x] Tombol "Test Database Connection" (AJAX)
    - [x] Tombol "Install"
    - [x] Progress indicator selama instalasi
- [x] Task: Implement installation logic
    - [x] Generate .env dari input
    - [x] Create semua tabel via SQL (users, products, stores, sales, audit_log, notas, nota_items)
    - [x] Insert admin user dengan password hash
    - [x] Set folder permissions
    - [x] Hapus install.php setelah sukses
- [ ] Task: Conductor - User Manual Verification 'Core Installer' (Protocol in workflow.md)

## Phase 2: Testing & Polish

- [ ] Task: Test installer on Apache environment
    - [ ] Verifikasi semua checklist hijau
    - [ ] Test koneksi database
    - [ ] Full install flow
    - [ ] Verifikasi login setelah install
- [ ] Task: Test installer on Nginx environment
    - [ ] Verifikasi deteksi server
    - [ ] Full install flow
    - [ ] Verifikasi auto-delete
- [ ] Task: Error handling & edge cases
    - [ ] Database connection gagal -> error message
    - [ ] Folder tidak writable -> warning
    - [ ] Installer dijalankan ulang -> redirect ke app
    - [ ] PHP version tidak memenuhi -> block install
- [ ] Task: Conductor - User Manual Verification 'Testing & Polish' (Protocol in workflow.md)
