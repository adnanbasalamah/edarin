<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Validation failed', 'messages' => $this->validator->getErrors()]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid credentials']);
        }

        if ($user['status'] !== 'active') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Account is inactive']);
        }

        $token = generateJWT($user);

        return $this->response->setJSON([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    }

    public function refresh()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized']);
        }

        $token = substr($authHeader, 7);
        $decoded = validateJWT($token);

        if ($decoded === null) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid or expired token']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($decoded->sub);

        if ($user === null || $user['status'] !== 'active') {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'User not found or inactive']);
        }

        $newToken = generateJWT($user);

        return $this->response->setJSON(['token' => $newToken]);
    }
}
