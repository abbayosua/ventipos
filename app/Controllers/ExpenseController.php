<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ExpenseController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo = Request::get('date_to', date('Y-m-t'));

        $expenses = Database::fetchAll(
            "SELECT e.*, u.name as user_name
             FROM expenses e
             LEFT JOIN users u ON u.id = e.created_by
             WHERE e.outlet_id = ? AND e.expense_date BETWEEN ? AND ?
             ORDER BY e.expense_date DESC, e.created_at DESC",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $total = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total
             FROM expenses WHERE outlet_id = ? AND expense_date BETWEEN ? AND ?",
            [$this->outletId(), $dateFrom, $dateTo]
        );

        $this->render('expenses.index', [
            'title' => 'Expenses',
            'expenses' => $expenses,
            'total' => $total->total,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $this->render('expenses.form', [
            'title' => 'New Expense',
            'expense' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::insert('expenses', [
            'outlet_id' => $this->outletId(),
            'category' => Request::post('category'),
            'amount' => (float) (Request::post('amount', 0)),
            'description' => Request::post('description'),
            'expense_date' => Request::post('expense_date', date('Y-m-d')),
            'created_by' => $this->userId(),
        ]);

        flash('success', 'Expense recorded.');
        $this->redirect('/expenses');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::delete('expenses', 'id = ? AND outlet_id = ?', [$id, $this->outletId()]);
        flash('success', 'Expense deleted.');
        $this->redirect('/expenses');
    }
}
