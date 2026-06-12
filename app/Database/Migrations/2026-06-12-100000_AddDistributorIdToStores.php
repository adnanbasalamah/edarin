<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDistributorIdToStores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('stores', [
            'distributor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id',
            ],
        ]);

        $this->db->query('ALTER TABLE stores ADD CONSTRAINT fk_stores_distributor FOREIGN KEY (distributor_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE stores DROP FOREIGN KEY fk_stores_distributor');
        $this->forge->dropColumn('stores', 'distributor_id');
    }
}