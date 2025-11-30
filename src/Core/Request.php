<?php

namespace App\Core;

class Request
{
    private array $queryParams;
    private array $bodyParams;
    private array $serverParams;

    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->bodyParams = $this->getJsonBody() ?? $_POST;
        $this->serverParams = $_SERVER;
    }

    public function getMethod(): string
    {
        return $this->serverParams['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $path = $this->serverParams['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBodyParams(): array
    {
        return $this->bodyParams;
    }

    public function getJsonBody(): ?array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return null;
        }

        $data = json_decode($input, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    public function getHeader(string $name): ?string
    {
        $name = strtoupper(str_replace('-', '_', $name));
        $key = 'HTTP_' . $name;
        
        return $this->serverParams[$key] ?? null;
    }
}