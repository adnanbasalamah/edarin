<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialAdminSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@edarin.com',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
