<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Community
{
    public static function posts(string $category = '', int $limit = 30): array
    {
        if ($category !== '') {
            return Database::all(
                "SELECT p.*, u.name AS author_name, u.avatar AS author_avatar,
                 (SELECT COUNT(*) FROM community_comments c WHERE c.post_id = p.id) AS comment_total
                 FROM community_posts p JOIN users u ON u.id = p.user_id
                 WHERE p.status = 'published' AND p.category = :cat ORDER BY p.created_at DESC LIMIT $limit",
                ['cat' => $category]
            );
        }
        return Database::all(
            "SELECT p.*, u.name AS author_name, u.avatar AS author_avatar,
             (SELECT COUNT(*) FROM community_comments c WHERE c.post_id = p.id) AS comment_total
             FROM community_posts p JOIN users u ON u.id = p.user_id
             WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT $limit"
        );
    }

    public static function post(int $id): ?array
    {
        return Database::one(
            'SELECT p.*, u.name AS author_name, u.avatar AS author_avatar, u.role AS author_role
             FROM community_posts p JOIN users u ON u.id = p.user_id WHERE p.id = :id',
            ['id' => $id]
        );
    }

    public static function comments(int $postId): array
    {
        return Database::all(
            'SELECT c.*, u.name AS author_name, u.avatar AS author_avatar FROM community_comments c
             JOIN users u ON u.id = c.user_id WHERE c.post_id = :id ORDER BY c.created_at ASC',
            ['id' => $postId]
        );
    }

    public static function toggleLike(int $postId, int $userId): bool
    {
        $exists = Database::scalar('SELECT COUNT(*) FROM community_likes WHERE post_id = :p AND user_id = :u', ['p' => $postId, 'u' => $userId]);
        if ($exists) {
            Database::delete('community_likes', 'post_id = :p AND user_id = :u', ['p' => $postId, 'u' => $userId]);
            Database::query('UPDATE community_posts SET likes_count = GREATEST(0, likes_count - 1) WHERE id = :id', ['id' => $postId]);
            return false;
        }
        Database::insert('community_likes', ['post_id' => $postId, 'user_id' => $userId, 'created_at' => date('Y-m-d H:i:s')]);
        Database::query('UPDATE community_posts SET likes_count = likes_count + 1 WHERE id = :id', ['id' => $postId]);
        return true;
    }
}
