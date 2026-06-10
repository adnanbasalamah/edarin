<?php

namespace App\Models;

use CodeIgniter\Model;

class NotaItemModel extends Model
{
    protected $table = 'nota_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nota_id',
        'product_id',
        'quantity',
        'return_qty',
        'price',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'nota_id'    => 'required|numeric',
        'product_id' => 'required|numeric',
        'quantity'   => 'required|numeric|greater_than_equal_to[0]',
        'return_qty' => 'permit_empty|numeric|greater_than_equal_to[0]',
        'price'      => 'required|decimal|greater_than_equal_to[0]',
    ];
}
