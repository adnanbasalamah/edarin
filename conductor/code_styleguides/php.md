# PHP Code Style Guide

## General Rules
- Code must follow PSR-12 coding standard guidelines.
- Use 4 spaces for indentation (no tabs).
- Files must be saved with UTF-8 encoding without BOM.

## CodeIgniter 3 Specifics
- Controllers and Models should be named with an uppercase first letter (e.g., `Items.php`, `Item_model.php`).
- Function and variable names should use `snake_case` (e.g., `get_item_info()`).
- Always use CodeIgniter's Query Builder or specific model methods to interact with the database instead of raw queries where possible.
- Return structured JSON for REST API endpoints and use appropriate HTTP response codes.

## Comments and Documentation
- **Bahasa Indonesia:** Semua komentar di dalam kode (code comments) HARUS ditulis dalam Bahasa Indonesia.
- **Documentation Blocks:** Gunakan PHPDoc blocks untuk kelas, properti, dan metode (functions). Jelaskan apa yang dilakukan oleh fungsi tersebut, parameter yang diterima, dan nilai kembaliannya.
- Berikan komentar pada blok logika yang kompleks untuk memudahkan pemahaman oleh pengembang lain.

## Example
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_stock extends CI_Controller {

    /**
     * Mengambil informasi stok berdasarkan SKU
     *
     * @param string $sku Kode barang (SKU)
     * @return void Menghasilkan response JSON
     */
    public function get_by_sku($sku) {
        // Memeriksa apakah SKU diberikan
        if (empty($sku)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_MISSING_SKU',
                        'message' => 'SKU tidak boleh kosong.'
                    ]
                ]));
            return;
        }

        // ... logika pencarian barang ...
    }
}
```
