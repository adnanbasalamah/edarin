<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'client_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'distributor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'store_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'note_date' => [
                'type' => 'DATE',
            ],
            'total_value' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'sync_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'synced', 'failed'],
                'default' => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->addForeignKey('distributor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('store_id', 'stores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notas');
    }

    public function down()
    {
        $this->forge->dropTable('notas');
    }
}
