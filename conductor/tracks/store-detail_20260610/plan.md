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

## Phase 2: Frontend - Halaman Detail Toko

- [ ] Task: Buat struktur halaman detail toko
    - [ ] Buat view template untuk halaman detail toko (`stores/detail.php`)
    - [ ] Terapkan layout responsif dengan Tailwind CSS (mobile-first)
    - [ ] Buat Alpine.js component untuk fetch data toko dari API
- [ ] Task: Integrasi Leaflet.js + OpenStreetMap
    - [ ] Sertakan library Leaflet.js (CDN atau local)
    - [ ] Inisialisasi peta dengan koordinat toko (lat, long)
    - [ ] Tambahkan marker/pin pada titik koordinat
    - [ ] Handle fallback: tampilkan "Lokasi tidak tersedia" jika koordinat kosong
- [ ] Task: Implementasi fallback states
    - [ ] Fallback foto: tampilkan placeholder jika foto tidak tersedia
    - [ ] Fallback koordinat: "Lokasi tidak tersedia"
    - [ ] Loading state saat fetch data
- [ ] Task: Tulis frontend test untuk halaman detail toko
    - [ ] Test: render semua field data toko dengan benar
    - [ ] Test: peta dirender dengan koordinat yang benar
    - [ ] Test: fallback states bekerja dengan benar
- [ ] Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md)

## Phase 3: Navigasi dari Daftar Toko

- [ ] Task: Ubah nama toko di daftar toko menjadi link
    - [ ] Modifikasi view daftar toko agar nama toko dapat diklik
    - [ ] Link mengarah ke `/stores/{id}`
    - [ ] Pastikan navigasi berfungsi di mobile dan desktop
- [ ] Task: Tulis test untuk navigasi daftar toko
    - [ ] Test: klik nama toko mengarahkan ke URL yang benar
- [ ] Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md)

## Phase 4: Tombol Edit & Finalisasi

- [ ] Task: Tambahkan tombol Edit di halaman detail toko
    - [ ] Tambahkan tombol Edit yang mengarah ke halaman edit toko
    - [ ] Pastikan tombol hanya muncul untuk role yang berwenang
- [ ] Task: Tulis integration test end-to-end
    - [ ] Test: alur lengkap dari daftar toko -> detail -> edit
- [ ] Task: Conductor - User Manual Verification 'Phase 4' (Protocol in workflow.md)
