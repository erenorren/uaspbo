<?php
// src/Core/Router.php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple Router untuk REST API E-Learning
 */
class Router
{
    /** @var array<int, array{method:string, path:string, handler:callable}> */
    private array $routes = [];

    public function __construct(
        private string $basePath = '/api' // semua endpoint dimulai dengan /api
    ) {}

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        header('Content-Type: application/json');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Hilangkan base path (/api) dari URL
        if ($this->basePath !== '' && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
            if ($uri === '') {
                $uri = '/';
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // buang full match
                // panggil controller method dengan parameter dari URL
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // Endpoint tidak ditemukan
        http_response_code(404);
        echo json_encode([
            'success'     => false,
            'status_code' => 404,
            'message'     => 'Endpoint not found',
            'data'        => null,
            'errors'      => null,
        ]);
    }

    private function convertPathToRegex(string $path): string
    {
        // Contoh: /courses/:id  →  ^/courses/(?P<id>[^/]+)$
        $pattern = preg_replace(
            '/\/:([a-zA-Z0-9_]+)/',
            '/(?P<$1>[^/]+)',
            $path
        );

        return '#^' . $pattern . '$#';
    }
}
