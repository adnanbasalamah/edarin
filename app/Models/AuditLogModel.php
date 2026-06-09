<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $validationRules = [
        'user_id'     => 'permit_empty|is_natural_no_zero',
        'action'      => 'required|max_length[50]',
        'entity_type' => 'required|max_length[50]',
        'entity_id'   => 'permit_empty|is_natural_no_zero',
    ];
}
