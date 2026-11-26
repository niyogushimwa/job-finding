<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Models\Admin;
use App\Models\Opportunity;
use App\Models\User;

final class AdminController extends Controller
{
    private const ROLES = ['seeker', 'employer', 'freelancer', 'admin'];
    private const USER_STATUSES = ['active', 'pending', 'suspended', 'deactivated'];
    private const EMPLOYER_STATUSES = ['unverified', 'pending', 'verified', 'rejected'];
    private const JOB_STATUSES = ['draft', 'pending_review', 'published', 'closed', 'archived', 'rejected'];
    private const EXTERNAL_STATUSES = ['draft', 'published', 'archived', 'expired'];
    private const EXTERNAL_VERIFICATION = ['unverified', 'verified', 'rejected'];
    private const REPORT_TYPES = ['job', 'employer', 'external_opportunity', 'user', 'community_post'];
    private const REPORT_REASONS = ['scam', 'fake_job', 'payment_request', 'misleading_salary', 'inappropriate', 'fraud', 'other'];
    private const REPORT_STATUSES = ['open', 'investigating', 'resolved', 'dismissed'];
    private const QUESTION_CATEGORIES = ['common', 'technical', 'hr', 'behavioral'];
    private const AD_POSITIONS = ['home_banner', 'sidebar', 'footer', 'radar'];
    private const SETTING_KEYS = ['site_name', 'tagline', 'maintenance_mode', 'jobs_require_approval', 'radar_note', 'contact_email', 'min_password_length'];

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function renderAdmin(string $view, array $data = [], string $title = ''): void
    {
        $this->render('admin.' . $view, $data, $title ?: 'Administration');
    }

    private function query(string $key): string
    {
        return trim((string) $this->request->query($key, ''));
    }

    private function postString(string $key, string $default = ''): string
    {
        return trim((string) $this->request->post($key, $default));
    }

    private function whitelist(string $value, array $allowed): string
    {
        return in_array($value, $allowed, true) ? $value : '';
    }

