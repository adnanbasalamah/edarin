<?php

namespace App\Controllers;

use App\Models\StoreModel;
use App\Models\AuditLogModel;
use CodeIgniter\Images\Image;

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

        if (! $this->validate($rules)) {
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

        $imagePath = $this->processStoreImage($storeId);

        if ($imagePath) {
            $storeModel->update($storeId, ['image' => $imagePath]);
        }

        $this->logAudit('create', 'store', $storeId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['message' => 'Store created', 'id' => $storeId, 'image' => $imagePath]);
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

        if (! $this->validate($rules)) {
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

        $imagePath = $this->processStoreImage($id);

        if ($imagePath) {
            if ($store['image'] && file_exists(WRITEPATH . $store['image'])) {
                unlink(WRITEPATH . $store['image']);
            }
            $data['image'] = $imagePath;
        }

        if ($data !== []) {
            $storeModel->update($id, $data);
        }

        $this->logAudit('update', 'store', $id);

        return $this->response->setJSON(['message' => 'Store updated', 'image' => $imagePath]);
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

        if ($store['image'] && file_exists(WRITEPATH . $store['image'])) {
            unlink(WRITEPATH . $store['image']);
        }

        $storeModel->delete($id);
        $this->logAudit('delete', 'store', $id);

        return $this->response->setJSON(['message' => 'Store deleted']);
    }

    public function image(string $filename)
    {
        $filepath = WRITEPATH . 'uploads/stores/' . $filename;

        if (! is_file($filepath)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Image not found']);
        }

        $mimeType = match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string) filesize($filepath))
            ->setHeader('Cache-Control', 'max-age=86400')
            ->setBody(file_get_contents($filepath));
    }

    private function processStoreImage(int $storeId): ?string
    {
        $file = $this->request->getFile('image');

        if (! $file || ! $file->isValid()) {
            return null;
        }

        $mimeType = $file->getMimeType();
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array($mimeType, $allowedTypes)) {
            return null;
        }

        $uploadDir = WRITEPATH . 'uploads/stores/';

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = match ($mimeType) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $filename = 'store_' . $storeId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        \Config\Services::image()
            ->withFile($file)
            ->fit(800, 800, 'center')
            ->save($filepath, 85);

        return 'uploads/stores/' . $filename;
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
