<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $user = null;

    public static function user(): ?array
    {
        if (self::$user === null && Session::get('user_id')) {
            self::$user = User::find((int) Session::get('user_id'));
            if (!self::$user || self::$user['status'] !== 'active') {
                self::$user = null;
                Session::forget('user_id');
            }
        }
        return self::$user;
    }

    public static function id(): ?int { return self::user()['id'] ?? null; }
    public static function role(): ?string { return self::user()['role'] ?? null; }
    public static function check(): bool { return self::user() !== null; }
    public static function guest(): bool { return !self::check(); }
    public static function hasRole(string ...$roles): bool { return in_array(self::role(), $roles, true); }

    /** Permission gates used throughout the application. */
    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        return match ($permission) {
            'admin' => $user['role'] === 'admin',
            'employer' => $user['role'] === 'employer',
            'seeker' => $user['role'] === 'seeker',
            'freelancer' => $user['role'] === 'freelancer',
            'post_job' => in_array($user['role'], ['employer', 'admin'], true),
            'apply' => in_array($user['role'], ['seeker', 'freelancer'], true),
            'message' => $user['role'] !== 'admin' || $user['role'] === 'admin',
            default => false,
        };
    }

    public static function attempt(string $email, string $password): array
    {
        $user = User::findByEmail(trim(mb_strtolower($email)));
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [false, 'Invalid email or password.'];
        }
        if ($user['status'] === 'suspended') {
            return [false, 'This account has been suspended. Please contact support.'];
        }
        if ($user['status'] === 'deactivated') {
            return [false, 'This account has been deactivated. Please contact support to reactivate it.'];
        }
        self::login($user);
        return [true, $user];
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
        self::$user = $user;
        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
        User::logAudit((int) $user['id'], 'login', 'User logged in');
    }

    public static function logout(): void
    {
        if (self::id()) {
            User::logAudit(self::id(), 'logout', 'User logged out');
        }
        self::$user = null;
        Session::forget('user_id');
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }
        Session::regenerate();
    }

    public static function refresh(): void
    {
        self::$user = null;
        self::user();
    }
}
