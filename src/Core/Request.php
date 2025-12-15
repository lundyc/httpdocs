<?php

declare(strict_types=1);

namespace MyClubHub\Core;

final class Request
{
    private string $method;
    private string $path;
    private array $queryParams;
    private array $bodyParams;
    private array $server;

    public function __construct(array $server, array $queryParams, array $bodyParams)
    {
        $this->server = $server;
        $this->method = strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET'));
        $this->path = $this->normalisePath((string)($server['REQUEST_URI'] ?? '/'));
        $this->queryParams = $queryParams;
        $this->bodyParams = $bodyParams;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBodyParams(): array
    {
        return $this->bodyParams;
    }

    public function getServerParam(string $key, ?string $default = null): ?string
    {
        $value = $this->server[$key] ?? $default;

        return $value === null ? null : (string)$value;
    }

    private function normalisePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        $normalised = '/' . trim($trimmed, '/');

        return $normalised === '' ? '/' : $normalised;
    }
}
