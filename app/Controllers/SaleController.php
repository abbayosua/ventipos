<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SaleController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $sales = Database::fetchAll(
            "SELECT s.*, c.name as customer_name, u.name as user_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.created_by
             WHERE s.outlet_id = ?
             ORDER BY s.created_at DESC",
            [$this->outletId()]
        );

        $this->render('sales.index', [
            'title' => 'Sales',
            'sales' => $sales,
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $sale = Database::fetch(
            "SELECT s.*, c.name as customer_name, c.phone as customer_phone, u.name as user_name, o.name as outlet_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.created_by
             LEFT JOIN outlets o ON o.id = s.outlet_id
             WHERE s.id = ? AND s.outlet_id = ?",
            [$id, $this->outletId()]
        );

        if (!$sale) {
            flash('error', 'Sale not found.');
            $this->redirect('/sales');
        }

        $items = Database::fetchAll(
            "SELECT si.*, p.name as product_name, p.sku
             FROM sale_items si
             JOIN products p ON p.id = si.product_id
             WHERE si.sale_id = ?",
            [$id]
        );

        $payments = Database::fetchAll(
            "SELECT * FROM payments WHERE sale_id = ?",
            [$id]
        );

        $this->render('sales.show', [
            'title' => 'Sale #' . $sale->invoice_no,
            'sale' => $sale,
            'items' => $items,
            'payments' => $payments,
        ]);
    }
}
