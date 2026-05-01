<?php
require_once __DIR__ . '/config.php';
$config = require __DIR__ . '/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/app/Helpers/helpers.php';

use App\Core\Database;

Database::init($config['database']);

echo "Seeding VentiPOS with demo data...\n";

// Check if demo user exists
$existing = Database::fetch("SELECT id FROM users WHERE email = 'admin@ventipos.com'");
if ($existing) {
    echo "Demo data already exists. Removing old data first...\n";
    // Clean old demo data
    Database::query("SET FOREIGN_KEY_CHECKS=0");
    Database::query("DELETE FROM payments WHERE sale_id IN (SELECT id FROM sales WHERE outlet_id IN (SELECT id FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))))")->execute();
    Database::query("DELETE FROM sale_items WHERE sale_id IN (SELECT id FROM sales WHERE outlet_id IN (SELECT id FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))))");
    Database::query("DELETE FROM stock_movements WHERE outlet_id IN (SELECT id FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))");
    Database::query("DELETE FROM sales WHERE outlet_id IN (SELECT id FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))");
    Database::query("DELETE FROM expenses WHERE outlet_id IN (SELECT id FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))");
    Database::query("DELETE FROM stock WHERE product_id IN (SELECT id FROM products WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company'))");
    Database::query("DELETE FROM products WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM categories WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM customers WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM suppliers WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM settings WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM currency_rates WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM outlets WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM company_user WHERE company_id IN (SELECT id FROM companies WHERE name='Demo Company')");
    Database::query("DELETE FROM companies WHERE name='Demo Company'");
    Database::query("DELETE FROM users WHERE email = 'admin@ventipos.com'");
    Database::query("DELETE FROM users WHERE email LIKE 'cashier%@ventipos.com'");
    Database::query("SET FOREIGN_KEY_CHECKS=1");
}

echo "Creating company and users...\n";
Database::beginTransaction();

$companyId = Database::insert('companies', [
    'name' => 'Demo Company',
    'email' => 'info@democompany.com',
    'phone' => '555-0100',
    'address' => '123 Main Street, Downtown',
    'currency_code' => 'USD',
    'currency_symbol' => '$',
    'timezone' => 'UTC',
]);

$userId = Database::insert('users', [
    'name' => 'Admin User',
    'email' => 'admin@ventipos.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
]);
Database::insert('company_user', ['company_id' => $companyId, 'user_id' => $userId, 'role' => 'owner']);

// Cashier users
$cashierIds = [];
foreach (['Alice Cashier', 'Bob Cashier'] as $i => $name) {
    $cid = Database::insert('users', [
        'name' => $name,
        'email' => 'cashier' . ($i+1) . '@ventipos.com',
        'password' => password_hash('cashier123', PASSWORD_DEFAULT),
    ]);
    Database::insert('company_user', ['company_id' => $companyId, 'user_id' => $cid, 'role' => 'cashier']);
    $cashierIds[] = $cid;
}

// Outlets
$outletMainId = Database::insert('outlets', ['company_id' => $companyId, 'name' => 'Main Store', 'code' => 'MAIN', 'address' => '123 Main Street', 'phone' => '555-0101']);
$outletBranchId = Database::insert('outlets', ['company_id' => $companyId, 'name' => 'Branch Store', 'code' => 'BRANCH', 'address' => '456 Oak Avenue', 'phone' => '555-0102']);
$outlets = [$outletMainId, $outletBranchId];

// Currency rates
Database::insert('currency_rates', ['company_id' => $companyId, 'code' => 'USD', 'symbol' => '$', 'rate' => 1.000000, 'is_base' => 1]);
Database::insert('currency_rates', ['company_id' => $companyId, 'code' => 'EUR', 'symbol' => '€', 'rate' => 0.920000, 'is_base' => 0]);
Database::insert('currency_rates', ['company_id' => $companyId, 'code' => 'GBP', 'symbol' => '£', 'rate' => 0.790000, 'is_base' => 0]);

