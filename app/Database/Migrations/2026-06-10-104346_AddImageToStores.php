<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageToStores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('stores', [
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('stores', 'image');
    }
}
