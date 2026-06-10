# Implementation Plan: Upload Gambar Toko

## Phase 1: Database & Backend API [checkpoint: 7535125]

- [x] Task: Create stores image column migration
    - [x] Create migration file menambahkan kolom `image` (VARCHAR 255, nullable) ke tabel `stores`
    - [x] Run migration
- [x] Task: Write tests for store image upload
    - [x] Write test untuk POST api/stores dengan multipart/form-data (image + store data)
    - [x] Write test untuk GET api/stores/:id (memastikan response memiliki `image` field)
    - [x] Confirm all tests fail (Red phase)
- [x] Task: Implement store image upload di Stores controller
    - [x] Update create() untuk menerima file upload (image)
    - [x] Implement image processing: crop square 1:1 center, resize ke 800x800, simpan di writable/uploads/stores/
    - [x] Update show() response menyertakan URL gambar
    - [x] Update StoreModel allowedFields menambahkan `image`
    - [x] Run tests to confirm they pass (Green phase)
- [x] Task: Write tests for store update with image
    - [x] Write test untuk PUT api/stores/:id dengan image
    - [x] Write test untuk validasi file type (hanya JPG) dan size (max 2MB)
    - [x] Confirm tests fail (Red phase)
- [x] Task: Implement store update with image support
    - [x] Update Stores controller update() untuk menerima image
    - [x] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Database & Backend API' (Protocol in workflow.md)

## Phase 2: Frontend Integration [checkpoint: aaa09fc]

- [x] Task: Add camera capture to store form
    - [x] Tambahkan input type=file dengan accept=image/* capture=camera di form Tambah Store
    - [x] Tambahkan preview gambar sebelum submit
    - [x] Kirim sebagai FormData (multipart) bukan JSON
- [x] Task: Display store image in store detail/edit
    - [x] Tampilkan gambar di form edit toko
    - [x] Tampilkan placeholder jika tidak ada gambar
    - [x] Styling responsive mobile-first
- [ ] Task: Conductor - User Manual Verification 'Frontend Integration' (Protocol in workflow.md)
