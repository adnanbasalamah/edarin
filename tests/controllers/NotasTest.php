<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class NotasTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private string $adminToken = '';
    private int $storeId = 0;
    private int $productId = 0;
    private int $notaId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123',
            ]);

        $json = json_decode($result->getJSON(), true);
        $this->adminToken = $json['token'] ?? '';

        $storeRes = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Nota Test',
            'owner'   => 'Test Owner',
            'address' => 'Jl. Nota Test',
            'phone'   => '081111',
        ]);
        $this->storeId = json_decode($storeRes->getJSON(), true)['id'] ?? 0;

        $productRes = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/products', [
            'name'  => 'Produk Nota Test',
            'price' => 10000,
            'unit'  => 'pcs',
        ]);
        $this->productId = json_decode($productRes->getJSON(), true)['id'] ?? 0;

        $db = db_connect();
        $db->table('notas')->insert([
            'client_id'      => 'nota_list_test_' . uniqid(),
            'distributor_id' => 1,
            'store_id'       => $this->storeId,
            'note_date'      => date('Y-m-d'),
            'total_value'    => 250000.00,
            'sync_status'    => 'synced',
        ]);
        $this->notaId = $db->insertID();

        $db->table('nota_items')->insert([
            'nota_id'    => $this->notaId,
            'product_id' => $this->productId,
            'quantity'   => 10,
            'return_qty' => 2,
            'price'      => 25000.00,
        ]);
    }

    public function testListNotasReturnsArray()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/notas');

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
    }

    public function testShowNotaReturnsDetails()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/notas/' . $this->notaId);

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertEquals($this->notaId, $json['id'] ?? null);
        $this->assertArrayHasKey('items', $json);
    }

    public function testShowNotaNotFound()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/notas/99999');

        $result->assertStatus(404);
    }

    public function testNotasListedOnlyWithAuth()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/notas');

        $result->assertStatus(200);
    }
}
