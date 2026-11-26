<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL', ['email' => $email]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($exceptId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        return (bool) Database::scalar($sql, $params);
    }

    public static function register(array $data): int
    {
        Database::begin();
        try {
            $userId = Database::insert('users', [
                'name' => $data['name'],
                'email' => mb_strtolower(trim($data['email'])),
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 11]),
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
                'verification_token' => bin2hex(random_bytes(32)),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Database::insert('notification_preferences', ['user_id' => $userId]);
            if ($data['role'] === 'seeker') {
                Database::insert('job_seekers', [
                    'user_id' => $userId,
                    'location' => $data['location'] ?? null,
                    'country_id' => $data['country_id'] ?? 1,
                ]);
            } elseif ($data['role'] === 'employer') {
                Database::insert('companies', [
                    'name' => $data['company_name'] ?? ($data['name'] . ' Company'),
                    'slug' => slugify(($data['company_name'] ?? $data['name']) . '-' . $userId),
                    'description' => $data['company_description'] ?? null,
                    'location' => $data['location'] ?? 'Kigali',
                    'country_id' => $data['country_id'] ?? 1,
                    'email' => $data['email'],
                    'verification_status' => 'unverified',
                ]);
                $companyId = (int) Database::connection()->lastInsertId();
                Database::insert('employers', ['user_id' => $userId, 'company_id' => $companyId]);
            } elseif ($data['role'] === 'freelancer') {
                Database::insert('freelancer_profiles', [
                    'user_id' => $userId,
                    'title' => $data['headline'] ?? 'Freelance Professional',
                    'bio' => $data['bio'] ?? null,
                    'currency' => 'RWF',
                ]);
            }
            Database::commit();
            self::logAudit($userId, 'register', 'New ' . $data['role'] . ' account registered');
            return $userId;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function logAudit(int $userId, string $action, string $description, ?string $ip = null): void
    {
        Database::insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function profile(int $userId): array
    {
        $user = self::find($userId) ?? [];
        $seeker = Database::one('SELECT * FROM job_seekers WHERE user_id = :id', ['id' => $userId]) ?? [];
        $employer = Database::one('SELECT * FROM employers WHERE user_id = :id', ['id' => $userId]) ?? [];
        $company = null;
        if (!empty($employer['company_id'])) {
            $company = Database::one('SELECT * FROM companies WHERE id = :id', ['id' => $employer['company_id']]);
        }
        $freelancer = Database::one('SELECT * FROM freelancer_profiles WHERE user_id = :id', ['id' => $userId]) ?? [];
        return [
            'user' => $user,
            'seeker' => $seeker,
            'employer' => $employer,
            'company' => $company,
            'freelancer' => $freelancer,
        ];
    }

    public static function completion(int $userId): int
    {
        $user = self::find($userId) ?? [];
        $seeker = Database::one('SELECT * FROM job_seekers WHERE user_id = :id', ['id' => $userId]) ?? [];
        $checks = [
            !empty($user['name']), !empty($user['phone']), !empty($user['avatar']),
            !empty($seeker['headline']), !empty($seeker['summary']), !empty($seeker['location']),
            !empty($seeker['experience_years']), !empty($seeker['education_level']),
            Database::count('user_skills', 'user_id = :id', ['id' => $userId]) > 0,
            Database::count('education', 'user_id = :id', ['id' => $userId]) > 0,
            Database::count('experience', 'user_id = :id', ['id' => $userId]) > 0,
            Database::count('portfolios', 'user_id = :id', ['id' => $userId]) > 0,
        ];
        $percent = (int) round(count(array_filter($checks)) / count($checks) * 100);
        Database::update('job_seekers', ['profile_completion' => $percent], 'user_id = :id', ['id' => $userId]);
        return $percent;
    }
}
