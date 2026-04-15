<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * @param array<string, mixed> $data
     */
    public function json(array $data, int $status = 200): void
    {
        $this->sendHeaders($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function notFound(string $message = 'Not found'): void
    {
        $this->sendHeaders(404);
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function badRequest(string $message): void
    {
        $this->sendHeaders(400);
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function internalError(string $message = 'Internal server error'): void
    {
        $this->sendHeaders(500);
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function sendHeaders(int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
    }
}
