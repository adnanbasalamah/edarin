<?php

namespace App\Controllers;

use App\Models\StoreModel;
use App\Models\AuditLogModel;

class Stores extends BaseController
{
    public function index()
    {
        $storeModel = new StoreModel();
        $stores = $storeModel->findAll();

        return $this->response->setJSON($stores);
    }

    public function show($id = null)
    {
        $storeModel = new StoreModel();
        $store = $storeModel->find($id);

        if ($store === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Store not found']);
        }

        return $this->response->setJSON($store);
    }

    public function create()
    {
        $rules = [
            'name'    => 'required|max_length[255]',
            'owner'   => 'required|max_length[255]',
            'address' => 'required',
            'phone'   => 'required|max_length[20]',
            'latitude'  => 'permit_empty|numeric',
            'longitude' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $storeModel = new StoreModel();
        $storeModel->insert([
            'name'    => $this->getInput('name'),
            'owner'   => $this->getInput('owner'),
            'address' => $this->getInput('address'),
            'phone'   => $this->getInput('phone'),
            'latitude'  => $this->getInput('latitude'),
            'longitude' => $this->getInput('longitude'),
        ]);

        $storeId = $storeModel->insertID;

        $this->logAudit('create', 'store', $storeId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['message' => 'Store created', 'id' => $storeId]);
    }

    public function update($id = null)
    {
        $storeModel = new StoreModel();
        $store = $storeModel->find($id);

        if ($store === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Store not found']);
        }

        $rules = [
            'name'    => 'permit_empty|max_length[255]',
            'owner'   => 'permit_empty|max_length[255]',
            'address' => 'permit_empty',
            'phone'   => 'permit_empty|max_length[20]',
            'latitude'  => 'permit_empty|numeric',
            'longitude' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $data = [];
        foreach (['name', 'owner', 'address', 'phone', 'latitude', 'longitude'] as $field) {
            $value = $this->getInput($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        $storeModel->update($id, $data);
        $this->logAudit('update', 'store', $id);

        return $this->response->setJSON(['message' => 'Store updated']);
    }

    public function delete($id = null)
    {
        $storeModel = new StoreModel();
        $store = $storeModel->find($id);

        if ($store === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Store not found']);
        }

        $storeModel->delete($id);
        $this->logAudit('delete', 'store', $id);

        return $this->response->setJSON(['message' => 'Store deleted']);
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
