<?php

declare(strict_types=1);

namespace MyClubHub\Core;

use Closure;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $normalised = $this->normalisePath($path);
        $this->routes['GET'][$normalised] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $methodRoutes = $this->routes[$request->getMethod()] ?? [];
        $handler = $methodRoutes[$request->getPath()] ?? null;

        if ($handler === null) {
            return $this->notFound();
        }

        $response = $handler($request);

        if ($response instanceof Response) {
            return $response;
        }

        if ($response instanceof Closure) {
            $response = $response();
        }

        return Response::html((string)$response);
    }

    private function notFound(): Response
    {
        return Response::html('<h1>404 - Page Not Found</h1>', 404);
    }

    private function normalisePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }
}
