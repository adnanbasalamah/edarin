# Specification: Auto-Hide Mobile Sidebar Menu

## Overview
Perbaiki menu samping (sidebar) mobile agar tidak menutupi layar saat terbuka. Menu akan otomatis tertutup (auto-hide) setelah pengguna memilih salah satu item navigasi. Menu hanya muncul saat tombol hamburger diklik dan langsung menghilang setelah navigasi dipilih.

## Functional Requirements

### FR-1: Auto-Hide on Navigation
- Saat pengguna mengklik item navigasi di menu samping mobile, menu langsung menutup secara otomatis.
- Tidak perlu menutup menu secara manual (klik overlay atau tombol close).

### FR-2: Menu Overlay Tetap Ada
- Overlay gelap (backdrop) tetap ada sebagai indikator visual saat menu terbuka.
- Klik pada overlay tetap bisa menutup menu (existing behavior dipertahankan).

### FR-3: Nav Items Behavior
- Semua item navigasi di sidebar mobile menutup menu setelah diklik.
- Halaman langsung berpindah ke tujuan navigasi yang dipilih.

### FR-4: Hamburger Toggle
- Tombol hamburger di header tetap berfungsi untuk membuka/menutup menu.
- Ikon dan posisi tombol tidak berubah.

## Non-Functional Requirements
- **Performance:** Menu harus menutup dalam <100ms setelah item diklik.
- **Mobile-first:** Perubahan hanya berlaku untuk perangkat mobile (lg:hidden).
- **Semua user:** Berlaku untuk admin dan distributor.

## Acceptance Criteria
1. Buka menu mobile -> klik item navigasi -> menu langsung tertutup.
2. Halaman baru langsung muncul tanpa menu menutupi konten.
3. Tombol hamburger masih berfungsi normal.
4. Klik overlay gelap masih bisa menutup menu.

## Out of Scope
- Perubahan posisi menu (tetap di samping kiri)
- Perubahan ikon atau teks menu
- Animasi transisi khusus
