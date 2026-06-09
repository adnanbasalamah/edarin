<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class Audit extends BaseController
{
    public function index()
    {
        $auditLog = new AuditLogModel();
        $builder = $auditLog->builder();
        $builder->select('audit_log.*, users.username');
        $builder->join('users', 'users.id = audit_log.user_id', 'left');
        $builder->orderBy('audit_log.created_at', 'DESC');
        $builder->limit(200);

        $logs = $builder->get()->getResultArray();

        return $this->response->setJSON($logs);
    }
}
