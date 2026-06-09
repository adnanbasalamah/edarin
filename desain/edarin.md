Saya ingin membuat Aplikasi Namanya *Edarin*

*Tech Stack*

Ini adalah Aplikasi Single Page App (web-based utk mobile) yg nanti akan jadi Button di Android

Tech Stack nya Apline JS, TailwindCss, dan backend nya CodeIgniter 4, database nya MariaDB/MySQL
Install code igniter 4 tinggal pakai perintah ini 
 "composer create-project codeigniter4/appstarter nama-proyek"

Aplikasi Web ini mengambil dan mengirim data ke server menggunakan REST API.
Loginnya menggunakan JWT

*Pengguna*

Usernya
- Admin (yg nanti akan membuatkan akun dan passwd utk para pengedar, memasukkan produk dan harganya)
- Para Pengedar (nanti akan dibuatkan akun dan password)

Admin akan bekerja di Layar Komputer
Pengedar akan bekerja di HP.

*Kegunaan Aplikasi ini* 

Pengedar mampu
- Memasukkan Data Toko (nama toko, pemilik / PJ, Alamat Toko,  No HP, Koordinat(otomatis) )
- Memasukkan Jualan masing2 item barang per toko dan juga jumlah retur masing2 barang
- Melihat Laporan Penjualannya
- Mendownload data penjualannya sendiri (harian/mingguan/bulanan)

Admin Mampu 
- Melihat semua data termasuk data penjualan
- memasukkan / mengedit / menghapus data produk
- memasukkan / mengedit / menghapus data toko
- memasukkan / mengedit / menghapus data pengedar

Pengedar Hanya mampu melihat dan mendownload data penjualan (harian/mingguan/bulanan)

*Tampilan Aplikasi*

*Halaman Pengedar*
- Login (ada di direktori desain/1-login)
- Laporan Penjualan (ada di direktori desain/6-pengedar-laporan)
- Input Data Toko Baru (ada di direktori desain/7-pengedar-input-jualan)
- Input Data Penjualan (ada di direktori desain/8-pengedar-input-toko)

*Halaman Admin*
- Login (ada di direktori desain/1-login)
- Dashboard (Data Pengedaran) (tampilan ada di direktori desain/2-dashboard)
- View , Add & Edit Data 
  * Store (Toko) (tampilan ada di direktori desain/5-store)
  * Sales Person (Pengedar)(tampilan ada di direktori desain/4-sales-person)
  * Product (Barang) (tampilan ada di direktori desain/3-inventory)
 
Detail desainnya (warna dll) ada di direktori desain/DESIGN.md
logonya ada di direktori desain/Edarin_logo.png

*Pertimbangan Design Khusus*

Akan ada kemungkinan tidak ada koneksi internet ketika pengedar ini keliling, jadi pastikan aplikasi bisa menyimpan data di lokal (offline storage), misalnya dgn indexedDB utk data jualan dan cache API utk data html dan image, kemudian bisa mendeteksi status koneksi (apakah online atau offline), dan ketika online, kirim pesan secara otomatis (bisa dgn background sync, atau dgn fallback manual). 

Ingat pengedar ini tidak hanya satu, akan banyak org mengirim data, jadi pastikan data yg dikirim tdk bentrok. bisa dgn ID unik atau dgn timestamp. juga ada notifikasi ke pengguna bahwa nanti data akan dikirim otomatis. supaya dia tdk bingung
