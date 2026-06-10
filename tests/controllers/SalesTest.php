<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class SalesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private string $adminToken = '';
    private int $storeId = 0;
    private int $productId = 0;

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
            'name'    => 'Toko Testing',
            'owner'   => 'Test',
            'address' => 'Jl. Test',
            'phone'   => '081111',
        ]);
        $this->storeId = json_decode($storeRes->getJSON(), true)['id'] ?? 0;

        $productRes = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/products', [
            'name'  => 'Produk Test',
            'price' => 10000,
            'unit'  => 'pcs',
        ]);
        $this->productId = json_decode($productRes->getJSON(), true)['id'] ?? 0;
    }

    public function testListSalesReturnsArray()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/sales');

        $result->assertStatus(200);
    }

    public function testCreateSale()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => 'test_client_' . uniqid(),
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 10,
            'return_qty' => 2,
            'sale_date'  => date('Y-m-d'),
        ]);

        $result->assertStatus(201);
    }

    public function testCreateSaleFailsWithoutClientId()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 10,
            'sale_date'  => date('Y-m-d'),
        ]);

        $result->assertStatus(400);
    }

    public function testCreateDuplicateSaleReturns409()
    {
        $clientId = 'dup_client_' . uniqid();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => $clientId,
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 5,
            'sale_date'  => date('Y-m-d'),
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => $clientId,
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 5,
            'sale_date'  => date('Y-m-d'),
        ]);

        $result->assertStatus(409);
    }

    public function testSalesSummaryReturnsCount()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => 'summary_test_' . uniqid(),
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 3,
            'sale_date'  => date('Y-m-d'),
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/sales/summary');

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('today_sales', $json);
        $this->assertGreaterThanOrEqual(1, $json['today_sales']);
    }

    public function testCreateSaleAlsoCreatesNota()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => 'nota_test_' . uniqid(),
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 10,
            'return_qty' => 2,
            'sale_date'  => date('Y-m-d'),
        ]);

        $db = db_connect();
        $notas = $db->table('notas')->where('store_id', $this->storeId)->get()->getResultArray();
        $this->assertNotEmpty($notas, 'Nota should be created for the store.');
        $this->assertCount(1, $notas);

        $notaItems = $db->table('nota_items')->where('nota_id', $notas[0]['id'])->get()->getResultArray();
        $this->assertNotEmpty($notaItems, 'Nota items should be created.');
        $this->assertCount(1, $notaItems);
    }

    public function testCreateMultipleSalesSameStoreCreatesOneNota()
    {
        $clientId1 = 'multi_nota_1_' . uniqid();
        $clientId2 = 'multi_nota_2_' . uniqid();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => $clientId1,
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 5,
            'sale_date'  => date('Y-m-d'),
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/sales', [
            'client_id'  => $clientId2,
            'store_id'   => $this->storeId,
            'product_id' => $this->productId,
            'quantity'   => 3,
            'sale_date'  => date('Y-m-d'),
        ]);

        $db = db_connect();
        $notas = $db->table('notas')->where('store_id', $this->storeId)->get()->getResultArray();
        $this->assertCount(1, $notas, 'Only one nota should exist for the same store and date.');

        $notaItems = $db->table('nota_items')->where('nota_id', $notas[0]['id'])->get()->getResultArray();
        $this->assertCount(2, $notaItems, 'Both sales should be items in the same nota.');
    }
}
