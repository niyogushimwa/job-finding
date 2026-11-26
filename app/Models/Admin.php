<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Admin
{
    public static function platformStats(): array
    {
        $users = (int) Database::scalar('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL');
        $active = (int) Database::scalar("SELECT COUNT(*) FROM users WHERE status = 'active' AND last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return [
            'users' => $users,
            'active_users' => $active,
            'employers' => (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'employer'"),
            'companies' => (int) Database::scalar('SELECT COUNT(*) FROM companies WHERE deleted_at IS NULL'),
            'published_jobs' => (int) Database::scalar("SELECT COUNT(*) FROM jobs WHERE status = 'published'"),
            'pending_jobs' => (int) Database::scalar("SELECT COUNT(*) FROM jobs WHERE status = 'pending_review'"),
            'external_opportunities' => (int) Database::scalar("SELECT COUNT(*) FROM external_opportunities WHERE status = 'published'"),
            'applications' => (int) Database::scalar('SELECT COUNT(*) FROM applications'),
            'reported' => (int) Database::scalar("SELECT COUNT(*) FROM reports WHERE status IN ('open','investigating')"),
            'verified_companies' => (int) Database::scalar("SELECT COUNT(*) FROM companies WHERE verification_status = 'verified'"),
            'community_posts' => (int) Database::scalar("SELECT COUNT(*) FROM community_posts WHERE status = 'published'"),
            'newsletter' => (int) Database::scalar("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'"),
        ];
    }

    public static function popularCategories(int $limit = 6): array
    {
        return Database::all(
            "SELECT cat.name, cat.slug, COUNT(j.id) AS total FROM job_categories cat
             LEFT JOIN jobs j ON j.category_id = cat.id AND j.status = 'published'
             GROUP BY cat.id ORDER BY total DESC, cat.name ASC LIMIT $limit"
        );
    }

    public static function mostViewedJobs(int $limit = 6): array
    {
        return Database::all("SELECT id, title, slug, views_count, applications_count FROM jobs WHERE status = 'published' ORDER BY views_count DESC LIMIT $limit");
    }

    public static function activeCompanies(int $limit = 6): array
    {
        return Database::all(
            "SELECT c.id, c.name, c.slug, c.logo, c.location, c.verification_status, COUNT(j.id) AS jobs
             FROM companies c LEFT JOIN jobs j ON j.company_id = c.id AND j.status = 'published'
             WHERE c.deleted_at IS NULL GROUP BY c.id ORDER BY jobs DESC, c.name LIMIT $limit"
        );
    }

    public static function applicationTrend(int $days = 14): array
    {
        return Database::all(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total FROM applications
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY DATE(created_at) ORDER BY day",
            ['days' => $days]
        );
    }

    public static function employerStats(int $userId): array
    {
        $employer = Database::one('SELECT id FROM employers WHERE user_id = :id', ['id' => $userId]);
        if (!$employer) {
            return array_fill_keys(['jobs', 'published', 'applications', 'shortlisted', 'interviews', 'views'], 0);
        }
        $eid = (int) $employer['id'];
        return [
            'jobs' => (int) Database::scalar('SELECT COUNT(*) FROM jobs WHERE employer_id = :id AND deleted_at IS NULL', ['id' => $eid]),
            'published' => (int) Database::scalar("SELECT COUNT(*) FROM jobs WHERE employer_id = :id AND status = 'published'", ['id' => $eid]),
            'applications' => (int) Database::scalar('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = :id', ['id' => $eid]),
            'shortlisted' => (int) Database::scalar("SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = :id AND a.status IN ('shortlisted','interview_scheduled','interview_completed','selected')", ['id' => $eid]),
            'interviews' => (int) Database::scalar('SELECT COUNT(*) FROM interviews WHERE employer_id = :uid', ['uid' => $userId]),
            'views' => (int) Database::scalar('SELECT COALESCE(SUM(views_count),0) FROM jobs WHERE employer_id = :id', ['id' => $eid]),
        ];
    }

    public static function seekerStats(int $userId): array
    {
        $apps = Database::all('SELECT status, COUNT(*) AS total FROM applications WHERE user_id = :id GROUP BY status', ['id' => $userId]);
        $byStatus = [];
        foreach ($apps as $row) {
            $byStatus[$row['status']] = (int) $row['total'];
        }
        return [
            'applications' => array_sum($byStatus),
            'by_status' => $byStatus,
            'saved' => (int) Database::scalar('SELECT COUNT(*) FROM saved_jobs WHERE user_id = :id', ['id' => $userId]),
            'skills' => (int) Database::scalar('SELECT COUNT(*) FROM user_skills WHERE user_id = :id', ['id' => $userId]),
            'completion' => User::completion($userId),
        ];
    }
}
