<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\NotaModel;
use App\Models\NotaItemModel;

class MigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();
    }

    public function testExistingSalesGroupedIntoNotas()
    {
        $db = db_connect();

        $db->table('stores')->insert([
            'name'    => 'Migration Store',
            'owner'   => 'Owner',
            'address' => 'Jl. Migrate',
            'phone'   => '081111',
        ]);
        $storeId = $db->insertID();

        $db->table('products')->insert([
            'name'  => 'Migration Product',
            'price' => 10000,
            'unit'  => 'pcs',
        ]);
        $productId = $db->insertID();

        $saleDate = '2026-06-10';

        $db->table('sales')->insert([
            'client_id'      => 'mig_test_1_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $storeId,
            'product_id'     => $productId,
            'quantity'       => 5,
            'return_qty'     => 1,
            'sale_date'      => $saleDate,
            'sync_status'    => 'synced',
        ]);

        $db->table('sales')->insert([
            'client_id'      => 'mig_test_2_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $storeId,
            'product_id'     => $productId,
            'quantity'       => 3,
            'return_qty'     => 0,
            'sale_date'      => $saleDate,
            'sync_status'    => 'synced',
        ]);

        $db->table('sales')->insert([
            'client_id'      => 'mig_test_3_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $storeId,
            'product_id'     => $productId,
            'quantity'       => 2,
            'return_qty'     => 1,
            'sale_date'      => '2026-06-11',
            'sync_status'    => 'synced',
        ]);

        $migration = new \App\Database\Migrations\MigrateSalesToNotas();
        $migration->up();

        $notaModel = new NotaModel();
        $notas = $notaModel->where('store_id', $storeId)->findAll();

        $this->assertCount(2, $notas, 'Should create 2 notas: one per store per date.');

        $nota1 = $notaModel->where('store_id', $storeId)->where('note_date', '2026-06-10')->first();
        $this->assertNotNull($nota1);

        $notaItemModel = new NotaItemModel();
        $items1 = $notaItemModel->where('nota_id', $nota1['id'])->findAll();
        $this->assertCount(2, $items1, 'Nota for 2026-06-10 should have 2 items.');
        $this->assertEquals(5 * 10000 + 3 * 10000, (float) $nota1['total_value']);

        $nota2 = $notaModel->where('store_id', $storeId)->where('note_date', '2026-06-11')->first();
        $this->assertNotNull($nota2);

        $items2 = $notaItemModel->where('nota_id', $nota2['id'])->findAll();
        $this->assertCount(1, $items2, 'Nota for 2026-06-11 should have 1 item.');
        $this->assertEquals(2 * 10000, (float) $nota2['total_value']);
    }

    public function testMigrationHandlesEmptySales()
    {
        $db = db_connect();
        $db->table('sales')->truncate();

        $migration = new \App\Database\Migrations\MigrateSalesToNotas();
        $migration->up();

        $notaModel = new NotaModel();
        $this->assertEquals(0, $notaModel->countAllResults());
    }
}
