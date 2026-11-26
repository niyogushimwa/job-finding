<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Admin;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;

final class SeekerController extends Controller
{
    private const SIDEBAR_LINKS = [
        ['Dashboard', 'dashboard', 'chart'],
        ['Find jobs', 'jobs', 'search'],
        ['Recommended for you', 'recommended-jobs', 'star'],
        ['My applications', 'applications', 'briefcase'],
        ['Saved jobs', 'saved-jobs', 'bookmark'],
        ['Job alerts', 'job-alerts', 'bell'],
        ['My CV', 'my-cv', 'edit'],
        ['My skills', 'my-skills', 'award'],
        ['My portfolio', 'my-portfolio', 'rocket'],
        ['Public profile', 'profile', 'user'],
    ];

    private const EMPLOYMENT_TYPES = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'internship' => 'Internship',
        'contract' => 'Contract',
        'remote' => 'Remote',
        'freelance' => 'Freelance',
        'temporary' => 'Temporary',
    ];

    private const WITHDRAWABLE = ['submitted', 'under_review', 'shortlisted'];

    /** Only these tables may ever be used for profile entries. */
    private const ENTRY_TABLES = [
        'education' => 'education',
        'experience' => 'experience',
        'certification' => 'certifications',
    ];

    private const ENTRY_RULES = [
        'education' => [
            'school' => 'required|max:180',
            'degree' => 'required|max:120',
            'field_of_study' => 'max:120',
            'start_year' => 'integer',
            'end_year' => 'integer',
            'grade' => 'max:40',
            'description' => 'max:5000',
        ],
        'experience' => [
            'company' => 'required|max:180',
            'title' => 'required|max:150',
            'start_date' => 'date',
            'end_date' => 'date',
            'description' => 'max:5000',
        ],
        'certification' => [
            'name' => 'required|max:180',
            'issuer' => 'max:180',
            'issued_year' => 'integer',
            'credential_id' => 'max:120',
            'credential_url' => 'url',
        ],
    ];

    // ------------------------------------------------------------------ Dashboard

    public function dashboard(): void
    {
        $userId = (int) Auth::id();
        $stats = Admin::seekerStats($userId);
        $applications = Application::forSeeker($userId);
        $interviews = Database::all(
            'SELECT i.*, j.title AS job_title, j.slug AS job_slug, c.name AS company_name
             FROM interviews i
             JOIN jobs j ON j.id = i.job_id
             LEFT JOIN companies c ON c.id = j.company_id
             WHERE i.applicant_id = :id AND i.status = "scheduled" AND i.scheduled_at >= NOW()
             ORDER BY i.scheduled_at ASC LIMIT 5',
            ['id' => $userId]
        );
        $notifications = Database::all(
            'SELECT id, type, title, body, link, is_read, created_at FROM notifications WHERE user_id = :id ORDER BY id DESC LIMIT 8',
            ['id' => $userId]
        );
        foreach ($notifications as &$notification) {
            $notification['href'] = $notification['link'] ? url($notification['link']) : url('notifications');
        }
        unset($notification);

        $this->renderDash('seeker.dashboard', [
            'profile' => User::profile($userId),
            'stats' => $stats,
            'completion' => (int) ($stats['completion'] ?? User::completion($userId)),
            'applications' => array_slice($applications, 0, 5),
            'savedCount' => (int) ($stats['saved'] ?? 0),
            'recommended' => Job::recommendations($userId, 4),
            'interviews' => $interviews,
            'notifications' => $notifications,
        ], 'My Dashboard', 'dashboard');
    }

    // -------------------------------------------------------------------- Profile

    public function profile(): void
    {
        $userId = (int) Auth::id();
        $this->renderDash('seeker.profile', array_merge([
            'profile' => User::profile($userId),
            'completion' => User::completion($userId),
            'publicUrl' => url('u/' . $userId),
        ], $this->profileData($userId)), 'My Profile', 'profile');
    }

    public function editProfile(): void
    {
        $userId = (int) Auth::id();
        $this->renderDash('seeker.edit', array_merge($this->profileData($userId), [
            'profile' => User::profile($userId),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
            'employmentTypes' => self::EMPLOYMENT_TYPES,
        ]), 'Edit Profile', 'profile');
    }

    public function updateProfile(): void
    {
        $userId = (int) Auth::id();
        $errors = $this->validate([
            'name' => 'required|min:2|max:150',
            'phone' => 'max:40',
            'headline' => 'max:200',
            'summary' => 'max:5000',
            'gender' => 'in:male,female,other,prefer_not_to_say',
            'date_of_birth' => 'date',
            'location' => 'max:150',
            'preferred_location' => 'max:150',
            'expected_salary_min' => 'numeric',
            'expected_salary_max' => 'numeric',
            'salary_period' => 'in:hourly,monthly,yearly',
            'experience_years' => 'integer',
            'education_level' => 'max:80',
            'availability' => 'max:80',
            'linkedin_url' => 'url',
            'github_url' => 'url',
            'website_url' => 'url',
        ]);

        $salaryMinRaw = $this->inputString('expected_salary_min');
        $salaryMaxRaw = $this->inputString('expected_salary_max');
        $yearsRaw = $this->inputString('experience_years');
        if ($salaryMinRaw !== '' && (float) $salaryMinRaw < 0) {
            $errors['expected_salary_min'] = 'Expected minimum salary cannot be negative.';
        }
        if ($salaryMaxRaw !== '' && (float) $salaryMaxRaw < 0) {
            $errors['expected_salary_max'] = 'Expected maximum salary cannot be negative.';
        }
        if ($salaryMinRaw !== '' && $salaryMaxRaw !== '' && (float) $salaryMaxRaw < (float) $salaryMinRaw) {
            $errors['expected_salary_max'] = 'Maximum salary cannot be lower than minimum salary.';
        }
        if ($yearsRaw !== '' && ((int) $yearsRaw < 0 || (int) $yearsRaw > 60)) {
            $errors['experience_years'] = 'Enter experience between 0 and 60 years.';
        }
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('profile/edit');
        }

        $dobRaw = $this->inputString('date_of_birth');
        $dob = ($dobRaw !== '' && strtotime($dobRaw)) ? date('Y-m-d', (int) strtotime($dobRaw)) : null;
        $gender = $this->inputString('gender');
        if (!in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true)) {
            $gender = null;
        }
        $period = $this->inputString('salary_period', 'monthly');
        if (!in_array($period, ['hourly', 'monthly', 'yearly'], true)) {
            $period = 'monthly';
        }
        $countryRaw = $this->inputString('country_id');
        $countryId = $countryRaw !== '' && ctype_digit($countryRaw) ? (int) $countryRaw : null;
        if ($countryId !== null && !Database::scalar('SELECT COUNT(*) FROM countries WHERE id = :id AND active = 1', ['id' => $countryId])) {
            $countryId = null;
        }

        $types = [];
        $submittedTypes = $this->request->post('employment_types', []);
        if (is_array($submittedTypes)) {
            foreach ($submittedTypes as $type) {
                if (is_scalar($type) && isset(self::EMPLOYMENT_TYPES[(string) $type])) {
                    $types[(string) $type] = (string) $type;
                }
            }
        }

        $phone = $this->inputString('phone');
        Database::update('users', [
            'name' => $this->inputString('name'),
            'phone' => $phone !== '' ? $phone : null,
        ], 'id = :id', ['id' => $userId]);

        $seekerData = [
            'headline' => $this->inputString('headline') ?: null,
            'summary' => $this->inputString('summary') ?: null,
            'gender' => $gender,
            'date_of_birth' => $dob,
            'location' => $this->inputString('location') ?: null,
            'preferred_location' => $this->inputString('preferred_location') ?: null,
            'country_id' => $countryId,
            'expected_salary_min' => $salaryMinRaw !== '' ? round((float) $salaryMinRaw, 2) : null,
            'expected_salary_max' => $salaryMaxRaw !== '' ? round((float) $salaryMaxRaw, 2) : null,
            'salary_period' => $period,
            'experience_years' => $yearsRaw !== '' ? (int) $yearsRaw : 0,
            'education_level' => $this->inputString('education_level') ?: null,
            'employment_types' => $types ? implode(',', $types) : null,
            'remote_only' => !empty($this->request->post('remote_only')) ? 1 : 0,
            'availability' => $this->inputString('availability') ?: null,
            'linkedin_url' => $this->inputString('linkedin_url') ?: null,
            'github_url' => $this->inputString('github_url') ?: null,
            'website_url' => $this->inputString('website_url') ?: null,
            'open_to_work' => !empty($this->request->post('open_to_work')) ? 1 : 0,
        ];
        if (Database::scalar('SELECT COUNT(*) FROM job_seekers WHERE user_id = :id', ['id' => $userId])) {
            Database::update('job_seekers', $seekerData, 'user_id = :uid', ['uid' => $userId]);
        } else {
            Database::insert('job_seekers', array_merge(['user_id' => $userId], $seekerData));
        }

        User::completion($userId);
        $this->audit('profile_update', 'Updated personal and professional profile');
        clear_old();
        flash('success', 'Your profile has been updated.');
        redirect('profile');
    }

    public function uploadAvatar(): void
    {
        $userId = (int) Auth::id();
        try {
            $path = Upload::handle(
                $this->request->file('avatar'),
                'avatars',
                (array) config('upload.allowed_images', ['jpg', 'jpeg', 'png', 'webp'])
            );
            if ($path === null) {
                flash('error', 'Please choose an image to upload.');
                redirect('profile/edit');
            }
            $old = Database::scalar('SELECT avatar FROM users WHERE id = :id', ['id' => $userId]);
            Database::update('users', ['avatar' => $path], 'id = :id', ['id' => $userId]);
            $this->deleteUploadedFile(is_string($old) ? $old : null, 'avatars');
            $this->audit('avatar_upload', 'Uploaded a new profile photo');
            flash('success', 'Your profile photo has been updated.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('profile/edit');
    }

    // ------------------------------------------------------------------------- CV

    public function cv(): void
    {
        $userId = (int) Auth::id();
        $document = Database::one('SELECT * FROM cv_documents WHERE user_id = :id', ['id' => $userId]);
        $profile = User::profile($userId);
        $sections = $this->profileData($userId);
        $stored = [];
        if ($document && is_string($document['data'])) {
            $decoded = json_decode($document['data'], true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }
        $cv = $stored ? $this->normalizeCvData($stored, $profile, $sections) : $this->defaultCvData($profile, $sections);
        $template = $document['template'] ?? 'modern';
        if (!in_array($template, ['classic', 'modern', 'creative'], true)) {
            $template = 'modern';
        }
        $cv['template'] = $template;

        $resumes = Database::all('SELECT * FROM resumes WHERE user_id = :id ORDER BY is_default DESC, id DESC', ['id' => $userId]);
        $this->renderDash('seeker.cv', [
            'cv' => $cv,
            'cvTitle' => $document['title'] ?? 'My CV',
            'template' => $template,
            'resumes' => $resumes,
        ], 'My CV', 'my-cv');
    }

    public function saveCv(): void
    {
        $userId = (int) Auth::id();
        $decoded = json_decode($this->inputString('data'), true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            flash('error', 'The CV data could not be saved. Please try again.');
            redirect('my-cv');
        }
        $profile = User::profile($userId);
        $cv = $this->normalizeCvData($decoded, $profile, $this->profileData($userId));
        $template = $this->inputString('template', 'modern');
        if (!in_array($template, ['classic', 'modern', 'creative'], true)) {
            $template = 'modern';
        }
        $title = mb_substr($this->inputString('title', 'My CV'), 0, 150) ?: 'My CV';
        $json = json_encode($cv, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            flash('error', 'The CV data could not be encoded.');
            redirect('my-cv');
        }

        $existing = Database::one('SELECT id FROM cv_documents WHERE user_id = :id', ['id' => $userId]);
        if ($existing) {
            Database::update('cv_documents', [
                'title' => $title,
                'template' => $template,
                'data' => $json,
            ], 'user_id = :uid', ['uid' => $userId]);
        } else {
            Database::insert('cv_documents', [
                'user_id' => $userId,
                'title' => $title,
                'template' => $template,
                'data' => $json,
            ]);
        }
        $this->audit('cv_save', 'Saved the online CV (' . $template . ' template)');
        flash('success', 'Your CV has been saved. You can download the print-ready version any time.');
        redirect('my-cv');
    }

    public function downloadCv(): void
    {
        $userId = (int) Auth::id();
        $document = Database::one('SELECT * FROM cv_documents WHERE user_id = :id', ['id' => $userId]);
        $profile = User::profile($userId);
        $sections = $this->profileData($userId);
        $stored = ($document && is_string($document['data'])) ? json_decode($document['data'], true) : [];
        $cv = is_array($stored) && $stored
            ? $this->normalizeCvData($stored, $profile, $sections)
            : $this->defaultCvData($profile, $sections);
        $template = $document['template'] ?? 'modern';
        if (!in_array($template, ['classic', 'modern', 'creative'], true)) {
            $template = 'modern';
        }
        $cv['template'] = $template;

        $name = $this->cvText($cv['contact']['name'] ?? ($profile['user']['name'] ?? ''), 150);
        $fileSafe = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?: 'cv');
        $filename = 'cv-' . trim($fileSafe, '-') . '.html';

        ob_start();
        include BASE_PATH . '/app/Views/partials/cv_paper.php';
        $paper = (string) ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . e('CV - ' . $name) . '</title>'
            . '<style>body{margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif}'
            . '.cv-paper{max-width:820px;margin:24px auto;background:#fff;color:#1f2937;padding:40px;box-shadow:0 8px 28px rgba(0,0,0,.12)}'
            . '.cv-paper h1{font-size:2rem;color:#0b1f3a;margin:0}.cv-paper h2{font-size:1.1rem;border-bottom:2px solid #e6b34a;padding-bottom:6px;margin-top:28px}'
            . '.cv-template-classic{font-family:Georgia,serif}.cv-template-creative{border-top:8px solid #e6b34a}'
            . '.cv-muted{color:#6b7280}.cv-grid{display:flex;gap:18px;flex-wrap:wrap;font-size:.9rem}'
            . '.cv-item{margin-bottom:16px}.cv-item strong{display:block}'
            . '@media print{body{background:#fff}.cv-paper{box-shadow:none;margin:0;max-width:none;padding:0}}</style>'
            . '</head><body>' . $paper . '</body></html>';
        exit;
    }

    public function uploadResume(): void
    {
        $userId = (int) Auth::id();
        $file = $this->request->file('resume');
        try {
            $path = Upload::handle(
                $file,
                'resumes',
                (array) config('upload.allowed_documents', ['pdf']),
                (int) config('upload.max_size')
            );
            if ($path === null) {
                flash('error', 'Please choose a PDF file to upload.');
                redirect('my-cv');
            }
            $isFirst = Database::count('resumes', 'user_id = :id', ['id' => $userId]) === 0;
            $original = is_array($file) ? basename(str_replace('\\', '/', (string) ($file['name'] ?? ''))) : '';
            Database::insert('resumes', [
                'user_id' => $userId,
                'title' => mb_substr($this->inputString('title', 'My CV'), 0, 150) ?: 'My CV',
                'file_path' => $path,
                'original_name' => mb_substr($original, 0, 255) ?: null,
                'is_default' => $isFirst ? 1 : 0,
            ]);
            $this->audit('resume_upload', 'Uploaded a PDF resume');
            flash('success', $isFirst ? 'Your resume has been uploaded and set as default.' : 'Your resume has been uploaded.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('my-cv');
    }

    public function downloadResume(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $resume = Database::one('SELECT * FROM resumes WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        if (!$resume) {
            flash('error', 'That resume could not be found.');
            redirect('my-cv');
        }
        $relative = str_replace('\\', '/', (string) $resume['file_path']);
        $base = realpath(rtrim((string) config('upload.dir'), '/'));
        $path = $base ? realpath($base . '/' . ltrim($relative, '/')) : false;
        if (
            !$base || !$path || !is_file($path)
            || !str_starts_with($relative, 'resumes/')
            || !str_starts_with(str_replace('\\', '/', $path), str_replace('\\', '/', $base) . '/')
            || basename($path) !== basename($relative)
        ) {
            flash('error', 'That resume file is no longer available.');
            redirect('my-cv');
        }
        $downloadName = basename(str_replace('\\', '/', (string) ($resume['original_name'] ?: 'resume.pdf')));
        if (!str_ends_with(strtolower($downloadName), '.pdf')) {
            $downloadName .= '.pdf';
        }
        $downloadName = str_replace('"', '', $downloadName);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $downloadName . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=0');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function defaultResume(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $resume = Database::one('SELECT id FROM resumes WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        if (!$resume) {
            flash('error', 'That resume could not be found.');
            redirect('my-cv');
        }
        Database::begin();
        try {
            Database::query('UPDATE resumes SET is_default = 0 WHERE user_id = :uid', ['uid' => $userId]);
            Database::query('UPDATE resumes SET is_default = 1 WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
            Database::commit();
            flash('success', 'Default resume updated.');
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'The default resume could not be changed.');
        }
        redirect('my-cv');
    }

    // -------------------------------------------------------------- Applications

    public function applications(): void
    {
        $statuses = (array) config('application_statuses', []);
        $status = $this->inputString('status');
        if ($status !== '' && !array_key_exists($status, $statuses)) {
            $status = '';
        }
        $applications = Application::forSeeker((int) Auth::id(), $status);
        $viewId = (int) $this->inputString('view');
        $detail = null;
        $history = [];
        foreach ($applications as $application) {
            if ($viewId > 0 && (int) $application['id'] === $viewId) {
                $detail = $application;
                $history = Application::history($viewId);
                break;
            }
        }
        $this->renderDash('seeker.applications', [
            'applications' => $applications,
            'statuses' => $statuses,
            'activeStatus' => $status,
            'detail' => $detail,
            'history' => $history,
            'withdrawable' => self::WITHDRAWABLE,
        ], 'My Applications', 'applications');
    }

    public function withdraw(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $application = Database::one(
            'SELECT a.id, a.status, j.title FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.id = :id AND a.user_id = :uid',
            ['id' => $id, 'uid' => $userId]
        );
        if (!$application) {
            flash('error', 'That application could not be found.');
            redirect('applications');
        }
        if (!in_array($application['status'], self::WITHDRAWABLE, true)) {
            flash('error', 'This application can no longer be withdrawn. Please contact the employer if you need help.');
            redirect('applications');
        }
        Database::begin();
        try {
            Database::update('applications', ['status' => 'withdrawn'], 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
            Database::insert('application_history', [
                'application_id' => $id,
                'from_status' => $application['status'],
                'to_status' => 'withdrawn',
                'changed_by' => $userId,
                'note' => 'Applicant withdrew the application for "' . $application['title'] . '".',
            ]);
            Database::commit();
            $this->audit('application_withdraw', 'Withdrew application #' . $id);
            flash('success', 'Your application has been withdrawn.');
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'The application could not be withdrawn. Please try again.');
        }
        redirect('applications');
    }

    // --------------------------------------------------------------- Saved/recommended

    public function savedJobs(): void
    {
        $jobs = Database::all(
            'SELECT ' . Job::SELECT . '
             FROM saved_jobs sj
             JOIN jobs j ON j.id = sj.job_id
             LEFT JOIN companies c ON c.id = j.company_id
             LEFT JOIN job_categories cat ON cat.id = j.category_id
             LEFT JOIN industries ind ON ind.id = j.industry_id
             WHERE sj.user_id = :id AND j.deleted_at IS NULL
             ORDER BY sj.created_at DESC',
            ['id' => (int) Auth::id()]
        );
        $this->renderDash('seeker.saved', ['jobs' => $jobs], 'Saved Jobs', 'saved-jobs');
    }

    public function recommended(): void
    {
        $jobs = Job::recommendations((int) Auth::id(), 18);
        $this->renderDash('seeker.recommended', [
            'jobs' => $jobs,
            'showMatch' => true,
        ], 'Recommended for You', 'recommended-jobs');
    }

    // -------------------------------------------------------------------- Alerts

    public function alerts(): void
    {
        $this->renderDash('seeker.alerts', [
            'alerts' => Database::all(
                'SELECT a.*, cat.name AS category_name FROM job_alerts a
                 LEFT JOIN job_categories cat ON cat.id = a.category_id
                 WHERE a.user_id = :id ORDER BY a.id DESC',
                ['id' => (int) Auth::id()]
            ),
            'categories' => Database::all('SELECT id, name FROM job_categories WHERE active = 1 ORDER BY name'),
            'employmentTypes' => self::EMPLOYMENT_TYPES,
        ], 'Job Alerts', 'job-alerts');
    }

    public function createAlert(): void
    {
        $errors = $this->validate([
            'name' => 'required|max:120',
            'keywords' => 'max:255',
            'location' => 'max:150',
            'category_id' => 'integer',
            'employment_type' => 'in:full_time,part_time,internship,contract,remote,freelance,temporary',
            'frequency' => 'required|in:daily,weekly',
        ]);
        $categoryRaw = $this->inputString('category_id');
        $categoryId = $categoryRaw !== '' && ctype_digit($categoryRaw) ? (int) $categoryRaw : null;
        if ($categoryId !== null && !Database::scalar('SELECT COUNT(*) FROM job_categories WHERE id = :id AND active = 1', ['id' => $categoryId])) {
            $errors['category_id'] = 'Choose a valid category.';
        }
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('job-alerts');
        }

        $frequency = $this->inputString('frequency', 'daily');
        $employmentType = $this->inputString('employment_type');
        if (!isset(self::EMPLOYMENT_TYPES[$employmentType])) {
            $employmentType = null;
        }
        $active = array_key_exists('active', $this->request->post) && empty($this->request->post('active')) ? 0 : 1;
        Database::insert('job_alerts', [
            'user_id' => (int) Auth::id(),
            'name' => $this->inputString('name'),
            'keywords' => $this->inputString('keywords') ?: null,
            'location' => $this->inputString('location') ?: null,
            'category_id' => $categoryId,
            'employment_type' => $employmentType,
            'frequency' => in_array($frequency, ['daily', 'weekly'], true) ? $frequency : 'daily',
            'active' => $active,
        ]);
        clear_old();
        flash('success', 'Job alert created. We will keep an eye out for matching roles.');
        redirect('job-alerts');
    }

    public function deleteAlert(): void
    {
        $id = (int) $this->request->route('id');
        $alert = Database::one('SELECT id FROM job_alerts WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => (int) Auth::id()]);
        if (!$alert) {
            flash('error', 'That job alert could not be found.');
            redirect('job-alerts');
        }
        Database::delete('job_alerts', 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => (int) Auth::id()]);
        flash('success', 'Job alert removed.');
        redirect('job-alerts');
    }

    // -------------------------------------------------------------------- Skills

    public function skills(): void
    {
        $userId = (int) Auth::id();
        $this->renderDash('seeker.skills', [
            'skills' => $this->profileData($userId)['skills'],
            'allSkills' => Database::all('SELECT id, name, category FROM skills ORDER BY name'),
            'levels' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced', 'expert' => 'Expert'],
        ], 'My Skills', 'my-skills');
    }

    public function addSkill(): void
    {
        $userId = (int) Auth::id();
        $skillIdRaw = $this->inputString('skill_id');
        $skillId = $skillIdRaw !== '' && ctype_digit($skillIdRaw) ? (int) $skillIdRaw : 0;
        $level = $this->inputString('level', 'intermediate');
        $yearsRaw = $this->inputString('years', '0');
        $errors = [];
        if ($skillId === 0 || !Database::scalar('SELECT COUNT(*) FROM skills WHERE id = :id', ['id' => $skillId])) {
            $errors['skill_id'] = 'Please choose a valid skill.';
        }
        if (!in_array($level, ['beginner', 'intermediate', 'advanced', 'expert'], true)) {
            $errors['level'] = 'Choose a valid skill level.';
        }
        if ($yearsRaw !== '' && (!is_numeric($yearsRaw) || (float) $yearsRaw < 0 || (float) $yearsRaw > 50)) {
            $errors['years'] = 'Enter years of experience between 0 and 50.';
        }
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('my-skills');
        }
        if (Database::scalar('SELECT COUNT(*) FROM user_skills WHERE user_id = :uid AND skill_id = :sid', ['uid' => $userId, 'sid' => $skillId])) {
            flash('error', 'That skill is already on your profile. Choose another one or update your existing entry.');
            redirect('my-skills');
        }
        Database::insert('user_skills', [
            'user_id' => $userId,
            'skill_id' => $skillId,
            'level' => $level,
            'years' => $yearsRaw !== '' ? round((float) $yearsRaw, 1) : 0,
        ]);
        User::completion($userId);
        flash('success', 'Skill added to your profile.');
        redirect('my-skills');
    }

    public function deleteSkill(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $row = Database::one('SELECT id, skill_id FROM user_skills WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        if (!$row) {
            flash('error', 'That skill could not be found on your profile.');
            redirect('my-skills');
        }
        Database::begin();
        try {
            Database::delete('skill_endorsements', 'skill_id = :sid AND user_id = :uid', ['sid' => (int) $row['skill_id'], 'uid' => $userId]);
            Database::delete('user_skills', 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'The skill could not be removed. Please try again.');
            redirect('my-skills');
        }
        User::completion($userId);
        flash('success', 'Skill removed from your profile.');
        redirect('my-skills');
    }

    public function endorseSkill(): void
    {
        $viewerId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $row = Database::one(
            'SELECT us.id, us.skill_id, us.user_id, s.name AS skill_name FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.id = :id',
            ['id' => $id]
        );
        if (!$row) {
            flash('error', 'That skill could not be found.');
            redirect('my-skills');
        }
        $ownerId = (int) $row['user_id'];
        if ($ownerId === $viewerId) {
            flash('error', 'You cannot endorse your own skills.');
            redirect('my-skills');
        }
        $target = Database::one('SELECT id FROM users WHERE id = :id AND status = "active"', ['id' => $ownerId]);
        $redirectTo = $target ? 'u/' . $ownerId : 'my-skills';
        if (!$target) {
            flash('error', 'That profile is no longer available.');
            redirect($redirectTo);
        }
        $exists = Database::scalar(
            'SELECT COUNT(*) FROM skill_endorsements WHERE skill_id = :sid AND user_id = :uid AND endorsed_by = :by',
            ['sid' => (int) $row['skill_id'], 'uid' => $ownerId, 'by' => $viewerId]
        );
        if ($exists) {
            flash('info', 'You have already endorsed this skill.');
            redirect($redirectTo);
        }
        Database::insert('skill_endorsements', [
            'skill_id' => (int) $row['skill_id'],
            'user_id' => $ownerId,
            'endorsed_by' => $viewerId,
        ]);
        flash('success', 'You endorsed ' . $row['skill_name'] . '.');
        redirect($redirectTo);
    }

    // ----------------------------------------------------------------- Portfolio

    public function portfolio(): void
    {
        $this->renderDash('seeker.portfolio', [
            'portfolios' => Database::all('SELECT * FROM portfolios WHERE user_id = :id ORDER BY id DESC', ['id' => (int) Auth::id()]),
        ], 'My Portfolio', 'my-portfolio');
    }

    public function addPortfolio(): void
    {
        $userId = (int) Auth::id();
        $errors = $this->validate([
            'title' => 'required|max:180',
            'description' => 'max:5000',
            'url' => 'url',
            'repository_url' => 'url',
            'tags' => 'max:255',
            'completed_at' => 'date',
        ]);
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('my-portfolio');
        }
        $completedRaw = $this->inputString('completed_at');
        $completed = ($completedRaw !== '' && strtotime($completedRaw)) ? date('Y-m-d', (int) strtotime($completedRaw)) : null;
        Database::insert('portfolios', [
            'user_id' => $userId,
            'title' => $this->inputString('title'),
            'description' => $this->inputString('description') ?: null,
            'url' => $this->inputString('url') ?: null,
            'repository_url' => $this->inputString('repository_url') ?: null,
            'tags' => $this->inputString('tags') ?: null,
            'completed_at' => $completed,
        ]);
        User::completion($userId);
        $this->audit('portfolio_add', 'Added a portfolio project');
        flash('success', 'Portfolio project added.');
        redirect('my-portfolio');
    }

    public function deletePortfolio(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $row = Database::one('SELECT id FROM portfolios WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        if (!$row) {
            flash('error', 'That portfolio project could not be found.');
            redirect('my-portfolio');
        }
        Database::delete('portfolios', 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        User::completion($userId);
        flash('success', 'Portfolio project removed.');
        redirect('my-portfolio');
    }

    // ------------------------------------------------------------ Profile entries

    public function addEntry(): void
    {
        $userId = (int) Auth::id();
        $type = $this->inputString('type');
        if (!isset(self::ENTRY_TABLES[$type], self::ENTRY_RULES[$type])) {
            flash('error', 'Choose a valid entry type.');
            redirect('profile/edit');
        }
        $errors = $this->validate(self::ENTRY_RULES[$type]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('profile/edit');
        }

        if ($type === 'education') {
            $data = [
                'school' => $this->inputString('school'),
                'degree' => $this->inputString('degree'),
                'field_of_study' => $this->inputString('field_of_study') ?: null,
                'start_year' => $this->nullableInt('start_year'),
                'end_year' => $this->nullableInt('end_year'),
                'grade' => $this->inputString('grade') ?: null,
                'description' => $this->inputString('description') ?: null,
            ];
        } elseif ($type === 'experience') {
            $data = [
                'company' => $this->inputString('company'),
                'title' => $this->inputString('title'),
                'start_date' => $this->nullableDate('start_date'),
                'end_date' => $this->nullableDate('end_date'),
                'is_current' => !empty($this->request->post('is_current')) ? 1 : 0,
                'description' => $this->inputString('description') ?: null,
            ];
        } else {
            $data = [
                'name' => $this->inputString('name'),
                'issuer' => $this->inputString('issuer') ?: null,
                'issued_year' => $this->nullableInt('issued_year'),
                'credential_id' => $this->inputString('credential_id') ?: null,
                'credential_url' => $this->inputString('credential_url') ?: null,
                'verified' => 0,
            ];
        }
        Database::insert(self::ENTRY_TABLES[$type], array_merge(['user_id' => $userId], $data));
        User::completion($userId);
        $this->audit('entry_add', 'Added a ' . $type . ' entry to the profile');
        clear_old();
        flash('success', ucfirst($type) . ' entry added.');
        redirect('profile/edit');
    }

    public function deleteEntry(): void
    {
        $userId = (int) Auth::id();
        $id = (int) $this->request->route('id');
        $type = $this->inputString('type');
        if (!isset(self::ENTRY_TABLES[$type])) {
            flash('error', 'Choose a valid entry type.');
            redirect('profile/edit');
        }
        $table = self::ENTRY_TABLES[$type];
        $row = Database::one("SELECT id FROM `$table` WHERE id = :id AND user_id = :uid", ['id' => $id, 'uid' => $userId]);
        if (!$row) {
            flash('error', 'That profile entry could not be found.');
            redirect('profile/edit');
        }
        Database::delete($table, 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
        User::completion($userId);
        $this->audit('entry_delete', 'Removed a ' . $type . ' entry from the profile');
        flash('success', 'Profile entry removed.');
        redirect('profile/edit');
    }

    // ------------------------------------------------------------- Notifications

    public function notifications(): void
    {
        $userId = (int) Auth::id();
        $total = (int) Database::scalar('SELECT COUNT(*) FROM notifications WHERE user_id = :id', ['id' => $userId]);
        $pager = new Paginator($total, 30, (int) $this->inputString('page', '1'));
        $notifications = Database::all(
            'SELECT id, type, title, body, link, is_read, created_at FROM notifications
             WHERE user_id = :id ORDER BY id DESC LIMIT :limit OFFSET :offset',
            ['id' => $userId, 'limit' => $pager->perPage, 'offset' => $pager->offset]
        );
        foreach ($notifications as &$notification) {
            $notification['href'] = $notification['link'] ? url($notification['link']) : '';
        }
        unset($notification);
        $this->renderDash('seeker.notifications', [
            'notifications' => $notifications,
            'pager' => $pager,
        ], 'Notifications', 'notifications');
    }

    // ------------------------------------------------------------------ Settings

    public function settings(): void
    {
        $userId = (int) Auth::id();
        $prefs = Database::one('SELECT * FROM notification_preferences WHERE user_id = :id', ['id' => $userId])
            ?? ['email_enabled' => 1, 'match_enabled' => 1, 'deadline_enabled' => 1,
                'message_enabled' => 1, 'application_enabled' => 1, 'in_app_enabled' => 1];
        $this->renderDash('seeker.settings', [
            'user' => User::find($userId) ?? [],
            'prefs' => $prefs,
        ], 'Settings', 'settings');
    }

    public function updateSettings(): void
    {
        $userId = (int) Auth::id();
        $email = mb_strtolower($this->inputString('email'));
        $newPassword = (string) $this->request->post('new_password', '');
        $rules = [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email|max:190',
            'phone' => 'max:40',
        ];
        if ($newPassword !== '') {
            $rules['current_password'] = 'required';
            $rules['new_password'] = 'required|min:8|confirmed';
        }
        $errors = $this->validate($rules);
        if (User::emailExists($email, $userId)) {
            $errors['email'] = 'This email address is already in use.';
        }
        $currentHash = null;
        if ($newPassword !== '') {
            $account = User::find($userId) ?? [];
            $currentHash = $account['password_hash'] ?? null;
            if (!$currentHash || !password_verify($this->inputString('current_password'), (string) $currentHash)) {
                $errors['current_password'] = 'Your current password is incorrect.';
            }
        }
        if ($errors) {
            old_input(['name' => $this->inputString('name'), 'email' => $email, 'phone' => $this->inputString('phone')]);
            flash('error', implode(' ', $errors));
            redirect('settings');
        }

        $phone = $this->inputString('phone');
        $updates = [
            'name' => $this->inputString('name'),
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
        ];
        if ($newPassword !== '') {
            $updates['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 11]);
        }
        Database::update('users', $updates, 'id = :id', ['id' => $userId]);

        $prefFields = ['email_enabled', 'match_enabled', 'deadline_enabled', 'message_enabled', 'application_enabled', 'in_app_enabled'];
        $prefData = [];
        foreach ($prefFields as $field) {
            if (array_key_exists($field, $this->request->post)) {
                $prefData[$field] = !empty($this->request->post($field)) ? 1 : 0;
            }
        }
        if ($prefData) {
            if (Database::scalar('SELECT COUNT(*) FROM notification_preferences WHERE user_id = :id', ['id' => $userId])) {
                Database::update('notification_preferences', $prefData, 'user_id = :uid', ['uid' => $userId]);
            } else {
                Database::insert('notification_preferences', array_merge(['user_id' => $userId], $prefData));
            }
        }

        if ($newPassword !== '') {
            $this->audit('password_change', 'Changed account password');
        }
        $this->audit('settings_update', 'Updated account settings');
        Auth::refresh();
        clear_old();
        flash('success', 'Your settings have been saved.');
        redirect('settings');
    }

    public function deactivate(): void
    {
        $userId = (int) Auth::id();
        User::logAudit($userId, 'deactivate', 'User deactivated their own account', $this->request->ip());
        Database::update('users', ['status' => 'deactivated'], 'id = :id', ['id' => $userId]);
        Auth::logout();
        flash('success', 'Your account has been deactivated. Contact support if you would like it reactivated.');
        redirect('login');
    }

    // ------------------------------------------------------------- Public profile

    public function publicProfile(): void
    {
        $id = (int) $this->request->route('id');
        $user = User::find($id);
        if (!$user || $user['status'] !== 'active' || !empty($user['deleted_at'])) {
            abort(404, 'This profile is not available.');
        }
        $profile = User::profile($id);
        if (empty($profile['seeker'])) {
            abort(404, 'This profile is not available.');
        }
        $isOwner = Auth::id() === $id;
        $isAdmin = Auth::hasRole('admin');
        if (!$isOwner && !$isAdmin && (int) ($profile['seeker']['open_to_work'] ?? 0) !== 1) {
            abort(403, 'This profile is not currently public.');
        }
        $canSeeContact = $isOwner || $isAdmin;
        if (!$canSeeContact) {
            unset($profile['user']['email'], $profile['user']['phone']);
        }

        $this->render('seeker.public_profile', array_merge([
            'profile' => $profile,
            'completion' => User::completion($id),
            'canSeeContact' => $canSeeContact,
            'isOwner' => $isOwner,
        ], $this->profileData($id)), (string) $user['name']);
    }

    // ------------------------------------------------------------------ Helpers

    private function renderDash(string $view, array $data, string $title, string $active): void
    {
        $data['sideTitle'] = 'Job seeker workspace';
        $data['active'] = $active;
        $data['links'] = self::SIDEBAR_LINKS;
        $this->render($view, $data, $title);
    }

    private function inputString(string $key, string $default = ''): string
    {
        $value = $this->request->input($key, $default);
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    private function nullableInt(string $key): ?int
    {
        $value = $this->inputString($key);
        return $value !== '' && filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : null;
    }

    private function nullableDate(string $key): ?string
    {
        $value = $this->inputString($key);
        return ($value !== '' && strtotime($value)) ? date('Y-m-d', (int) strtotime($value)) : null;
    }

    /** Delete an old upload only when it really lives inside the expected directory. */
    private function deleteUploadedFile(?string $path, string $directory): void
    {
        if (!$path || $path === '') {
            return;
        }
        $relative = str_replace('\\', '/', $path);
        $prefix = trim($directory, '/') . '/';
        if (!str_starts_with($relative, $prefix) || str_contains($relative, '..')) {
            return;
        }
        $base = realpath(rtrim((string) config('upload.dir'), '/') . '/' . trim($directory, '/'));
        $full = realpath(rtrim((string) config('upload.dir'), '/') . '/' . ltrim($relative, '/'));
        if (!$base || !$full || !is_file($full)) {
            return;
        }
        if (!str_starts_with(str_replace('\\', '/', $full), str_replace('\\', '/', $base) . '/')) {
            return;
        }
        @unlink($full);
    }

    /** @return array{education:array,experience:array,certifications:array,portfolios:array,skills:array} */
    private function profileData(int $userId): array
    {
        return [
            'education' => Database::all(
                'SELECT * FROM education WHERE user_id = :id ORDER BY end_year DESC, start_year DESC, id DESC',
                ['id' => $userId]
            ),
            'experience' => Database::all(
                'SELECT * FROM experience WHERE user_id = :id ORDER BY is_current DESC, start_date DESC, id DESC',
                ['id' => $userId]
            ),
            'certifications' => Database::all(
                'SELECT * FROM certifications WHERE user_id = :id ORDER BY issued_year DESC, id DESC',
                ['id' => $userId]
            ),
            'portfolios' => Database::all(
                'SELECT * FROM portfolios WHERE user_id = :id ORDER BY id DESC',
                ['id' => $userId]
            ),
            'skills' => Database::all(
                'SELECT us.id AS user_skill_id, us.level, us.years, s.id AS skill_id, s.name, s.category,
                        (SELECT COUNT(*) FROM skill_endorsements se WHERE se.skill_id = s.id AND se.user_id = us.user_id) AS endorsements
                 FROM user_skills us JOIN skills s ON s.id = us.skill_id
                 WHERE us.user_id = :id ORDER BY endorsements DESC, s.name ASC',
                ['id' => $userId]
            ),
        ];
    }

    /** Build CV data from the real profile when a seeker has not saved a document yet. */
    private function defaultCvData(array $profile, array $sections): array
    {
        $user = $profile['user'] ?? [];
        $seeker = $profile['seeker'] ?? [];
        $cv = [
            'title' => 'My CV',
            'contact' => [
                'name' => (string) ($user['name'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'phone' => (string) ($user['phone'] ?? ''),
                'location' => (string) ($seeker['location'] ?? ''),
                'linkedin' => (string) ($seeker['linkedin_url'] ?? ''),
                'github' => (string) ($seeker['github_url'] ?? ''),
                'website' => (string) ($seeker['website_url'] ?? ''),
            ],
            'full_name' => (string) ($user['name'] ?? ''),
            'headline' => (string) ($seeker['headline'] ?? ''),
            'summary' => (string) ($seeker['summary'] ?? ''),
            'experience' => [],
            'education' => [],
            'skills' => [],
            'projects' => [],
            'languages' => [],
        ];
        foreach ($sections['experience'] as $row) {
            $cv['experience'][] = [
                'company' => (string) ($row['company'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'start_date' => (string) ($row['start_date'] ?? ''),
                'end_date' => !empty($row['is_current']) ? 'Present' : (string) ($row['end_date'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
            ];
        }
        foreach ($sections['education'] as $row) {
            $cv['education'][] = [
                'school' => (string) ($row['school'] ?? ''),
                'degree' => (string) ($row['degree'] ?? ''),
                'field_of_study' => (string) ($row['field_of_study'] ?? ''),
                'start_year' => (string) ($row['start_year'] ?? ''),
                'end_year' => (string) ($row['end_year'] ?? ''),
                'grade' => (string) ($row['grade'] ?? ''),
            ];
        }
        foreach ($sections['skills'] as $row) {
            $cv['skills'][] = ['name' => (string) ($row['name'] ?? ''), 'level' => (string) ($row['level'] ?? '')];
        }
        foreach ($sections['portfolios'] as $row) {
            $cv['projects'][] = [
                'title' => (string) ($row['title'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'url' => (string) ($row['url'] ?? ''),
                'tags' => (string) ($row['tags'] ?? ''),
            ];
        }
        return $cv;
    }

    /** Accept only the documented CV shape, with bounded text and list sizes. */
    private function normalizeCvData(array $data, array $profile, array $sections): array
    {
        $contactInput = is_array($data['contact'] ?? null) ? $data['contact'] : [];
        $contact = [];
        foreach ([
            'name' => 150, 'email' => 190, 'phone' => 40, 'location' => 150,
            'linkedin' => 255, 'github' => 255, 'website' => 255,
        ] as $field => $limit) {
            $contact[$field] = $this->cvText($contactInput[$field] ?? '', $limit);
        }
        $user = $profile['user'] ?? [];
        $seeker = $profile['seeker'] ?? [];
        if ($contact['name'] === '') {
            $contact['name'] = $this->cvText($data['full_name'] ?? ($user['name'] ?? ''), 150);
        }
        foreach (['email' => 'email', 'phone' => 'phone'] as $field => $userField) {
            if ($contact[$field] === '') {
                $contact[$field] = $this->cvText($user[$userField] ?? '', $field === 'email' ? 190 : 40);
            }
        }
        if ($contact['location'] === '') {
            $contact['location'] = $this->cvText($seeker['location'] ?? '', 150);
        }

        return [
            'contact' => $contact,
            'full_name' => $contact['name'],
            'headline' => $this->cvText($data['headline'] ?? ($seeker['headline'] ?? ''), 200),
            'summary' => $this->cvText($data['summary'] ?? ($seeker['summary'] ?? ''), 5000),
            'experience' => $this->cvList($data['experience'] ?? [], 30, [
                'company' => 180, 'title' => 150, 'start_date' => 40, 'end_date' => 40, 'description' => 3000,
            ]),
            'education' => $this->cvList($data['education'] ?? [], 30, [
                'school' => 180, 'degree' => 120, 'field_of_study' => 120,
                'start_year' => 20, 'end_year' => 20, 'grade' => 40,
            ]),
            'skills' => $this->cvList($data['skills'] ?? [], 80, ['name' => 120, 'level' => 40]),
            'projects' => $this->cvList($data['projects'] ?? [], 30, [
                'title' => 180, 'description' => 3000, 'url' => 255, 'tags' => 255,
            ]),
            'languages' => $this->cvList($data['languages'] ?? [], 20, ['name' => 120, 'level' => 60]),
        ];
    }

    private function cvText(mixed $value, int $max): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        return mb_substr(trim(strip_tags((string) $value)), 0, $max);
    }

    private function cvList(mixed $value, int $maxItems, array $fields): array
    {
        if (!is_array($value)) {
            return [];
        }
        $rows = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }
            $row = [];
            foreach ($fields as $field => $limit) {
                $row[$field] = $this->cvText($item[$field] ?? '', $limit);
            }
            $rows[] = $row;
            if (count($rows) >= $maxItems) {
                break;
            }
        }
        return $rows;
    }
}
