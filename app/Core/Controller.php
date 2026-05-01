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

    protected function toBaseCurrency(float $amount): float
    {
        $base = Session::get('base_currency', 'IDR');
        $display = Session::get('display_currency', 'IDR');
        if ($base === $display) return $amount;

        $rate = Database::fetch(
            "SELECT rate FROM currency_rates WHERE company_id = ? AND code = ?",
            [$this->companyId(), $display]
        );
        if ($rate && (float)$rate->rate > 0) {
            return $amount / (float)$rate->rate;
        }
        return $amount;
    }

    protected function toDisplayCurrency(float $amount): float
    {
        $base = Session::get('base_currency', 'IDR');
        $display = Session::get('display_currency', 'IDR');
        if ($base === $display || !$display) return $amount;

        $rate = Database::fetch(
            "SELECT rate FROM currency_rates WHERE company_id = ? AND code = ?",
            [$this->companyId(), $display]
        );
        if ($rate && (float)$rate->rate > 0) {
            return $amount * (float)$rate->rate;
        }
        return $amount;
    }

    protected function displayCurrencySymbol(): string
    {
        $display = Session::get('display_currency', 'IDR');
        $rate = Database::fetch(
            "SELECT symbol FROM currency_rates WHERE company_id = ? AND code = ?",
            [$this->companyId(), $display]
        );
        return $rate->symbol ?? ($display === 'IDR' ? 'Rp' : '$');
    }
}
