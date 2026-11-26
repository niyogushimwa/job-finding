<?php
declare(strict_types=1);

/**
 * JobLink Rwanda — single front controller.
 * Every request is routed through app/bootstrap.php and config/routes.php.
 */
require __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;

$request = Request::capture();

// Serve only public real files (assets, uploads, manifest, service worker).
// Private paths such as /.env, /config/app.php and /database/schema.sql must never be downloadable.
$publicFile = $request->path === '/manifest.json'
    || $request->path === '/sw.js'
    || str_starts_with($request->path, '/assets/')
    || str_starts_with($request->path, '/uploads/');
$requestedFile = $publicFile ? realpath(BASE_PATH . $request->path) : false;
if ($requestedFile && is_file($requestedFile) && str_starts_with($requestedFile, BASE_PATH . DIRECTORY_SEPARATOR)) {
    // Defence in depth: never execute or disclose code/configuration files.
    $extension = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));
    if (in_array($extension, ['php', 'phtml', 'inc', 'sql', 'env', 'log', 'git', 'md'], true)) {
        http_response_code(404);
        exit;
    }
    $mimes = [
        'css' => 'text/css; charset=UTF-8', 'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8', 'svg' => 'image/svg+xml',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp', 'pdf' => 'application/pdf', 'ico' => 'image/x-icon',
        'woff2' => 'font/woff2', 'txt' => 'text/plain; charset=UTF-8',
    ];
    header('Content-Type: ' . ($mimes[$extension] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($requestedFile));
    header('X-Content-Type-Options: nosniff');
    readfile($requestedFile);
    exit;
}

if ($request->path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (['', '/jobs', '/companies', '/opportunity-radar', '/internships', '/remote-jobs', '/freelance', '/scholarships', '/career-centre', '/interview-prep', '/community', '/about', '/contact'] as $path) {
        echo '<url><loc>' . e(url($path)) . '</loc><changefreq>weekly</changefreq></url>';
    }
    echo '</urlset>';
    exit;
}

if ($request->path === '/robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "User-agent: *\nAllow: /\nSitemap: " . url('sitemap.xml') . "\n";
    exit;
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

$router = new Router();
(require BASE_PATH . '/config/routes.php')($router);

try {
    Database::connection();
    $router->dispatch($request);
} catch (\Throwable $e) {
    $message = $e->getMessage();
    error_log('[JobLink] ' . $message . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (config('app.debug')) {
        echo '<div style="font-family:monospace;padding:2rem;max-width:900px"><h1>Application error</h1><p>' . e($message) . '</p><pre>' . e($e->getTraceAsString()) . '</pre></div>';
    } else {
        echo '<h1>Something went wrong</h1><p>Our team has been notified. Please try again later.</p>';
    }
}
