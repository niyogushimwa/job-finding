<?php
/**
 * Application configuration. Values come from environment variables (.env)
 * so no secret is ever committed to the repository.
 */
return [
    'app' => [
        'name' => env('APP_NAME', 'JobLink Rwanda'),
        'tagline' => 'Your Skills Deserve an Opportunity.',
        'url' => rtrim(env('APP_URL', 'http://localhost/job-finding'), '/'),
        'env' => env('APP_ENV', 'production'),
        'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'timezone' => env('APP_TIMEZONE', 'Africa/Kigali'),
        'locale' => env('APP_DEFAULT_LANG', 'en'),
        'supported_locales' => ['en' => 'English', 'rw' => 'Kinyarwanda', 'fr' => 'Français'],
        'version' => '1.0.0',
    ],
    'database' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'name' => env('DB_NAME', 'job_finding'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],
    'mail' => [
        'driver' => env('MAIL_DRIVER', 'log'),
        'from' => env('MAIL_FROM', 'hello@joblinkrwanda.rw'),
        'from_name' => env('MAIL_FROM_NAME', 'JobLink Rwanda'),
        'smtp_host' => env('SMTP_HOST', ''),
        'smtp_port' => (int) env('SMTP_PORT', 587),
        'smtp_user' => env('SMTP_USER', ''),
        'smtp_pass' => env('SMTP_PASS', ''),
        'smtp_encryption' => env('SMTP_ENCRYPTION', 'tls'),
    ],
    'upload' => [
        'max_size' => (int) env('UPLOAD_MAX_SIZE', 5242880),
        'dir' => dirname(__DIR__) . '/uploads',
        'allowed_documents' => ['pdf'],
        'allowed_images' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'security' => [
        'session_name' => env('SESSION_NAME', 'joblink_session'),
        'login_max_attempts' => 5,
        'login_lock_minutes' => 15,
        'remember_days' => 30,
    ],
    'roles' => [
        'seeker' => 'Job Seeker',
        'employer' => 'Employer',
        'freelancer' => 'Freelancer',
        'admin' => 'Administrator',
    ],
    'application_statuses' => [
        'submitted' => 'Submitted',
        'under_review' => 'Under review',
        'shortlisted' => 'Shortlisted',
        'interview_scheduled' => 'Interview scheduled',
        'interview_completed' => 'Interview completed',
        'selected' => 'Selected',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
    ],
];
