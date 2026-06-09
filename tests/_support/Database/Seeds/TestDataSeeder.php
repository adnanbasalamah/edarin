<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@edarin.com',
            'password_hash' => '$2y$12$0VLTKmQyHdr7bU5vRXul2ue6oLqX/5fBmbskXlfaOlhuXNyHYPQvm',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
