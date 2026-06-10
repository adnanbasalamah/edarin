<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\NotaModel;
use App\Models\NotaItemModel;
use App\Models\AuditLogModel;
use App\Models\ProductModel;

class Sales extends BaseController
{
    public function index()
    {
        $saleModel = new SaleModel();
        $userId = $this->request->user->sub ?? null;
        $role = $this->request->user->role ?? '';

        $builder = $saleModel->builder();
        $builder->select('sales.*, stores.name as store_name, products.name as product_name');
        $builder->join('stores', 'stores.id = sales.store_id', 'left');
        $builder->join('products', 'products.id = sales.product_id', 'left');

        if ($role !== 'admin') {
            $builder->where('sales.distributor_id', $userId);
        }

        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        if ($dateFrom) $builder->where('sales.sale_date >=', $dateFrom);
        if ($dateTo) $builder->where('sales.sale_date <=', $dateTo);

        $sales = $builder->orderBy('sales.created_at', 'DESC')->get()->getResultArray();

        return $this->response->setJSON($sales);
    }

    public function summary()
    {
        $saleModel = new SaleModel();
        $today = date('Y-m-d');
        $db = db_connect();

        $todayCount = $saleModel->where('sale_date', $today)->countAllResults();

        $todaySales = $db->table('sales s')
            ->select('COALESCE(SUM(s.quantity * p.price), 0) as total_sales')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->where('s.sale_date', $today)
            ->get()
            ->getRow();

        $totalSales = $db->table('sales s')
            ->select('COALESCE(SUM(s.quantity * p.price), 0) as total_sales')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->get()
            ->getRow();

        $returnSales = $db->table('sales s')
            ->select('COALESCE(SUM(s.return_qty * p.price), 0) as total_return')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->get()
            ->getRow();

        $totalReturn = (float) ($returnSales->total_return ?? 0);
        $totalAll = (float) ($totalSales->total_sales ?? 1);
        $returnRate = $totalAll > 0 ? round(($totalReturn / $totalAll) * 100, 1) : 0;

        return $this->response->setJSON([
            'today_sales'     => $todayCount,
            'today_sales_idr' => (int) ($todaySales->total_sales ?? 0),
            'total_sales_idr' => (int) $totalAll,
            'return_rate'     => $returnRate,
        ]);
    }

    public function create()
    {
        $rules = [
            'client_id'     => 'required|max_length[255]',
            'store_id'      => 'required|numeric',
            'product_id'    => 'required|numeric',
            'quantity'      => 'required|numeric|greater_than_equal_to[0]',
            'return_qty'    => 'permit_empty|numeric|greater_than_equal_to[0]',
            'sale_date'     => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $saleModel = new SaleModel();
        $clientId = $this->getInput('client_id');

        $existing = $saleModel->where('client_id', $clientId)->first();
        if ($existing) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON(['message' => 'Sale already exists', 'id' => $existing['id']]);
        }

        $userId = $this->request->user->sub ?? null;
        $storeId = $this->getInput('store_id');
        $productId = $this->getInput('product_id');
        $quantity = $this->getInput('quantity');
        $returnQty = $this->getInput('return_qty') ?? 0;
        $saleDate = $this->getInput('sale_date');

        $saleModel->insert([
            'client_id'      => $clientId,
            'distributor_id' => $userId,
            'store_id'       => $storeId,
            'product_id'     => $productId,
            'quantity'       => $quantity,
            'return_qty'     => $returnQty,
            'sale_date'      => $saleDate,
            'sync_status'    => 'synced',
        ]);

        $saleId = $saleModel->insertID;

        $notaId = $this->syncSaleToNota($userId, $storeId, $productId, $quantity, $returnQty, $saleDate);

        $this->logAudit('create', 'sale', $saleId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['message' => 'Sale created', 'id' => $saleId, 'nota_id' => $notaId]);
    }

    private function syncSaleToNota(int $distributorId, int $storeId, int $productId, int $quantity, int $returnQty, string $saleDate): int
    {
        $notaModel = new NotaModel();
        $notaItemModel = new NotaItemModel();
        $productModel = new ProductModel();

        $product = $productModel->find($productId);
        $price = $product ? (float) $product['price'] : 0;

        $existingNota = $notaModel
            ->where('store_id', $storeId)
            ->where('note_date', $saleDate)
            ->where('distributor_id', $distributorId)
            ->first();

        if ($existingNota) {
            $notaId = $existingNota['id'];

            $notaItemModel->insert([
                'nota_id'    => $notaId,
                'product_id' => $productId,
                'quantity'   => $quantity,
                'return_qty' => $returnQty,
                'price'      => $price,
            ]);

            $newTotal = (float) $existingNota['total_value'] + ($quantity * $price);
            $notaModel->update($notaId, ['total_value' => $newTotal]);
        } else {
            $notaId = $notaModel->insert([
                'client_id'      => 'nota_' . $notaModel->countAll() . '_' . uniqid(),
                'distributor_id' => $distributorId,
                'store_id'       => $storeId,
                'note_date'      => $saleDate,
                'total_value'    => $quantity * $price,
                'sync_status'    => 'synced',
            ]);

            $notaItemModel->insert([
                'nota_id'    => $notaId,
                'product_id' => $productId,
                'quantity'   => $quantity,
                'return_qty' => $returnQty,
                'price'      => $price,
            ]);
        }

        return $notaId;
    }

    private function logAudit(string $action, string $entityType, ?int $entityId): void
    {
        $auditLog = new AuditLogModel();
        $userId = $this->request->user->sub ?? null;

        $auditLog->insert([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => json_encode($this->getInputAll()),
        ]);
    }
}
