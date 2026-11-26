<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Job
{
    public const SELECT = "j.*, c.name AS company_name, c.slug AS company_slug, c.logo AS company_logo,
        c.verification_status AS company_verification, cat.name AS category_name, ind.name AS industry_name";

    public const FROM = "FROM jobs j
        LEFT JOIN companies c ON c.id = j.company_id
        LEFT JOIN job_categories cat ON cat.id = j.category_id
        LEFT JOIN industries ind ON ind.id = j.industry_id";

    public static function findFull(int $id): ?array
    {
        return Database::one('SELECT ' . self::SELECT . ' ' . self::FROM . ' WHERE j.id = :id AND j.deleted_at IS NULL', ['id' => $id]);
    }

    public static function search(array $filters, Paginator $paginator): array
    {
        $where = ["j.status = 'published'", '(j.deadline IS NULL OR j.deadline >= CURDATE())', 'j.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(j.title LIKE :q OR j.description LIKE :q OR j.requirements LIKE :q OR c.name LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }
        if (!empty($filters['location'])) {
            $where[] = '(j.location LIKE :location OR j.work_mode = :remote)';
            $params['location'] = '%' . trim((string) $filters['location']) . '%';
            $params['remote'] = 'remote';
        }
        if (!empty($filters['country'])) {
            $where[] = 'j.country_id = :country';
            $params['country'] = (int) $filters['country'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'j.category_id = :category';
            $params['category'] = (int) $filters['category'];
        }
        if (!empty($filters['industry'])) {
            $where[] = 'j.industry_id = :industry';
            $params['industry'] = (int) $filters['industry'];
        }
        if (!empty($filters['company'])) {
            $where[] = 'j.company_id = :company';
            $params['company'] = (int) $filters['company'];
        }
        if (!empty($filters['employment_type'])) {
            $where[] = 'j.employment_type = :employment_type';
            $params['employment_type'] = $filters['employment_type'];
        }
        if (!empty($filters['work_mode'])) {
            $where[] = 'j.work_mode = :work_mode';
            $params['work_mode'] = $filters['work_mode'];
        }
        if (!empty($filters['experience_level'])) {
            $where[] = 'j.experience_level = :experience_level';
            $params['experience_level'] = $filters['experience_level'];
        }
        if (!empty($filters['education_level'])) {
            $where[] = 'j.education_level LIKE :education_level';
            $params['education_level'] = '%' . $filters['education_level'] . '%';
        }
        if (!empty($filters['salary_min'])) {
            $where[] = 'j.salary_max >= :salary_min';
            $params['salary_min'] = (float) $filters['salary_min'];
        }
        if (!empty($filters['salary_max'])) {
            $where[] = 'j.salary_min <= :salary_max';
            $params['salary_max'] = (float) $filters['salary_max'];
        }
        if (!empty($filters['posted_within'])) {
            $where[] = 'j.published_at >= DATE_SUB(NOW(), INTERVAL :days DAY)';
            $params['days'] = (int) $filters['posted_within'];
        }
        if (!empty($filters['deadline_before'])) {
            $where[] = 'j.deadline <= :deadline_before';
            $params['deadline_before'] = $filters['deadline_before'];
        }
        if (!empty($filters['featured'])) {
            $where[] = 'j.featured = 1';
        }

        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM jobs j LEFT JOIN companies c ON c.id = j.company_id LEFT JOIN job_categories cat ON cat.id = j.category_id LEFT JOIN industries ind ON ind.id = j.industry_id WHERE $sqlWhere", $params);

        $sort = match ($filters['sort'] ?? 'latest') {
            'oldest' => 'j.published_at ASC',
            'salary' => 'j.salary_max DESC',
            'popular' => 'j.views_count DESC',
            'deadline' => 'j.deadline ASC',
            default => 'j.featured DESC, j.published_at DESC',
        };

        $rows = Database::all(
            'SELECT ' . self::SELECT . ' ' . self::FROM . " WHERE $sqlWhere ORDER BY $sort LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $paginator->perPage, 'offset' => $paginator->offset])
        );
        return ['rows' => $rows, 'total' => $total];
    }

    public static function skills(int $jobId): array
    {
        return Database::all(
            'SELECT s.*, js.is_required, js.weight FROM job_skills js JOIN skills s ON s.id = js.skill_id WHERE js.job_id = :id ORDER BY js.weight DESC, s.name',
            ['id' => $jobId]
        );
    }

    /** Deterministic compatibility score computed from real profile and job data. */
    public static function matchScore(array $job, int $userId): array
    {
        $seeker = Database::one('SELECT * FROM job_seekers WHERE user_id = :id', ['id' => $userId]) ?? [];
        $userSkills = Database::all(
            'SELECT s.id, s.name, s.category, us.level, us.years FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = :id',
            ['id' => $userId]
        );
        $required = self::skills((int) $job['id']);
        $skillWeights = 0;
        $matchedWeights = 0;
        $matched = [];
        $missing = [];
        foreach ($required as $skill) {
            $skillWeights += (int) $skill['weight'];
            foreach ($userSkills as $own) {
                if ((int) $own['id'] === (int) $skill['id']) {
                    $matchedWeights += (int) $skill['weight'];
                    $matched[] = $skill['name'];
                    continue 2;
                }
            }
            $missing[] = $skill['name'] . ((int) $skill['is_required'] === 1 ? ' (required)' : '');
        }
        $skillScore = $skillWeights > 0 ? ($matchedWeights / $skillWeights) * 42 : 24;

        $levels = ['entry' => 0, 'junior' => 1, 'mid' => 3, 'senior' => 5, 'executive' => 8];
        $needed = $levels[$job['experience_level']] ?? 0;
        $years = (float) ($seeker['experience_years'] ?? 0);
        $experienceScore = $needed === 0 ? 14 : min(14, (int) round(($years / $needed) * 14));

        $educationScore = empty($job['education_level']) ? 10 : (
            mb_strtolower((string) ($seeker['education_level'] ?? '')) === mb_strtolower((string) $job['education_level']) ? 10 : 5
        );

        $location = mb_strtolower((string) $job['location']);
        $preferred = mb_strtolower((string) ($seeker['preferred_location'] ?? $seeker['location'] ?? ''));
        $remote = $job['work_mode'] === 'remote' || !empty($seeker['remote_only']);
        $locationScore = $remote || ($preferred !== '' && ($location === $preferred || str_contains($location, $preferred) || str_contains($preferred, $location))) ? 14 : 5;

        $types = array_filter(explode(',', (string) ($seeker['employment_types'] ?? '')));
        $typeScore = (in_array($job['employment_type'], $types, true) || $job['work_mode'] === 'remote') ? 10 : 3;

        $categoryMap = [
            'Software & IT' => ['Programming', 'Databases', 'Web', 'Design', 'Data'],
            'Remote Work' => ['Programming', 'Design', 'Data', 'Soft skills'],
            'Marketing & Sales' => ['Business', 'Design', 'Soft skills'],
            'Finance & Accounting' => ['Finance', 'Analysis'],
            'Education & Training' => ['Education', 'Languages'],
            'Health & Medicine' => ['Health'],
        ];
        $jobCategory = (string) ($job['category_name'] ?? '');
        $userSkillCategories = array_unique(array_column($userSkills, 'category'));
        $categoryScore = 0;
        foreach ($categoryMap[$jobCategory] ?? [] as $allowed) {
            if (in_array($allowed, $userSkillCategories, true)) {
                $categoryScore = 10;
                break;
            }
        }
        if ($categoryScore === 0 && !empty($userSkills)) {
            $categoryScore = 4;
        }

        $score = (int) round($skillScore + $experienceScore + $educationScore + $locationScore + $typeScore + $categoryScore);
        $score = max(0, min(100, $score));
        return [
            'score' => $score,
            'matched' => $matched,
            'missing' => $missing,
            'breakdown' => [
                'Skills' => (int) round($skillScore),
                'Experience' => $experienceScore,
                'Education' => $educationScore,
                'Location' => $locationScore,
                'Employment type' => $typeScore,
                'Category' => $categoryScore,
            ],
        ];
    }

    public static function recommendations(int $userId, int $limit = 12): array
    {
        $jobs = Database::all(
            'SELECT ' . self::SELECT . ' ' . self::FROM . " WHERE j.status = 'published' AND (j.deadline IS NULL OR j.deadline >= CURDATE()) AND j.deleted_at IS NULL ORDER BY j.published_at DESC LIMIT 150"
        );
        $results = [];
        foreach ($jobs as $job) {
            $match = self::matchScore($job, $userId);
            $job['match_score'] = $match['score'];
            $job['match'] = $match;
            $results[] = $job;
        }
        usort($results, static fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        return array_slice($results, 0, $limit);
    }

    public static function recordView(int $jobId, ?int $userId, string $ip): void
    {
        Database::query('UPDATE jobs SET views_count = views_count + 1 WHERE id = :id', ['id' => $jobId]);
        Database::insert('job_views', ['job_id' => $jobId, 'user_id' => $userId, 'ip' => $ip, 'viewed_at' => date('Y-m-d H:i:s')]);
    }

    public static function isSaved(int $jobId, ?int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        return (bool) Database::scalar('SELECT COUNT(*) FROM saved_jobs WHERE job_id = :j AND user_id = :u', ['j' => $jobId, 'u' => $userId]);
    }

    public static function hasApplied(int $jobId, ?int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        return (bool) Database::scalar('SELECT COUNT(*) FROM applications WHERE job_id = :j AND user_id = :u', ['j' => $jobId, 'u' => $userId]);
    }
}
