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
        $this->render('auth.register', ['title' => 'Register'], 'auth');
    }

    public function register(): void
    {
        $name = Request::post('name');
        $email = Request::post('email');
        $password = Request::post('password');
        $companyName = Request::post('company_name');

        $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Session::flash('error', 'Email already registered.');
            $this->redirect('/register');
        }

        try {
            Database::beginTransaction();

            $userId = Database::insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $companyId = Database::insert('companies', [
                'name' => $companyName,
                'email' => $email,
                'currency_code' => 'IDR',
                'currency_symbol' => 'Rp',
                'base_currency' => 'IDR',
                'display_currency' => 'IDR',
            ]);

            Database::insert('company_user', [
                'company_id' => $companyId,
                'user_id' => $userId,
                'role' => 'owner',
            ]);

            Database::insert('outlets', [
                'company_id' => $companyId,
                'name' => 'Main Store',
                'code' => 'MAIN',
            ]);

            Database::insert('currency_rates', [
                'company_id' => $companyId,
                'code' => 'IDR',
                'symbol' => 'Rp',
                'rate' => 1.000000,
                'is_base' => 1,
            ]);

            Database::commit();

            Session::flash('success', 'Registration successful. Please login.');
            $this->redirect('/login');
        } catch (\Throwable $e) {
            Database::rollback();
            Session::flash('error', 'Registration failed: ' . $e->getMessage());
            $this->redirect('/register');
        }
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
