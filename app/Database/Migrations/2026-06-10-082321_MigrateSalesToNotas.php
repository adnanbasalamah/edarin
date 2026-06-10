<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateSalesToNotas extends Migration
{
    public function up()
    {
        $sales = $this->db->table('sales')
            ->select('store_id, distributor_id, sale_date')
            ->distinct()
            ->get()
            ->getResultArray();

        $notaModel = new \App\Models\NotaModel();
        $notaItemModel = new \App\Models\NotaItemModel();
        $productModel = new \App\Models\ProductModel();

        foreach ($sales as $group) {
            $items = $this->db->table('sales')
                ->where('store_id', $group['store_id'])
                ->where('distributor_id', $group['distributor_id'])
                ->where('sale_date', $group['sale_date'])
                ->get()
                ->getResultArray();

            if (empty($items)) {
                continue;
            }

            $totalValue = 0.0;

            $notaId = $notaModel->insert([
                'client_id'      => 'migrated_' . $group['store_id'] . '_' . $group['sale_date'] . '_' . uniqid(),
                'distributor_id' => $group['distributor_id'],
                'store_id'       => $group['store_id'],
                'note_date'      => $group['sale_date'],
                'total_value'    => 0.00,
                'sync_status'    => 'synced',
            ]);

            foreach ($items as $item) {
                $product = $productModel->find($item['product_id']);
                $price = $product ? (float) $product['price'] : 0;

                $lineTotal = (int) $item['quantity'] * $price;
                $totalValue += $lineTotal;

                $notaItemModel->insert([
                    'nota_id'    => $notaId,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'return_qty' => $item['return_qty'] ?? 0,
                    'price'      => $price,
                ]);
            }

            $notaModel->update($notaId, ['total_value' => $totalValue]);
        }
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->db->table('nota_items')->truncate();
        $this->db->table('notas')->truncate();
        $this->db->enableForeignKeyChecks();
    }
}
