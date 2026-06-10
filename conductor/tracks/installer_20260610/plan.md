# Implementation Plan: Universal Web Installer

## Phase 1: Core Installer [checkpoint: 5f51881]

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
- [x] Task: Conductor - User Manual Verification 'Core Installer' (Protocol in workflow.md)

## Phase 2: Testing & Polish [checkpoint: 5f51881]

- [x] Task: Test installer on Apache environment
    - [x] Verifikasi semua checklist hijau
    - [x] Test koneksi database
    - [x] Full install flow
    - [x] Verifikasi login setelah install
- [x] Task: Error handling & edge cases
    - [x] Database connection gagal -> error message
    - [x] Folder tidak writable -> warning
    - [x] Installer dijalankan ulang -> redirect ke app
    - [x] PHP version tidak memenuhi -> block install
- [x] Task: Conductor - User Manual Verification 'Testing & Polish' (Protocol in workflow.md)
