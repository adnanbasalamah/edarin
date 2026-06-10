# Implementation Plan: Universal Web Installer

## Phase 1: Core Installer

- [ ] Task: Create installer file structure
    - [ ] Buat `public/install.php` sebagai single-file installer
    - [ ] Styling dengan Tailwind CDN (tanpa dependency)
    - [ ] Header dengan logo Edarin dan judul "Instalasi"
- [ ] Task: Implement system requirements check
    - [ ] Cek PHP version >= 8.2
    - [ ] Cek extensions: mysqli, gd, json, mbstring, fileinfo
    - [ ] Cek writable/ dapat ditulis
    - [ ] Deteksi web server (Apache/Nginx)
    - [ ] Tampilkan checklist hijau/merah
- [ ] Task: Implement installer form
    - [ ] Form database: host, port, user, password, nama db
    - [ ] Form admin: username, email, password
    - [ ] Tombol "Test Database Connection" (AJAX)
    - [ ] Tombol "Install"
    - [ ] Progress indicator selama instalasi
- [ ] Task: Implement installation logic
    - [ ] Generate .env dari input
    - [ ] Create semua tabel via SQL (users, products, stores, sales, audit_log, notas, nota_items)
    - [ ] Insert admin user dengan password hash
    - [ ] Set folder permissions
    - [ ] Hapus install.php setelah sukses
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
