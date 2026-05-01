<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isAuthenticated(): bool
    {
        return Session::has('user_id');
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    protected function requireCompany(): void
    {
        if (!Session::has('company_id')) {
            $this->redirect('/settings');
        }
    }

    protected function companyId(): int
    {
        return (int) Session::get('company_id');
    }

    protected function outletId(): int
    {
        return (int) Session::get('outlet_id');
    }

    protected function userId(): int
    {
        return (int) Session::get('user_id');
    }

    protected function role(): string
    {
        return Session::get('role', 'cashier');
    }
}
