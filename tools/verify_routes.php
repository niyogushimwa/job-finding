<?php
declare(strict_types=1);

/** Development helper: confirms every declared route resolves to a real controller action. */
require dirname(__DIR__) . '/app/bootstrap.php';

$source = (string) file_get_contents(dirname(__DIR__) . '/config/routes.php');
preg_match_all("/'([A-Za-z]+Controller)@([A-Za-z]+)'/", $source, $matches, PREG_SET_ORDER);

$missing = [];
foreach ($matches as $match) {
    $class = 'App\\Controllers\\' . $match[1];
    if (!class_exists($class)) {
        $missing[] = $match[0] . ' (class missing)';
        continue;
    }
    if (!method_exists($class, $match[2])) {
        $missing[] = $match[0] . ' (method missing)';
    }
}

if ($missing) {
    fwrite(STDERR, "Missing route targets:\n - " . implode("\n - ", $missing) . "\n");
    exit(1);
}

echo 'OK: ' . count($matches) . " declared route handlers resolve to controller actions.\n";
