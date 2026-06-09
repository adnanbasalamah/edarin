<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class StoresTest extends CIUnitTestCase
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

    public function testListStoresReturnsArray()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores');

        $result->assertStatus(200);
    }

    public function testCreateStoreAndDelete()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Sembako Makmur',
            'owner'   => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 123',
            'phone'   => '08123456789',
        ]);

        $createResult->assertStatus(201);
        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'] ?? null;
        $this->assertNotNull($storeId);

        $deleteResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->delete('api/stores/' . $storeId);

        $deleteResult->assertStatus(200);
    }

    public function testCreateStoreFailsWithoutName()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'owner'   => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 123',
            'phone'   => '08123456789',
        ]);

        $result->assertStatus(400);
    }

    public function testGetSingleStoreNotFound()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores/999');

        $result->assertStatus(404);
    }

    public function testUpdateStore()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Lama',
            'owner'   => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 123',
            'phone'   => '08123456789',
        ]);

        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $updateResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->put('api/stores/' . $storeId, [
            'name' => 'Toko Baru',
        ]);

        $updateResult->assertStatus(200);
    }
}
