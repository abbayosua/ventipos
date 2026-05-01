<?php

namespace App\Core;

class Request
{
    protected static string $method;
    protected static string $uri;

    public static function init(): void
    {
        self::$method = strtoupper($_SERVER['REQUEST_METHOD']);

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }
        self::$uri = '/' . trim($uri, '/');
        self::$uri = preg_replace('#/+#', '/', self::$uri);
    }

    public static function method(): string
    {
        return self::$method;
    }

    public static function uri(): string
    {
        return self::$uri;
    }

    public static function isGet(): bool
    {
        return self::$method === 'GET';
    }

    public static function isPost(): bool
    {
        return self::$method === 'POST';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }
}
