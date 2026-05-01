<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class SupplierController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $search = Request::get('search');

        $sql = "SELECT * FROM suppliers WHERE company_id = ? AND is_active = 1";
        $params = [$this->companyId()];

        if ($search) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY name";

        $this->render('suppliers.index', [
            'title' => 'Suppliers',
            'suppliers' => Database::fetchAll($sql, $params),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireCompany();
        $this->render('suppliers.form', ['title' => 'New Supplier', 'supplier' => null]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $name = Request::post('name');
        if (empty($name)) {
            flash('error', 'Supplier name is required.');
            $this->redirect('/suppliers/create');
        }

        Database::insert('suppliers', [
            'company_id' => $this->companyId(),
            'name' => $name,
            'contact_person' => Request::post('contact_person'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'address' => Request::post('address'),
            'tax_number' => Request::post('tax_number'),
        ]);

        flash('success', 'Supplier created.');
        $this->redirect('/suppliers');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $supplier = Database::fetch("SELECT * FROM suppliers WHERE id = ? AND company_id = ? AND is_active = 1", [$id, $this->companyId()]);
        if (!$supplier) { flash('error', 'Supplier not found.'); $this->redirect('/suppliers'); }

        $this->render('suppliers.form', ['title' => 'Edit Supplier', 'supplier' => $supplier]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('suppliers', [
            'name' => Request::post('name'),
            'contact_person' => Request::post('contact_person'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'address' => Request::post('address'),
            'tax_number' => Request::post('tax_number'),
        ], 'id = ? AND company_id = ?', [$id, $this->companyId()]);

        flash('success', 'Supplier updated.');
        $this->redirect('/suppliers');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();
        Database::update('suppliers', ['is_active' => 0], 'id = ? AND company_id = ?', [$id, $this->companyId()]);
        flash('success', 'Supplier deleted.');
        $this->redirect('/suppliers');
    }
}