echo "Creating categories...\n";
$categories = [];
$catData = [
    ['name' => 'Beverages', 'description' => 'Soft drinks, juices, water'],
    ['name' => 'Food & Snacks', 'description' => 'Chips, cookies, candy'],
    ['name' => 'Dairy & Eggs', 'description' => 'Milk, cheese, yogurt, eggs'],
    ['name' => 'Bakery', 'description' => 'Bread, pastries, cakes'],
    ['name' => 'Household', 'description' => 'Cleaning supplies, paper products'],
    ['name' => 'Personal Care', 'description' => 'Soap, shampoo, hygiene'],
    ['name' => 'Electronics', 'description' => 'Accessories, cables, batteries'],
    ['name' => 'Stationery', 'description' => 'Pens, paper, office supplies'],
];
foreach ($catData as $c) {
    $categories[] = Database::insert('categories', [
        'company_id' => $companyId,
        'name' => $c['name'],
        'description' => $c['description'],
    ]);
}

echo "Creating 40 products...\n";
$products = [];
$productData = [
    // Beverages (cat 0)
    ['Coca Cola 355ml', 'BVR001', '4901234567890', 0.80, 1.50, 10, 'pcs'],
    ['Pepsi 355ml', 'BVR002', '4901234567891', 0.75, 1.40, 10, 'pcs'],
    ['Spring Water 500ml', 'BVR003', '4901234567892', 0.30, 0.80, 0, 'pcs'],
    ['Orange Juice 1L', 'BVR004', '4901234567893', 1.20, 2.50, 10, 'pcs'],
    ['Ice Tea Lemon 500ml', 'BVR005', '4901234567894', 0.60, 1.20, 0, 'pcs'],
    ['Energy Drink 250ml', 'BVR006', '4901234567895', 1.00, 2.80, 10, 'pcs'],

    // Food & Snacks (cat 1)
    ['Potato Chips Original 150g', 'FOD001', '4901234567896', 0.90, 1.80, 0, 'pcs'],
    ['Chocolate Bar 100g', 'FOD002', '4901234567897', 1.10, 2.20, 0, 'pcs'],
    ['Gummy Bears 200g', 'FOD003', '4901234567898', 0.70, 1.50, 0, 'pcs'],
    ['Mixed Nuts 150g', 'FOD004', '4901234567899', 2.00, 3.50, 0, 'pcs'],
    ['Crackers 200g', 'FOD005', '4901234567800', 0.85, 1.60, 0, 'pcs'],
    ['Granola Bar Box 6pk', 'FOD006', '4901234567801', 1.50, 3.00, 0, 'pcs'],

    // Dairy & Eggs (cat 2)
    ['Whole Milk 1L', 'DRY001', '4901234567802', 0.90, 1.60, 0, 'pcs'],
    ['Cheddar Cheese 200g', 'DRY002', '4901234567803', 2.50, 4.50, 10, 'pcs'],
    ['Greek Yogurt 500g', 'DRY003', '4901234567804', 1.20, 2.40, 0, 'pcs'],
    ['Eggs Farm Fresh 12pk', 'DRY004', '4901234567805', 1.80, 3.20, 0, 'pcs'],

    // Bakery (cat 3)
    ['White Bread 600g', 'BAK001', '4901234567806', 0.70, 1.40, 0, 'pcs'],
    ['Croissant 4pk', 'BAK002', '4901234567807', 1.30, 2.60, 0, 'pcs'],
    ['Chocolate Muffin', 'BAK003', '4901234567808', 0.90, 1.80, 0, 'pcs'],

    // Household (cat 4)
    ['Dish Soap 500ml', 'HOU001', '4901234567809', 1.20, 2.20, 10, 'pcs'],
    ['All-Purpose Cleaner 750ml', 'HOU002', '4901234567810', 1.50, 2.80, 10, 'pcs'],
    ['Paper Towels 6 Rolls', 'HOU003', '4901234567811', 3.00, 5.50, 0, 'pcs'],
    ['Trash Bags 30pk', 'HOU004', '4901234567812', 2.00, 3.80, 0, 'pcs'],

    // Personal Care (cat 5)
    ['Hand Soap 250ml', 'PRS001', '4901234567813', 1.00, 2.00, 10, 'pcs'],
    ['Shampoo 400ml', 'PRS002', '4901234567814', 2.50, 4.50, 10, 'pcs'],
    ['Toothpaste 100ml', 'PRS003', '4901234567815', 1.20, 2.40, 0, 'pcs'],
    ['Deodorant 50ml', 'PRS004', '4901234567816', 1.80, 3.20, 0, 'pcs'],
    ['Toilet Paper 12 Rolls', 'PRS005', '4901234567817', 3.50, 6.00, 0, 'pcs'],

    // Electronics (cat 6)
    ['AAA Batteries 4pk', 'ELC001', '4901234567818', 1.50, 3.00, 10, 'pcs'],
    ['USB-C Cable 1m', 'ELC002', '4901234567819', 2.00, 4.00, 10, 'pcs'],
    ['Phone Charger', 'ELC003', '4901234567820', 5.00, 10.00, 10, 'pcs'],
    ['LED Light Bulb', 'ELC004', '4901234567821', 1.80, 3.50, 0, 'pcs'],
    ['Earbuds', 'ELC005', '4901234567822', 3.00, 6.50, 10, 'pcs'],

    // Stationery (cat 7)
    ['Ballpoint Pen Blue 10pk', 'STA001', '4901234567823', 1.00, 2.00, 0, 'pcs'],
    ['A4 Notebook 200pg', 'STA002', '4901234567824', 1.50, 3.00, 0, 'pcs'],
    ['Sticky Notes 3x3 5pk', 'STA003', '4901234567825', 1.20, 2.50, 0, 'pcs'],
    ['Scotch Tape', 'STA004', '4901234567826', 0.60, 1.20, 0, 'pcs'],
    ['Highlighter Set 4pk', 'STA005', '4901234567827', 1.40, 2.80, 0, 'pcs'],
];

