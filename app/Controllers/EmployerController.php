<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Core\Upload;
use App\Models\Admin;
use App\Models\Application;
use App\Models\User;

final class EmployerController extends Controller
{
    private const EMPLOYMENT_TYPES = ['full_time', 'part_time', 'internship', 'contract', 'remote', 'freelance', 'temporary'];
    private const WORK_MODES = ['onsite', 'remote', 'hybrid'];
    private const EXPERIENCE_LEVELS = ['entry', 'junior', 'mid', 'senior', 'executive'];
    private const SALARY_PERIODS = ['hourly', 'monthly', 'yearly'];
    private const CURRENCIES = ['RWF', 'USD', 'EUR', 'KES', 'UGX'];
    private const JOB_STATUSES = ['draft', 'pending_review', 'published', 'closed', 'archived', 'rejected'];
    private const MANAGE_STATUSES = ['draft', 'pending_review', 'published', 'closed', 'archived'];
    private const SHORTLIST_STATUSES = ['shortlisted', 'interview_scheduled', 'interview_completed', 'selected'];
    private const INTERVIEW_STATUSES = ['scheduled', 'completed', 'cancelled'];

    /** employer row for the current user; admins may not have one. */
    private function context(): array
    {
        $userId = (int) Auth::id();
        $employer = Database::one('SELECT * FROM employers WHERE user_id = :id', ['id' => $userId]);
        $company = null;
        if ($employer && !empty($employer['company_id'])) {
            $company = Database::one('SELECT * FROM companies WHERE id = :id AND deleted_at IS NULL', ['id' => (int) $employer['company_id']]);
        }
        $isAdmin = Auth::hasRole('admin');
        return [
            'user_id' => $userId,
            'employer' => $employer,
            'employer_id' => (int) ($employer['id'] ?? 0),
            'company' => $company,
            'is_admin' => $isAdmin,
            'notice' => ($isAdmin && !$employer)
                ? 'You are viewing the employer workspace as an administrator. Actions that require an employer profile (posting jobs and editing a company) are unavailable.'
                : '',
        ];
    }

    private function renderEmployer(string $view, array $data, string $title): void
    {
        $data['ctx'] ??= $this->context();
        $this->render('employer.' . $view, $data, $title);
    }

