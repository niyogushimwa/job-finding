<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Notifications;
use App\Core\Paginator;

final class Application
{
    public static function apply(int $jobId, int $userId, string $coverLetter, ?int $resumeId, int $score): int
    {
        $job = Database::one('SELECT id, title, employer_id FROM jobs WHERE id = :id', ['id' => $jobId]);
        if (!$job) {
            throw new \RuntimeException('This opportunity is no longer available.');
        }
        Database::begin();
        try {
            $id = Database::insert('applications', [
                'job_id' => $jobId,
                'user_id' => $userId,
                'cover_letter' => $coverLetter,
                'resume_id' => $resumeId,
                'match_score' => $score,
                'status' => 'submitted',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Database::query('UPDATE jobs SET applications_count = applications_count + 1 WHERE id = :id', ['id' => $jobId]);
            Database::insert('application_history', [
                'application_id' => $id, 'to_status' => 'submitted', 'changed_by' => $userId,
                'note' => 'Application submitted', 'created_at' => date('Y-m-d H:i:s'),
            ]);
            Notifications::create((int) $job['employer_id'], 'application', 'New application received',
                'A candidate applied for "' . $job['title'] . '".', '/employer/applicants?job_id=' . $jobId);
            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function updateStatus(int $applicationId, string $status, int $changedBy, string $note = ''): void
    {
        $allowed = array_keys(config('application_statuses'));
        if (!in_array($status, $allowed, true)) {
            throw new \RuntimeException('Invalid application status.');
        }
        $app = Database::one('SELECT a.*, j.title, e.user_id AS employer_user_id FROM applications a JOIN jobs j ON j.id = a.job_id LEFT JOIN employers e ON e.id = j.employer_id WHERE a.id = :id', ['id' => $applicationId]);
        if (!$app) {
            throw new \RuntimeException('Application not found.');
        }
        Database::begin();
        try {
            Database::update('applications', ['status' => $status], 'id = :id', ['id' => $applicationId]);
            Database::insert('application_history', [
                'application_id' => $applicationId,
                'from_status' => $app['status'],
                'to_status' => $status,
                'changed_by' => $changedBy,
                'note' => $note ?: ('Status changed to ' . config('application_statuses.' . $status)),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Notifications::create((int) $app['user_id'], 'application', 'Application update: ' . config('application_statuses.' . $status),
                'Your application for "' . $app['title'] . '" is now ' . config('application_statuses.' . $status) . '.', '/applications');
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function history(int $applicationId): array
    {
        return Database::all('SELECT h.*, u.name AS changed_by_name FROM application_history h LEFT JOIN users u ON u.id = h.changed_by WHERE h.application_id = :id ORDER BY h.created_at ASC', ['id' => $applicationId]);
    }

    public static function forSeeker(int $userId, string $status = ''): array
    {
        $where = 'a.user_id = :id';
        $params = ['id' => $userId];
        if ($status !== '') {
            $where .= ' AND a.status = :status';
            $params['status'] = $status;
        }
        return Database::all(
            "SELECT a.*, j.title, j.slug, j.location, j.employment_type, j.deadline, j.company_id,
             c.name AS company_name, c.logo AS company_logo
             FROM applications a JOIN jobs j ON j.id = a.job_id
             LEFT JOIN companies c ON c.id = j.company_id
             WHERE $where ORDER BY a.created_at DESC",
            $params
        );
    }

    public static function forEmployer(int $employerId, array $filters = [], ?Paginator $paginator = null): array
    {
        $where = ['e.user_id = :eid'];
        $params = ['eid' => $employerId];
        if (!empty($filters['job_id'])) {
            $where[] = 'a.job_id = :job';
            $params['job'] = (int) $filters['job_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'a.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['skill'])) {
            $where[] = 'EXISTS (SELECT 1 FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = a.user_id AND s.name LIKE :skill)';
            $params['skill'] = '%' . $filters['skill'] . '%';
        }
        if (!empty($filters['education'])) {
            $where[] = 'js.education_level LIKE :education';
            $params['education'] = '%' . $filters['education'] . '%';
        }
        $sqlWhere = implode(' AND ', $where);
        $sql = "SELECT a.*, j.title AS job_title, j.slug AS job_slug, u.name AS applicant_name, u.email AS applicant_email,
               js.headline, js.experience_years, js.education_level, js.location,
               (SELECT GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = a.user_id) AS applicant_skills
               FROM applications a JOIN jobs j ON j.id = a.job_id JOIN employers e ON e.id = j.employer_id
               JOIN users u ON u.id = a.user_id LEFT JOIN job_seekers js ON js.user_id = u.id
               WHERE $sqlWhere ORDER BY a.created_at DESC";
        if ($paginator) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params['limit'] = $paginator->perPage;
            $params['offset'] = $paginator->offset;
        }
        return Database::all($sql, $params);
    }
}
