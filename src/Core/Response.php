<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private $body;

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody($body): self
    {
        $this->body = $body;
        return $this;
    }

    public function json($data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode)
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data, JSON_PRETTY_PRINT))
            ->send();
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->body !== null) {
            echo $this->body;
        }

        exit;
    }
}