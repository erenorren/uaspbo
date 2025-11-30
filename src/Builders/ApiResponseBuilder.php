<?php

namespace App\Builders;

class ApiResponseBuilder
{
    private int $statusCode = 200;
    private string $message = '';
    private mixed $data = null;
    private array $errors = [];
    private array $meta = [];

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function addMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function build(): array
    {
        $response = [
            'success' => $this->statusCode >= 200 && $this->statusCode < 300,
            'status_code' => $this->statusCode,
        ];

        if (!empty($this->message)) {
            $response['message'] = $this->message;
        }

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        return $response;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($this->build(), JSON_PRETTY_PRINT);
        exit;
    }

    // Static helper methods
    public static function success($data = null, string $message = 'Success'): self
    {
        return (new self())
            ->setStatusCode(200)
            ->setMessage($message)
            ->setData($data);
    }

    public static function created($data = null, string $message = 'Resource created'): self
    {
        return (new self())
            ->setStatusCode(201)
            ->setMessage($message)
            ->setData($data);
    }

    public static function error(string $message, int $code = 400, array $errors = []): self
    {
        return (new self())
            ->setStatusCode($code)
            ->setMessage($message)
            ->setErrors($errors);
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return (new self())
            ->setStatusCode(404)
            ->setMessage($message);
    }

    public static function validationError(array $errors): self
    {
        return (new self())
            ->setStatusCode(422)
            ->setMessage('Validation failed')
            ->setErrors($errors);
    }
}