    private function postString(string $key, string $default = ''): string
    {
        $value = $this->request->post($key, $default);
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    private function queryString(string $key, string $default = ''): string
    {
        $value = $this->request->query($key, $default);
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    private function postInt(string $key, int $default = 0): int
    {
        $value = $this->request->post($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    private function enumPost(string $key, array $allowed, string $default): string
    {
        $value = $this->postString($key, $default);
        return in_array($value, $allowed, true) ? $value : $default;
    }

    /** Foreign keys are only accepted when the referenced, active row exists. */
    private function foreignId(string $key, string $table): ?int
    {
        $id = $this->postInt($key);
        if ($id <= 0) {
            return null;
        }
        $exists = Database::scalar("SELECT id FROM `$table` WHERE id = :id", ['id' => $id]);
        return $exists ? $id : null;
    }

    /** SQL scope for jobs: admins without an employer row may view everything. */
    private function jobScope(array $ctx, string $alias = 'j'): array
    {
        if ($ctx['employer_id'] > 0) {
            return [$alias . '.employer_id = :eid', ['eid' => $ctx['employer_id']]];
        }
        return ['1=1', []];
    }

    private function ownedJob(int $id, array $ctx): array
    {
        [$scope, $params] = $this->jobScope($ctx);
        $job = Database::one(
            'SELECT j.*, c.name AS company_name FROM jobs j LEFT JOIN companies c ON c.id = j.company_id
             WHERE j.id = :id AND j.deleted_at IS NULL AND ' . $scope,
            array_merge(['id' => $id], $params)
        );
        if (!$job) {
            abort(404, 'Opportunity not found.');
        }
        return $job;
    }

    private function ownedApplication(int $id, array $ctx): array
    {
        [$scope, $params] = $this->jobScope($ctx);
        $app = Database::one(
            'SELECT a.*, j.title AS job_title, j.slug AS job_slug, j.employer_id AS job_employer_id,
                    u.name AS applicant_name, u.email AS applicant_email, u.phone AS applicant_phone
             FROM applications a JOIN jobs j ON j.id = a.job_id JOIN users u ON u.id = a.user_id
             WHERE a.id = :id AND j.deleted_at IS NULL AND ' . $scope,
            array_merge(['id' => $id], $params)
        );
        if (!$app) {
            abort(404, 'Application not found.');
        }
        return $app;
    }

    private function jobOptions(array $ctx): array
    {
        [$scope, $params] = $this->jobScope($ctx);
        return Database::all(
            'SELECT j.id, j.title FROM jobs j WHERE j.deleted_at IS NULL AND ' . $scope . ' ORDER BY j.title',
            $params
        );
    }

    private function formOptions(array $ctx): array
    {
        return [
            'categories' => Database::all('SELECT id, name FROM job_categories WHERE active = 1 ORDER BY name'),
            'industries' => Database::all('SELECT id, name FROM industries WHERE active = 1 ORDER BY name'),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
            'skills' => Database::all('SELECT id, name, category FROM skills ORDER BY name'),
            'company' => $ctx['company'],
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'workModes' => self::WORK_MODES,
            'experienceLevels' => self::EXPERIENCE_LEVELS,
            'salaryPeriods' => self::SALARY_PERIODS,
            'currencies' => self::CURRENCIES,
        ];
    }

    private function applicationFilters(array $filters): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['job_id'])) {
            $where[] = 'a.job_id = :job';
            $params['job'] = (int) $filters['job_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'a.status = :status';
            $params['status'] = (string) $filters['status'];
        }
        if (!empty($filters['skill'])) {
            $where[] = 'EXISTS (SELECT 1 FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = a.user_id AND s.name LIKE :skill)';
            $params['skill'] = '%' . $filters['skill'] . '%';
        }
        if (!empty($filters['education'])) {
            $where[] = 'js.education_level LIKE :education';
            $params['education'] = '%' . $filters['education'] . '%';
        }
        return [$where, $params];
    }

    private const APPLICATION_SELECT = "a.*, j.title AS job_title, j.slug AS job_slug,
        u.name AS applicant_name, u.email AS applicant_email,
        js.headline, js.experience_years, js.education_level, js.location,
        (SELECT GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') FROM user_skills us
         JOIN skills s ON s.id = us.skill_id WHERE us.user_id = a.user_id) AS applicant_skills";

    /** Application::forEmployer filters by the employer's user id; admin overview uses its own scoped query. */
    private function applications(array $ctx, array $filters = [], ?Paginator $pager = null): array
    {
        if ($ctx['employer_id'] > 0) {
            return Application::forEmployer($ctx['user_id'], $filters, $pager);
        }
        [$scope, $scopeParams] = $this->jobScope($ctx);
        [$where, $params] = $this->applicationFilters($filters);
        $sql = 'SELECT ' . self::APPLICATION_SELECT . '
                FROM applications a JOIN jobs j ON j.id = a.job_id JOIN users u ON u.id = a.user_id
                LEFT JOIN job_seekers js ON js.user_id = u.id
                WHERE ' . $scope . ' AND ' . (implode(' AND ', $where) ?: '1=1') . '
                ORDER BY a.created_at DESC';
        $bound = array_merge($scopeParams, $params);
        if ($pager) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $bound['limit'] = $pager->perPage;
            $bound['offset'] = $pager->offset;
        }
        return Database::all($sql, $bound);
    }

    private function applicationCount(array $ctx, array $filters): int
    {
        [$scope, $scopeParams] = $this->jobScope($ctx);
        [$where, $params] = $this->applicationFilters($filters);
        $sql = 'SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id
                JOIN users u ON u.id = a.user_id LEFT JOIN job_seekers js ON js.user_id = u.id
                WHERE ' . $scope . ' AND ' . (implode(' AND ', $where) ?: '1=1');
        return (int) Database::scalar($sql, array_merge($scopeParams, $params));
    }

    private function uniqueJobSlug(string $title): string
    {
        $base = slugify($title) ?: 'opportunity';
        for ($i = 0; $i < 10; $i++) {
            $slug = $base . '-' . bin2hex(random_bytes(3));
            if (!Database::scalar('SELECT COUNT(*) FROM jobs WHERE slug = :slug', ['slug' => $slug])) {
                return $slug;
            }
        }
        return $base . '-' . bin2hex(random_bytes(8));
    }

    private function syncJobSkills(int $jobId): void
    {
        Database::delete('job_skills', 'job_id = :id', ['id' => $jobId]);
        $raw = $this->request->post('skills', []);
        $ids = is_array($raw) ? array_values(array_unique(array_filter(array_map('intval', $raw), static fn(int $id): bool => $id > 0))) : [];
        if (!$ids) {
            return;
        }
        [$placeholders, $params] = Database::in($ids);
        $valid = Database::all("SELECT id FROM skills WHERE id IN ($placeholders)", $params);
        $requiredRaw = $this->request->post('skills_required', []);
        $required = is_array($requiredRaw) ? array_map('intval', $requiredRaw) : [];
        foreach ($valid as $skill) {
            Database::insert('job_skills', [
                'job_id' => $jobId,
                'skill_id' => (int) $skill['id'],
                'is_required' => in_array((int) $skill['id'], $required, true) ? 1 : 0,
                'weight' => 2,
            ]);
        }
    }

    private function selectedSkills(int $jobId): array
    {
        $selected = [];
        foreach (Database::all('SELECT skill_id, is_required FROM job_skills WHERE job_id = :id', ['id' => $jobId]) as $row) {
            $selected[(int) $row['skill_id']] = (int) $row['is_required'] === 1;
        }
        return $selected;
    }

    /** @return array{0: array<string,string>, 1: array<string,mixed>} */
    private function jobFields(): array
    {
        $errors = $this->validate([
            'title' => 'required|min:5|max:200',
            'description' => 'required|min:30',
            'location' => 'required|max:150',
            'deadline' => 'date',
            'salary_min' => 'numeric',
            'salary_max' => 'numeric',
            'education_level' => 'max:80',
        ]);

        $deadline = $this->postString('deadline');
        if ($deadline !== '') {
            $parsed = \DateTime::createFromFormat('!Y-m-d', $deadline);
            if (!$parsed || $parsed->format('Y-m-d') !== $deadline) {
                $errors['deadline'] = 'Enter a valid date.';
            } elseif (strtotime($deadline) < strtotime(date('Y-m-d'))) {
                $errors['deadline'] = 'The application deadline must be today or later.';
            }
        }
        $salaryMin = $this->postString('salary_min');
        $salaryMax = $this->postString('salary_max');
        if ($salaryMin !== '' && (float) $salaryMin < 0) {
            $errors['salary_min'] = 'Salary cannot be negative.';
        }
        if ($salaryMax !== '' && (float) $salaryMax < 0) {
            $errors['salary_max'] = 'Salary cannot be negative.';
        }
        if ($salaryMin !== '' && $salaryMax !== '' && (float) $salaryMax < (float) $salaryMin) {
            $errors['salary_max'] = 'Maximum salary must not be lower than the minimum.';
        }
        $currency = strtoupper($this->postString('salary_currency', 'RWF'));
        if (!in_array($currency, self::CURRENCIES, true)) {
            $currency = 'RWF';
        }

        $data = [
            'title' => $this->postString('title'),
            'description' => $this->postString('description'),
            'requirements' => $this->postString('requirements') ?: null,
            'responsibilities' => $this->postString('responsibilities') ?: null,
            'benefits' => $this->postString('benefits') ?: null,
            'location' => $this->postString('location', 'Kigali') ?: 'Kigali',
            'employment_type' => $this->enumPost('employment_type', self::EMPLOYMENT_TYPES, 'full_time'),
            'work_mode' => $this->enumPost('work_mode', self::WORK_MODES, 'onsite'),
            'experience_level' => $this->enumPost('experience_level', self::EXPERIENCE_LEVELS, 'entry'),
            'education_level' => $this->postString('education_level') ?: null,
            'salary_min' => $salaryMin !== '' ? (float) $salaryMin : null,
            'salary_max' => $salaryMax !== '' ? (float) $salaryMax : null,
            'salary_currency' => $currency,
            'salary_period' => $this->enumPost('salary_period', self::SALARY_PERIODS, 'monthly'),
            'deadline' => $deadline !== '' ? $deadline : null,
            'category_id' => $this->foreignId('category_id', 'job_categories'),
            'industry_id' => $this->foreignId('industry_id', 'industries'),
            'country_id' => $this->foreignId('country_id', 'countries'),
        ];
        return [$errors, $data];
    }

    private function removeOldImage(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'companies/') || str_contains($path, '..')) {
            return;
        }
        $file = rtrim((string) config('upload.dir'), '/') . '/' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public function dashboard(): void
    {
        $ctx = $this->context();
        [$scope, $params] = $this->jobScope($ctx);
        $stats = Admin::employerStats($ctx['user_id']);

        $recentJobs = Database::all(
            'SELECT j.id, j.title, j.slug, j.status, j.views_count, j.created_at,
                    c.name AS company_name,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants_count
             FROM jobs j LEFT JOIN companies c ON c.id = j.company_id
             WHERE j.deleted_at IS NULL AND ' . $scope . ' ORDER BY j.created_at DESC LIMIT 6',
            $params
        );
        $recentApplications = array_slice($this->applications($ctx), 0, 6);

        $adminOverview = $ctx['is_admin'] && $ctx['employer_id'] === 0;
        $interviewWhere = $adminOverview ? '1=1' : 'i.employer_id = :uid';
        $interviewParams = $adminOverview ? [] : ['uid' => $ctx['user_id']];
        $upcoming = Database::all(
            "SELECT i.id, i.scheduled_at, i.duration_minutes, i.status,
                    u.name AS applicant_name, j.title AS job_title
             FROM interviews i JOIN users u ON u.id = i.applicant_id JOIN jobs j ON j.id = i.job_id
             WHERE $interviewWhere AND i.status = 'scheduled' AND i.scheduled_at >= NOW()
             ORDER BY i.scheduled_at ASC LIMIT 6",
            $interviewParams
        );

        $this->renderEmployer('dashboard', [
            'ctx' => $ctx,
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'recentApplications' => $recentApplications,
            'upcoming' => $upcoming,
            'unverified' => $ctx['company'] !== null && ($ctx['company']['verification_status'] ?? '') !== 'verified',
            'statuses' => (array) config('application_statuses'),
        ], 'Employer Dashboard');
    }

