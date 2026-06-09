<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'description',
        'price',
        'unit',
        'status',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'name'  => 'required|max_length[255]',
        'price' => 'required|numeric|greater_than[0]',
        'unit'  => 'required|max_length[50]',
        'status' => 'required|in_list[active,inactive]',
    ];
}
