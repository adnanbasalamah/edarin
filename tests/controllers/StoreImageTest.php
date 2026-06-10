<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class StoreImageTest extends CIUnitTestCase
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

    public function testCreateStoreWorksWithJsonBody()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/stores', [
            'name'    => 'Toko Test Image',
            'owner'   => 'Pemilik',
            'address' => 'Jl. Test',
            'phone'   => '081111',
        ]);

        $result->assertStatus(201);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('id', $json);
        $this->assertNull($json['image'], 'Image should be null when not uploading a file');
    }

    public function testShowStoreReturnsImageField()
    {
        $db = db_connect();
        $db->table('stores')->insert([
            'name'    => 'Toko Show Image',
            'owner'   => 'Owner',
            'address' => 'Alamat',
            'phone'   => '081111',
            'image'   => 'uploads/stores/test_image.jpg',
        ]);
        $storeId = $db->insertID();

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores/' . $storeId);

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('image', $json);
        $this->assertEquals('uploads/stores/test_image.jpg', $json['image']);
    }

    public function testStoreShowWithoutImageReturnsNull()
    {
        $db = db_connect();
        $db->table('stores')->insert([
            'name'    => 'Toko No Image',
            'owner'   => 'Owner',
            'address' => 'Alamat',
            'phone'   => '081111',
        ]);
        $storeId = $db->insertID();

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/stores/' . $storeId);

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('image', $json);
        $this->assertNull($json['image']);
    }

    public function testUpdateStoreWithImageField()
    {
        $db = db_connect();
        $db->table('stores')->insert([
            'name'    => 'Old Store',
            'owner'   => 'Owner',
            'address' => 'Old',
            'phone'   => '081111',
        ]);
        $storeId = $db->insertID();

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->put('api/stores/' . $storeId, [
            'name' => 'Updated Store',
        ]);

        $result->assertStatus(200);

        $db = db_connect();
        $updated = $db->table('stores')->where('id', $storeId)->get()->getRowArray();
        $this->assertEquals('Updated Store', $updated['name']);
    }
}
