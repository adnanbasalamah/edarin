<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class DistributorsTest extends CIUnitTestCase
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

    public function testListDistributorsReturnsArray()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/distributors');

        $result->assertStatus(200);
    }

    public function testCreateDistributorAndDeactivate()
    {
        $createResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/distributors', [
            'username' => 'distributor_test',
            'email'    => 'distributor@test.com',
        ]);

        $createResult->assertStatus(201);
        $json = json_decode($createResult->getJSON(), true);
        $this->assertNotNull($json['id']);
        $this->assertArrayHasKey('password', $json);
        $this->assertNotEmpty($json['password']);

        $deleteResult = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->delete('api/distributors/' . $json['id']);

        $deleteResult->assertStatus(200);
    }

    public function testCreateDistributorFailsWithoutUsername()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/distributors', [
            'email' => 'distributor@test.com',
        ]);

        $result->assertStatus(400);
    }

    public function testGetSingleDistributorNotFound()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->get('api/distributors/999');

        $result->assertStatus(404);
    }

    public function testCreateDistributorReturnsGeneratedPassword()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/distributors', [
            'username' => 'distributor_pass_test',
            'email'    => 'distributor_pass@test.com',
        ]);

        $result->assertStatus(201);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('password', $json);
        $this->assertEquals(8, strlen($json['password']));
    }

    public function testCreateDistributorWithManualPassword()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->withBodyFormat('json')->post('api/distributors', [
            'username' => 'distributor_manual',
            'email'    => 'distributor_manual@test.com',
            'password' => 'rahasia123',
        ]);

        $result->assertStatus(201);
        $json = json_decode($result->getJSON(), true);
        $this->assertEquals('rahasia123', $json['password']);
    }
}
