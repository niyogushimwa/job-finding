<?php
declare(strict_types=1);

/** JobLink Rwanda application bootstrap. */
define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

require BASE_PATH . '/app/Core/Support.php';

/** Load a .env file into getenv() without overwriting real environment variables. */
function load_dotenv(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

load_dotenv(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/app.php';
date_default_timezone_set($config['app']['timezone']);
mb_internal_encoding('UTF-8');

if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
if (!is_dir(BASE_PATH . '/storage/logs')) {
    @mkdir(BASE_PATH . '/storage/logs', 0775, true);
}
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

// PSR-4-ish auto-loader for app classes.
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Core\\' => BASE_PATH . '/app/Core/',
        'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
        'App\\Models\\' => BASE_PATH . '/app/Models/',
    ];
    foreach ($prefixes as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $file = $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
            return;
        }
    }
});

$registry = new class($config) {
    private array $items;
    public function __construct(array $config) { $this->items = $config; }
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
};

// Simple service container.
$GLOBALS['app_container'] = [
    'config' => $registry,
    'db' => null,
];

App\Core\Session::start($config['security']['session_name']);
App\Core\I18n::boot($config['app']['locale'], $config['app']['supported_locales']);
App\Core\Csrf::boot();
