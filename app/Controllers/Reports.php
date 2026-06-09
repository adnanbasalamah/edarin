<?php

namespace App\Controllers;

use App\Models\SaleModel;

class Reports extends BaseController
{
    public function dashboard()
    {
        $db = db_connect();

        $topProducts = $db->table('sales s')
            ->select('p.name, p.unit, SUM(s.quantity) as total_qty, SUM(s.quantity * p.price) as total_revenue')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->groupBy('s.product_id')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $topStores = $db->table('sales s')
            ->select('st.name, SUM(s.quantity * p.price) as total_sales')
            ->join('stores st', 'st.id = s.store_id', 'left')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->groupBy('s.store_id')
            ->orderBy('total_sales', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $returnStores = $db->table('sales s')
            ->select('st.name, SUM(s.return_qty * p.price) as total_return, SUM(s.quantity * p.price) as total_sales')
            ->join('stores st', 'st.id = s.store_id', 'left')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->groupBy('s.store_id')
            ->having('total_return >', 0)
            ->orderBy('total_return', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        $monthlyTrend = $db->table('sales s')
            ->select("DATE_FORMAT(s.sale_date, '%Y-%m') as month, SUM(s.quantity * p.price) as total")
            ->join('products p', 'p.id = s.product_id', 'left')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->limit(12)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'top_products' => $topProducts,
            'top_stores'   => $topStores,
            'return_stores' => $returnStores,
            'monthly_trend' => $monthlyTrend,
        ]);
    }

    public function stats()
    {
        $db = db_connect();
        $userId = $this->request->user->sub ?? null;
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');

        $period = $this->request->getGet('period') ?? 'today';

        switch ($period) {
            case 'today': $from = $today; $to = $today; break;
            case 'week': $from = $weekStart; $to = $today; break;
            case 'month': $from = $monthStart; $to = $today; break;
            default: $from = $today; $to = $today;
        }

        $stats = $db->table('sales s')
            ->select('COUNT(*) as count, COALESCE(SUM(s.quantity * p.price), 0) as total_revenue')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->where('s.distributor_id', $userId)
            ->where('s.sale_date >=', $from)
            ->where('s.sale_date <=', $to)
            ->get()
            ->getRow();

        $totalRevenue = (int) ($stats->total_revenue ?? 0);
        $count = (int) ($stats->count ?? 0);
        $avgPerTx = $count > 0 ? (int) ($totalRevenue / $count) : 0;

        $prevDay = date('Y-m-d', strtotime('-1 day', strtotime($from)));
        $prevStats = $db->table('sales s')
            ->select('COALESCE(SUM(s.quantity * p.price), 0) as total_revenue')
            ->join('products p', 'p.id = s.product_id', 'left')
            ->where('s.distributor_id', $userId)
            ->where('s.sale_date', $prevDay)
            ->get()
            ->getRow();
        $prevRevenue = (int) ($prevStats->total_revenue ?? 0);
        $percentChange = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

        $daily = $db->table('sales s')
            ->select("s.sale_date, COALESCE(SUM(s.quantity * p.price), 0) as total")
            ->join('products p', 'p.id = s.product_id', 'left')
            ->where('s.distributor_id', $userId)
            ->where('s.sale_date >=', $weekStart)
            ->where('s.sale_date <=', $today)
            ->groupBy('s.sale_date')
            ->orderBy('s.sale_date', 'ASC')
            ->get()
            ->getResultArray();

        $transactions = $db->table('sales s')
            ->select('s.client_id, s.sale_date, s.quantity, s.return_qty, s.sync_status, st.name as store_name, s.created_at')
            ->join('stores st', 'st.id = s.store_id', 'left')
            ->where('s.distributor_id', $userId)
            ->where('s.sale_date >=', $from)
            ->where('s.sale_date <=', $to)
            ->orderBy('s.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $chartData = [];
        for ($d = strtotime($weekStart); $d <= strtotime($today); $d = strtotime('+1 day', $d)) {
            $dateStr = date('Y-m-d', $d);
            $found = false;
            foreach ($daily as $day) {
                if ($day['sale_date'] === $dateStr) {
                    $chartData[] = ['day' => $dayNames[date('w', $d)], 'date' => $dateStr, 'total' => (float) $day['total']];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $chartData[] = ['day' => $dayNames[date('w', $d)], 'date' => $dateStr, 'total' => 0];
            }
        }

        return $this->response->setJSON([
            'total_revenue'   => $totalRevenue,
            'count'           => $count,
            'avg_per_tx'      => $avgPerTx,
            'percent_change'  => $percentChange,
            'chart_data'      => $chartData,
            'transactions'    => $transactions,
        ]);
    }

    public function download()
    {
        $saleModel = new SaleModel();
        $userId = $this->request->user->sub ?? null;
        $role = $this->request->user->role ?? '';

        $builder = $saleModel->builder();
        $builder->select('sales.sale_date, stores.name as store_name, products.name as product_name, products.price, sales.quantity, sales.return_qty');
        $builder->join('stores', 'stores.id = sales.store_id', 'left');
        $builder->join('products', 'products.id = sales.product_id', 'left');

        if ($role !== 'admin') {
            $builder->where('sales.distributor_id', $userId);
        }

        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        if ($dateFrom) $builder->where('sales.sale_date >=', $dateFrom);
        if ($dateTo) $builder->where('sales.sale_date <=', $dateTo);

        $sales = $builder->orderBy('sales.sale_date', 'DESC')->limit(10000)->get()->getResultArray();

        $filename = 'laporan_penjualan_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Tanggal', 'Toko', 'Product', 'Harga', 'Jual', 'Retur', 'Total']);

        foreach ($sales as $sale) {
            $total = ($sale['quantity'] * $sale['price']);
            fputcsv($output, [
                $sale['sale_date'],
                $sale['store_name'],
                $sale['product_name'],
                $sale['price'],
                $sale['quantity'],
                $sale['return_qty'],
                $total,
            ]);
        }

        fclose($output);
        exit;
    }

    public function index()
    {
        $saleModel = new SaleModel();
        $userId = $this->request->user->sub ?? null;
        $role = $this->request->user->role ?? '';

        $builder = $saleModel->builder();
        $builder->select('sales.*, stores.name as store_name, products.name as product_name, products.price as product_price');
        $builder->join('stores', 'stores.id = sales.store_id', 'left');
        $builder->join('products', 'products.id = sales.product_id', 'left');

        if ($role !== 'admin') {
            $builder->where('sales.distributor_id', $userId);
        }

        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        if ($dateFrom) $builder->where('sales.sale_date >=', $dateFrom);
        if ($dateTo) $builder->where('sales.sale_date <=', $dateTo);

        $sales = $builder->orderBy('sales.sale_date', 'DESC')->get()->getResultArray();

        return $this->response->setJSON($sales);
    }
}
