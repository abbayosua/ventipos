<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

class SettingController extends Controller
{
    protected array $currencySymbols = [
        'USD' => '$', 'IDR' => 'Rp', 'EUR' => '€', 'GBP' => '£',
        'JPY' => '¥', 'SGD' => 'S$', 'MYR' => 'RM', 'PHP' => '₱',
        'THB' => '฿', 'VND' => '₫', 'CNY' => '¥', 'AUD' => 'A$',
        'CAD' => 'C$', 'CHF' => 'Fr', 'KRW' => '₩', 'INR' => '₹',
        'SAR' => '﷼', 'AED' => 'د.إ',
    ];

    public function index(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$this->companyId()]);
        $settings = Database::fetchAll("SELECT `key`, `value` FROM settings WHERE company_id = ?", [$this->companyId()]);
        $settingsMap = [];
        foreach ($settings as $s) $settingsMap[$s->key] = $s->value;

        // Auto-fetch live exchange rates
        $base = $company->currency_code ?? 'USD';
        try {
            $json = @file_get_contents("https://api.frankfurter.app/latest?from={$base}");
            if ($json) {
                $data = json_decode($json);
                if ($data && isset($data->rates)) {
                    Database::update('currency_rates', ['rate' => 1.000000], 'company_id = ? AND is_base = 1', [$this->companyId()]);
                    foreach ($data->rates as $code => $rate) {
                        $symbol = $this->currencySymbols[$code] ?? '';
                        $existing = Database::fetch(
                            "SELECT id FROM currency_rates WHERE company_id = ? AND code = ?",
                            [$this->companyId(), $code]
                        );
                        if ($existing) {
                            Database::update('currency_rates', ['rate' => $rate, 'symbol' => $symbol], 'id = ?', [$existing->id]);
                        } else {
                            Database::insert('currency_rates', [
                                'company_id' => $this->companyId(),
                                'code' => $code,
                                'symbol' => $symbol,
                                'rate' => $rate,
                                'is_base' => 0,
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - rates just won't update
        }

        $this->render('settings.index', [
            'title' => 'Settings',
            'company' => $company,
            'settings' => $settingsMap,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('companies', [
            'name' => Request::post('name'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'address' => Request::post('address'),
            'timezone' => Request::post('timezone', 'UTC'),
        ], 'id = ?', [$this->companyId()]);

        Session::set('company_name', Request::post('name'));

        flash('success', 'Settings updated.');
        $this->redirect('/settings');
    }

    public function outlets(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $outlets = Database::fetchAll("SELECT * FROM outlets WHERE company_id = ? ORDER BY name", [$this->companyId()]);

        $this->render('settings.outlets', [
            'title' => 'Outlets',
            'outlets' => $outlets,
        ]);
    }

    public function outletStore(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::insert('outlets', [
            'company_id' => $this->companyId(),
            'name' => Request::post('name'),
            'code' => Request::post('code'),
            'display_currency' => Request::post('display_currency', 'IDR'),
            'address' => Request::post('address'),
            'phone' => Request::post('phone'),
            'email' => Request::post('email'),
        ]);

        flash('success', 'Outlet created.');
        $this->redirect('/settings/outlets');
    }

    public function outletUpdate(string $id): void
    {
        $this->requireAuth();
        $this->requireCompany();

        Database::update('outlets', [
            'name' => Request::post('name'),
            'code' => Request::post('code'),
            'address' => Request::post('address'),
            'phone' => Request::post('phone'),
            'email' => Request::post('email'),
        ], 'id = ? AND company_id = ?', [$id, $this->companyId()]);

        flash('success', 'Outlet updated.');
        $this->redirect('/settings/outlets');
    }

    public function switchOutlet(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $outletId = Request::post('outlet_id');
        $outlet = Database::fetch("SELECT * FROM outlets WHERE id = ? AND company_id = ?", [$outletId, $this->companyId()]);

        if ($outlet) {
            Session::set('outlet_id', $outlet->id);
            Session::set('outlet_name', $outlet->name);
            Session::set('display_currency', $outlet->display_currency ?? 'IDR');
        }

        $this->redirect('/dashboard');
    }

    public function users(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $users = Database::fetchAll(
            "SELECT u.id, u.name, u.email, cu.role, cu.is_active
             FROM company_user cu JOIN users u ON u.id = cu.user_id
             WHERE cu.company_id = ? ORDER BY u.name",
            [$this->companyId()]
        );

        $this->render('settings.users', [
            'title' => 'Users',
            'users' => $users,
        ]);
    }

    public function userInvite(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $name = Request::post('name');
        $email = Request::post('email');
        $password = Request::post('password');
        $role = Request::post('role', 'cashier');

        $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $userId = $existing->id;
            $already = Database::fetch("SELECT id FROM company_user WHERE company_id = ? AND user_id = ?", [$this->companyId(), $userId]);
            if ($already) {
                flash('error', 'User already belongs to this company.');
                $this->redirect('/settings/users');
            }
        } else {
            $userId = Database::insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        Database::insert('company_user', [
            'company_id' => $this->companyId(),
            'user_id' => $userId,
            'role' => $role,
        ]);

        flash('success', 'User invited.');
        $this->redirect('/settings/users');
    }

    public function fetchRates(): void
    {
        $this->requireAuth();
        $this->requireCompany();

        $company = Database::fetch("SELECT currency_code FROM companies WHERE id = ?", [$this->companyId()]);
        $base = $company->currency_code ?? 'USD';

        try {
            $json = file_get_contents("https://api.frankfurter.app/latest?from={$base}");
            $data = json_decode($json);

            if (!$data || !isset($data->rates)) {
                flash('error', 'Failed to fetch rates from API.');
                $this->redirect('/settings');
            }

            Database::update('currency_rates', ['rate' => 1.000000], 'company_id = ? AND is_base = 1', [$this->companyId()]);

            foreach ($data->rates as $code => $rate) {
                $symbol = $this->currencySymbols[$code] ?? '';
                $existing = Database::fetch(
                    "SELECT id FROM currency_rates WHERE company_id = ? AND code = ?",
                    [$this->companyId(), $code]
                );

                if ($existing) {
                    Database::update('currency_rates', ['rate' => $rate, 'symbol' => $symbol], 'id = ?', [$existing->id]);
                } else {
                    Database::insert('currency_rates', [
                        'company_id' => $this->companyId(),
                        'code' => $code,
                        'symbol' => $symbol,
                        'rate' => $rate,
                        'is_base' => 0,
                    ]);
                }
            }

            flash('success', 'Live exchange rates updated (' . count((array)$data->rates) . ' currencies).');
        } catch (\Throwable $e) {
            flash('error', 'API error: ' . $e->getMessage());
        }

        $this->redirect('/settings');
    }
}
