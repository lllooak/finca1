<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router - lightweight regex router supporting named params.
 * Routes map to [Controller, method].
 */
final class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function any(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        // Convert {name} to named capture groups, {name:\d+} for typed
        $regex = preg_replace_callback('~\{([a-zA-Z_]+)(?::([^}]+))?\}~', function ($m) {
            $name = $m[1];
            $sub  = $m[2] ?? '[^/]+';
            return '(?P<' . $name . '>' . $sub . ')';
        }, $pattern);
        $regex = '~^' . $regex . '$~u';

        $this->routes[] = compact('method', 'regex', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($path), '/');
        if ($path === '/') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action($params);
                return;
            }
        }

        $this->notFound();
    }

    public function notFound(): void
    {
        http_response_code(404);
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
    }
}
