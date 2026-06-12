<?php

namespace App\Models;

use CodeIgniter\Model;

class StoreModel extends Model
{
    protected $table = 'stores';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'distributor_id',
        'name',
        'owner',
        'address',
        'phone',
        'image',
        'latitude',
        'longitude',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'distributor_id' => 'permit_empty|is_natural_no_zero',
        'name'           => 'required|max_length[255]',
        'owner'          => 'required|max_length[255]',
        'address'        => 'required',
        'phone'          => 'required|max_length[20]',
    ];
}
