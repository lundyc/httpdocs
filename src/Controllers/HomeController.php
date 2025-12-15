<?php

declare(strict_types=1);

namespace MyClubHub\Controllers;

use MyClubHub\Core\Request;
use MyClubHub\Core\Response;

final class HomeController
{
    public function __construct(private array $config)
    {
    }

    public function index(Request $request): Response
    {
        $body = $this->render('home', [
            'title' => $this->config['name'] ?? 'MyClubHub',
            'message' => 'Foundation bootstrap online. Health check available at /health.',
        ]);

        return Response::html($body);
    }

    public function login(Request $request): Response
    {
        $body = $this->render('home', [
            'title' => 'Login',
            'message' => 'Authentication portal not wired yet.',
        ]);

        return Response::html($body);
    }

    public function admin(Request $request): Response
    {
        $body = $this->render('home', [
            'title' => 'Admin',
            'message' => 'Admin entrypoint placeholder.',
        ]);

        return Response::html($body);
    }

    private function render(string $view, array $data = []): string
    {
        $viewFile = PROJECT_ROOT . '/src/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException('View not found: ' . $viewFile);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;

        return (string)ob_get_clean();
    }
}
