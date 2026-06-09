<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class IntegrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = null;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';

    private string $token = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();
    }

    public function testCompleteAdminWorkflow()
    {
        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123',
            ]);
        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->token = $json['token'] ?? '';
        $this->assertNotEmpty($this->token);

        $headers = ['Authorization' => 'Bearer ' . $this->token];

        $productRes = $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/products', [
                'name' => 'Produk A', 'price' => 5000, 'unit' => 'pcs',
            ]);
        $productRes->assertStatus(201);
        $productId = json_decode($productRes->getJSON(), true)['id'];

        $storeRes = $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/stores', [
                'name' => 'Toko A', 'owner' => 'Budi', 'address' => 'Jl. A', 'phone' => '081',
            ]);
        $storeRes->assertStatus(201);
        $storeId = json_decode($storeRes->getJSON(), true)['id'];

        $distRes = $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/distributors', [
                'username' => 'distro_e2e', 'email' => 'distro_e2e@test.com',
            ]);
        $distRes->assertStatus(201);

        $saleRes = $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/sales', [
                'client_id' => 'e2e_' . uniqid(),
                'store_id' => $storeId,
                'product_id' => $productId,
                'quantity' => 10,
                'sale_date' => date('Y-m-d'),
            ]);
        $saleRes->assertStatus(201);

        $updateRes = $this->withHeaders($headers)
            ->withBodyFormat('json')->put('api/products/' . $productId, [
                'price' => 6000,
            ]);
        $updateRes->assertStatus(200);

        $listRes = $this->withHeaders($headers)->get('api/sales');
        $listRes->assertStatus(200);
        $sales = json_decode($listRes->getJSON(), true);
        $this->assertGreaterThanOrEqual(1, count($sales));
        $this->assertEquals('Toko A', $sales[0]['store_name']);
        $this->assertEquals('Produk A', $sales[0]['product_name']);

        $reportRes = $this->withHeaders($headers)->get('api/reports');
        $reportRes->assertStatus(200);

        $summaryRes = $this->withHeaders($headers)->get('api/sales/summary');
        $summaryRes->assertStatus(200);
    }

    public function testAuditLogCreatedOnCrudOperations()
    {
        $result = $this->withBodyFormat('json')
            ->post('api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123',
            ]);
        $json = json_decode($result->getJSON(), true);
        $token = $json['token'] ?? '';
        $headers = ['Authorization' => 'Bearer ' . $token];

        $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/products', [
                'name' => 'Audit Product', 'price' => 10000, 'unit' => 'pcs',
            ]);

        $this->withHeaders($headers)
            ->withBodyFormat('json')->post('api/stores', [
                'name' => 'Audit Store', 'owner' => 'Test', 'address' => 'Jl. Audit', 'phone' => '082',
            ]);

        $db = db_connect('tests');
        $auditLogs = $db->table('audit_log')
            ->where('entity_type', 'product')
            ->orWhere('entity_type', 'store')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $this->assertGreaterThanOrEqual(2, count($auditLogs));
        $actions = array_column($auditLogs, 'action');
        $this->assertContains('create', $actions);
    }
}
