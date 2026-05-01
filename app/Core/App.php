<?php

namespace App\Core;

class App
{
    protected array $config;
    protected Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router();

        Session::start($config['session']['lifetime'] ?? 86400);
        Database::init($config['database']);
        Request::init();

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        $this->router->get('/', 'AuthController@loginForm');
        $this->router->get('/login', 'AuthController@loginForm');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/logout', 'AuthController@logout');
        $this->router->get('/register', 'AuthController@registerForm');
        $this->router->post('/register', 'AuthController@register');

        $this->router->get('/dashboard', 'DashboardController@index');

        $this->router->get('/pos', 'PosController@index');
        $this->router->post('/pos/checkout', 'PosController@checkout');
        $this->router->get('/pos/products', 'PosController@searchProducts');
        $this->router->get('/pos/customers', 'PosController@searchCustomers');

        $this->router->get('/products', 'ProductController@index');
        $this->router->get('/products/create', 'ProductController@create');
        $this->router->post('/products/store', 'ProductController@store');
        $this->router->get('/products/edit/{id}', 'ProductController@edit');
        $this->router->post('/products/update/{id}', 'ProductController@update');
        $this->router->post('/products/delete/{id}', 'ProductController@destroy');

        $this->router->get('/categories', 'CategoryController@index');
        $this->router->get('/categories/create', 'CategoryController@create');
        $this->router->post('/categories/store', 'CategoryController@store');
        $this->router->get('/categories/edit/{id}', 'CategoryController@edit');
        $this->router->post('/categories/update/{id}', 'CategoryController@update');
        $this->router->post('/categories/delete/{id}', 'CategoryController@destroy');

        $this->router->get('/customers', 'CustomerController@index');
        $this->router->get('/customers/create', 'CustomerController@create');
        $this->router->post('/customers/store', 'CustomerController@store');
        $this->router->get('/customers/edit/{id}', 'CustomerController@edit');
        $this->router->post('/customers/update/{id}', 'CustomerController@update');
        $this->router->post('/customers/delete/{id}', 'CustomerController@destroy');

        $this->router->get('/suppliers', 'SupplierController@index');
        $this->router->get('/suppliers/create', 'SupplierController@create');
        $this->router->post('/suppliers/store', 'SupplierController@store');
        $this->router->get('/suppliers/edit/{id}', 'SupplierController@edit');
        $this->router->post('/suppliers/update/{id}', 'SupplierController@update');
        $this->router->post('/suppliers/delete/{id}', 'SupplierController@destroy');

        $this->router->get('/stock', 'StockController@index');
        $this->router->get('/stock/adjustment', 'StockController@adjustmentForm');
        $this->router->post('/stock/adjustment', 'StockController@adjustmentStore');
        $this->router->get('/stock/movements/{id}', 'StockController@movements');

        $this->router->get('/expenses', 'ExpenseController@index');
        $this->router->get('/expenses/create', 'ExpenseController@create');
        $this->router->post('/expenses/store', 'ExpenseController@store');
        $this->router->post('/expenses/delete/{id}', 'ExpenseController@destroy');

        $this->router->get('/sales', 'SaleController@index');
        $this->router->get('/sales/{id}', 'SaleController@show');

        $this->router->get('/reports/daily', 'ReportController@daily');
        $this->router->get('/reports/top-products', 'ReportController@topProducts');
        $this->router->get('/reports/profit-loss', 'ReportController@profitLoss');

        $this->router->get('/settings', 'SettingController@index');
        $this->router->post('/settings/update', 'SettingController@update');
        $this->router->get('/settings/outlets', 'SettingController@outlets');
        $this->router->post('/settings/outlets/store', 'SettingController@outletStore');
        $this->router->post('/settings/outlets/update/{id}', 'SettingController@outletUpdate');
        $this->router->post('/settings/switch-outlet', 'SettingController@switchOutlet');
        $this->router->get('/settings/users', 'SettingController@users');
        $this->router->post('/settings/users/invite', 'SettingController@userInvite');
    }

    public function run(): void
    {
        try {
            $this->router->dispatch(
                Request::method(),
                Request::uri()
            );
        } catch (\Throwable $e) {
            if ($this->config['app']['debug'] ?? false) {
                echo '<h3>' . $e->getMessage() . '</h3>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            } else {
                http_response_code(500);
                echo 'An error occurred.';
            }
        }
    }
}
