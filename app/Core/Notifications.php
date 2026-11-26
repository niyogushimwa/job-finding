<?php
declare(strict_types=1);

namespace App\Core;

final class Notifications
{
    public static function create(int $userId, string $type, string $title, string $body, string $link = ''): int
    {
        $id = Database::insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $prefs = Database::one('SELECT email_enabled FROM notification_preferences WHERE user_id = :id', ['id' => $userId]);
        $user = Database::one('SELECT email, name FROM users WHERE id = :id', ['id' => $userId]);
        if ($user && ($prefs['email_enabled'] ?? 1)) {
            Mailer::send($user['email'], $title, $body . ($link ? "\n\nOpen: " . url($link) : ''), $user['name']);
        }
        return $id;
    }

    /** Notify all saved-job owners when a deadline is within 3 days. */
    public static function sendDeadlineReminders(): int
    {
        $rows = Database::all(
            "SELECT sj.user_id, j.id, j.title FROM saved_jobs sj
             JOIN jobs j ON j.id = sj.job_id
             WHERE j.deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
               AND j.status = 'published'
               AND NOT EXISTS (SELECT 1 FROM notifications n WHERE n.user_id = sj.user_id AND n.type = 'deadline' AND n.link = CONCAT('/jobs/', j.id) AND DATE(n.created_at) = CURDATE())"
        );
        foreach ($rows as $row) {
            self::create((int) $row['user_id'], 'deadline', 'Application deadline approaching',
                'The deadline for "' . $row['title'] . '" is close. Apply now if you are interested.', '/jobs/' . $row['id']);
        }
        return count($rows);
    }

    /** Notify seekers whose profile matches a newly published job. */
    public static function notifyMatchingSeekers(int $jobId): void
    {
        $job = Database::one('SELECT id, title, location, employment_type FROM jobs WHERE id = :id', ['id' => $jobId]);
        if (!$job) {
            return;
        }
        $seekers = Database::all(
            "SELECT u.id FROM users u JOIN job_seekers js ON js.user_id = u.id
             WHERE u.role = 'seeker' AND u.status = 'active'
               AND (js.preferred_location = :loc OR js.preferred_location = '' OR :loc = '')
             LIMIT 100",
            ['loc' => $job['location']]
        );
        foreach ($seekers as $seeker) {
            self::create((int) $seeker['id'], 'new_match', 'A new opportunity matches your profile',
                '"' . $job['title'] . '" was just published in ' . $job['location'] . '.', '/jobs/' . $job['id']);
        }
    }
}
