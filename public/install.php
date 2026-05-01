<?php
session_start();

$baseUrl = rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/');
$lockFile = __DIR__ . '/../install.lock';
$configFile = __DIR__ . '/../config.php';
$schemaFile = __DIR__ . '/../database/schema.sql';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
$error = '';
$success = '';

// If already installed, show locked
if (file_exists($lockFile) && $step === 0) {
    $step = -1;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['install'] = array_merge($_SESSION['install'] ?? [], $_POST);

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_db':
                // Just save to session, no test needed
                $success = 'Database credentials saved.';
                break;

            case 'test_db':
                $db = $_SESSION['install'];
                try {
                    $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db['db_user'], $db['db_pass']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $success = 'Connection successful!';

                    if (!empty($db['db_name'])) {
                        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $pdo->exec("USE `{$db['db_name']}`");
                        $success .= ' Database created/verified.';
                    }
                } catch (\Exception $e) {
                    $error = 'Connection failed: ' . $e->getMessage();
                }
                break;

            case 'run_migration':
                $db = $_SESSION['install'];
                try {
                    $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};dbname={$db['db_name']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db['db_user'], $db['db_pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]);

                    if (!file_exists($schemaFile)) {
                        $error = 'Schema file not found at: ' . $schemaFile;
                        break;
                    }

                    $sql = file_get_contents($schemaFile);
                    // Remove comment lines
                    $lines = explode("\n", $sql);
                    $cleanLines = array_filter($lines, fn($l) => !str_starts_with(trim($l), '--') && !str_starts_with(trim($l), 'CREATE DATABASE') && !str_starts_with(trim($l), 'USE '));
                    $cleanSql = implode("\n", $cleanLines);
                    // Split by semicolons
                    $statements = array_filter(
                        array_map('trim', explode(';', $cleanSql)),
                        fn($s) => !empty($s)
                    );
                    foreach ($statements as $stmt) {
                        $pdo->exec($stmt);
                    }

                    $escapedUrl = addslashes($baseUrl);
                    $escapedHost = addslashes($db['db_host']);
                    $escapedUser = addslashes($db['db_user']);
                    $escapedPass = addslashes($db['db_pass']);
                    $escapedDb = addslashes($db['db_name']);

                    $configContent = <<<PHP
<?php

return [
    'app' => [
        'name' => 'VentiPOS',
        'version' => '1.0.0',
        'debug' => false,
        'url' => '{$escapedUrl}',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '{$escapedHost}',
        'port' => {$db['db_port']},
        'dbname' => '{$escapedDb}',
        'username' => '{$escapedUser}',
        'password' => '{$escapedPass}',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'session' => [
        'lifetime' => 86400,
    ],
];

PHP;
                    file_put_contents($configFile, $configContent);
                    $success = 'All tables created and configuration saved!';
                } catch (\Exception $e) {
                    $error = 'Migration failed: ' . $e->getMessage();
                }
                break;

            case 'create_admin':
                $db = $_SESSION['install'];
                $name = $_POST['admin_name'] ?? '';
                $email = $_POST['admin_email'] ?? '';
                $password = $_POST['admin_password'] ?? '';
                $companyName = $_POST['company_name'] ?? 'My Company';

                if (empty($name) || empty($email) || empty($password)) {
                    $error = 'Please fill in all admin fields.';
                    break;
                }
                if (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                    break;
                }

                try {
                    $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};dbname={$db['db_name']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db['db_user'], $db['db_pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]);
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                    $userId = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO companies (name, email, currency_code, currency_symbol, base_currency, display_currency) VALUES (?, ?, 'IDR', 'Rp', 'IDR', 'IDR')");
                    $stmt->execute([$companyName, $email]);
                    $companyId = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO company_user (company_id, user_id, role) VALUES (?, ?, 'owner')");
                    $stmt->execute([$companyId, $userId]);

                    $stmt = $pdo->prepare("INSERT INTO outlets (company_id, name, code, display_currency) VALUES (?, 'Main Store', 'MAIN', 'IDR')");
                    $stmt->execute([$companyId]);

                    $stmt = $pdo->prepare("INSERT INTO currency_rates (company_id, code, symbol, rate, is_base) VALUES (?, 'IDR', 'Rp', 1.000000, 1)");
                    $stmt->execute([$companyId]);

                    $pdo->commit();
                    $success = 'Admin account created! Company and Main Store are ready.';
                    $_SESSION['install']['admin_done'] = true;
                    $_SESSION['install']['_company_id'] = $companyId;
                    $_SESSION['install']['_outlet_id'] = $pdo->lastInsertId();
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $error = 'Failed to create admin: ' . $e->getMessage();
                }
                break;

            case 'seed_data':
                $db = $_SESSION['install'];
                try {
                    $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};dbname={$db['db_name']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db['db_user'], $db['db_pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]);

                    $companyId = $pdo->query("SELECT id FROM companies ORDER BY id DESC LIMIT 1")->fetchColumn();
                    $outletId = $pdo->query("SELECT id FROM outlets WHERE company_id = {$companyId} ORDER BY id ASC LIMIT 1")->fetchColumn();

                    if (!$companyId || !$outletId) {
                        $error = 'No company/outlet found. Please create admin account first.';
                        break;
                    }

                    // Categories
                    $cats = ['Beverages','Food & Snacks','Dairy & Eggs','Bakery','Household','Personal Care','Electronics','Stationery'];
                    $catIds = [];
                    foreach ($cats as $c) {
                        $pdo->prepare("INSERT INTO categories (company_id, name, description) VALUES (?, ?, ?)")->execute([$companyId, $c, '']);
                        $catIds[] = $pdo->lastInsertId();
                    }

                    // Products
                    $prods = [
                        ['Coca Cola 355ml', 'BVR001', 0.80, 1.50, 10, $catIds[0], 'pcs', 80],
                        ['Pepsi 355ml', 'BVR002', 0.75, 1.40, 10, $catIds[0], 'pcs', 65],
                        ['Spring Water 500ml', 'BVR003', 0.30, 0.80, 0, $catIds[0], 'pcs', 120],
                        ['Orange Juice 1L', 'BVR004', 1.20, 2.50, 10, $catIds[0], 'pcs', 45],
                        ['Ice Tea Lemon 500ml', 'BVR005', 0.60, 1.20, 0, $catIds[0], 'pcs', 55],
                        ['Potato Chips 150g', 'FOD001', 0.90, 1.80, 0, $catIds[1], 'pcs', 90],
                        ['Chocolate Bar 100g', 'FOD002', 1.10, 2.20, 0, $catIds[1], 'pcs', 75],
                        ['Mixed Nuts 150g', 'FOD003', 2.00, 3.50, 0, $catIds[1], 'pcs', 40],
                        ['Crackers 200g', 'FOD004', 0.85, 1.60, 0, $catIds[1], 'pcs', 60],
                        ['Whole Milk 1L', 'DRY001', 0.90, 1.60, 0, $catIds[2], 'pcs', 50],
                        ['Cheddar Cheese 200g', 'DRY002', 2.50, 4.50, 10, $catIds[2], 'pcs', 30],
                        ['Greek Yogurt 500g', 'DRY003', 1.20, 2.40, 0, $catIds[2], 'pcs', 35],
                        ['White Bread 600g', 'BAK001', 0.70, 1.40, 0, $catIds[3], 'pcs', 40],
                        ['Croissant 4pk', 'BAK002', 1.30, 2.60, 0, $catIds[3], 'pcs', 25],
                        ['Chocolate Muffin', 'BAK003', 0.90, 1.80, 0, $catIds[3], 'pcs', 30],
                        ['Dish Soap 500ml', 'HOU001', 1.20, 2.20, 10, $catIds[4], 'pcs', 45],
                        ['Paper Towels 6pk', 'HOU002', 3.00, 5.50, 0, $catIds[4], 'pcs', 25],
                        ['Trash Bags 30pk', 'HOU003', 2.00, 3.80, 0, $catIds[4], 'pcs', 35],
                        ['Hand Soap 250ml', 'PRS001', 1.00, 2.00, 10, $catIds[5], 'pcs', 50],
                        ['Shampoo 400ml', 'PRS002', 2.50, 4.50, 10, $catIds[5], 'pcs', 30],
                        ['Toothpaste 100ml', 'PRS003', 1.20, 2.40, 0, $catIds[5], 'pcs', 40],
                        ['Toilet Paper 12 Rolls', 'PRS004', 3.50, 6.00, 0, $catIds[5], 'pcs', 35],
                        ['AAA Batteries 4pk', 'ELC001', 1.50, 3.00, 10, $catIds[6], 'pcs', 60],
                        ['USB-C Cable 1m', 'ELC002', 2.00, 4.00, 10, $catIds[6], 'pcs', 35],
                        ['Phone Charger', 'ELC003', 5.00, 10.00, 10, $catIds[6], 'pcs', 20],
                        ['LED Light Bulb', 'ELC004', 1.80, 3.50, 0, $catIds[6], 'pcs', 40],
                        ['A4 Notebook 200pg', 'STA001', 1.50, 3.00, 0, $catIds[7], 'pcs', 50],
                        ['Ballpoint Pen 10pk', 'STA002', 1.00, 2.00, 0, $catIds[7], 'pcs', 75],
                        ['Sticky Notes 5pk', 'STA003', 1.20, 2.50, 0, $catIds[7], 'pcs', 40],
                    ];

                    foreach ($prods as $p) {
                        $pdo->prepare("INSERT INTO products (company_id, category_id, name, sku, cost_price, selling_price, tax_rate, unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                            ->execute([$companyId, $p[5], $p[0], $p[1], $p[2], $p[3], $p[4], $p[6]]);
                        $pid = $pdo->lastInsertId();
                        $pdo->prepare("INSERT INTO stock (product_id, outlet_id, quantity) VALUES (?, ?, ?)")->execute([$pid, $outletId, $p[7]]);
                    }

                    // Customers
                    $customers = [
                        ['Sarah Johnson','sarah@email.com','555-1001','123 Main St'],
                        ['Michael Chen','michael@email.com','555-1002','456 Oak Ave'],
                        ['Emily Davis','emily@email.com','555-1003','789 Pine Rd'],
                        ['James Wilson','james@email.com','555-1004','321 Elm St'],
                        ['Maria Garcia','maria@email.com','555-1005','654 Maple Dr'],
                    ];
                    $custIds = [];
                    foreach ($customers as $c) {
                        $pdo->prepare("INSERT INTO customers (company_id, name, email, phone, address) VALUES (?, ?, ?, ?, ?)")
                            ->execute([$companyId, $c[0], $c[1], $c[2], $c[3]]);
                        $custIds[] = $pdo->lastInsertId();
                    }

                    // Suppliers
                    $suppliers = [
                        ['Fresh Beverages Inc.', 'Tom Wilson', 'tom@freshbev.com', '555-2001'],
                        ['Snack Foods Co.', 'Anna Lee', 'anna@snackco.com', '555-2002'],
                        ['Dairy Fresh Ltd.', 'Peter Jones', 'peter@dairyfresh.com', '555-2003'],
                    ];
                    foreach ($suppliers as $s) {
                        $pdo->prepare("INSERT INTO suppliers (company_id, name, contact_person, email, phone) VALUES (?, ?, ?, ?, ?)")
                            ->execute([$companyId, $s[0], $s[1], $s[2], $s[3]]);
                    }

                    // EUR currency
                    $pdo->prepare("INSERT INTO currency_rates (company_id, code, symbol, rate, is_base) VALUES (?, 'EUR', '€', 0.000057, 0)")
                        ->execute([$companyId]);

                    $success = ' Demo data seeded successfully!';
                    $counts = ['products' => count($prods), 'customers' => count($customers), 'suppliers' => count($suppliers), 'categories' => count($cats)];
                    $_SESSION['install']['seed_counts'] = $counts;
                    $_SESSION['install']['seed_done'] = true;
                } catch (\Exception $e) {
                    $error = 'Seed failed: ' . $e->getMessage();
                }
                break;

            case 'finish':
                file_put_contents($lockFile, date('Y-m-d H:i:s'));
                session_destroy();
                header('Location: ' . $baseUrl . '/login');
                exit;
        }
    }

    if (empty($error)) {
        $nextStep = $step + 1;
        header("Location: ?step={$nextStep}");
        exit;
    }
}

$s = $_SESSION['install'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install VentiPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .install-container { max-width: 720px; margin: 40px auto; }
        .step-indicator { display: flex; gap: 0; margin-bottom: 30px; background: #fff; border-radius: .5rem; padding: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .step-dot { flex: 1; text-align: center; padding: 8px 5px; font-size: .8rem; border-bottom: 3px solid #e9ecef; color: #adb5bd; }
        .step-dot.active { border-color: #0d6efd; color: #0d6efd; font-weight: bold; }
        .step-dot.done { border-color: #198754; color: #198754; }
        .check-ok { color: #198754; }
        .check-fail { color: #dc3545; }
        .logo { text-align: center; margin-bottom: 25px; }
        .logo h1 { font-size: 2rem; font-weight: 300; }
        .card { border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .form-label { font-weight: 500; font-size: .85rem; }
        .table-checks td { border: none; padding: .4rem .75rem; }
    </style>
</head>
<body class="bg-light">
    <div class="install-container">
        <div class="logo">
            <h1>🛒 VentiPOS</h1>
            <p class="text-muted">Point of Sale — Installation Wizard</p>
        </div>

        <?php if ($step === -1): ?>
            <div class="card">
                <div class="card-body text-center p-5">
                    <h4 class="mb-3">🔒 Already Installed</h4>
                    <p class="text-muted">VentiPOS is already configured. To reinstall, delete the <code>install.lock</code> file from the server.</p>
                    <a href="<?= $baseUrl ?>/login" class="btn btn-primary btn-lg">Go to Login →</a>
                </div>
            </div>
            <?php exit; ?>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <?php $labels = ['Requirements', 'Database', 'Migration', 'Admin', 'Seed Data', 'Done']; ?>
            <?php foreach ($labels as $i => $label): ?>
                <div class="step-dot <?= $step > $i ? 'done' : ($step === $i ? 'active' : '') ?>">
                    <?= $step > $i ? '✓' : ($i + 1) ?>.<br><?= $label ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Step 0: Requirements -->
        <?php if ($step === 0): ?>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="mb-3">✅ System Requirements</h4>
                <?php
                $checks = [
                    'PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
                    'PDO Extension' => extension_loaded('PDO'),
                    'PDO MySQL' => extension_loaded('pdo_mysql'),
                    'MySQLi Extension' => extension_loaded('mysqli'),
                    'JSON Extension' => extension_loaded('json'),
                    'MBString Extension' => extension_loaded('mbstring'),
                    'config.php Writable' => is_writable(__DIR__ . '/..'),
                    'Schema File Exists' => file_exists($schemaFile),
                ];
                $allOk = true;
                ?>
                <table class="table table-borderless table-checks mb-3">
                    <?php foreach ($checks as $label => $ok): $allOk = $allOk && $ok; ?>
                        <tr>
                            <td><?= htmlspecialchars($label) ?></td>
                            <td class="text-end"><?= $ok ? '<span class="check-ok fw-bold">✓ Passed</span>' : '<span class="check-fail fw-bold">✗ Failed</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php if ($allOk): ?>
                    <p class="text-success mb-3">All requirements met! Ready to install.</p>
                    <a href="?step=1" class="btn btn-primary btn-lg w-100">Start Installation →</a>
                <?php else: ?>
                    <p class="text-danger mb-0">Please fix the failed requirements above before continuing.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 1: Database -->
        <?php if ($step === 1): ?>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="mb-3">🗄️ Database Configuration</h4>
                <p class="text-muted">Enter your MySQL database credentials.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="save_db">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Host</label>
                            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($s['db_host'] ?? 'localhost') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="db_port" class="form-control" value="<?= htmlspecialchars($s['db_port'] ?? '3306') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Database Name</label>
                            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($s['db_name'] ?? 'ventipos') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($s['db_user'] ?? 'root') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" name="db_pass" class="form-control" value="<?= htmlspecialchars($s['db_pass'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary" onclick="this.form.action.value='test_db'">🔌 Test Connection</button>
                        <button type="submit" class="btn btn-primary flex-grow-1">💾 Save & Continue →</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 2: Migration -->
        <?php if ($step === 2): ?>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="mb-3">📦 Database Migration</h4>
                <p class="text-muted">This will create all 16 tables in <strong><?= htmlspecialchars($s['db_name'] ?? '') ?></strong>.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="run_migration">
                    <div class="row row-cols-2 row-cols-md-4 g-2 mb-4">
                        <?php $tables = ['companies','users','company_user','outlets','categories','products','stock','stock_movements','customers','suppliers','sales','sale_items','payments','expenses','currency_rates','settings']; ?>
                        <?php foreach ($tables as $t): ?>
                            <div class="col"><span class="check-ok">✓</span> <?= $t ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">🚀 Run Migration</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 3: Admin -->
        <?php if ($step === 3): ?>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="mb-3">👤 Create Admin Account</h4>
                <p class="text-muted">Set up your company and administrator account.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-lg" value="<?= htmlspecialchars($s['company_name'] ?? 'My Store') ?>" required>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admin Name</label>
                            <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($s['admin_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($s['admin_email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="admin_password" class="form-control" value="<?= htmlspecialchars($s['admin_password'] ?? '') ?>" required minlength="6">
                        <div class="form-text">At least 6 characters.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">👤 Create Account</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 4: Seed Data -->
        <?php if ($step === 4): ?>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="mb-3">🌱 Demo Data</h4>
                <p class="text-muted">Seed your database with sample data so you can start exploring immediately.</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><div class="border rounded p-3 text-center"><h5>29 Products</h5><small class="text-muted">8 categories</small></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 text-center"><h5>5 Customers</h5><small class="text-muted">+ 3 suppliers</small></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 text-center"><h5>2 Currencies</h5><small class="text-muted">USD + EUR</small></div></div>
                </div>
                <div class="d-flex gap-3">
                    <form method="POST" style="flex:1">
                        <input type="hidden" name="action" value="seed_data">
                        <button type="submit" class="btn btn-success btn-lg w-100">🌱 Seed Demo Data</button>
                    </form>
                    <form method="POST" style="flex:1">
                        <input type="hidden" name="action" value="finish">
                        <button type="submit" class="btn btn-outline-secondary btn-lg w-100">⏭️ Skip</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 5: Complete -->
        <?php if ($step === 5): ?>
        <div class="card border-success">
            <div class="card-body text-center p-5">
                <h4 class="mb-3">🎉 Installation Complete!</h4>
                <?php if (!empty($s['seed_counts'])): ?>
                    <p class="text-muted">
                        Seeded <?= $s['seed_counts']['products'] ?> products,
                        <?= $s['seed_counts']['customers'] ?> customers,
                        <?= $s['seed_counts']['suppliers'] ?> suppliers,
                        <?= $s['seed_counts']['categories'] ?> categories.
                    </p>
                <?php endif; ?>
                <p class="text-muted">VentiPOS is ready to use. Click below to log in.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="finish">
                    <button type="submit" class="btn btn-primary btn-lg px-5">🚀 Go to Login →</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
