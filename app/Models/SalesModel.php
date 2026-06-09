<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'distributor_id',
        'store_id',
        'product_id',
        'quantity',
        'return_qty',
        'sale_date',
        'sync_status',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'client_id'       => 'required|max_length[255]',
        'distributor_id'  => 'required|is_natural_no_zero',
        'store_id'        => 'required|is_natural_no_zero',
        'product_id'      => 'required|is_natural_no_zero',
        'quantity'        => 'required|integer|greater_than_equal_to[0]',
        'return_qty'      => 'permit_empty|integer|greater_than_equal_to[0]',
        'sale_date'       => 'required|valid_date',
        'sync_status'     => 'required|in_list[pending,synced,failed]',
    ];
}