foreach ($productData as $i => $p) {
    $catIndex = [
        0,0,0,0,0,0,
        1,1,1,1,1,1,
        2,2,2,2,
        3,3,3,
        4,4,4,4,
        5,5,5,5,5,
        6,6,6,6,6,
        7,7,7,7,7,
    ];
    $pid = Database::insert('products', [
        'company_id' => $companyId,
        'category_id' => $categories[$catIndex[$i]],
        'name' => $p[0],
        'sku' => $p[1],
        'barcode' => $p[2],
        'cost_price' => $p[3],
        'selling_price' => $p[4],
        'tax_rate' => $p[5],
        'unit' => $p[6],
    ]);
    $products[] = $pid;

    foreach ($outlets as $oid) {
        $qty = rand(15, 120);
        Database::insert('stock', ['product_id' => $pid, 'outlet_id' => $oid, 'quantity' => $qty]);
    }
}

echo "Creating customers...\n";
$customerIds = [];
$customerNames = [
    ['Sarah Johnson', 'sarah@email.com', '555-1001'],
    ['Michael Chen', 'michael@email.com', '555-1002'],
    ['Emily Davis', 'emily@email.com', '555-1003'],
    ['James Wilson', 'james@email.com', '555-1004'],
    ['Maria Garcia', 'maria@email.com', '555-1005'],
    ['David Thompson', 'david@email.com', '555-1006'],
    ['Lisa Anderson', 'lisa@email.com', '555-1007'],
    ['Robert Martinez', 'robert@email.com', '555-1008'],
    ['Jennifer Taylor', 'jennifer@email.com', '555-1009'],
    ['Kevin Brown', 'kevin@email.com', '555-1010'],
];
foreach ($customerNames as $cn) {
    $customerIds[] = Database::insert('customers', [
        'company_id' => $companyId,
        'name' => $cn[0],
        'email' => $cn[1],
        'phone' => $cn[2],
        'address' => rand(100, 999) . ' ' . ['Elm St', 'Oak Ave', 'Pine Rd', 'Maple Dr', 'Cedar Ln'][rand(0,4)] . ', City',
    ]);
}

echo "Creating suppliers...\n";
$supplierData = [
    ['Fresh Beverages Inc.', 'Tom Wilson', 'beverages@freshsupply.com', '555-2001'],
    ['Snack Foods Co.', 'Anna Lee', 'orders@snackco.com', '555-2002'],
    ['Dairy Fresh Ltd.', 'Peter Jones', 'sales@dairyfresh.com', '555-2003'],
    ['Household Essentials Corp.', 'Linda Green', 'orders@household-essentials.com', '555-2004'],
    ['Tech Accessories Wholesale', 'Steve Kim', 'info@techacc-wholesale.com', '555-2005'],
];
foreach ($supplierData as $sd) {
    Database::insert('suppliers', [
        'company_id' => $companyId,
        'name' => $sd[0],
        'contact_person' => $sd[1],
        'email' => $sd[2],
        'phone' => $sd[3],
    ]);
}

