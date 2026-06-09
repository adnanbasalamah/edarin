<?php

use CodeIgniter\Test\CIUnitTestCase;

class JWTHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('jwt');
    }

    public function testGenerateJWTReturnsString()
    {
        $user = [
            'id' => 1,
            'role' => 'admin',
            'username' => 'testuser',
        ];

        $token = generateJWT($user);
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function testValidateJWTWithValidToken()
    {
        $user = [
            'id' => 1,
            'role' => 'admin',
            'username' => 'testuser',
        ];

        $token = generateJWT($user);
        $decoded = validateJWT($token);

        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded->sub);
        $this->assertEquals('admin', $decoded->role);
        $this->assertEquals('testuser', $decoded->username);
    }

    public function testValidateJWTWithInvalidToken()
    {
        $result = validateJWT('invalid.token.here');
        $this->assertNull($result);
    }

    public function testValidateJWTWithExpiredToken()
    {
        $result = validateJWT('eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjF9.fake');
        $this->assertNull($result);
    }
}
