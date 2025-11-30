<?php

namespace App\Core;

class Router
{
    private array $routes = [];

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
        $request = new Request();
        $method = $request->getMethod();
        $uri = $request->getPath();

        // Remove base path if exists
        $basePath = '/api';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Pass request object as first parameter
                array_unshift($matches, $request);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // Route not found
        $response = new Response();
        $response->json([
            'success' => false,
            'status_code' => 404,
            'message' => 'Endpoint not found'
        ], 404);
    }

    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}