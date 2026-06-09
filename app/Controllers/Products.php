<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\AuditLogModel;

class Products extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $products = $productModel->findAll();

        return $this->response->setJSON($products);
    }

    public function show($id = null)
    {
        $productModel = new ProductModel();
        $product = $productModel->find($id);

        if ($product === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Product not found']);
        }

        return $this->response->setJSON($product);
    }

    public function create()
    {
        $rules = [
            'name'  => 'required|max_length[255]',
            'price' => 'required|numeric|greater_than[0]',
            'unit'  => 'required|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $productModel = new ProductModel();
        $productModel->insert([
            'name' => $this->getInput('name'),
            'description' => $this->getInput('description'),
            'price' => $this->getInput('price'),
            'unit' => $this->getInput('unit'),
            'status' => $this->getInput('status') ?? 'active',
        ]);

        $productId = $productModel->insertID;

        $this->logAudit('create', 'product', $productId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['message' => 'Product created', 'id' => $productId]);
    }

    public function update($id = null)
    {
        $productModel = new ProductModel();
        $product = $productModel->find($id);

        if ($product === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Product not found']);
        }

        $rules = [
            'name'  => 'permit_empty|max_length[255]',
            'price' => 'permit_empty|numeric|greater_than[0]',
            'unit'  => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $data = [];
        foreach (['name', 'description', 'price', 'unit', 'status'] as $field) {
            $value = $this->getInput($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        $productModel->update($id, $data);
        $this->logAudit('update', 'product', $id);

        return $this->response->setJSON(['message' => 'Product updated']);
    }

    public function delete($id = null)
    {
        $productModel = new ProductModel();
        $product = $productModel->find($id);

        if ($product === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Product not found']);
        }

        $productModel->delete($id);
        $this->logAudit('delete', 'product', $id);

        return $this->response->setJSON(['message' => 'Product deleted']);
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
