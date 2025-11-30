<?php

namespace App\Core;

class Request
{
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
    }

    public function getBody(): array
    {
        $body = [];
        
        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        
        if ($this->getMethod() === 'POST' || $this->getMethod() === 'PUT' || $this->getMethod() === 'DELETE') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
            
            // Also get JSON input
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonInput)) {
                $body = array_merge($body, $jsonInput);
            }
        }
        
        return $body;
    }

    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getHeader(string $name): ?string
    {
        $headers = getallheaders();
        return $headers[$name] ?? null;
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type') ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
}