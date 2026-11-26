<?php
declare(strict_types=1);

use App\Core\I18n;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('app')) {
    function app(string $key): mixed
    {
        $container = $GLOBALS['app_container'] ?? [];
        if (isset($container[$key])) {
            return $container[$key];
        }
        $config = $container['config'] ?? null;
        return $config?->get($key);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $config = $GLOBALS['app_container']['config'] ?? null;
        return $config ? $config->get($key, $default) : $default;
    }
}

if (!function_exists('e')) {
    /** HTML-escape every dynamic value rendered in a view. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('t')) {
    function t(string $key, array $replace = []): string
    {
        return I18n::translate($key, $replace);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(App\Core\Csrf::token()) . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return App\Core\Csrf::token();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path = ''): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(int $code = 404, string $message = ''): never
    {
        http_response_code($code);
        $title = $code === 403 ? 'Access denied' : ($code === 500 ? 'Server error' : 'Page not found');
        $view = BASE_PATH . '/app/Views/errors/error.php';
        if (is_file($view)) {
            include $view;
        } else {
            echo '<h1>' . $code . ' ' . e($title) . '</h1><p>' . e($message) . '</p>';
        }
        exit;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower(trim($value))) ?? '';
        return trim($value, '-');
    }
}

if (!function_exists('str_limit')) {
    function str_limit(?string $value, int $limit = 140): string
    {
        $value = trim((string) $value);
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit - 1) . '…';
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $date): string
    {
        if (!$date) {
            return '—';
        }
        $seconds = max(0, time() - strtotime($date));
        return match (true) {
            $seconds < 60 => 'just now',
            $seconds < 3600 => floor($seconds / 60) . ' min ago',
            $seconds < 86400 => floor($seconds / 3600) . ' h ago',
            $seconds < 604800 => floor($seconds / 86400) . ' d ago',
            default => date('M j, Y', strtotime($date)),
        };
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M j, Y'): string
    {
        return $date && strtotime($date) ? date($format, strtotime($date)) : '—';
    }
}

if (!function_exists('format_money')) {
    function format_money(?float $amount, string $currency = 'RWF', string $period = 'monthly'): string
    {
        if ($amount === null || $amount <= 0) {
            return 'Salary not specified';
        }
        $symbols = ['RWF' => 'RWF ', 'USD' => '$', 'EUR' => '€', 'KES' => 'KSh ', 'UGX' => 'UGX '];
        return ($symbols[$currency] ?? $currency . ' ') . number_format($amount, 0) . ' / ' . $period;
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('take_flash')) {
    function take_flash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }
}

if (!function_exists('old_input')) {
    /** Store submitted input for redisplay after validation errors. */
    function old_input(array $data): void
    {
        $_SESSION['_old'] = $data;
    }
}

if (!function_exists('clear_old')) {
    function clear_old(): void
    {
        unset($_SESSION['_old']);
    }
}

if (!function_exists('icon')) {
    /** Inline SVG icon set (no external icon dependencies). */
    function icon(string $name, int $size = 20, string $class = ''): string
    {
        $paths = [
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 13h20"/>',
            'map' => '<path d="M12 21s7-6.4 7-11a7 7 0 1 0-14 0c0 4.6 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
            'chevron' => '<path d="m9 18 6-6-6-6"/>',
            'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
            'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.6 3-6 7-6s7 2.4 7 6M16 5a3.5 3.5 0 0 1 0 7M18 14c2.5.7 4 2.7 4 6"/>',
            'bookmark' => '<path d="M6 3h12v18l-6-4-6 4V3Z"/>',
            'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
            'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 6 9 7 9-7"/>',
            'check' => '<path d="m4 12 5 5L20 6"/>',
            'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'star' => '<path d="m12 3 2.7 5.6 6.3.9-4.5 4.4 1 6.1-5.5-2.9L6.5 20l1-6.1L3 9.5l6.3-.9L12 3Z"/>',
            'shield' => '<path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
            'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21h-4v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3v-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3h4v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1H21v4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14 0 18M12 3c-3 3.5-3 14 0 18"/>',
            'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/>',
            'upload' => '<path d="M12 17V5m0 0 4 4m-4-4-4 4M4 19h16"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'edit' => '<path d="M4 20h4l11-11-4-4L4 16v4ZM13 7l4 4"/>',
            'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14"/>',
            'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'heart' => '<path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11Z"/>',
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'moon' => '<path d="M20 14A8 8 0 0 1 10 4a8 8 0 1 0 10 10Z"/>',
            'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
            'graduation' => '<path d="m2 8 10-5 10 5-10 5L2 8Z"/><path d="M6 10v5c0 2 2.7 3.5 6 3.5s6-1.5 6-3.5v-5M22 8v6"/>',
            'rocket' => '<path d="M12 2c4 2 6 6 6 11l-6 4-6-4c0-5 2-9 6-11Z"/><circle cx="12" cy="9" r="2"/><path d="m6 16-3 3 5-1m10-2 3 3-5-1M9 20l3 2 3-2"/>',
            'code' => '<path d="m8 8-4 4 4 4m8-8 4 4-4 4M14 4l-4 16"/>',
            'chat' => '<path d="M20 15a3 3 0 0 1-3 3H8l-4 3V6a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v9Z"/>',
            'flag' => '<path d="M5 21V4m0 0h12l-2 4 2 4H5"/>',
            'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/>',
            'filter' => '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5Z"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
            'building' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/>',
            'award' => '<circle cx="12" cy="9" r="5"/><path d="m8 13-2 8 6-3 6 3-2-8"/>',
        ];
        $path = $paths[$name] ?? $paths['info'];
        return '<svg class="icon ' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    }
}
