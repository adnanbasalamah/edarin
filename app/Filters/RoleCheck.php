<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = $request->user ?? null;

        if ($user === null) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized'])
                ->send();
        }

        $requiredRole = $arguments[0] ?? null;

        if ($requiredRole !== null && $user->role !== $requiredRole) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Forbidden'])
                ->send();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
