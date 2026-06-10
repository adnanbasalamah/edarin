<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\NotaItemModel;

class NotaItemModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private NotaItemModel $model;
    private int $notaId = 0;
    private int $productId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new NotaItemModel();

        $db = db_connect();

        $db->table('stores')->insert([
            'name'    => 'Test Store',
            'owner'   => 'Test Owner',
            'address' => 'Jl. Test',
            'phone'   => '081111',
        ]);
        $storeId = $db->insertID();

        $db->table('products')->insert([
            'name'  => 'Test Product',
            'price' => 10000,
            'unit'  => 'pcs',
        ]);
        $this->productId = $db->insertID();

        $db->table('notas')->insert([
            'client_id'      => 'nota_items_test_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $storeId,
            'note_date'      => date('Y-m-d'),
            'total_value'    => 0.00,
            'sync_status'    => 'pending',
        ]);
        $this->notaId = $db->insertID();
    }

    public function testCreateNotaItem()
    {
        $data = [
            'nota_id'    => $this->notaId,
            'product_id' => $this->productId,
            'quantity'   => 10,
            'return_qty' => 2,
            'price'      => 15000.00,
        ];

        $id = $this->model->insert($data);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $item = $this->model->find($id);
        $this->assertNotNull($item);
        $this->assertEquals($data['nota_id'], $item['nota_id']);
        $this->assertEquals($data['quantity'], $item['quantity']);
        $this->assertEquals($data['price'], $item['price']);
    }

    public function testNotaItemValidationFailsWithoutRequiredFields()
    {
        $result = $this->model->insert([
            'nota_id' => $this->notaId,
        ]);

        $this->assertFalse($result);
        $errors = $this->model->errors();
        $this->assertNotEmpty($errors);
    }

    public function testNotaItemBelongsToNota()
    {
        $id = $this->model->insert([
            'nota_id'    => $this->notaId,
            'product_id' => $this->productId,
            'quantity'   => 5,
            'price'      => 20000.00,
        ]);

        $item = $this->model->find($id);
        $this->assertEquals($this->notaId, $item['nota_id']);
    }

    public function testNotaItemBelongsToProduct()
    {
        $id = $this->model->insert([
            'nota_id'    => $this->notaId,
            'product_id' => $this->productId,
            'quantity'   => 3,
            'return_qty' => 1,
            'price'      => 10000.00,
        ]);

        $item = $this->model->find($id);
        $this->assertEquals($this->productId, $item['product_id']);
    }
}
