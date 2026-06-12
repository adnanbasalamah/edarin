<?php

namespace App\Controllers;

use App\Models\StoreModel;
use App\Models\AuditLogModel;

class Stores extends BaseController
{
    public function index()
    {
        $storeModel = new StoreModel();
        $userId = $this->request->user->sub ?? null;
        $role = $this->request->user->role ?? '';

        $builder = $storeModel->builder();

        if ($role !== 'admin') {
            $builder->where('distributor_id', $userId);
        }

        $stores = $builder->get()->getResultArray();

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

        $role = $this->request->user->role ?? '';
        $userId = $this->request->user->sub ?? null;

        if ($role !== 'admin' && (int) $store['distributor_id'] !== (int) $userId) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Forbidden: you do not own this store']);
        }

        return $this->response->setJSON($store);
    }

    public function create()
    {
        try {
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

            $userId = $this->request->user->sub ?? null;
            $role = $this->request->user->role ?? '';

            $distributorId = $userId;
            if ($role === 'admin') {
                $inputDistId = $this->getInput('distributor_id');
                $distributorId = $inputDistId !== null && $inputDistId !== '' ? $inputDistId : null;
            }

            $storeModel = new StoreModel();
            $storeModel->insert([
                'distributor_id' => $distributorId,
                'name'    => $this->getInput('name'),
                'owner'   => $this->getInput('owner'),
                'address' => $this->getInput('address'),
                'phone'   => $this->getInput('phone'),
                'latitude'  => $this->getInput('latitude') ?: null,
                'longitude' => $this->getInput('longitude') ?: null,
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
        } catch (\Throwable $e) {
            log_message('error', 'Store create error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function update($id = null)
    {
        try {
            $storeModel = new StoreModel();
            $store = $storeModel->find($id);

            if ($store === null) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON(['error' => 'Store not found']);
            }

            $role = $this->request->user->role ?? '';
            $userId = $this->request->user->sub ?? null;

            if ($role !== 'admin' && (int) $store['distributor_id'] !== (int) $userId) {
                return $this->response
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'Forbidden: you do not own this store']);
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
                if ($value !== null && $value !== '') {
                    $data[$field] = $value;
                } else if ($value === '' && in_array($field, ['latitude', 'longitude'])) {
                    $data[$field] = null;
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
                if (! $storeModel->update($id, $data)) {
                    return $this->response
                        ->setStatusCode(400)
                        ->setJSON(['error' => 'Update failed', 'messages' => $storeModel->errors()]);
                }
            }

            $this->logAudit('update', 'store', $id);

            return $this->response->setJSON(['message' => 'Store updated', 'image' => $imagePath]);
        } catch (\Throwable $e) {
            log_message('error', 'Store update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => $e->getMessage()]);
        }
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

        $role = $this->request->user->role ?? '';
        $userId = $this->request->user->sub ?? null;

        if ($role !== 'admin' && (int) $store['distributor_id'] !== (int) $userId) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Forbidden: you do not own this store']);
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
            mkdir($uploadDir, 0777, true);
        }

        $ext = match ($mimeType) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $filename = 'store_' . $storeId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        try {
            \Config\Services::image()
                ->withFile($file)
                ->fit(800, 800, 'center')
                ->save($filepath, 85);
        } catch (\Throwable $e) {
            if ($file->move($uploadDir, $filename)) {
                return 'uploads/stores/' . $filename;
            }
            log_message('error', 'Store image save failed: ' . $e->getMessage());
            return null;
        }

        return 'uploads/stores/' . $filename;
    }

    private function logAudit(string $action, string $entityType, ?int $entityId): void
    {
        try {
            $auditLog = new AuditLogModel();
            $payload = $this->request->getPost() ?: $this->request->getJSON(true) ?: [];
            $userId = null;
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($authHeader, 'Bearer ')) {
                helper('jwt');
                $decoded = validateJWT(substr($authHeader, 7));
                if ($decoded) {
                    $userId = $decoded->sub ?? null;
                }
            }

            $auditLog->insert([
                'user_id'      => $userId,
                'action'       => $action,
                'entity_type'  => $entityType,
                'entity_id'    => $entityId,
                'details'      => json_encode($payload),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log error: ' . $e->getMessage());
        }
    }
}
