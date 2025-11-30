<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

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
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $method = $this->request->getMethod();
        $uri = $this->request->getPath();

        // Remove base path if exists
        $basePath = '/api';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Handle root API path
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Extract named parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }
                
                // Call handler with parameters
                call_user_func_array($route['handler'], array_values($params));
                return;
            }
        }

        // Route not found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'status_code' => 404,
            'message' => 'Endpoint not found: ' . $uri
        ]);
    }

    private function convertPathToRegex(string $path): string
    {
        // Convert :id to regex pattern
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}