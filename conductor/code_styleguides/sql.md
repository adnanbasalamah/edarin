# SQL Code Style Guide

## General Rules
- Kata kunci (keywords) SQL seperti `SELECT`, `FROM`, `WHERE`, `UPDATE`, `INSERT` disarankan ditulis menggunakan huruf kapital (UPPERCASE) untuk kemudahan membaca.
- Gunakan `snake_case` untuk penamaan tabel dan kolom (e.g., `ospos_items`, `item_number`).
- Berikan spasi setelah koma, sama seperti menulis dalam kalimat.
- Lakukan indentasi pada klausa lanjutan (seperti `WHERE`, `JOIN`, `ORDER BY`) agar query mudah dipahami secara visual.

## Comments
- **Bahasa Indonesia:** Semua komentar yang disisipkan di dalam query atau skrip SQL HARUS ditulis dalam Bahasa Indonesia.
- Jelaskan mengapa suatu `JOIN` kompleks dilakukan, atau mengapa terdapat filter `WHERE` yang spesifik pada sebuah query.

## Example
```sql
-- Mengambil data barang yang stoknya di bawah batas minimum
-- dan hanya untuk barang yang belum dihapus (deleted = 0)
SELECT 
    i.item_id, 
    i.item_number AS sku, 
    i.name, 
    iq.quantity, 
    i.reorder_level
FROM 
    ospos_items i
JOIN 
    ospos_item_quantities iq ON i.item_id = iq.item_id
WHERE 
    iq.quantity < i.reorder_level
    AND i.deleted = 0;
```
