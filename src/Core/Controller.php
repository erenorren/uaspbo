<?php

namespace App\Core;

use App\Builders\ApiResponseBuilder;

abstract class Controller
{
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected function getQueryParams(): array
    {
        return $_GET;
    }

    protected function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}