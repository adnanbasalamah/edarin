<?php

namespace App\Models;

use CodeIgniter\Model;

class StoreModel extends Model
{
    protected $table = 'stores';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'owner',
        'address',
        'phone',
        'latitude',
        'longitude',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'name'    => 'required|max_length[255]',
        'owner'   => 'required|max_length[255]',
        'address' => 'required',
        'phone'   => 'required|max_length[20]',
    ];
}
