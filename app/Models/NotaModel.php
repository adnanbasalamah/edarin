<?php

namespace App\Models;

use CodeIgniter\Model;

class NotaModel extends Model
{
    protected $table = 'notas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'distributor_id',
        'store_id',
        'note_date',
        'total_value',
        'sync_status',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'client_id'      => 'required|max_length[255]',
        'distributor_id' => 'required|numeric',
        'store_id'       => 'required|numeric',
        'note_date'      => 'required|valid_date[Y-m-d]',
        'total_value'    => 'permit_empty|decimal',
        'sync_status'    => 'permit_empty|in_list[pending,synced,failed]',
    ];
}