    private function idOrNull(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '0' || filter_var($value, FILTER_VALIDATE_INT) === false) {
            return null;
        }
        return (int) $value;
    }

    private function maybeDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time === false ? null : date('Y-m-d', $time);
    }

    private function maybeDateTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time === false ? null : date('Y-m-d H:i:s', $time);
    }

    private function pageNumber(): int
    {
        return max(1, (int) $this->request->query('page', 1));
    }

    /* ------------------------------------------------------------------ */
    /* Overview                                                            */
    /* ------------------------------------------------------------------ */

    public function dashboard(): void
    {
        $stats = Admin::platformStats();
        $trend = Admin::applicationTrend(14);
        $categories = Admin::popularCategories();
        $topJobs = Admin::mostViewedJobs();
        $reports = Database::all(
            'SELECT r.*, u.name AS reporter_name FROM reports r
             LEFT JOIN users u ON u.id = r.reporter_id
             WHERE r.status IN (\'open\',\'investigating\')
             ORDER BY r.created_at DESC LIMIT 6'
        );
        $users = Database::all(
            'SELECT id, name, email, role, status, created_at FROM users
             WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 6'
        );
        $announcements = Database::all(
            'SELECT a.*, u.name AS admin_name FROM announcements a
             LEFT JOIN users u ON u.id = a.admin_id
             ORDER BY a.created_at DESC LIMIT 5'
        );

        $this->renderAdmin('dashboard', compact('stats', 'trend', 'categories', 'topJobs', 'reports', 'users', 'announcements'), 'Administration overview');
    }

    /* ------------------------------------------------------------------ */
    /* Users                                                               */
    /* ------------------------------------------------------------------ */

    public function users(): void
    {
        $role = $this->whitelist($this->query('role'), self::ROLES);
        $status = $this->whitelist($this->query('status'), self::USER_STATUSES);
        $q = $this->query('q');

        $where = ['u.deleted_at IS NULL'];
        $params = [];
        if ($role !== '') {
            $where[] = 'u.role = :role';
            $params['role'] = $role;
        }
        if ($status !== '') {
            $where[] = 'u.status = :status';
            $params['status'] = $status;
        }
        if ($q !== '') {
            $where[] = '(u.name LIKE :q OR u.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) FROM users u WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 15, $this->pageNumber());
        $rows = Database::all(
            "SELECT u.*, e.id AS employer_profile_id,
                    (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = e.id) AS jobs_count
             FROM users u LEFT JOIN employers e ON e.user_id = u.id
             WHERE $sqlWhere ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderAdmin('users', compact('rows', 'pager', 'role', 'status', 'q'), 'User management');
    }

    public function userStatus(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), self::USER_STATUSES);
        if ($id <= 0 || $status === '') {
            flash('error', 'Invalid account status request.');
            redirect('admin/users');
        }
        if ($id === (int) Auth::id()) {
            flash('error', 'You cannot change your own account status.');
            redirect('admin/users');
        }
        $user = Database::one('SELECT id, name, status FROM users WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
        if (!$user) {
            flash('error', 'That account could not be found.');
            redirect('admin/users');
        }

        Database::update('users', ['status' => $status], 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'user_status',
            'Changed ' . $user['name'] . ' status from ' . $user['status'] . ' to ' . $status,
            $this->request->ip()
        );
        flash('success', 'Account status updated to ' . $status . '.');
        redirect('admin/users');
    }

    /* ------------------------------------------------------------------ */
    /* Employers                                                           */
    /* ------------------------------------------------------------------ */

    public function employers(): void
    {
        $status = $this->whitelist($this->query('status'), self::EMPLOYER_STATUSES);
        $q = $this->query('q');

        $where = ['c.deleted_at IS NULL'];
        $params = [];
        if ($status !== '') {
            $where[] = 'c.verification_status = :status';
            $params['status'] = $status;
        }
        if ($q !== '') {
            $where[] = '(c.name LIKE :q OR u.name LIKE :q OR u.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $rows = Database::all(
            "SELECT c.id, c.name, c.slug, c.location, c.website, c.verification_status, c.verified_at, c.created_at,
                    u.id AS user_id, u.name AS owner_name, u.email AS owner_email, u.status AS user_status,
                    (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.deleted_at IS NULL) AS jobs_count
             FROM companies c
             LEFT JOIN employers e ON e.company_id = c.id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE $sqlWhere ORDER BY c.created_at DESC",
            $params
        );

        $this->renderAdmin('employers', compact('rows', 'status', 'q'), 'Employer verification');
    }

    public function verifyEmployer(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), self::EMPLOYER_STATUSES);
        if ($id <= 0 || $status === '') {
            flash('error', 'Invalid verification request.');
            redirect('admin/employers');
        }
        $company = Database::one('SELECT id, name, verification_status FROM companies WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
        if (!$company) {
            flash('error', 'That company could not be found.');
            redirect('admin/employers');
        }

        $data = ['verification_status' => $status];
        if ($status === 'verified') {
            $data['verified_by'] = (int) Auth::id();
            $data['verified_at'] = date('Y-m-d H:i:s');
        } else {
            $data['verified_by'] = null;
            $data['verified_at'] = null;
        }
        Database::update('companies', $data, 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'employer_verification',
            'Set ' . $company['name'] . ' verification from ' . $company['verification_status'] . ' to ' . $status,
            $this->request->ip()
        );
        flash('success', 'Company verification updated to ' . $status . '.');
        redirect('admin/employers');
    }

    /* ------------------------------------------------------------------ */
    /* Jobs                                                                */
    /* ------------------------------------------------------------------ */

    public function jobs(): void
    {
        $status = $this->whitelist($this->query('status'), self::JOB_STATUSES);
        $q = $this->query('q');

        $joins = ' FROM jobs j
                   LEFT JOIN companies c ON c.id = j.company_id
                   LEFT JOIN employers e ON e.id = j.employer_id
                   LEFT JOIN users u ON u.id = e.user_id
                   LEFT JOIN job_categories cat ON cat.id = j.category_id ';
        $where = ['j.deleted_at IS NULL'];
        $params = [];
        if ($status !== '') {
            $where[] = 'j.status = :status';
            $params['status'] = $status;
        }
        if ($q !== '') {
            $where[] = '(j.title LIKE :q OR c.name LIKE :q OR u.name LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) $joins WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 15, $this->pageNumber());
        $rows = Database::all(
            "SELECT j.id, j.title, j.slug, j.status, j.location, j.deadline, j.views_count,
                    j.applications_count, j.created_at, j.published_at,
                    c.name AS company_name, u.name AS employer_name, cat.name AS category_name
             $joins WHERE $sqlWhere ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderAdmin('jobs', compact('rows', 'pager', 'status', 'q'), 'Job moderation');
    }

    public function moderateJob(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), self::JOB_STATUSES);
        if ($id <= 0 || $status === '') {
            flash('error', 'Invalid moderation request.');
            redirect('admin/jobs');
        }
        $job = Database::one('SELECT id, title, status, published_at FROM jobs WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
        if (!$job) {
            flash('error', 'That job could not be found.');
            redirect('admin/jobs');
        }

        $data = ['status' => $status];
        if ($status === 'published' && empty($job['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        Database::update('jobs', $data, 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'job_moderation',
            'Changed job "' . $job['title'] . '" from ' . $job['status'] . ' to ' . $status,
            $this->request->ip()
        );
        flash('success', 'Job status updated to ' . $status . '.');
        redirect('admin/jobs');
    }

    /* ------------------------------------------------------------------ */
    /* External opportunities                                              */
    /* ------------------------------------------------------------------ */

    public function external(): void
    {
        $status = $this->whitelist($this->query('status'), self::EXTERNAL_STATUSES);
        $verification = $this->whitelist($this->query('verification'), self::EXTERNAL_VERIFICATION);
        $q = $this->query('q');

        $joins = ' FROM external_opportunities e
                   LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
                   LEFT JOIN countries co ON co.id = e.country_id
                   LEFT JOIN job_categories cat ON cat.id = e.category_id ';
        $where = ['1=1'];
        $params = [];
        if ($status !== '') {
            $where[] = 'e.status = :status';
            $params['status'] = $status;
        }
        if ($verification !== '') {
            $where[] = 'e.verification_status = :verification';
            $params['verification'] = $verification;
        }
        if ($q !== '') {
            $where[] = '(e.title LIKE :q OR e.organization LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) $joins WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 15, $this->pageNumber());
        $rows = Database::all(
            "SELECT e.*, oc.name AS opportunity_category_name, co.name AS country_name, cat.name AS category_name
             $joins WHERE $sqlWhere ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderAdmin('external', compact('rows', 'pager', 'status', 'verification', 'q'), 'External opportunities');
    }

    private function externalFormData(?array $opportunity, string $action): array
    {
        return [
            'opportunity' => $opportunity,
            'formAction' => $action,
            'opportunityCategories' => Database::all('SELECT id, name FROM opportunity_categories ORDER BY name'),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
            'jobCategories' => Database::all('SELECT id, name FROM job_categories WHERE active = 1 ORDER BY name'),
            'statuses' => self::EXTERNAL_STATUSES,
            'verificationStatuses' => self::EXTERNAL_VERIFICATION,
        ];
    }

    public function createExternal(): void
    {
        $data = $this->externalFormData(null, url('admin/external-opportunities'));
        $this->renderAdmin('external_form', $data, 'Add external opportunity');
    }

    public function editExternal(): void
    {
        $id = (int) $this->request->route('id');
        $opportunity = $id > 0 ? Opportunity::findExternal($id) : null;
        if (!$opportunity) {
            flash('error', 'That opportunity could not be found.');
            redirect('admin/external-opportunities');
        }
        $data = $this->externalFormData($opportunity, url('admin/external-opportunities/' . $id . '/edit'));
        $this->renderAdmin('external_form', $data, 'Edit external opportunity');
    }

    private function saveExternal(?int $id): void
    {
        $back = $id > 0 ? 'admin/external-opportunities/' . $id . '/edit' : 'admin/external-opportunities/create';
        if ($id !== null && $id <= 0) {
            flash('error', 'Invalid opportunity.');
            redirect('admin/external-opportunities');
        }
        if ($id !== null && !Database::one('SELECT id FROM external_opportunities WHERE id = :id', ['id' => $id])) {
            flash('error', 'That opportunity could not be found.');
            redirect('admin/external-opportunities');
        }

        $errors = $this->validate([
            'title' => 'required|max:220',
            'organization' => 'required|max:180',
            'application_url' => 'required|url|max:500',
            'source_url' => 'url|max:500',
            'deadline' => 'date',
        ]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please correct the highlighted fields.');
            redirect($back);
        }

        $data = [
            'title' => $this->postString('title'),
            'organization' => $this->postString('organization'),
            'application_url' => $this->postString('application_url'),
        ];
        $duplicate = Opportunity::duplicate($data, $id);
        // The model's title/organisation fallback does not exclude the edited record.
        if ($duplicate && $id !== null && (int) $duplicate['id'] === $id) {
            $duplicate = null;
        }
        $duplicateOverridden = false;
        if ($duplicate) {
            if ($this->postString('force') !== '1') {
                old_input($this->request->post);
                flash('warning', 'This looks like a duplicate of opportunity #' . $duplicate['id'] . ' (' . $duplicate['title'] . '). Tick "Override duplicate" to save anyway.');
                redirect($back);
            }
            $duplicateOverridden = true;
        }

        $status = $this->whitelist($this->postString('status', 'draft'), self::EXTERNAL_STATUSES) ?: 'draft';
        $verification = $this->whitelist($this->postString('verification_status', 'unverified'), self::EXTERNAL_VERIFICATION) ?: 'unverified';
        $sourceUrl = $this->postString('source_url');

        $record = [
            'title' => $data['title'],
            'organization' => $data['organization'],
            'description' => $this->postString('description') ?: null,
            'requirements' => $this->postString('requirements') ?: null,
            'location' => $this->postString('location') ?: null,
            'country_id' => $this->idOrNull($this->request->post('country_id')),
            'category_id' => $this->idOrNull($this->request->post('category_id')),
            'opportunity_category_id' => $this->idOrNull($this->request->post('opportunity_category_id')),
            'deadline' => $this->maybeDate($this->postString('deadline')),
            'salary' => $this->postString('salary') ?: null,
            'currency' => $this->postString('currency') ?: null,
            'source_name' => $this->postString('source_name') ?: null,
            'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
            'application_url' => $data['application_url'],
            'application_instructions' => $this->postString('application_instructions') ?: null,
            'status' => $status,
            'verification_status' => $verification,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
            'fingerprint' => Opportunity::fingerprint($data),
            'is_featured' => $this->request->post('is_featured') ? 1 : 0,
        ];
        if ($verification === 'verified') {
            $record['verified_by'] = (int) Auth::id();
            $record['verified_at'] = date('Y-m-d H:i:s');
        } else {
            $record['verified_by'] = null;
            $record['verified_at'] = null;
        }

        if ($id === null) {
            $record['created_by'] = (int) Auth::id();
            $record['discovered_at'] = date('Y-m-d H:i:s');
            $newId = Database::insert('external_opportunities', $record);
            User::logAudit(
                (int) Auth::id(),
                'external_created',
                'Created external opportunity "' . $data['title'] . '" (#' . $newId . ')',
                $this->request->ip()
            );
            flash('success', 'External opportunity created.');
        } else {
            Database::update('external_opportunities', $record, 'id = :id', ['id' => $id]);
            User::logAudit(
                (int) Auth::id(),
                'external_updated',
                'Updated external opportunity "' . $data['title'] . '" (#' . $id . ')',
                $this->request->ip()
            );
            flash('success', 'External opportunity updated.');
        }
        if ($duplicateOverridden) {
            flash('warning', 'Duplicate warning overridden — the opportunity was saved.');
        }
        clear_old();
        redirect('admin/external-opportunities');
    }

    public function storeExternal(): void
    {
        $this->saveExternal(null);
    }

    public function updateExternal(): void
    {
        $this->saveExternal((int) $this->request->route('id'));
    }

    public function externalStatus(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), self::EXTERNAL_STATUSES);
        if ($id <= 0 || $status === '') {
            flash('error', 'Invalid status request.');
            redirect('admin/external-opportunities');
        }
        $row = Database::one('SELECT id, title, published_at FROM external_opportunities WHERE id = :id', ['id' => $id]);
        if (!$row) {
            flash('error', 'That opportunity could not be found.');
            redirect('admin/external-opportunities');
        }

        $data = ['status' => $status];
        if ($status === 'published' && empty($row['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        Database::update('external_opportunities', $data, 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'external_status',
            'Changed external opportunity "' . $row['title'] . '" to ' . $status,
            $this->request->ip()
        );
        flash('success', 'Opportunity status updated to ' . $status . '.');
        redirect('admin/external-opportunities');
    }

    public function verifyExternal(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), self::EXTERNAL_VERIFICATION);
        if ($id <= 0 || $status === '') {
            flash('error', 'Invalid verification request.');
            redirect('admin/external-opportunities');
        }
        $row = Database::one('SELECT id, title, verification_status FROM external_opportunities WHERE id = :id', ['id' => $id]);
        if (!$row) {
            flash('error', 'That opportunity could not be found.');
            redirect('admin/external-opportunities');
        }

        $data = ['verification_status' => $status];
        if ($status === 'verified') {
            $data['verified_by'] = (int) Auth::id();
            $data['verified_at'] = date('Y-m-d H:i:s');
        } else {
            $data['verified_by'] = null;
            $data['verified_at'] = null;
        }
        Database::update('external_opportunities', $data, 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'external_verification',
            'Set opportunity "' . $row['title'] . '" verification from ' . $row['verification_status'] . ' to ' . $status,
            $this->request->ip()
        );
        flash('success', 'Opportunity verification updated to ' . $status . '.');
        redirect('admin/external-opportunities');
    }

    public function deleteExternal(): void
    {
        $id = (int) $this->request->route('id');
        if ($id <= 0) {
            flash('error', 'Invalid delete request.');
            redirect('admin/external-opportunities');
        }
        $row = Database::one('SELECT id, title FROM external_opportunities WHERE id = :id', ['id' => $id]);
        if (!$row) {
            flash('error', 'That opportunity could not be found.');
            redirect('admin/external-opportunities');
        }
        Database::delete('external_opportunities', 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'external_deleted',
            'Deleted external opportunity "' . $row['title'] . '" (#' . $id . ')',
            $this->request->ip()
        );
        flash('success', 'External opportunity deleted.');
        redirect('admin/external-opportunities');
    }

    /* ------------------------------------------------------------------ */
    /* Categories                                                          */
    /* ------------------------------------------------------------------ */

    public function categories(): void
    {
        $jobCategories = Database::all(
            'SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM jobs j WHERE j.category_id = c.id) AS jobs_count
             FROM job_categories c LEFT JOIN job_categories p ON p.id = c.parent_id
             ORDER BY c.name'
        );
        $opportunityCategories = Database::all(
            'SELECT oc.*,
                    (SELECT COUNT(*) FROM external_opportunities e WHERE e.opportunity_category_id = oc.id) AS opportunities_count
             FROM opportunity_categories oc ORDER BY oc.name'
        );
        $parents = Database::all('SELECT id, name FROM job_categories ORDER BY name');

        $this->renderAdmin('categories', compact('jobCategories', 'opportunityCategories', 'parents'), 'Categories');
    }

    public function storeCategory(): void
    {
        $type = $this->whitelist($this->postString('type'), ['job', 'opportunity']);
        if ($type === '') {
            flash('error', 'Choose a valid category type.');
            redirect('admin/categories');
        }
        $errors = $this->validate([
            'name' => 'required|max:120',
            'slug' => 'max:140',
        ]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please provide a category name.');
            redirect('admin/categories');
        }

        $name = $this->postString('name');
        $slug = $this->postString('slug');
        if ($slug === '') {
            $slug = slugify($name);
        }
        if ($slug === '') {
            flash('error', 'A valid slug is required.');
            redirect('admin/categories');
        }

        $table = $type === 'job' ? 'job_categories' : 'opportunity_categories';
        if (Database::scalar("SELECT COUNT(*) FROM `$table` WHERE slug = :slug", ['slug' => $slug])) {
            old_input($this->request->post);
            flash('error', 'That slug is already in use. Please choose another one.');
            redirect('admin/categories');
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->postString('description') ?: null,
            'active' => 1,
        ];
        if ($type === 'job') {
            $icon = $this->postString('icon');
            $data['icon'] = $icon !== '' && preg_match('/^[a-z0-9_-]+$/i', $icon) ? $icon : null;
            $parentId = $this->idOrNull($this->request->post('parent_id'));
            if ($parentId !== null && !Database::one('SELECT id FROM job_categories WHERE id = :id', ['id' => $parentId])) {
                flash('error', 'The selected parent category does not exist.');
                redirect('admin/categories');
            }
            $data['parent_id'] = $parentId;
        }

        Database::insert($table, $data);
        User::logAudit(
            (int) Auth::id(),
            'category_created',
            'Created ' . $type . ' category "' . $name . '"',
            $this->request->ip()
        );
        clear_old();
        flash('success', 'Category created.');
        redirect('admin/categories');
    }

    public function deleteCategory(): void
    {
        $type = $this->whitelist($this->postString('type'), ['job', 'opportunity']);
        $id = (int) $this->request->route('id');
        if ($type === '' || $id <= 0) {
            flash('error', 'Invalid delete request.');
            redirect('admin/categories');
        }
        $table = $type === 'job' ? 'job_categories' : 'opportunity_categories';
        $row = Database::one("SELECT id, name FROM `$table` WHERE id = :id", ['id' => $id]);
        if (!$row) {
            flash('error', 'That category could not be found.');
            redirect('admin/categories');
        }

        Database::delete($table, 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'category_deleted',
            'Deleted ' . $type . ' category "' . $row['name'] . '" (#' . $id . ')',
            $this->request->ip()
        );
        flash('success', 'Category deleted. Linked records were left intact.');
        redirect('admin/categories');
    }

    /* ------------------------------------------------------------------ */
    /* Verification queue                                                  */
    /* ------------------------------------------------------------------ */

    public function verification(): void
    {
        $companies = Database::all(
            "SELECT c.id, c.name, c.location, c.verification_status, c.created_at,
                    u.name AS owner_name, u.email AS owner_email
             FROM companies c
             LEFT JOIN employers e ON e.company_id = c.id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE c.deleted_at IS NULL AND c.verification_status IN ('unverified','pending')
             ORDER BY c.created_at DESC LIMIT 50"
        );
        $externals = Database::all(
            "SELECT id, title, organization, status, verification_status, application_url, discovered_at
             FROM external_opportunities WHERE verification_status = 'unverified'
             ORDER BY discovered_at DESC LIMIT 50"
        );
        $jobs = Database::all(
            "SELECT j.id, j.title, j.created_at, c.name AS company_name
             FROM jobs j LEFT JOIN companies c ON c.id = j.company_id
             WHERE j.deleted_at IS NULL AND j.status = 'pending_review'
             ORDER BY j.created_at DESC LIMIT 50"
        );
        $certifications = Database::all(
            'SELECT cert.id, cert.name, cert.issuer, cert.credential_url, cert.created_at,
                    u.name AS user_name, u.email AS user_email
             FROM certifications cert JOIN users u ON u.id = cert.user_id
             WHERE cert.verified = 0 ORDER BY cert.created_at DESC LIMIT 50'
        );

        $this->renderAdmin('verification', compact('companies', 'externals', 'jobs', 'certifications'), 'Verification queue');
    }

    /* ------------------------------------------------------------------ */
    /* Reports                                                             */
    /* ------------------------------------------------------------------ */

    public function reports(): void
    {
        $type = $this->whitelist($this->query('type'), self::REPORT_TYPES);
        $reason = $this->whitelist($this->query('reason'), self::REPORT_REASONS);
        $status = $this->whitelist($this->query('status'), self::REPORT_STATUSES);

        $where = ['1=1'];
        $params = [];
        if ($type !== '') {
            $where[] = 'r.type = :type';
            $params['type'] = $type;
        }
        if ($reason !== '') {
            $where[] = 'r.reason = :reason';
            $params['reason'] = $reason;
        }
        if ($status !== '') {
            $where[] = 'r.status = :status';
            $params['status'] = $status;
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) FROM reports r WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 15, $this->pageNumber());
        $rows = Database::all(
            "SELECT r.*, u.name AS reporter_name
             FROM reports r LEFT JOIN users u ON u.id = r.reporter_id
             WHERE $sqlWhere ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );
        $messages = Database::all('SELECT id, name, email, subject, message, status, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 20');

        $this->renderAdmin('reports', compact('rows', 'pager', 'messages', 'type', 'reason', 'status'), 'Reports and messages');
    }

    public function resolveReport(): void
    {
        $id = (int) $this->request->route('id');
        $status = $this->whitelist($this->postString('status'), ['resolved', 'dismissed', 'investigating']);
        $errors = $this->validate(['resolution' => 'max:2000']);
        if ($id <= 0 || $status === '' || $errors) {
            flash('error', 'Invalid resolution request or the note is too long.');
            redirect('admin/reports');
        }
        $report = Database::one('SELECT id, status FROM reports WHERE id = :id', ['id' => $id]);
        if (!$report) {
            flash('error', 'That report could not be found.');
            redirect('admin/reports');
        }

        $note = $this->postString('resolution');
        Database::update('reports', [
            'status' => $status,
            'handled_by' => (int) Auth::id(),
            'resolution' => $note !== '' ? $note : null,
        ], 'id = :id', ['id' => $id]);
        Database::insert('moderation_logs', [
            'report_id' => $id,
            'admin_id' => (int) Auth::id(),
            'action' => $status,
            'notes' => $note !== '' ? $note : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        User::logAudit(
            (int) Auth::id(),
            'report_resolved',
            'Marked report #' . $id . ' as ' . $status,
            $this->request->ip()
        );
        flash('success', 'Report marked as ' . $status . '.');
        redirect('admin/reports');
    }

    /* ------------------------------------------------------------------ */
    /* Content                                                             */
    /* ------------------------------------------------------------------ */

    public function content(): void
    {
        $counts = [
            'articles' => (int) Database::scalar('SELECT COUNT(*) FROM articles'),
            'questions' => (int) Database::scalar('SELECT COUNT(*) FROM interview_questions'),
            'tips' => (int) Database::scalar('SELECT COUNT(*) FROM career_tips'),
            'stories' => (int) Database::scalar('SELECT COUNT(*) FROM success_stories'),
            'newsletters' => (int) Database::scalar('SELECT COUNT(*) FROM newsletter_subscribers'),
        ];
        $articles = Database::all(
            'SELECT a.*, u.name AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.author_id ORDER BY a.created_at DESC LIMIT 15'
        );
        $questions = Database::all('SELECT * FROM interview_questions ORDER BY id DESC LIMIT 15');

        $this->renderAdmin('content', compact('counts', 'articles', 'questions'), 'Content management');
    }

    public function storeArticle(): void
    {
        $errors = $this->validate([
            'title' => 'required|max:220',
            'slug' => 'max:240',
            'body' => 'required',
            'category' => 'max:80',
            'status' => 'in:draft,published,archived',
        ]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please correct the highlighted article fields.');
            redirect('admin/content');
        }

        $title = $this->postString('title');
        $slug = $this->postString('slug');
        if ($slug === '') {
            $slug = slugify($title);
        }
        $status = $this->whitelist($this->postString('status', 'draft'), ['draft', 'published', 'archived']) ?: 'draft';
        if ($slug === '') {
            flash('error', 'A valid slug is required.');
            redirect('admin/content');
        }
        if (Database::scalar('SELECT COUNT(*) FROM articles WHERE slug = :slug', ['slug' => $slug])) {
            old_input($this->request->post);
            flash('error', 'That slug is already used by another article.');
            redirect('admin/content');
        }

        Database::insert('articles', [
            'author_id' => (int) Auth::id(),
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->postString('excerpt') ?: null,
            'body' => (string) $this->request->post('body', ''),
            'category' => $this->postString('category') ?: null,
            'status' => $status,
            'featured' => $this->request->post('featured') ? 1 : 0,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
        ]);
        User::logAudit(
            (int) Auth::id(),
            'article_created',
            'Created article "' . $title . '" with status ' . $status,
            $this->request->ip()
        );
        clear_old();
        flash('success', 'Article saved.');
        redirect('admin/content');
    }

    public function deleteArticle(): void
    {
        $id = (int) $this->request->route('id');
        if ($id <= 0) {
            flash('error', 'Invalid delete request.');
            redirect('admin/content');
        }
        $article = Database::one('SELECT id, title FROM articles WHERE id = :id', ['id' => $id]);
        if (!$article) {
            flash('error', 'That article could not be found.');
            redirect('admin/content');
        }
        Database::delete('articles', 'id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'article_deleted',
            'Deleted article "' . $article['title'] . '" (#' . $id . ')',
            $this->request->ip()
        );
        flash('success', 'Article deleted.');
        redirect('admin/content');
    }

    public function storeQuestion(): void
    {
        $errors = $this->validate([
            'question' => 'required',
            'sample_answer' => 'max:5000',
            'tips' => 'max:2000',
        ]);
        $category = $this->whitelist($this->postString('category', 'common'), self::QUESTION_CATEGORIES);
        if ($errors || $category === '') {
            old_input($this->request->post);
            flash('error', 'Please provide a valid question and category.');
            redirect('admin/content');
        }

        Database::insert('interview_questions', [
            'category' => $category,
            'question' => $this->postString('question'),
            'sample_answer' => $this->postString('sample_answer') ?: null,
            'tips' => $this->postString('tips') ?: null,
            'active' => $this->request->post('active') ? 1 : 0,
        ]);
        User::logAudit(
            (int) Auth::id(),
            'question_created',
            'Added a ' . $category . ' interview question',
            $this->request->ip()
        );
        clear_old();
        flash('success', 'Interview question added.');
        redirect('admin/content');
    }

    /* ------------------------------------------------------------------ */
    /* Advertisements                                                      */
    /* ------------------------------------------------------------------ */

    public function ads(): void
    {
        $ads = Database::all('SELECT * FROM advertisements ORDER BY id DESC');
        $this->renderAdmin('ads', ['ads' => $ads, 'positions' => self::AD_POSITIONS], 'Advertisements');
    }

    public function storeAd(): void
    {
        $errors = $this->validate([
            'title' => 'required|max:180',
            'link' => 'url|max:500',
        ]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please correct the highlighted fields.');
            redirect('admin/ads');
        }

        $position = $this->whitelist($this->postString('position', 'sidebar'), self::AD_POSITIONS) ?: 'sidebar';
        $startsAt = $this->maybeDateTime($this->postString('starts_at'));
        $endsAt = $this->maybeDateTime($this->postString('ends_at'));
        if (($this->postString('starts_at') !== '' && $startsAt === null) || ($this->postString('ends_at') !== '' && $endsAt === null)) {
            old_input($this->request->post);
            flash('error', 'Please enter valid start and end dates.');
            redirect('admin/ads');
        }

        Database::insert('advertisements', [
            'title' => $this->postString('title'),
            'description' => $this->postString('description') ?: null,
            'link' => $this->postString('link') ?: null,
            'position' => $position,
            'active' => $this->request->post('active') ? 1 : 0,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
        User::logAudit(
            (int) Auth::id(),
            'ad_created',
            'Created advertisement "' . $this->postString('title') . '"',
            $this->request->ip()
        );
        clear_old();
        flash('success', 'Advertisement created.');
        redirect('admin/ads');
    }

    public function toggleAd(): void
    {
        $id = (int) $this->request->route('id');
        if ($id <= 0) {
            flash('error', 'Invalid advertisement.');
            redirect('admin/ads');
        }
        $ad = Database::one('SELECT id, title, active FROM advertisements WHERE id = :id', ['id' => $id]);
        if (!$ad) {
            flash('error', 'That advertisement could not be found.');
            redirect('admin/ads');
        }

        Database::query('UPDATE advertisements SET active = 1 - active WHERE id = :id', ['id' => $id]);
        User::logAudit(
            (int) Auth::id(),
            'ad_toggled',
            'Toggled advertisement "' . $ad['title'] . '" from ' . ((int) $ad['active'] === 1 ? 'active' : 'inactive'),
            $this->request->ip()
        );
        flash('success', 'Advertisement visibility toggled.');
        redirect('admin/ads');
    }

    /* ------------------------------------------------------------------ */
    /* Analytics                                                           */
    /* ------------------------------------------------------------------ */

    public function analytics(): void
    {
        $stats = Admin::platformStats();
        $trend = Admin::applicationTrend(30);
        $categories = Admin::popularCategories();
        $topJobs = Admin::mostViewedJobs();
        $companies = Admin::activeCompanies();
        $registrations = Database::all(
            'SELECT DATE(created_at) AS day, COUNT(*) AS total FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) ORDER BY day'
        );
        $views = Database::all(
            'SELECT DATE(viewed_at) AS day, COUNT(*) AS total FROM job_views
             WHERE viewed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(viewed_at) ORDER BY day'
        );

        $this->renderAdmin('analytics', compact('stats', 'trend', 'categories', 'topJobs', 'companies', 'registrations', 'views'), 'Platform analytics');
    }

    /* ------------------------------------------------------------------ */
    /* Settings and announcements                                          */
    /* ------------------------------------------------------------------ */

    public function settings(): void
    {
        $settings = [];
        foreach (Database::all('SELECT setting_key, setting_value FROM platform_settings') as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        foreach (self::SETTING_KEYS as $key) {
            if (!array_key_exists($key, $settings)) {
                $settings[$key] = '';
            }
        }
        $announcements = Database::all(
            'SELECT a.*, u.name AS admin_name FROM announcements a
             LEFT JOIN users u ON u.id = a.admin_id
             ORDER BY a.created_at DESC LIMIT 20'
        );

        $this->renderAdmin('settings', compact('settings', 'announcements'), 'Platform settings');
    }

    public function updateSettings(): void
    {
        $booleans = ['maintenance_mode', 'jobs_require_approval'];
        $changed = [];
        foreach (self::SETTING_KEYS as $key) {
            $posted = array_key_exists($key, $this->request->post);
            if (!$posted && !in_array($key, $booleans, true)) {
                continue;
            }
            if (in_array($key, $booleans, true)) {
                $value = $this->request->post($key) ? '1' : '0';
            } else {
                $value = $this->postString($key);
                if ($key === 'min_password_length') {
                    $length = filter_var($value, FILTER_VALIDATE_INT);
                    $value = (string) ($length === false ? 8 : max(8, min(72, (int) $length)));
                }
                if (mb_strlen($value) > 500) {
                    continue;
                }
            }

            Database::query(
                'INSERT INTO platform_settings (setting_key, setting_value, updated_by)
                 VALUES (:key, :value, :by)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)',
                ['key' => $key, 'value' => $value, 'by' => (int) Auth::id()]
            );
            $changed[] = $key;
        }

        if ($changed === []) {
            flash('error', 'No recognised settings were submitted.');
            redirect('admin/settings');
        }
        User::logAudit(
            (int) Auth::id(),
            'settings_updated',
            'Updated platform settings: ' . implode(', ', $changed),
            $this->request->ip()
        );
        flash('success', 'Platform settings saved.');
        redirect('admin/settings');
    }

    public function storeAnnouncement(): void
    {
        $errors = $this->validate([
            'title' => 'required|max:200',
            'body' => 'required|max:5000',
        ]);
        $role = $this->whitelist($this->postString('target_role'), self::ROLES);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please correct the announcement fields.');
            redirect('admin/settings');
        }

        Database::insert('announcements', [
            'admin_id' => (int) Auth::id(),
            'title' => $this->postString('title'),
            'body' => $this->postString('body'),
            'target_role' => $role !== '' ? $role : null,
            'active' => $this->request->post('active') ? 1 : 0,
        ]);
        User::logAudit(
            (int) Auth::id(),
            'announcement_created',
            'Posted announcement "' . $this->postString('title') . '"',
            $this->request->ip()
        );
        clear_old();
        flash('success', 'Announcement posted.');
        redirect('admin/settings');
    }

    /* ------------------------------------------------------------------ */
    /* Audit log                                                           */
    /* ------------------------------------------------------------------ */

    public function auditLogs(): void
    {
        $action = $this->query('action');
        $q = $this->query('q');

        $where = ['1=1'];
        $params = [];
        if ($action !== '') {
            $where[] = 'al.action LIKE :action';
            $params['action'] = '%' . $action . '%';
        }
        if ($q !== '') {
            $where[] = '(al.description LIKE :q OR u.name LIKE :q OR al.ip LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE $sqlWhere",
            $params
        );
        $pager = new Paginator($total, 20, $this->pageNumber());
        $rows = Database::all(
            "SELECT al.*, u.name AS user_name, u.email AS user_email
             FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id
             WHERE $sqlWhere ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $this->renderAdmin('audit', compact('rows', 'pager', 'action', 'q'), 'Audit logs');
    }
}
