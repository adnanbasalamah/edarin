# Specification: Universal Web Installer

## Overview
Installer web satu halaman yang mudah digunakan di hosting Apache maupun Nginx. User mengisi form sekali, klik Install, dan semua otomatis selesai. Installer menghapus dirinya sendiri setelah sukses.

## Functional Requirements

### FR-1: Welcome & Requirements Check
- Deteksi otomatis web server (Apache/Nginx).
- Cek PHP version >= 8.2.
- Cek PHP extensions: mysqli, gd, json, mbstring, fileinfo.
- Cek folder writable/ writable.
- Tampilkan status ceklis (hijau/merah) untuk tiap requirement.

### FR-2: Single Page Form
- Satu halaman berisi semua input:
  - **Database:** host, port, username, password, nama database
  - **Admin Account:** username, email, password
- Tombol "Test Database Connection" untuk verifikasi sebelum install.
- Tombol "Install" untuk memulai instalasi.

### FR-3: Automated Installation
- Generate file `.env` dari input user.
- Jalankan semua migrasi database (buat tabel: users, products, stores, sales, audit_log, notas, nota_items).
- Buat admin user dengan password yang diinput.
- Set folder permissions writable untuk uploads/.

### FR-4: Post-Install Security
- Hapus file installer (`install.php`) secara otomatis setelah sukses.
- Redirect ke halaman login aplikasi.
- Tampilkan pesan sukses dengan link ke aplikasi.

## Non-Functional Requirements
- **Single file:** Hanya satu file `install.php` yang perlu di-upload.
- **No CLI:** Semua proses via browser, tidak perlu SSH.
- **Responsive:** Tampilan mobile-friendly.
- **Timeout:** Instalasi selesai dalam <30 detik.

## Acceptance Criteria
1. Upload `install.php` ke server, buka di browser -> muncul form instalasi.
2. Isi form, klik Test DB -> koneksi berhasil.
3. Klik Install -> .env dibuat, tabel dibuat, admin dibuat.
4. Installer auto-delete, redirect ke app.html.
5. Bisa login dengan admin yang baru dibuat.

## Out of Scope
- Update/reinstall existing installation
- Backup/restore database
- Multi-language installer
- Theme/template installer