    public function company(): void
    {
        $ctx = $this->context();
        $this->renderEmployer('company', [
            'ctx' => $ctx,
            'company' => $ctx['company'],
            'industries' => Database::all('SELECT id, name FROM industries WHERE active = 1 ORDER BY name'),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
        ], 'Company Profile');
    }

    public function updateCompany(): void
    {
        $ctx = $this->context();
        if (!$ctx['company']) {
            flash('error', 'An employer profile with a company is required before you can edit company details.');
            redirect('employer/company');
        }
        $company = $ctx['company'];
        $errors = $this->validate([
            'name' => 'required|max:180',
            'email' => 'email|max:190',
            'website' => 'url|max:255',
            'phone' => 'max:40',
            'location' => 'max:150',
            'address' => 'max:255',
            'company_size' => 'max:60',
            'description' => 'max:5000',
        ]);
        $founded = $this->postString('founded_year');
        if ($founded !== '' && (!ctype_digit($founded) || (int) $founded < 1800 || (int) $founded > (int) date('Y') + 1)) {
            $errors['founded_year'] = 'Enter a valid founding year.';
        }
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('employer/company');
        }

        $name = $this->postString('name');
        $data = [
            'name' => $name,
            'description' => $this->postString('description') ?: null,
            'industry_id' => $this->foreignId('industry_id', 'industries'),
            'country_id' => $this->foreignId('country_id', 'countries'),
            'website' => $this->postString('website') ?: null,
            'email' => $this->postString('email') ?: null,
            'phone' => $this->postString('phone') ?: null,
            'location' => $this->postString('location') ?: null,
            'address' => $this->postString('address') ?: null,
            'company_size' => $this->postString('company_size') ?: null,
            'founded_year' => $founded !== '' ? (int) $founded : null,
        ];
        if ($name !== $company['name']) {
            $base = slugify($name) ?: 'company-' . (int) $company['id'];
            $taken = (int) Database::scalar(
                'SELECT COUNT(*) FROM companies WHERE slug = :slug AND id <> :id',
                ['slug' => $base, 'id' => (int) $company['id']]
            );
            $data['slug'] = $taken ? $base . '-' . (int) $company['id'] : $base;
            if ($taken && Database::scalar('SELECT COUNT(*) FROM companies WHERE slug = :slug', ['slug' => $data['slug']])) {
                $data['slug'] .= '-' . bin2hex(random_bytes(2));
            }
        }

