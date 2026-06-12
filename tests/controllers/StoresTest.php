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
    private string $distToken = '';
    private int $distUserId = 0;
    private string $dist2Token = '';
    private int $dist2UserId = 0;

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

        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'distributor',
                'password' => 'dist123',
            ]);
        $json = json_decode($result->getJSON(), true);
        $this->distToken = $json['token'] ?? '';
        $this->distUserId = $json['user']['id'] ?? 0;

        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'distributor2',
                'password' => 'dist2123',
            ]);
        $json = json_decode($result->getJSON(), true);
        $this->dist2Token = $json['token'] ?? '';
        $this->dist2UserId = $json['user']['id'] ?? 0;
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

    public function testShowStoreReturnsAllRequiredFields()
    {
        $db = db_connect();
        $db->table('stores')->insert([
            'distributor_id' => null,
            'name'           => 'Toko Detail Test',
            'owner'          => 'Pemilik Detail',
            'address'        => 'Jl. Detail No. 1',
            'phone'          => '08123456789',
            'image'          => 'uploads/stores/detail_test.jpg',
            'latitude'       => -6.2000000,
            'longitude'      => 106.8166667,
        ]);
        $storeId = $db->insertID();

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores/' . $storeId);

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('owner', $json);
        $this->assertArrayHasKey('address', $json);
        $this->assertArrayHasKey('phone', $json);
        $this->assertArrayHasKey('image', $json);
        $this->assertArrayHasKey('latitude', $json);
        $this->assertArrayHasKey('longitude', $json);
        $this->assertArrayHasKey('distributor_id', $json);

        $this->assertEquals('Toko Detail Test', $json['name']);
        $this->assertEquals('Pemilik Detail', $json['owner']);
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

    public function testDistributorCreateStoreHasDistributorId()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Distributor',
            'owner'   => 'Ahmad',
            'address' => 'Jl. Dist No. 1',
            'phone'   => '081200000001',
        ]);

        $createResult->assertStatus(201);
        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $showResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->get('api/stores/' . $storeId);

        $showResult->assertStatus(200);
        $store = json_decode($showResult->getJSON(), true);
        $this->assertEquals($this->distUserId, (int) $store['distributor_id']);
    }

    public function testDistributorCanOnlySeeOwnStores()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Milik Dist1',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. Dist1',
            'phone'   => '0812000001',
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->dist2Token,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Milik Dist2',
            'owner'   => 'Dist2 Owner',
            'address' => 'Jl. Dist2',
            'phone'   => '0812000002',
        ]);

        $result1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->get('api/stores');

        $result1->assertStatus(200);
        $stores1 = json_decode($result1->getJSON(), true);
        foreach ($stores1 as $store) {
            $this->assertEquals($this->distUserId, (int) $store['distributor_id']);
        }

        $result2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->dist2Token,
        ])->get('api/stores');

        $result2->assertStatus(200);
        $stores2 = json_decode($result2->getJSON(), true);
        foreach ($stores2 as $store) {
            $this->assertEquals($this->dist2UserId, (int) $store['distributor_id']);
        }
    }

    public function testDistributorCannotViewOtherDistributorStore()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Dist1 Only',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. Dist1 Only',
            'phone'   => '0812000003',
        ]);

        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->dist2Token,
        ])->get('api/stores/' . $storeId);

        $result->assertStatus(403);
    }

    public function testDistributorCannotUpdateOtherDistributorStore()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Update Test',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. Update Test',
            'phone'   => '0812000004',
        ]);

        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->dist2Token,
        ])->withBodyFormat('json')->put('api/stores/' . $storeId, [
            'name' => 'Hacked Store',
        ]);

        $result->assertStatus(403);
    }

    public function testAdminCanSeeAllStores()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Admin Visible 1',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. Admin1',
            'phone'   => '0812000005',
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->dist2Token,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Admin Visible 2',
            'owner'   => 'Dist2 Owner',
            'address' => 'Jl. Admin2',
            'phone'   => '0812000006',
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores');

        $result->assertStatus(200);
    }

    public function testAdminCanViewDistributorStore()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Admin View',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. AdminView',
            'phone'   => '0812000007',
        ]);

        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores/' . $storeId);

        $result->assertStatus(200);
    }

    public function testDistributorCanUpdateOwnStore()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Own Update',
            'owner'   => 'Dist1 Owner',
            'address' => 'Jl. OwnUpdate',
            'phone'   => '0812000008',
        ]);

        $json = json_decode($createResult->getJSON(), true);
        $storeId = $json['id'];

        $updateResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->distToken,
        ])->withBodyFormat('json')->put('api/stores/' . $storeId, [
            'name' => 'Toko Updated',
        ]);

        $updateResult->assertStatus(200);
    }
}