<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $pattern, string $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    protected function addRoute(string $method, string $pattern, string $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                [$controller, $action] = explode('@', $route['handler']);
                $controllerClass = 'App\\Controllers\\' . $controller;

                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller {$controllerClass} not found");
                }

                $instance = new $controllerClass();

                if (!method_exists($instance, $action)) {
                    throw new \RuntimeException("Action {$action} not found in {$controllerClass}");
                }

                call_user_func_array([$instance, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
