<?php
declare(strict_types=1);

namespace App\Core;

/** Compact regex router with middleware support. */
final class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function middleware(string $name, callable $handler): void
    {
        $this->middleware[$name] = $handler;
    }

    public function add(string $methods, string $pattern, string $handler, array $middleware = []): void
    {
        foreach (explode('|', $methods) as $method) {
            $this->routes[] = [
                'method' => strtoupper(trim($method)),
                'regex' => $this->compile($pattern),
                'handler' => $handler,
                'middleware' => $middleware,
                'pattern' => $pattern,
            ];
        }
    }

    public function get(string $pattern, string $handler, array $middleware = []): void { $this->add('GET', $pattern, $handler, $middleware); }
    public function post(string $pattern, string $handler, array $middleware = []): void { $this->add('POST', $pattern, $handler, $middleware); }
    public function any(string $pattern, string $handler, array $middleware = []): void { $this->add('GET|POST', $pattern, $handler, $middleware); }

    private function compile(string $pattern): string
    {
        // An optional placeholder owns the slash before it, so both /jobs/4 and
        // /jobs/4/slug match without requiring a doubled slash.
        $pattern = preg_replace('/\/(?=\{[a-zA-Z_]+\?\})/', '', $pattern) ?? $pattern;
        $pattern = preg_replace_callback('/\{([a-zA-Z_]+)(\?)?\}/', static function ($m) {
            return ($m[2] ?? '') === '?' ? '(?:/(?P<' . $m[1] . '>[^/]+))?' : '(?P<' . $m[1] . '>[^/]+)';
        }, $pattern) ?? $pattern;
        // '#' is the delimiter, so literal slashes need no escaping. Escaping here
        // would also escape the '/' inside optional groups and break {slug?} routes.
        return '#^' . $pattern . '$#u';
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            $request->setRouteParams($params);
            foreach ($route['middleware'] as $middleware) {
                [$name, $argument] = array_pad(explode(':', $middleware, 2), 2, null);
                if (!isset($this->middleware[$name])) {
                    throw new \RuntimeException("Unknown middleware: $name");
                }
                $result = ($this->middleware[$name])($request, $argument);
                if ($result === null || $result === false) {
                    return; // middleware handled the response (redirect / abort)
                }
            }
            [$class, $method] = explode('@', $route['handler'], 2);
            $fqcn = 'App\\Controllers\\' . $class;
            if (!class_exists($fqcn)) {
                throw new \RuntimeException("Controller not found: $fqcn");
            }
            $controller = new $fqcn($request);
            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Action not found: $fqcn::$method");
            }
            $controller->{$method}();
            return;
        }
        abort(404, 'The page you requested could not be found.');
    }
}
