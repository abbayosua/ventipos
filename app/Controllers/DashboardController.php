<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $companyId = $this->companyId();
        $outletId = $this->outletId();

        $todaySales = Database::fetch(
            "SELECT COALESCE(SUM(total), 0) as total FROM sales 
             WHERE outlet_id = ? AND DATE(created_at) = CURDATE()",
            [$outletId]
        )->total;

        $todayCount = Database::fetch(
            "SELECT COUNT(*) as count FROM sales 
             WHERE outlet_id = ? AND DATE(created_at) = CURDATE()",
            [$outletId]
        )->count;

        $productCount = Database::fetch(
            "SELECT COUNT(*) as count FROM products WHERE company_id = ? AND is_active = 1",
            [$companyId]
        )->count;

        $customerCount = Database::fetch(
            "SELECT COUNT(*) as count FROM customers WHERE company_id = ? AND is_active = 1",
            [$companyId]
        )->count;

        $lowStock = Database::fetchAll(
            "SELECT p.name as product_name, s.quantity 
             FROM stock s JOIN products p ON p.id = s.product_id 
             WHERE s.outlet_id = ? AND s.quantity <= 5 AND p.is_active = 1
             ORDER BY s.quantity ASC LIMIT 10",
            [$outletId]
        );

        $hourlySales = Database::fetchAll(
            "SELECT HOUR(created_at) as hour, SUM(total) as total 
             FROM sales WHERE outlet_id = ? AND DATE(created_at) = CURDATE() 
             GROUP BY HOUR(created_at) ORDER BY hour",
            [$outletId]
        );

        $chartLabels = [];
        $chartData = [];
        for ($h = 7; $h <= 22; $h++) {
            $chartLabels[] = sprintf('%02d:00', $h);
            $found = 0;
            foreach ($hourlySales as $row) {
                if ((int)$row->hour === $h) {
                    $found = (float)$row->total;
                    break;
                }
            }
            $chartData[] = $found;
        }

        $this->render('dashboard.index', [
            'title' => 'Dashboard',
            'todaySales' => $todaySales,
            'todayCount' => $todayCount,
            'productCount' => $productCount,
            'customerCount' => $customerCount,
            'lowStock' => $lowStock,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}
