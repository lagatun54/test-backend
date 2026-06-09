<?php

declare(strict_types=1);

namespace App;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly ?array $body,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        $body = null;
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            $body = is_array($decoded) ? $decoded : [];
        }

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            body: $body,
        );
    }
}
