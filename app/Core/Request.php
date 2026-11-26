<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $routeParams = [];

    public function setRouteParams(array $params): void { $this->routeParams = $params; }
    public function route(string $key, mixed $default = null): mixed { return $this->routeParams[$key] ?? $default; }

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $post,
        public readonly array $files,
        public readonly array $headers
    ) {}

    public static function capture(): self
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = str_replace('\\', '/', dirname($script));
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }
        if (!defined('BASE_URL')) {
            define('BASE_URL', $baseDir);
        }
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);
        $path = $uri;
        if ($baseDir !== '' && str_starts_with($path, $baseDir . '/')) {
            $path = substr($path, strlen($baseDir));
        } elseif ($baseDir !== '' && $path === $baseDir) {
            $path = '/';
        }
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $path = '/';
        }
        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_GET,
            $_POST,
            $_FILES,
            function_exists('getallheaders') ? getallheaders() : []
        );
    }

    public function is(string $method): bool { return $this->method === strtoupper($method); }
    public function isPost(): bool { return $this->is('POST'); }
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest'
            || str_contains(strtolower($this->header('Accept', '')), 'application/json');
    }
    public function header(string $name, string $default = ''): string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return (string) $value;
            }
        }
        return $default;
    }
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }
    public function post(string $key, mixed $default = null): mixed { return $this->post[$key] ?? $default; }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function file(string $key): ?array { return $this->files[$key] ?? null; }
    public function ip(): string { return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'); }
    public function userAgent(): string { return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250); }
}
