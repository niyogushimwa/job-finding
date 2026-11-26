<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Notifications;

final class Message
{
    public static function threads(int $userId): array
    {
        return Database::all(
            "SELECT t.*,
             (SELECT body FROM messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_message,
             (SELECT created_at FROM messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_at,
             (SELECT COUNT(*) FROM messages m WHERE m.thread_id = t.id AND m.recipient_id = :unread_uid AND m.is_read = 0) AS unread,
             (SELECT GROUP_CONCAT(u.name SEPARATOR ', ') FROM thread_participants tp JOIN users u ON u.id = tp.user_id WHERE tp.thread_id = t.id AND u.id <> :other_uid) AS participants
             FROM message_threads t JOIN thread_participants mine ON mine.thread_id = t.id AND mine.user_id = :my_uid
             ORDER BY t.updated_at DESC",
            ['unread_uid' => $userId, 'other_uid' => $userId, 'my_uid' => $userId]
        );
    }

    public static function thread(int $threadId, int $userId): ?array
    {
        return Database::one(
            'SELECT t.* FROM message_threads t JOIN thread_participants p ON p.thread_id = t.id
             WHERE t.id = :id AND p.user_id = :uid',
            ['id' => $threadId, 'uid' => $userId]
        );
    }

    public static function messages(int $threadId): array
    {
        return Database::all(
            'SELECT m.*, u.name AS sender_name, u.avatar AS sender_avatar FROM messages m
             JOIN users u ON u.id = m.sender_id WHERE m.thread_id = :id ORDER BY m.id ASC',
            ['id' => $threadId]
        );
    }

    public static function startThread(int $from, int $to, string $subject, string $body): int
    {
        Database::begin();
        try {
            $existing = Database::one(
                'SELECT t.id FROM message_threads t
                 JOIN thread_participants a ON a.thread_id = t.id AND a.user_id = :from
                 JOIN thread_participants b ON b.thread_id = t.id AND b.user_id = :to
                 WHERE t.subject = :subject LIMIT 1',
                ['from' => $from, 'to' => $to, 'subject' => $subject]
            );
            if ($existing) {
                $threadId = (int) $existing['id'];
            } else {
                $threadId = Database::insert('message_threads', ['subject' => $subject, 'created_at' => date('Y-m-d H:i:s')]);
                Database::insert('thread_participants', ['thread_id' => $threadId, 'user_id' => $from]);
                Database::insert('thread_participants', ['thread_id' => $threadId, 'user_id' => $to]);
            }
            self::send($threadId, $from, $to, $body);
            Database::commit();
            return $threadId;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function send(int $threadId, int $from, int $to, string $body): int
    {
        $id = Database::insert('messages', [
            'thread_id' => $threadId, 'sender_id' => $from, 'recipient_id' => $to,
            'body' => $body, 'created_at' => date('Y-m-d H:i:s'),
        ]);
        Database::query('UPDATE message_threads SET updated_at = NOW() WHERE id = :id', ['id' => $threadId]);
        $sender = Database::one('SELECT name FROM users WHERE id = :id', ['id' => $from]);
        Notifications::create($to, 'message', 'New message from ' . ($sender['name'] ?? 'a member'),
            str_limit($body, 140), '/messages?thread=' . $threadId);
        return $id;
    }

    public static function markRead(int $threadId, int $userId): void
    {
        Database::update('messages', ['is_read' => 1], 'thread_id = :t AND recipient_id = :u', ['t' => $threadId, 'u' => $userId]);
    }
}
