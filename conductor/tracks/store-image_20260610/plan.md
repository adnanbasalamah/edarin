# Implementation Plan: Upload Gambar Toko

## Phase 1: Database & Backend API

- [ ] Task: Create stores image column migration
    - [ ] Create migration file menambahkan kolom `image` (VARCHAR 255, nullable) ke tabel `stores`
    - [ ] Run migration
- [ ] Task: Write tests for store image upload
    - [ ] Write test untuk POST api/stores dengan multipart/form-data (image + store data)
    - [ ] Write test untuk GET api/stores/:id (memastikan response memiliki `image` field)
    - [ ] Confirm all tests fail (Red phase)
- [ ] Task: Implement store image upload di Stores controller
    - [ ] Update create() untuk menerima file upload (image)
    - [ ] Implement image processing: crop square 1:1 center, resize ke 800x800, simpan di writable/uploads/stores/
    - [ ] Update show() response menyertakan URL gambar
    - [ ] Update StoreModel allowedFields menambahkan `image`
    - [ ] Run tests to confirm they pass (Green phase)
- [ ] Task: Write tests for store update with image
    - [ ] Write test untuk PUT api/stores/:id dengan image
    - [ ] Write test untuk validasi file type (hanya JPG) dan size (max 2MB)
    - [ ] Confirm tests fail (Red phase)
- [ ] Task: Implement store update with image support
    - [ ] Update Stores controller update() untuk menerima image
    - [ ] Run tests to confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Database & Backend API' (Protocol in workflow.md)

## Phase 2: Frontend Integration

- [ ] Task: Add camera capture to store form
    - [ ] Tambahkan input type=file dengan accept=image/* capture=camera di form Tambah Store
    - [ ] Tambahkan preview gambar sebelum submit
    - [ ] Kirim sebagai FormData (multipart) bukan JSON
- [ ] Task: Display store image in store detail/edit
    - [ ] Tampilkan gambar di form edit toko
    - [ ] Tampilkan placeholder jika tidak ada gambar
    - [ ] Styling responsive mobile-first
- [ ] Task: Conductor - User Manual Verification 'Frontend Integration' (Protocol in workflow.md)
