<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function boot(): void
    {
        if (!Session::get('_csrf')) {
            Session::put('_csrf', bin2hex(random_bytes(32)));
        }
    }

    public static function token(): string
    {
        return (string) Session::get('_csrf');
    }

    public static function valid(?string $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }

    public static function check(?string $token): void
    {
        if (!self::valid($token)) {
            http_response_code(419);
            abort(419, 'Your session expired. Please refresh the page and try again.');
        }
    }
}