echo "Creating historical sales (60 days)...\n";
$saleUserIds = array_merge([$userId], $cashierIds);
for ($day = 60; $day >= 0; $day--) {
    $date = date('Y-m-d', strtotime("-{$day} days"));
    // Skip some days (Sundays)
    if (date('w', strtotime($date)) == 0) continue;

    // Random number of sales per day (3-15)
    $dailySales = rand(3, 15);
    for ($s = 0; $s < $dailySales; $s++) {
        $outletId = $outlets[array_rand($outlets)];
        $customerId = rand(0, 2) == 0 ? $customerIds[array_rand($customerIds)] : null;
        $userId = $saleUserIds[array_rand($saleUserIds)];

        // Pick 1-5 random products
        $numItems = rand(1, 5);
        $itemProductIds = array_rand(array_flip($products), min($numItems, count($products)));
        if (!is_array($itemProductIds)) $itemProductIds = [$itemProductIds];

        $subtotal = 0;
        $totalTax = 0;
        $items = [];
        foreach ($itemProductIds as $pi) {
            $product = Database::fetch("SELECT id, selling_price, tax_rate FROM products WHERE id=?", [$pi]);
            if (!$product) continue;
            $qty = rand(1, 4);
            $price = (float)$product->selling_price;
            $lineTotal = $price * $qty;
            $tax = $lineTotal * ((float)$product->tax_rate / 100);
            $subtotal += $lineTotal;
            $totalTax += $tax;
            $items[] = ['product_id' => $pi, 'quantity' => $qty, 'price' => $price, 'tax_amount' => $tax, 'subtotal' => $lineTotal + $tax];
        }

        if (empty($items)) continue;

        $total = $subtotal + $totalTax;
        $paidAmount = $total;
        $paymentMethod = ['cash', 'card', 'cash', 'cash', 'transfer'][rand(0, 4)];
        $invoiceNo = 'INV-' . date('Ymd', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $hour = str_pad(rand(7, 21), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $createdAt = "{$date} {$hour}:{$minute}:00";

        // Insert sale with specific created_at
        $saleId = Database::insert('sales', [
            'outlet_id' => $outletId,
            'customer_id' => $customerId,
            'invoice_no' => $invoiceNo,
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'change_amount' => 0,
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'created_by' => $userId,
        ]);

        // Update the created_at timestamp
        Database::query("UPDATE sales SET created_at = ? WHERE id = ?", [$createdAt, $saleId]);

        foreach ($items as $item) {
            $siId = Database::insert('sale_items', [
                'sale_id' => $saleId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount_amount' => 0,
                'tax_amount' => $item['tax_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            Database::query("UPDATE stock SET quantity = quantity - ? WHERE product_id = ? AND outlet_id = ?",
                [$item['quantity'], $item['product_id'], $outletId]);

            Database::insert('stock_movements', [
                'product_id' => $item['product_id'],
                'outlet_id' => $outletId,
                'type' => 'out',
                'quantity' => $item['quantity'],
                'reference_type' => 'sale',
                'reference_id' => $saleId,
                'notes' => "Sale #{$invoiceNo}",
                'created_by' => $userId,
                'created_at' => $createdAt,
            ]);
        }
    }

    // Random expenses for some days
    if (rand(0, 3) == 0) {
        foreach ($outlets as $oid) {
            $expCategories = ['Utilities', 'Supplies', 'Maintenance'];
            Database::insert('expenses', [
                'outlet_id' => $oid,
                'category' => $expCategories[array_rand($expCategories)],
                'amount' => round(rand(10, 200) + rand(0, 99) / 100, 2),
                'description' => 'Daily operational expense',
                'expense_date' => $date,
                'created_by' => $userId,
            ]);
        }
    }
}

Database::commit();

echo "\n====================================\n";
echo "  ✅ DEMO DATA SEEDED SUCCESSFULLY\n";
echo "====================================\n\n";
echo "  URL:   http://ventipos.test\n";
echo "  Email: admin@ventipos.com\n";
echo "  Pass:  admin123\n\n";
echo "  Cashier 1: cashier1@ventipos.com / cashier123\n";
echo "  Cashier 2: cashier2@ventipos.com / cashier123\n\n";
echo "  Stats:\n";
echo "  - 1 company, 2 outlets\n";
echo "  - 3 users (1 admin + 2 cashiers)\n";
echo "  - " . count($categories) . " categories\n";
echo "  - " . count($products) . " products\n";
echo "  - " . count($customerIds) . " customers\n";
echo "  - " . count($supplierData) . " suppliers\n";
echo "  - 60 days of historical sales\n";
echo "  - Multi-currency (USD/EUR/GBP)\n";
echo "====================================\n";
