<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class AuthTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();
    }

    public function testLoginReturnsTokenWithValidCredentials()
    {
        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123',
            ]);

        $result->assertStatus(200);
        $result->assertJSONFragment([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
            ],
        ]);

        $json = $result->getJSON();
        $data = json_decode($json, true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFailsWithInvalidCredentials()
    {
        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'wrongpassword',
            ]);

        $result->assertStatus(401);
    }

    public function testLoginFailsWithMissingFields()
    {
        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
            ]);

        $result->assertStatus(400);
    }

    public function testRefreshFailsWithoutToken()
    {
        $result = $this->post('api/auth/refresh');
        $result->assertStatus(401);
    }

    public function testRefreshSucceedsWithValidToken()
    {
        $loginResult = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123',
            ]);

        $json = json_decode($loginResult->getJSON(), true);
        $token = $json['token'] ?? '';

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('api/auth/refresh');

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('token', $json);
        $this->assertNotEmpty($json['token']);
    }
}
