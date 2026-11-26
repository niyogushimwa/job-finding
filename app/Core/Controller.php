<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

abstract class Controller
{
    protected Request $request;
    protected array $data = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function render(string $view, array $data = [], string $title = ''): void
    {
        $this->data = array_merge($this->data, $data);
        $this->data['title'] = $title ?: ($this->data['title'] ?? config('app.name'));
        $this->data['request'] = $this->request;
        extract($this->data, EXTR_SKIP);
        ob_start();
        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }
        include $viewFile;
        $content = ob_get_clean();
        include BASE_PATH . '/app/Views/layouts/main.php';
    }

    protected function partial(string $view, array $data = []): string
    {
        extract(array_merge($this->data, $data), EXTR_SKIP);
        ob_start();
        include BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        return (string) ob_get_clean();
    }

    protected function json(mixed $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function validate(array $rules): array
    {
        return Validator::make($this->request->post, $rules);
    }

    protected function requireAuth(string ...$roles): void
    {
        if (!Auth::check()) {
            flash('error', 'Please log in to continue.');
            redirect('login');
        }
        if ($roles && !Auth::hasRole(...$roles)) {
            abort(403, 'You do not have permission to access this area.');
        }
    }

    protected function csrf(): void
    {
        Csrf::check((string) $this->request->post('_csrf', ''));
    }

    protected function audit(string $action, string $description): void
    {
        if (Auth::id()) {
            User::logAudit(Auth::id(), $action, $description, $this->request->ip());
        }
    }
}
