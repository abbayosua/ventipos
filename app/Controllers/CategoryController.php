<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class CategoryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $categories = Database::fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) as product_count
             FROM categories c WHERE c.company_id = ? AND c.is_active = 1 ORDER BY c.name",
            [$this->companyId()]
        );

        $this->render('categories.index', [
            'title' => 'Categories',
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $this->render('categories.form', [
            'title' => 'New Category',
            'category' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $name = Request::post('name');
        $description = Request::post('description');

        if (empty($name)) {
            flash('error', 'Category name is required.');
            $this->redirect('/categories/create');
        }

        Database::insert('categories', [
            'company_id' => $this->companyId(),
            'name' => $name,
            'description' => $description,
        ]);

        flash('success', 'Category created.');
        $this->redirect('/categories');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $category = Database::fetch(
            "SELECT * FROM categories WHERE id = ? AND company_id = ? AND is_active = 1",
            [$id, $this->companyId()]
        );

        if (!$category) {
            flash('error', 'Category not found.');
            $this->redirect('/categories');
        }

        $this->render('categories.form', [
            'title' => 'Edit Category',
            'category' => $category,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $category = Database::fetch(
            "SELECT id FROM categories WHERE id = ? AND company_id = ?",
            [$id, $this->companyId()]
        );

        if (!$category) {
            flash('error', 'Category not found.');
            $this->redirect('/categories');
        }

        Database::update('categories', [
            'name' => Request::post('name'),
            'description' => Request::post('description'),
        ], 'id = ?', [$id]);

        flash('success', 'Category updated.');
        $this->redirect('/categories');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('categories', ['is_active' => 0], 'id = ? AND company_id = ?', [$id, $this->companyId()]);

        flash('success', 'Category deleted.');
        $this->redirect('/categories');
    }
}
