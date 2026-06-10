<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\NotaModel;

class NotaModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private NotaModel $model;
    private int $storeId = 0;
    private int $productId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new NotaModel();

        $db = db_connect();
        $db->table('stores')->insert([
            'name'    => 'Test Store',
            'owner'   => 'Test Owner',
            'address' => 'Jl. Test',
            'phone'   => '081111',
        ]);
        $this->storeId = $db->insertID();

        $db->table('products')->insert([
            'name'  => 'Test Product',
            'price' => 10000,
            'unit'  => 'pcs',
        ]);
        $this->productId = $db->insertID();
    }

    public function testCreateNota()
    {
        $data = [
            'client_id'      => 'nota_test_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $this->storeId,
            'note_date'      => date('Y-m-d'),
            'total_value'    => 250000.00,
            'sync_status'    => 'pending',
        ];

        $id = $this->model->insert($data);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $nota = $this->model->find($id);
        $this->assertNotNull($nota);
        $this->assertEquals($data['client_id'], $nota['client_id']);
        $this->assertEquals($data['total_value'], $nota['total_value']);
    }

    public function testNotaValidationFailsWithoutRequiredFields()
    {
        $result = $this->model->insert([
            'client_id' => 'test',
        ]);

        $this->assertFalse($result);
        $errors = $this->model->errors();
        $this->assertNotEmpty($errors);
    }

    public function testNotaBelongsToDistributor()
    {
        $id = $this->model->insert([
            'client_id'      => 'nota_rel_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $this->storeId,
            'note_date'      => date('Y-m-d'),
            'total_value'    => 100000.00,
            'sync_status'    => 'synced',
        ]);

        $nota = $this->model->find($id);
        $this->assertEquals(1, $nota['distributor_id']);
    }

    public function testNotaBelongsToStore()
    {
        $id = $this->model->insert([
            'client_id'      => 'nota_store_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $this->storeId,
            'note_date'      => date('Y-m-d'),
            'total_value'    => 50000.00,
            'sync_status'    => 'synced',
        ]);

        $nota = $this->model->find($id);
        $this->assertEquals($this->storeId, $nota['store_id']);
    }
}
