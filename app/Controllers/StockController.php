<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class StockController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $search = Request::get('search');

        $sql = "SELECT p.id, p.name, p.sku, p.unit, COALESCE(s.quantity, 0) as quantity
                FROM products p
                LEFT JOIN stock s ON s.product_id = p.id AND s.outlet_id = ?
                WHERE p.company_id = ? AND p.is_active = 1";
        $params = [$this->outletId(), $this->companyId()];

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY p.name";

        $this->render('stock.index', [
            'title' => 'Stock',
            'items' => Database::fetchAll($sql, $params),
            'search' => $search,
        ]);
    }

    public function adjustmentForm(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $products = Database::fetchAll(
            "SELECT p.id, p.name, p.sku, COALESCE(s.quantity, 0) as current_qty
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id AND s.outlet_id = ?
             WHERE p.company_id = ? AND p.is_active = 1
             ORDER BY p.name",
            [$this->outletId(), $this->companyId()]
        );

        $this->render('stock.adjustment', [
            'title' => 'Stock Adjustment',
            'products' => $products,
        ]);
    }

    public function adjustmentStore(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $productId = Request::post('product_id');
        $type = Request::post('type');
        $quantity = (float) (Request::post('quantity', 0));
        $notes = Request::post('notes');

        if (!$productId || $quantity <= 0) {
            flash('error', 'Invalid product or quantity.');
            $this->redirect('/stock/adjustment');
        }

        try {
            Database::beginTransaction();

            if ($type === 'in') {
                Database::query(
                    "UPDATE stock SET quantity = quantity + ? WHERE product_id = ? AND outlet_id = ?",
                    [$quantity, $productId, $this->outletId()]
                );
            } elseif ($type === 'out') {
                $current = Database::fetch(
                    "SELECT quantity FROM stock WHERE product_id = ? AND outlet_id = ?",
                    [$productId, $this->outletId()]
                );
                if (!$current || $current->quantity < $quantity) {
                    throw new \RuntimeException('Insufficient stock to remove.');
                }
                Database::query(
                    "UPDATE stock SET quantity = quantity - ? WHERE product_id = ? AND outlet_id = ?",
                    [$quantity, $productId, $this->outletId()]
                );
            } else {
                Database::query(
                    "UPDATE stock SET quantity = ? WHERE product_id = ? AND outlet_id = ?",
                    [$quantity, $productId, $this->outletId()]
                );
            }

            Database::insert('stock_movements', [
                'product_id' => $productId,
                'outlet_id' => $this->outletId(),
                'type' => $type,
                'quantity' => $quantity,
                'notes' => $notes,
                'created_by' => $this->userId(),
            ]);

            Database::commit();
            flash('success', 'Stock adjusted.');
            $this->redirect('/stock');
        } catch (\Throwable $e) {
            Database::rollback();
            flash('error', $e->getMessage());
            $this->redirect('/stock/adjustment');
        }
    }

    public function movements(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $product = Database::fetch(
            "SELECT p.* FROM products p WHERE p.id = ? AND p.company_id = ?",
            [$id, $this->companyId()]
        );

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/stock');
        }

        $movements = Database::fetchAll(
            "SELECT sm.*, u.name as user_name
             FROM stock_movements sm
             LEFT JOIN users u ON u.id = sm.created_by
             WHERE sm.product_id = ? AND sm.outlet_id = ?
             ORDER BY sm.created_at DESC LIMIT 100",
            [$id, $this->outletId()]
        );

        $this->render('stock.movements', [
            'title' => 'Movements - ' . $product->name,
            'product' => $product,
            'movements' => $movements,
        ]);
    }
}
