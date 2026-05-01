<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class CustomerController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $search = Request::get('search');

        $sql = "SELECT * FROM customers WHERE company_id = ? AND is_active = 1";
        $params = [$this->companyId()];

        if ($search) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY name";

        $this->render('customers.index', [
            'title' => 'Customers',
            'customers' => Database::fetchAll($sql, $params),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireCompany();
        $this->render('customers.form', ['title' => 'New Customer', 'customer' => null]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $name = Request::post('name');
        if (empty($name)) {
            flash('error', 'Customer name is required.');
            $this->redirect('/customers/create');
        }

        Database::insert('customers', [
            'company_id' => $this->companyId(),
            'name' => $name,
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'address' => Request::post('address'),
            'tax_number' => Request::post('tax_number'),
        ]);

        flash('success', 'Customer created.');
        $this->redirect('/customers');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $customer = Database::fetch("SELECT * FROM customers WHERE id = ? AND company_id = ? AND is_active = 1", [$id, $this->companyId()]);
        if (!$customer) { flash('error', 'Customer not found.'); $this->redirect('/customers'); }

        $this->render('customers.form', ['title' => 'Edit Customer', 'customer' => $customer]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('customers', [
            'name' => Request::post('name'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'address' => Request::post('address'),
            'tax_number' => Request::post('tax_number'),
        ], 'id = ? AND company_id = ?', [$id, $this->companyId()]);

        flash('success', 'Customer updated.');
        $this->redirect('/customers');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();
        Database::update('customers', ['is_active' => 0], 'id = ? AND company_id = ?', [$id, $this->companyId()]);
        flash('success', 'Customer deleted.');
        $this->redirect('/customers');
    }
}
