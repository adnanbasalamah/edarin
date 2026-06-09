<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class JWTAuthFilterTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $migrate = false;

    public function testProtectedRouteFailsWithoutToken()
    {
        $result = $this->post('api/auth/refresh');
        $result->assertStatus(401);
    }

    public function testProtectedRouteFailsWithInvalidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer invalid.jwt.token',
        ])->post('api/auth/refresh');

        $result->assertStatus(401);
    }
}
