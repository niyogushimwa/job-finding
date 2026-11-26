<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Content
{
    public static function articles(string $category = '', int $limit = 12): array
    {
        if ($category !== '') {
            return Database::all("SELECT * FROM articles WHERE status = 'published' AND category = :cat ORDER BY published_at DESC LIMIT $limit", ['cat' => $category]);
        }
        return Database::all("SELECT * FROM articles WHERE status = 'published' ORDER BY featured DESC, published_at DESC LIMIT $limit");
    }

    public static function article(string $slug): ?array
    {
        return Database::one("SELECT * FROM articles WHERE slug = :slug AND status = 'published'", ['slug' => $slug]);
    }

    public static function questions(string $category = ''): array
    {
        if ($category !== '') {
            return Database::all('SELECT * FROM interview_questions WHERE active = 1 AND category = :cat ORDER BY id', ['cat' => $category]);
        }
        return Database::all('SELECT * FROM interview_questions WHERE active = 1 ORDER BY FIELD(category, "common", "behavioral", "technical", "hr"), id');
    }

    public static function tips(int $limit = 4): array
    {
        return Database::all("SELECT * FROM career_tips WHERE active = 1 ORDER BY id DESC LIMIT $limit");
    }

    public static function stories(): array
    {
        return Database::all("SELECT * FROM success_stories WHERE status = 'published' ORDER BY id DESC LIMIT 6");
    }

    public static function ads(string $position): array
    {
        return Database::all(
            "SELECT * FROM advertisements WHERE position = :pos AND active = 1
             AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW())
             ORDER BY id DESC LIMIT 3",
            ['pos' => $position]
        );
    }

    public static function setting(string $key, string $default = ''): string
    {
        return (string) (Database::scalar('SELECT setting_value FROM platform_settings WHERE setting_key = :k', ['k' => $key]) ?? $default);
    }
}
