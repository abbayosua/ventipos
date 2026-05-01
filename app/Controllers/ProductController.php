<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ProductController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $companyId = $this->companyId();
        $search = Request::get('search');
        $categoryId = Request::get('category_id');

        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.company_id = ? AND p.is_active = 1";
        $params = [$companyId];

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY p.name";

        $products = Database::fetchAll($sql, $params);

        $categories = Database::fetchAll(
            "SELECT id, name FROM categories WHERE company_id = ? AND is_active = 1 ORDER BY name",
            [$companyId]
        );

        $this->render('products.index', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $categoryId,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $categories = Database::fetchAll(
            "SELECT id, name FROM categories WHERE company_id = ? AND is_active = 1 ORDER BY name",
            [$this->companyId()]
        );

        $this->render('products.form', [
            'title' => 'New Product',
            'product' => null,
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $companyId = $this->companyId();
        $name = Request::post('name');
        $sku = Request::post('sku');
        $barcode = Request::post('barcode');
        $categoryId = Request::post('category_id');
        $costPrice = (float) (Request::post('cost_price') ?? 0);
        $sellingPrice = (float) (Request::post('selling_price') ?? 0);
        $taxRate = (float) (Request::post('tax_rate') ?? 0);
        $unit = Request::post('unit') ?: 'pcs';
        $description = Request::post('description');

        if (empty($name)) {
            flash('error', 'Product name is required.');
            $this->redirect('/products/create');
        }

        try {
            Database::beginTransaction();

            $productId = Database::insert('products', [
                'company_id' => $companyId,
                'category_id' => $categoryId ?: null,
                'name' => $name,
                'sku' => $sku,
                'barcode' => $barcode,
                'description' => $description,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'tax_rate' => $taxRate,
                'unit' => $unit,
            ]);

            $outlets = Database::fetchAll(
                "SELECT id FROM outlets WHERE company_id = ? AND is_active = 1",
                [$companyId]
            );

            foreach ($outlets as $outlet) {
                Database::insert('stock', [
                    'product_id' => $productId,
                    'outlet_id' => $outlet->id,
                    'quantity' => 0,
                ]);
            }

            Database::commit();

            flash('success', 'Product created.');
            $this->redirect('/products');
        } catch (\Throwable $e) {
            Database::rollback();
            flash('error', 'Failed to create product: ' . $e->getMessage());
            $this->redirect('/products/create');
        }
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $product = Database::fetch(
            "SELECT * FROM products WHERE id = ? AND company_id = ? AND is_active = 1",
            [$id, $this->companyId()]
        );

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/products');
        }

        $categories = Database::fetchAll(
            "SELECT id, name FROM categories WHERE company_id = ? AND is_active = 1 ORDER BY name",
            [$this->companyId()]
        );

        $this->render('products.form', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $product = Database::fetch(
            "SELECT id FROM products WHERE id = ? AND company_id = ?",
            [$id, $this->companyId()]
        );

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/products');
        }

        $categoryId = Request::post('category_id');

        Database::update('products', [
            'category_id' => $categoryId ?: null,
            'name' => Request::post('name'),
            'sku' => Request::post('sku'),
            'barcode' => Request::post('barcode'),
            'description' => Request::post('description'),
            'cost_price' => (float) (Request::post('cost_price') ?? 0),
            'selling_price' => (float) (Request::post('selling_price') ?? 0),
            'tax_rate' => (float) (Request::post('tax_rate') ?? 0),
            'unit' => Request::post('unit') ?: 'pcs',
        ], 'id = ?', [$id]);

        flash('success', 'Product updated.');
        $this->redirect('/products');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('products', ['is_active' => 0], 'id = ? AND company_id = ?', [$id, $this->companyId()]);

        flash('success', 'Product deleted.');
        $this->redirect('/products');
    }
}
