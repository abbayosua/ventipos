<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

class PosController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $companyId = $this->companyId();
        $outletId = $this->outletId();

        $categories = Database::fetchAll(
            "SELECT id, name FROM categories WHERE company_id = ? AND is_active = 1 ORDER BY name",
            [$companyId]
        );

        $products = Database::fetchAll(
            "SELECT p.id, p.name, p.sku, p.barcode, p.selling_price, p.tax_rate, p.unit, p.image,
                    COALESCE(s.quantity, 0) as stock_qty
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id AND s.outlet_id = ?
             WHERE p.company_id = ? AND p.is_active = 1
             ORDER BY p.name",
            [$outletId, $companyId]
        );

        $customers = Database::fetchAll(
            "SELECT id, name, phone FROM customers WHERE company_id = ? AND is_active = 1 ORDER BY name",
            [$companyId]
        );

        $currency = Database::fetch(
            "SELECT currency_symbol FROM companies WHERE id = ?",
            [$companyId]
        );

        $this->render('pos.index', [
            'title' => 'POS',
            'categories' => $categories,
            'products' => $products,
            'customers' => $customers,
            'currencySymbol' => $currency->currency_symbol ?? '$',
        ], 'pos');
    }

    public function searchProducts(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $query = Request::get('q');
        $categoryId = Request::get('category_id');

        $sql = "SELECT p.id, p.name, p.sku, p.barcode, p.selling_price, p.tax_rate, p.unit, p.image,
                       COALESCE(s.quantity, 0) as stock_qty
                FROM products p
                LEFT JOIN stock s ON s.product_id = p.id AND s.outlet_id = ?
                WHERE p.company_id = ? AND p.is_active = 1";
        $params = [$this->outletId(), $this->companyId()];

        if ($query) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY p.name LIMIT 50";

        $products = Database::fetchAll($sql, $params);
        $this->json($products);
    }

    public function searchCustomers(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $query = Request::get('q');

        $sql = "SELECT id, name, phone, email FROM customers WHERE company_id = ? AND is_active = 1";
        $params = [$this->companyId()];

        if ($query) {
            $sql .= " AND (name LIKE ? OR phone LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        $sql .= " ORDER BY name LIMIT 20";

        $this->json(Database::fetchAll($sql, $params));
    }

    public function checkout(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $outletId = $this->outletId();
        $userId = $this->userId();
        $items = json_decode(Request::post('items', '[]'), true);
        $paymentMethod = Request::post('payment_method', 'cash');
        $paidAmount = (float) (Request::post('paid_amount', 0));
        $customerId = Request::post('customer_id') ?: null;
        $discountType = Request::post('discount_type') ?: null;
        $discountValue = (float) (Request::post('discount_value', 0));
        $notes = Request::post('notes');

        if (empty($items)) {
            $this->json(['error' => 'Cart is empty'], 400);
        }

        try {
            Database::beginTransaction();

            $subtotal = 0;
            $totalTax = 0;

            foreach ($items as &$item) {
                $product = Database::fetch(
                    "SELECT p.*, COALESCE(s.quantity, 0) as stock_qty
                     FROM products p
                     LEFT JOIN stock s ON s.product_id = p.id AND s.outlet_id = ?
                     WHERE p.id = ? AND p.company_id = ?",
                    [$outletId, $item['product_id'], $this->companyId()]
                );

                if (!$product) {
                    throw new \RuntimeException("Product #{$item['product_id']} not found.");
                }

                if ($product->stock_qty < $item['quantity']) {
                    throw new \RuntimeException("Insufficient stock for {$product->name}. Available: {$product->stock_qty}");
                }

                $price = (float) $product->selling_price;
                $qty = (float) $item['quantity'];
                $lineTotal = $price * $qty;

                $itemDiscountType = $item['discount_type'] ?? null;
                $itemDiscountValue = (float) ($item['discount_value'] ?? 0);
                $itemDiscountAmount = 0;

                if ($itemDiscountType === 'percentage' && $itemDiscountValue > 0) {
                    $itemDiscountAmount = $lineTotal * ($itemDiscountValue / 100);
                } elseif ($itemDiscountType === 'fixed' && $itemDiscountValue > 0) {
                    $itemDiscountAmount = min($itemDiscountValue, $lineTotal);
                }

                $taxRate = (float) ($product->tax_rate ?? 0);
                $lineAfterDiscount = $lineTotal - $itemDiscountAmount;
                $lineTax = $lineAfterDiscount * ($taxRate / 100);

                $subtotal += $lineAfterDiscount;
                $totalTax += $lineTax;

                $item['price'] = $price;
                $item['discount_amount'] = $itemDiscountAmount;
                $item['tax_amount'] = $lineTax;
                $item['subtotal'] = $lineAfterDiscount + $lineTax;
                $item['_product_name'] = $product->name;
            }
            unset($item);

            $discountAmount = 0;
            if ($discountType === 'percentage' && $discountValue > 0) {
                $discountAmount = $subtotal * ($discountValue / 100);
            } elseif ($discountType === 'fixed' && $discountValue > 0) {
                $discountAmount = min($discountValue, $subtotal);
            }

            $total = $subtotal - $discountAmount + $totalTax;
            $change = max(0, $paidAmount - $total);

            $invoiceNo = 'INV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $saleId = Database::insert('sales', [
                'outlet_id' => $outletId,
                'customer_id' => $customerId,
                'invoice_no' => $invoiceNo,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_amount' => $totalTax,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $change,
                'payment_status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                Database::insert('sale_items', [
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $item['discount_amount'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $item['subtotal'],
                ]);

                Database::query(
                    "UPDATE stock SET quantity = quantity - ? WHERE product_id = ? AND outlet_id = ?",
                    [$item['quantity'], $item['product_id'], $outletId]
                );

                Database::insert('stock_movements', [
                    'product_id' => $item['product_id'],
                    'outlet_id' => $outletId,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id' => $saleId,
                    'notes' => "Sale #{$invoiceNo}",
                    'created_by' => $userId,
                ]);
            }

            if ($paidAmount > 0) {
                Database::insert('payments', [
                    'sale_id' => $saleId,
                    'method' => $paymentMethod,
                    'amount' => $paidAmount,
                    'reference' => $invoiceNo,
                ]);
            }

            Database::commit();

            $this->json([
                'success' => true,
                'sale_id' => $saleId,
                'invoice_no' => $invoiceNo,
                'change' => $change,
                'total' => $total,
            ]);
        } catch (\Throwable $e) {
            Database::rollback();
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
