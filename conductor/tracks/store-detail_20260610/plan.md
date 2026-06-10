# Rencana Implementasi: Halaman Detail Toko

## Phase 1: Backend - Verifikasi & Test Store Detail API [checkpoint: 2351d09]

- [x] Task: Verifikasi endpoint API detail toko sudah tersedia
    - [ ] Periksa route `GET /api/stores/{id}` di `app/Config/Routes.php`
    - [ ] Periksa controller dan method yang menangani endpoint tersebut
    - [ ] Pastikan response mengembalikan field: nama toko, owner, alamat, no HP, foto, latitude, longitude
- [x] Task: Tulis backend test untuk endpoint detail toko [64ff263]
    - [x] Test: endpoint mengembalikan data toko yang valid dengan status 200
    - [x] Test: endpoint mengembalikan 404 untuk toko yang tidak ditemukan
    - [x] Test: response menyertakan semua field yang diperlukan
- [x] Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md) [2351d09]

## Phase 2: Frontend - Halaman Detail Toko [checkpoint: bd2a61f]

- [x] Task: Buat struktur halaman detail toko [9b12aae]
    - [x] Buat view template untuk halaman detail toko (`stores/detail.php`)
    - [x] Terapkan layout responsif dengan Tailwind CSS (mobile-first)
    - [x] Buat Alpine.js component untuk fetch data toko dari API
- [x] Task: Integrasi Leaflet.js + OpenStreetMap [9b12aae]
    - [x] Sertakan library Leaflet.js (CDN atau local)
    - [x] Inisialisasi peta dengan koordinat toko (lat, long)
    - [x] Tambahkan marker/pin pada titik koordinat
    - [x] Handle fallback: tampilkan "Lokasi tidak tersedia" jika koordinat kosong
- [x] Task: Implementasi fallback states [9b12aae]
    - [x] Fallback foto: tampilkan placeholder jika foto tidak tersedia
    - [x] Fallback koordinat: "Lokasi tidak tersedia"
    - [x] Loading state saat fetch data
- [x] Task: Tulis frontend test untuk halaman detail toko [9b12aae]
    - [x] Test: render semua field data toko dengan benar
    - [x] Test: peta dirender dengan koordinat yang benar
    - [x] Test: fallback states bekerja dengan benar
- [x] Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md) [bd2a61f]

## Phase 3: Navigasi dari Daftar Toko [checkpoint: f5e8fd9]

- [x] Task: Ubah nama toko di daftar toko menjadi link [813e62d]
    - [x] Modifikasi view daftar toko agar nama toko dapat diklik
    - [x] Link mengarah ke `/stores/{id}`
    - [x] Pastikan navigasi berfungsi di mobile dan desktop
- [x] Task: Tulis test untuk navigasi daftar toko [813e62d]
    - [x] Test: klik nama toko mengarahkan ke URL yang benar
- [x] Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md) [f5e8fd9]

## Phase 4: Tombol Edit & Finalisasi

- [x] Task: Tambahkan tombol Edit di halaman detail toko [9b12aae]
    - [x] Tambahkan tombol Edit yang mengarah ke halaman edit toko
    - [x] Pastikan tombol hanya muncul untuk role yang berwenang
- [x] Task: Tulis integration test end-to-end [9b12aae]
    - [x] Test: alur lengkap dari daftar toko -> detail -> edit
- [ ] Task: Conductor - User Manual Verification 'Phase 4' (Protocol in workflow.md)
