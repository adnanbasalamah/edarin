<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class ProductsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private string $adminToken = '';

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
    }

    public function testListProductsReturnsArray()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/products');

        $result->assertStatus(200);
    }

    public function testCreateProductAndDelete()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/products', [
            'name' => 'Test Product',
            'price' => 15000,
            'unit' => 'pcs',
        ]);

        $createResult->assertStatus(201);
        $json = json_decode($createResult->getJSON(), true);
        $productId = $json['id'] ?? null;
        $this->assertNotNull($productId);

        $deleteResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->delete('api/products/' . $productId);

        $deleteResult->assertStatus(200);
    }

    public function testCreateProductFailsWithoutName()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/products', [
            'price' => 15000,
            'unit' => 'pcs',
        ]);

        $result->assertStatus(400);
    }

    public function testGetSingleProductNotFound()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/products/999');

        $result->assertStatus(404);
    }
}
