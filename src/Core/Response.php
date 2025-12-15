<?php

declare(strict_types=1);

namespace MyClubHub\Core;

final class Response
{
    private int $statusCode = 200;
    private array $headers = [
        'Content-Type' => 'text/html; charset=UTF-8',
    ];
    private string $body = '';

    public static function html(string $body, int $statusCode = 200): self
    {
        $response = new self();
        $response->setBody($body);
        $response->setStatusCode($statusCode);

        return $response;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        echo $this->body;
    }
}
