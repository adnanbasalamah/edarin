# Spesifikasi: Halaman Detail Toko

## Overview
Menambahkan halaman detail toko yang dapat diakses dengan mengklik nama toko dari daftar toko (*Store List*). Halaman ini menampilkan informasi lengkap toko beserta peta lokasi, dan menyediakan tombol untuk mengedit data toko.

## Functional Requirements

1. **Navigasi dari Daftar Toko:**
   - Nama toko di halaman daftar toko dibuat menjadi link yang dapat diklik.
   - Mengklik nama toko mengarahkan pengguna ke halaman detail toko (`/stores/{id}`).

2. **Tampilan Detail Toko:**
   - Menampilkan data toko: Nama Toko, Nama Pemilik (*Owner*), Alamat, Nomor HP.
   - Menampilkan foto toko (jika tersedia).
   - Menampilkan peta lokasi toko berdasarkan koordinat latitude & longitude menggunakan **Leaflet.js + OpenStreetMap**.
   - Pin/marker ditampilkan pada titik koordinat toko.

3. **Tombol Edit:**
   - Halaman detail dilengkapi tombol *Edit* yang mengarahkan ke halaman edit toko (jika sudah ada) atau membuka form edit.

4. **Backend:**
   - Menggunakan endpoint API yang sudah tersedia untuk mengambil data detail toko.

## Non-Functional Requirements

- **Responsif:** Halaman harus optimal di tampilan mobile (untuk distributor) dan desktop (untuk admin).
- **Performa:** Data toko dan peta dimuat dalam waktu < 2 detik.
- **Fallback:** Jika koordinat tidak tersedia, peta tidak ditampilkan dan diganti dengan pesan "Lokasi tidak tersedia". Jika foto tidak tersedia, tampilkan placeholder.

## Acceptance Criteria

- [ ] Mengklik nama toko di daftar toko membawa pengguna ke halaman `/stores/{id}`.
- [ ] Halaman detail menampilkan: Nama Toko, Owner, Alamat, No HP, Foto Toko.
- [ ] Peta Leaflet/OSM menampilkan pin di koordinat toko yang benar.
- [ ] Tombol *Edit* tersedia dan berfungsi.
- [ ] Fallback ditampilkan untuk data yang tidak tersedia (foto, koordinat).
- [ ] Tampilan responsif di perangkat mobile dan desktop.

## Out of Scope

- Membuat endpoint API baru (gunakan yang sudah ada).
- Fitur *inline editing* di halaman detail.
- Rute/navigasi di peta.
