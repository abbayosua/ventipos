<?php

if (!function_exists('baseUrl')) {
    function baseUrl(string $path = ''): string
    {
        $config = require __DIR__ . '/../../config.php';
        $url = $config['app']['url'];
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $path = ''): string
    {
        return baseUrl('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        return \App\Core\Session::flash($key, $message);
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $amount, ?string $to = null): string
    {
        static $cache = [];
        $to = $to ?? \App\Core\Session::get('display_currency', 'IDR');

        if (!isset($cache[$to])) {
            $companyId = \App\Core\Session::get('company_id');
            $base = \App\Core\Session::get('base_currency', 'IDR');

            if ($to === $base) {
                $cache[$to] = ['rate' => 1, 'symbol' => \App\Core\Session::get('currency_symbol', 'Rp')];
            } else {
                $rate = \App\Core\Database::fetch(
                    "SELECT rate, symbol FROM currency_rates WHERE company_id = ? AND code = ?",
                    [$companyId, $to]
                );
                if ($rate) {
                    $cache[$to] = ['rate' => (float)$rate->rate, 'symbol' => $rate->symbol ?: $to];
                } else {
                    $cache[$to] = ['rate' => 1, 'symbol' => $to];
                }
            }
        }

        $converted = $amount * $cache[$to]['rate'];
        return $cache[$to]['symbol'] . number_format($converted, 2);
    }
}

if (!function_exists('formatDate')) {
    function formatDate(string $date, string $format = 'd M Y'): string
    {
        return date($format, strtotime($date));
    }
}

if (!function_exists('csrfField')) {
    function csrfField(): string
    {
        $token = bin2hex(random_bytes(32));
        \App\Core\Session::set('csrf_token', $token);
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrfCheck')) {
    function csrfCheck(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        $saved = \App\Core\Session::get('csrf_token');
        if ($token !== $saved) {
            http_response_code(419);
            echo 'Session expired. Please go back and try again.';
            exit;
        }
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return \App\Lang\Lang::get($key, $replace);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config.php';
        }
        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}
