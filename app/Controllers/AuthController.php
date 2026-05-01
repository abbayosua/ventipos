<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth.login', ['title' => 'Login'], 'auth');
    }

    public function login(): void
    {
        $email = Request::post('email');
        $password = Request::post('password');

        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if (!$user || !password_verify($password, $user->password)) {
            Session::flash('error', 'Invalid credentials.');
            $this->redirect('/login');
        }

        Session::set('user_id', $user->id);
        Session::set('user_name', $user->name);
        Session::set('user_email', $user->email);

        $company = Database::fetch(
            "SELECT c.*, cu.role FROM company_user cu 
             JOIN companies c ON c.id = cu.company_id 
             WHERE cu.user_id = ? AND cu.is_active = 1 
             LIMIT 1",
            [$user->id]
        );

        if ($company) {
            Session::set('company_id', $company->id);
            Session::set('company_name', $company->name);
            Session::set('currency_symbol', $company->currency_symbol);
            Session::set('base_currency', $company->base_currency ?? 'IDR');
            Session::set('role', $company->role);

            $outlet = Database::fetch(
                "SELECT * FROM outlets WHERE company_id = ? AND is_active = 1 LIMIT 1",
                [$company->id]
            );
            if ($outlet) {
                Session::set('outlet_id', $outlet->id);
                Session::set('outlet_name', $outlet->name);
                Session::set('display_currency', $outlet->display_currency ?? 'IDR');
            } else {
                Session::set('display_currency', 'IDR');
            }
        }

        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $step = (int)(Request::get('step', 1));
        if ($step < 1 || $step > 4) $step = 1;

        // Load saved progress
        $wizard = Session::get('register_wizard', []);

        // Handle POST
        if (Request::isPost()) {
            $action = Request::post('action');

            if ($action === 'step1') {
                $name = Request::post('name');
                $email = Request::post('email');
                $password = Request::post('password');

                if (empty($name) || empty($email) || empty($password)) {
                    Session::flash('error', 'All fields are required.');
                    $this->redirect('/register?step=1');
                }
                if (strlen($password) < 6) {
                    Session::flash('error', 'Password must be at least 6 characters.');
                    $this->redirect('/register?step=1');
                }
                $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
                if ($existing) {
                    Session::flash('error', 'Email already registered.');
                    $this->redirect('/register?step=1');
                }

                $wizard['name'] = $name;
                $wizard['email'] = $email;
                $wizard['password'] = $password;
                Session::set('register_wizard', $wizard);
                $this->redirect('/register?step=2');
            }

            if ($action === 'step2') {
                $companyName = Request::post('company_name');
                $outletName = Request::post('outlet_name');
                $baseCurrency = Request::post('base_currency');
                $phone = Request::post('phone');
                $address = Request::post('address');

                if (empty($companyName) || empty($outletName) || empty($baseCurrency)) {
                    Session::flash('error', 'Company name, outlet name, and currency are required.');
                    $this->redirect('/register?step=2');
                }

                $wizard['company_name'] = $companyName;
                $wizard['outlet_name'] = $outletName;
                $wizard['base_currency'] = $baseCurrency;
                $wizard['phone'] = $phone;
                $wizard['address'] = $address;
                Session::set('register_wizard', $wizard);
                $this->redirect('/register?step=3');
            }

            if ($action === 'step3') {
                $wizard['seed_demo'] = Request::post('seed_demo') === '1';
                Session::set('register_wizard', $wizard);
                $this->redirect('/register?step=4');
            }

            if ($action === 'complete') {
                // Final creation
                if (empty($wizard['name']) || empty($wizard['email']) || empty($wizard['password'])) {
                    Session::flash('error', 'Registration data missing. Please start over.');
                    Session::remove('register_wizard');
                    $this->redirect('/register');
                }

                $currencies = [
                    'USD' => ['$', 'US Dollar'],
                    'IDR' => ['Rp', 'Indonesian Rupiah'],
                    'EUR' => ['€', 'Euro'],
                    'GBP' => ['£', 'British Pound'],
                    'SGD' => ['S$', 'Singapore Dollar'],
                    'MYR' => ['RM', 'Malaysian Ringgit'],
                ];
                $baseCurrency = $wizard['base_currency'] ?? 'IDR';
                $symbol = $currencies[$baseCurrency][0] ?? '$';

                try {
                    Database::beginTransaction();

                    $userId = Database::insert('users', [
                        'name' => $wizard['name'],
                        'email' => $wizard['email'],
                        'password' => password_hash($wizard['password'], PASSWORD_DEFAULT),
                    ]);

                    $companyId = Database::insert('companies', [
                        'name' => $wizard['company_name'],
                        'email' => $wizard['email'],
                        'phone' => $wizard['phone'] ?? '',
                        'address' => $wizard['address'] ?? '',
                        'currency_code' => $baseCurrency,
                        'currency_symbol' => $symbol,
                        'base_currency' => $baseCurrency,
                        'display_currency' => $baseCurrency,
                    ]);

                    Database::insert('company_user', [
                        'company_id' => $companyId,
                        'user_id' => $userId,
                        'role' => 'owner',
                    ]);

                    Database::insert('outlets', [
                        'company_id' => $companyId,
                        'name' => $wizard['outlet_name'],
                        'code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $wizard['outlet_name']), 0, 5)) ?: 'MAIN',
                        'display_currency' => $baseCurrency,
                        'phone' => $wizard['phone'] ?? '',
                        'address' => $wizard['address'] ?? '',
                    ]);

                    Database::insert('currency_rates', [
                        'company_id' => $companyId,
                        'code' => $baseCurrency,
                        'symbol' => $symbol,
                        'rate' => 1.000000,
                        'is_base' => 1,
                    ]);

                    // Seed demo data if requested
                    if (!empty($wizard['seed_demo'])) {
                        $this->seedDemoData($companyId, $baseCurrency, $symbol);
                    }

                    Database::commit();

                    Session::remove('register_wizard');
                    Session::flash('success', 'Registration successful! Please login.');
                    $this->redirect('/login');
                } catch (\Throwable $e) {
                    Database::rollback();
                    Session::flash('error', 'Registration failed: ' . $e->getMessage());
                    $this->redirect('/register?step=4');
                }
            }
        }

        $this->render('auth.register', [
            'title' => 'Register',
            'step' => $step,
            'wizard' => $wizard,
        ], 'auth');
    }

    protected function seedDemoData(int $companyId, string $baseCurrency, string $symbol): void
    {
        $pdo = Database::connection();

        // Categories
        $cats = ['Beverages','Food & Snacks','Dairy & Eggs','Bakery','Household','Personal Care','Electronics','Stationery'];
        $catIds = [];
        foreach ($cats as $c) {
            $pdo->prepare("INSERT INTO categories (company_id, name) VALUES (?, ?)")->execute([$companyId, $c]);
            $catIds[] = $pdo->lastInsertId();
        }

        // Products (prices in base currency — IDR)
        $price = fn($usd) => $baseCurrency === 'IDR' ? $usd * 16000 : $usd;
        $prods = [
            ['Coca Cola 355ml', 'BVR001', 1.50, 0.80, 10, $catIds[0], 80],
            ['Spring Water 500ml', 'BVR002', 0.80, 0.30, 0, $catIds[0], 120],
            ['Orange Juice 1L', 'BVR003', 2.50, 1.20, 10, $catIds[0], 45],
            ['Potato Chips 150g', 'FOD001', 1.80, 0.90, 0, $catIds[1], 90],
            ['Chocolate Bar 100g', 'FOD002', 2.20, 1.10, 0, $catIds[1], 75],
            ['Mixed Nuts 150g', 'FOD003', 3.50, 2.00, 0, $catIds[1], 40],
            ['Whole Milk 1L', 'DRY001', 1.60, 0.90, 0, $catIds[2], 50],
            ['Cheddar Cheese 200g', 'DRY002', 4.50, 2.50, 10, $catIds[2], 30],
            ['White Bread 600g', 'BAK001', 1.40, 0.70, 0, $catIds[3], 40],
            ['Croissant 4pk', 'BAK002', 2.60, 1.30, 0, $catIds[3], 25],
            ['Dish Soap 500ml', 'HOU001', 2.20, 1.20, 10, $catIds[4], 45],
            ['Paper Towels 6pk', 'HOU002', 5.50, 3.00, 0, $catIds[4], 25],
            ['Hand Soap 250ml', 'PRS001', 2.00, 1.00, 10, $catIds[5], 50],
            ['Shampoo 400ml', 'PRS002', 4.50, 2.50, 10, $catIds[5], 30],
            ['AAA Batteries 4pk', 'ELC001', 3.00, 1.50, 10, $catIds[6], 60],
            ['USB-C Cable 1m', 'ELC002', 4.00, 2.00, 10, $catIds[6], 35],
            ['A4 Notebook 200pg', 'STA001', 3.00, 1.50, 0, $catIds[7], 50],
            ['Ballpoint Pen 10pk', 'STA002', 2.00, 1.00, 0, $catIds[7], 75],
        ];

        $outletId = $pdo->query("SELECT id FROM outlets WHERE company_id = {$companyId} LIMIT 1")->fetchColumn();
        foreach ($prods as $p) {
            $sellPrice = $price($p[2]);
            $costPrice = $price($p[3]);
            $pdo->prepare("INSERT INTO products (company_id, category_id, name, sku, cost_price, selling_price, tax_rate, unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$companyId, $p[5], $p[0], $p[1], $costPrice, $sellPrice, $p[4], 'pcs']);
            $pid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO stock (product_id, outlet_id, quantity) VALUES (?, ?, ?)")->execute([$pid, $outletId, $p[6]]);
        }

        // Customers
        $customers = [
            ['Sarah Johnson','sarah@email.com','555-1001'],['Michael Chen','michael@email.com','555-1002'],
            ['Emily Davis','emily@email.com','555-1003'],['James Wilson','james@email.com','555-1004'],
            ['Maria Garcia','maria@email.com','555-1005'],
        ];
        foreach ($customers as $c) {
            $pdo->prepare("INSERT INTO customers (company_id, name, email, phone) VALUES (?, ?, ?, ?)")
                ->execute([$companyId, $c[0], $c[1], $c[2]]);
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

        // EUR rate
        $eurRate = $baseCurrency === 'IDR' ? 0.000057 : 0.92;
        $pdo->prepare("INSERT INTO currency_rates (company_id, code, symbol, rate, is_base) VALUES (?, 'EUR', '€', ?, 0)")
            ->execute([$companyId, $eurRate]);
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/login');
    }

    public function switchLang(string $locale): void
    {
        $available = array_keys(\App\Lang\Lang::available());
        if (in_array($locale, $available)) {
            Session::set('lang', $locale);
            \App\Lang\Lang::setLocale($locale);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }
}