        $oldImages = [];
        $uploadErrors = [];
        $allowed = (array) config('upload.allowed_images');
        foreach (['logo', 'banner'] as $field) {
            try {
                $path = Upload::handle($this->request->file($field), 'companies', $allowed);
                if ($path) {
                    $data[$field] = $path;
                    $oldImages[] = $company[$field] ?? null;
                }
            } catch (\Throwable $e) {
                $uploadErrors[] = $e->getMessage();
            }
        }

        Database::update('companies', $data, 'id = :id', ['id' => (int) $company['id']]);
        foreach ($oldImages as $old) {
            $this->removeOldImage($old);
        }
        $this->audit('company_update', 'Updated company profile #' . (int) $company['id']);
        clear_old();
        flash('success', 'Company profile updated.');
        if ($uploadErrors) {
            flash('error', implode(' ', $uploadErrors));
        }
        redirect('employer/company');
    }

    public function createJob(): void
    {
        $ctx = $this->context();
        $this->renderEmployer('job_form', array_merge($this->formOptions($ctx), [
            'ctx' => $ctx,
            'job' => null,
            'selectedSkills' => [],
            'formAction' => 'employer/jobs',
        ]), 'Post a Job');
    }

    public function storeJob(): void
    {
        $ctx = $this->context();
        if (!$ctx['employer_id']) {
            flash('error', 'An employer profile is required before you can post a job.');
            redirect('employer/jobs/create');
        }
        [$errors, $data] = $this->jobFields();
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('employer/jobs/create');
        }

        $requested = $this->postString('status', 'pending_review');
        if (!in_array($requested, ['draft', 'pending_review', 'published'], true)) {
            $requested = 'pending_review';
        }
        $moderated = false;
        if ($requested === 'published' && !$ctx['is_admin']) {
            $requested = 'pending_review';
            $moderated = true;
        }

        $data['employer_id'] = $ctx['employer_id'];
        $data['company_id'] = $ctx['company']['id'] ?? null;
        $data['slug'] = $this->uniqueJobSlug((string) $data['title']);
        $data['status'] = $requested;
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($requested === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        $jobId = Database::insert('jobs', $data);
        $this->syncJobSkills($jobId);
        User::logAudit($ctx['user_id'], 'job_create', 'Created job #' . $jobId . ' (' . $requested . ')', $this->request->ip());
        clear_old();
        flash('success', 'Opportunity created successfully.');
        if ($moderated) {
            flash('info', 'Your job was submitted for moderation. It will appear publicly after an administrator reviews it.');
        }
        redirect('employer/jobs');
    }

    public function manageJobs(): void
    {
        $ctx = $this->context();
        [$scope, $params] = $this->jobScope($ctx);
        $status = $this->queryString('status');
        if ($status !== '' && !in_array($status, self::JOB_STATUSES, true)) {
            $status = '';
        }
        $q = $this->queryString('q');
        $where = ["j.deleted_at IS NULL AND $scope"];
        if ($status !== '') {
            $where[] = 'j.status = :status';
            $params['status'] = $status;
        }
        if ($q !== '') {
            $where[] = '(j.title LIKE :q OR j.location LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM jobs j WHERE $sqlWhere",
            $params
        );
        $pager = new Paginator($total, 10, (int) $this->queryString('page', '1'));
        $jobs = Database::all(
            "SELECT j.*, c.name AS company_name,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants_count
             FROM jobs j LEFT JOIN companies c ON c.id = j.company_id
             WHERE $sqlWhere ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderEmployer('jobs', [
            'ctx' => $ctx,
            'jobs' => $jobs,
            'pager' => $pager,
            'status' => $status,
            'q' => $q,
            'statuses' => self::JOB_STATUSES,
            'allowedStatuses' => self::MANAGE_STATUSES,
        ], 'Manage Opportunities');
    }

    public function editJob(): void
    {
        $ctx = $this->context();
        $job = $this->ownedJob((int) $this->request->route('id'), $ctx);
        $this->renderEmployer('job_form', array_merge($this->formOptions($ctx), [
            'ctx' => $ctx,
            'job' => $job,
            'selectedSkills' => $this->selectedSkills((int) $job['id']),
            'formAction' => 'employer/jobs/' . (int) $job['id'] . '/edit',
        ]), 'Edit ' . $job['title']);
    }

    public function updateJob(): void
    {
        $ctx = $this->context();
        $job = $this->ownedJob((int) $this->request->route('id'), $ctx);
        [$errors, $data] = $this->jobFields();
        if ($errors) {
            old_input($this->request->post);
            flash('error', implode(' ', $errors));
            redirect('employer/jobs/' . (int) $job['id'] . '/edit');
        }
        Database::update('jobs', $data, 'id = :id AND deleted_at IS NULL', ['id' => (int) $job['id']]);
        $this->syncJobSkills((int) $job['id']);
        $this->audit('job_update', 'Updated job #' . (int) $job['id']);
        clear_old();
        flash('success', 'Opportunity updated.');
        redirect('employer/jobs');
    }

    public function changeStatus(): void
    {
        $ctx = $this->context();
        $job = $this->ownedJob((int) $this->request->route('id'), $ctx);
        $status = $this->postString('status');
        if (!in_array($status, self::MANAGE_STATUSES, true)) {
            flash('error', 'Choose a valid opportunity status.');
            redirect('employer/jobs');
        }
        if ($status === 'published' && !$ctx['is_admin']) {
            flash('error', 'Only administrators can publish opportunities directly. Your request has been kept as pending review.');
            redirect('employer/jobs');
        }
        $data = ['status' => $status];
        if ($status === 'published' && empty($job['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        Database::update('jobs', $data, 'id = :id', ['id' => (int) $job['id']]);
        $this->audit('job_status', 'Changed job #' . (int) $job['id'] . ' status to ' . $status);
        flash('success', 'Opportunity status changed to ' . str_replace('_', ' ', $status) . '.');
        redirect('employer/jobs');
    }

    public function deleteJob(): void
    {
        $ctx = $this->context();
        $job = $this->ownedJob((int) $this->request->route('id'), $ctx);
        Database::update('jobs', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => (int) $job['id']]);
        $this->audit('job_delete', 'Archived job #' . (int) $job['id']);
        flash('success', 'Opportunity removed from your workspace.');
        redirect('employer/jobs');
    }

    public function applicants(): void
    {
        $ctx = $this->context();
        $filters = [
            'job_id' => (int) $this->queryString('job_id', '0'),
            'status' => $this->queryString('status'),
            'skill' => $this->queryString('skill'),
            'education' => $this->queryString('education'),
        ];
        $statuses = (array) config('application_statuses');
        if ($filters['status'] !== '' && !array_key_exists($filters['status'], $statuses)) {
            $filters['status'] = '';
        }
        $pager = new Paginator($this->applicationCount($ctx, $filters), 12, (int) $this->queryString('page', '1'));
        $applications = $this->applications($ctx, $filters, $pager);

        $this->renderEmployer('applicants', [
            'ctx' => $ctx,
            'applications' => $applications,
            'pager' => $pager,
            'filters' => $filters,
            'jobs' => $this->jobOptions($ctx),
            'statuses' => $statuses,
        ], 'Applicants');
    }

    public function applicantDetail(): void
    {
        $ctx = $this->context();
        $app = $this->ownedApplication((int) $this->request->route('id'), $ctx);
        $userId = (int) $app['user_id'];
        $seeker = Database::one('SELECT * FROM job_seekers WHERE user_id = :id', ['id' => $userId]) ?? [];
        $resumes = Database::all('SELECT * FROM resumes WHERE user_id = :id ORDER BY is_default DESC, id DESC', ['id' => $userId]);
        $skills = Database::all(
            'SELECT s.name, us.level, us.years FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = :id ORDER BY s.name',
            ['id' => $userId]
        );
        $education = Database::all('SELECT * FROM education WHERE user_id = :id ORDER BY end_year DESC', ['id' => $userId]);
        $experience = Database::all('SELECT * FROM experience WHERE user_id = :id ORDER BY start_date DESC', ['id' => $userId]);

        $this->renderEmployer('applicant', [
            'ctx' => $ctx,
            'application' => $app,
            'seeker' => $seeker,
            'resumes' => $resumes,
            'skills' => $skills,
            'education' => $education,
            'experience' => $experience,
            'history' => Application::history((int) $app['id']),
            'statuses' => (array) config('application_statuses'),
        ], 'Applicant: ' . $app['applicant_name']);
    }

    public function updateApplication(): void
    {
        $ctx = $this->context();
        $app = $this->ownedApplication((int) $this->request->route('id'), $ctx);
        $statuses = (array) config('application_statuses');
        $status = $this->postString('status');
        $note = $this->postString('note');
        if (!array_key_exists($status, $statuses)) {
            flash('error', 'Choose a valid application status.');
            redirect('employer/applicants/' . (int) $app['id']);
        }
        if (mb_strlen($note) > 2000) {
            flash('error', 'Notes must not exceed 2000 characters.');
            redirect('employer/applicants/' . (int) $app['id']);
        }
        try {
            Application::updateStatus((int) $app['id'], $status, $ctx['user_id'], $note);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('employer/applicants/' . (int) $app['id']);
        }
        $this->audit('application_status', 'Changed application #' . (int) $app['id'] . ' status to ' . $status);
        flash('success', 'Application status updated and the candidate has been notified.');
        redirect('employer/applicants/' . (int) $app['id']);
    }

    public function candidates(): void
    {
        $ctx = $this->context();
        $filters = [
            'q' => $this->queryString('q'),
            'location' => $this->queryString('location'),
            'skill' => $this->queryString('skill'),
            'education' => $this->queryString('education'),
            'experience' => $this->queryString('experience'),
        ];
        $where = ['js.open_to_work = 1', "u.status = 'active'"];
        $params = [];
        if ($filters['q'] !== '') {
            $where[] = '(u.name LIKE :q OR js.headline LIKE :q OR js.summary LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if ($filters['location'] !== '') {
            $where[] = '(js.location LIKE :loc OR js.preferred_location LIKE :loc)';
            $params['loc'] = '%' . $filters['location'] . '%';
        }
        if ($filters['skill'] !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = u.id AND s.name LIKE :skill)';
            $params['skill'] = '%' . $filters['skill'] . '%';
        }
        if ($filters['education'] !== '') {
            $where[] = 'js.education_level LIKE :education';
            $params['education'] = '%' . $filters['education'] . '%';
        }
        if ($filters['experience'] !== '' && is_numeric($filters['experience'])) {
            $where[] = 'js.experience_years >= :experience';
            $params['experience'] = (float) $filters['experience'];
        }
        $sqlWhere = implode(' AND ', $where);
        $from = 'FROM job_seekers js JOIN users u ON u.id = js.user_id';
        $total = (int) Database::scalar("SELECT COUNT(*) $from WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 12, (int) $this->queryString('page', '1'));
        $candidates = Database::all(
            "SELECT u.id AS user_id, u.name, u.email,
                    js.headline, js.summary, js.location, js.preferred_location,
                    js.experience_years, js.education_level,
                    (SELECT GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') FROM user_skills us
                     JOIN skills s ON s.id = us.skill_id WHERE us.user_id = u.id) AS skills
             $from WHERE $sqlWhere ORDER BY u.name LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderEmployer('candidates', [
            'ctx' => $ctx,
            'candidates' => $candidates,
            'pager' => $pager,
            'filters' => $filters,
        ], 'Find Candidates');
    }

    public function shortlisted(): void
    {
        $ctx = $this->context();
        [$scope, $params] = $this->jobScope($ctx);
        [$placeholders, $inParams] = Database::in(self::SHORTLIST_STATUSES);
        $rows = Database::all(
            'SELECT ' . self::APPLICATION_SELECT . '
             FROM applications a JOIN jobs j ON j.id = a.job_id JOIN users u ON u.id = a.user_id
             LEFT JOIN job_seekers js ON js.user_id = u.id
             WHERE ' . $scope . ' AND a.status IN (' . $placeholders . ') ORDER BY a.created_at DESC',
            array_merge($params, $inParams)
        );
        $this->renderEmployer('shortlisted', [
            'ctx' => $ctx,
            'applications' => $rows,
            'statuses' => (array) config('application_statuses'),
        ], 'Shortlisted Candidates');
    }

    public function interviews(): void
    {
        $ctx = $this->context();
        $adminOverview = $ctx['is_admin'] && $ctx['employer_id'] === 0;
        $where = $adminOverview ? '1=1' : 'i.employer_id = :uid';
        $params = $adminOverview ? [] : ['uid' => $ctx['user_id']];
        $interviews = Database::all(
            "SELECT i.*, u.name AS applicant_name, j.title AS job_title
             FROM interviews i JOIN users u ON u.id = i.applicant_id JOIN jobs j ON j.id = i.job_id
             WHERE $where ORDER BY i.scheduled_at DESC LIMIT 100",
            $params
        );
        $this->renderEmployer('interviews', [
            'ctx' => $ctx,
            'interviews' => $interviews,
            'applications' => $this->jobApplicationsForForm($ctx),
            'statuses' => self::INTERVIEW_STATUSES,
        ], 'Interviews');
    }

    private function jobApplicationsForForm(array $ctx): array
    {
        [$scope, $params] = $this->jobScope($ctx);
        return Database::all(
            'SELECT a.id, a.status, j.title AS job_title, u.name AS applicant_name
             FROM applications a JOIN jobs j ON j.id = a.job_id JOIN users u ON u.id = a.user_id
             WHERE ' . $scope . ' ORDER BY a.created_at DESC LIMIT 200',
            $params
        );
    }

    public function scheduleInterview(): void
    {
        $ctx = $this->context();
        $app = $this->ownedApplication($this->postInt('application_id'), $ctx);
        $errors = [];
        $rawWhen = $this->postString('scheduled_at');
        $when = \DateTime::createFromFormat('!Y-m-d\TH:i', $rawWhen);
        if (!$when || $when->format('Y-m-d\TH:i') !== $rawWhen) {
            $when = \DateTime::createFromFormat('!Y-m-d H:i:s', $rawWhen);
            if (!$when || $when->format('Y-m-d H:i:s') !== $rawWhen) {
                $errors['scheduled_at'] = 'Choose a valid date and time.';
            }
        }
        $duration = $this->postInt('duration_minutes', 45);
        if ($duration < 15 || $duration > 240) {
            $errors['duration_minutes'] = 'Duration must be between 15 and 240 minutes.';
        }
        $location = $this->postString('location');
        $link = $this->postString('meeting_link');
        if ($location === '' && $link === '') {
            $errors['location'] = 'Provide a location or a meeting link.';
        }
        if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
            $errors['meeting_link'] = 'Enter a valid meeting URL starting with http:// or https://.';
        }
        $notes = $this->postString('notes');
        if (mb_strlen($notes) > 2000) {
            $errors['notes'] = 'Notes must not exceed 2000 characters.';
        }
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('employer/interviews');
        }

        $scheduledAt = $when->format('Y-m-d H:i:s');
        $interviewId = Database::insert('interviews', [
            'application_id' => (int) $app['id'],
            'employer_id' => $ctx['user_id'],
            'applicant_id' => (int) $app['user_id'],
            'job_id' => (int) $app['job_id'],
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $duration,
            'location' => $location !== '' ? $location : null,
            'meeting_link' => $link !== '' ? $link : null,
            'notes' => $notes !== '' ? $notes : null,
            'status' => 'scheduled',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        try {
            Application::updateStatus((int) $app['id'], 'interview_scheduled', $ctx['user_id'], 'Interview scheduled for ' . $scheduledAt);
        } catch (\RuntimeException $e) {
            Database::delete('interviews', 'id = :id', ['id' => $interviewId]);
            flash('error', $e->getMessage());
            redirect('employer/interviews');
        }
        $this->audit('interview_schedule', 'Scheduled interview #' . $interviewId . ' for application #' . (int) $app['id']);
        flash('success', 'Interview scheduled and the candidate has been notified.');
        redirect('employer/interviews');
    }

    public function interviewStatus(): void
    {
        $ctx = $this->context();
        $id = (int) $this->request->route('id');
        $where = 'i.id = :id';
        $params = ['id' => $id];
        if (!$ctx['is_admin']) {
            $where .= ' AND i.employer_id = :uid';
            $params['uid'] = $ctx['user_id'];
        }
        $interview = Database::one('SELECT i.* FROM interviews i WHERE ' . $where, $params);
        if (!$interview) {
            abort(404, 'Interview not found.');
        }
        $status = $this->postString('status');
        if (!in_array($status, self::INTERVIEW_STATUSES, true)) {
            flash('error', 'Choose a valid interview status.');
            redirect('employer/interviews');
        }
        Database::update('interviews', ['status' => $status], 'id = :id', ['id' => $id]);
        if ($status === 'completed') {
            try {
                Application::updateStatus((int) $interview['application_id'], 'interview_completed', $ctx['user_id'], 'Interview completed');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('employer/interviews');
            }
        }
        $this->audit('interview_status', 'Changed interview #' . $id . ' status to ' . $status);
        flash('success', 'Interview marked as ' . $status . '.');
        redirect('employer/interviews');
    }

    public function analytics(): void
    {
        $ctx = $this->context();
        [$scope, $params] = $this->jobScope($ctx);
        $stats = Admin::employerStats($ctx['user_id']);

        $statusRows = Database::all(
            "SELECT a.status, COUNT(*) AS total FROM applications a JOIN jobs j ON j.id = a.job_id
             WHERE $scope GROUP BY a.status",
            $params
        );
        $byStatus = [];
        foreach ($statusRows as $row) {
            $byStatus[$row['status']] = (int) $row['total'];
        }
        $monthly = Database::all(
            "SELECT DATE_FORMAT(a.created_at, '%Y-%m') AS period, COUNT(*) AS total
             FROM applications a JOIN jobs j ON j.id = a.job_id
             WHERE $scope AND a.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY period ORDER BY period",
            $params
        );
        $daily = Database::all(
            "SELECT DATE(a.created_at) AS period, COUNT(*) AS total
             FROM applications a JOIN jobs j ON j.id = a.job_id
             WHERE $scope AND a.created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
             GROUP BY period ORDER BY period",
            $params
        );
        $topJobs = Database::all(
            "SELECT j.id, j.title, j.views_count, COUNT(a.id) AS applicants_count
             FROM jobs j LEFT JOIN applications a ON a.job_id = j.id
             WHERE j.deleted_at IS NULL AND $scope
             GROUP BY j.id ORDER BY applicants_count DESC, j.views_count DESC LIMIT 6",
            $params
        );
        $topSkills = Database::all(
            "SELECT s.name, COUNT(*) AS total FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN user_skills us ON us.user_id = a.user_id JOIN skills s ON s.id = us.skill_id
             WHERE $scope GROUP BY s.id, s.name ORDER BY total DESC LIMIT 8",
            $params
        );

        $this->renderEmployer('analytics', [
            'ctx' => $ctx,
            'stats' => $stats,
            'byStatus' => $byStatus,
            'statuses' => (array) config('application_statuses'),
            'monthly' => $monthly,
            'daily' => $daily,
            'topJobs' => $topJobs,
            'topSkills' => $topSkills,
        ], 'Employer Analytics');
    }
}
