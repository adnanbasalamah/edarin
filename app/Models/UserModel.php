<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username',
        'email',
        'password_hash',
        'role',
        'status',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username,id,{id}]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'role'     => 'required|in_list[admin,distributor]',
        'status'   => 'required|in_list[active,inactive]',
    ];
}
