<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
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
        'client_id'      => 'required|max_length[255]|is_unique[sales.client_id,id,{id}]',
        'distributor_id' => 'required|numeric',
        'store_id'       => 'required|numeric',
        'product_id'     => 'required|numeric',
        'quantity'       => 'required|numeric|greater_than_equal_to[0]',
        'return_qty'     => 'permit_empty|numeric|greater_than_equal_to[0]',
        'sale_date'      => 'required|valid_date[Y-m-d]',
    ];
}
