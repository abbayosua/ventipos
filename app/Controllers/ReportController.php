<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ReportController extends Controller
{
    public function daily(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $date = Request::get('date', date('Y-m-d'));

        $sales = Database::fetchAll(
            "SELECT s.*, c.name as customer_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             WHERE s.outlet_id = ? AND DATE(s.created_at) = ?
             ORDER BY s.created_at",
            [$this->outletId(), $date]
        );

        $summary = Database::fetch(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total,
                    COALESCE(SUM(tax_amount), 0) as tax, COALESCE(SUM(paid_amount), 0) as paid
             FROM sales WHERE outlet_id = ? AND DATE(created_at) = ?",
            [$this->outletId(), $date]
        );

        $expenses = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM expenses
             WHERE outlet_id = ? AND expense_date = ?",
            [$this->outletId(), $date]
        );

        $this->render('reports.daily', [
            'title' => 'Daily Report - ' . $date,
            'date' => $date,
            'sales' => $sales,
            'summary' => $summary,
            'expenseTotal' => $expenses->total,
        ]);
    }

    public function topProducts(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo = Request::get('date_to', date('Y-m-t'));

        $products = Database::fetchAll(
            "SELECT p.name, p.sku, SUM(si.quantity) as total_qty,
                    SUM(si.subtotal) as total_revenue, p.cost_price,
                    (SUM(si.subtotal) - (SUM(si.quantity) * p.cost_price)) as total_profit
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             JOIN products p ON p.id = si.product_id
             WHERE s.outlet_id = ? AND DATE(s.created_at) BETWEEN ? AND ?
             GROUP BY si.product_id
             ORDER BY total_qty DESC LIMIT 20",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $this->render('reports.top-products', [
            'title' => 'Top Products',
            'products' => $products,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function profitLoss(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo = Request::get('date_to', date('Y-m-t'));

        $salesData = Database::fetch(
            "SELECT COUNT(*) as sale_count,
                    COALESCE(SUM(total), 0) as gross_sales,
                    COALESCE(SUM(tax_amount), 0) as tax,
                    COALESCE(SUM(discount_amount), 0) as discounts
             FROM sales WHERE outlet_id = ? AND DATE(created_at) BETWEEN ? AND ?",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $cogs = Database::fetch(
            "SELECT COALESCE(SUM(si.quantity * p.cost_price), 0) as total_cogs
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             JOIN products p ON p.id = si.product_id
             WHERE s.outlet_id = ? AND DATE(s.created_at) BETWEEN ? AND ?",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $expenses = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total_expenses
             FROM expenses WHERE outlet_id = ? AND expense_date BETWEEN ? AND ?",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $netSales = $salesData->gross_sales - $salesData->tax - $salesData->discounts;
        $grossProfit = $netSales - $cogs->total_cogs;
        $netProfit = $grossProfit - $expenses->total_expenses;

        $this->render('reports.profit-loss', [
            'title' => 'Profit & Loss',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'salesData' => $salesData,
            'cogs' => $cogs->total_cogs,
            'expenses' => $expenses->total_expenses,
            'netSales' => $netSales,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
        ]);
    }
}
