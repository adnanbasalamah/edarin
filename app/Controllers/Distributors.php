<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditLogModel;

class Distributors extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $distributors = $userModel->where('role', 'distributor')->findAll();

        return $this->response->setJSON($distributors);
    }

    public function show($id = null)
    {
        $userModel = new UserModel();
        $distributor = $userModel->where('role', 'distributor')->find($id);

        if ($distributor === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Distributor not found']);
        }

        return $this->response->setJSON($distributor);
    }

    public function create()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $inputPassword = $this->getInput('password');
        $plainPassword = $inputPassword ?: bin2hex(random_bytes(4));
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        $userModel = new UserModel();
        $userModel->insert([
            'username'      => $this->getInput('username'),
            'email'         => $this->getInput('email'),
            'password_hash' => $passwordHash,
            'role'          => 'distributor',
            'status'        => 'active',
        ]);

        $distributorId = $userModel->insertID;

        $this->logAudit('create', 'distributor', $distributorId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'message'  => 'Distributor created',
                'id'       => $distributorId,
                'username' => $this->getInput('username'),
                'password' => $plainPassword,
            ]);
    }

    public function update($id = null)
    {
        $userModel = new UserModel();
        $distributor = $userModel->where('role', 'distributor')->find($id);

        if ($distributor === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Distributor not found']);
        }

        $rules = [
            'username' => 'permit_empty|min_length[3]|max_length[100]|is_unique[users.username,id,' . $id . ']',
            'email'    => 'permit_empty|valid_email|is_unique[users.email,id,' . $id . ']',
            'password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $data = [];
        $username = $this->getInput('username');
        $email = $this->getInput('email');
        $password = $this->getInput('password');

        if ($username !== null) {
            $data['username'] = $username;
        }
        if ($email !== null) {
            $data['email'] = $email;
        }
        if ($password !== null) {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($data !== []) {
            $userModel->update($id, $data);
        }

        $this->logAudit('update', 'distributor', $id);

        return $this->response->setJSON(['message' => 'Distributor updated']);
    }

    public function delete($id = null)
    {
        $userModel = new UserModel();
        $distributor = $userModel->where('role', 'distributor')->find($id);

        if ($distributor === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Distributor not found']);
        }

        $userModel->update($id, ['status' => 'inactive']);
        $this->logAudit('deactivate', 'distributor', $id);

        return $this->response->setJSON(['message' => 'Distributor deactivated']);
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
