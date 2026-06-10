# Specification: Upload Gambar Toko

## Overview
Tambahkan fitur upload gambar toko menggunakan kamera device saat membuat data toko baru. Gambar di-crop ke rasio 1:1 (square) dan di-resize oleh server ke ukuran standar. Gambar disimpan di local server dan ditampilkan saat melihat detail toko.

## Functional Requirements

### FR-1: Upload Gambar via Kamera
- Saat mengisi form toko baru, tersedia tombol untuk membuka kamera device.
- Setelah foto diambil, gambar langsung ditampilkan sebagai preview di form.
- Gambar dikirim ke server bersama data toko saat form disimpan.

### FR-2: Server-Side Image Processing
- Server menerima gambar, melakukan crop ke rasio 1:1 (square) dari tengah gambar.
- Server me-resize gambar ke ukuran standar (maksimal 800x800px).
- Gambar disimpan di `writable/uploads/stores/` dengan nama unik (misal: `store_<id>_<timestamp>.jpg`).

### FR-3: Store Image di Database
- Tambahkan kolom `image` (VARCHAR 255, nullable) ke tabel `stores`.
- Kolom menyimpan path relatif ke file gambar (contoh: `uploads/stores/store_1_20260610.jpg`).

### FR-4: Tampilkan Gambar di Detail Toko
- Saat admin membuka detail/edit toko, gambar toko ditampilkan.
- Jika toko tidak memiliki gambar, tampilkan placeholder.

## Non-Functional Requirements
- **Ukuran file:** Maksimal 2MB per gambar.
- **Format:** Hanya menerima JPG/JPEG.
- **Mobile-first:** Tombol kamera mudah dijangkau di layar mobile.
- **Performance:** Upload dan proses gambar selesai dalam <3 detik.

## Acceptance Criteria
1. Admin membuka form Tambah Store -> ada tombol kamera.
2. Klik tombol kamera -> kamera device terbuka.
3. Ambil foto -> preview muncul di form.
4. Simpan toko -> gambar terupload, ter-crop 1:1, ter-resize 800x800.
5. Buka detail/edit toko -> gambar toko ditampilkan.
6. Toko tanpa gambar -> tampilkan placeholder.

## Out of Scope
- Upload dari gallery (hanya kamera)
- Multiple images per store
- Edit/replace gambar setelah toko dibuat
- Thumbnail di list toko
