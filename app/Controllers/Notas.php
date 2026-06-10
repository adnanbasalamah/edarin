<?php

namespace App\Controllers;

use App\Models\NotaModel;
use App\Models\NotaItemModel;

class Notas extends BaseController
{
    public function index()
    {
        $notaModel = new NotaModel();
        $userId = $this->request->user->sub ?? null;
        $role = $this->request->user->role ?? '';

        $builder = $notaModel->builder();
        $builder->select('notas.*, stores.name as store_name, stores.owner as store_owner, (SELECT COUNT(*) FROM nota_items WHERE nota_items.nota_id = notas.id) as item_count');
        $builder->join('stores', 'stores.id = notas.store_id', 'left');

        if ($role !== 'admin') {
            $builder->where('notas.distributor_id', $userId);
        }

        $builder->orderBy('notas.created_at', 'DESC');

        $notas = $builder->get()->getResultArray();

        return $this->response->setJSON($notas);
    }

    public function show($id = null)
    {
        if ($id === null) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Nota ID is required']);
        }

        $notaModel = new NotaModel();
        $nota = $notaModel
            ->select('notas.*, stores.name as store_name, stores.owner as store_owner, stores.address as store_address, stores.phone as store_phone, users.username as distributor_name')
            ->join('stores', 'stores.id = notas.store_id', 'left')
            ->join('users', 'users.id = notas.distributor_id', 'left')
            ->find($id);

        if (! $nota) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Nota not found']);
        }

        $notaItemModel = new NotaItemModel();
        $items = $notaItemModel
            ->select('nota_items.*, products.name as product_name')
            ->join('products', 'products.id = nota_items.product_id', 'left')
            ->where('nota_items.nota_id', $id)
            ->findAll();

        $nota['items'] = $items;

        return $this->response->setJSON($nota);
    }
}
