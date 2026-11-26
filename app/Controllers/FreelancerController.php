<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Notifications;
use App\Core\Validator;
use App\Models\Job;
use App\Models\User;

final class FreelancerController extends Controller
{
    private const SIDEBAR_LINKS = [
        ['Overview', 'freelancer/dashboard', 'chart'],
        ['Freelance profile', 'freelancer/profile', 'user'],
        ['My services', 'freelancer/services', 'briefcase'],
        ['Projects board', 'freelancer/projects', 'rocket'],
        ['Find jobs', 'jobs', 'search'],
        ['My applications', 'applications', 'briefcase'],
        ['My portfolio', 'my-portfolio', 'star'],
    ];

    private const CURRENCIES = ['RWF', 'USD', 'EUR', 'KES', 'UGX'];

    /** Fetch the current freelancer profile or send the user to role setup. */
    private function freelancerProfile(): array
    {
        $profile = Database::one('SELECT * FROM freelancer_profiles WHERE user_id = :id', ['id' => (int) Auth::id()]);
        if (!$profile) {
            flash('error', 'Please set up your freelancer profile to continue.');
            redirect('select-role');
        }
        return $profile;
    }

    private function renderWorkspace(string $view, array $data, string $title): void
    {
        $this->render($view, array_merge([
            'sideTitle' => 'Freelancer workspace',
            'links' => self::SIDEBAR_LINKS,
        ], $data), $title);
    }

    /** Shared guard for optional non-negative money fields. */
    private function rejectNegative(array $errors, string $field, mixed $value): array
    {
        $raw = trim((string) $value);
        if (!isset($errors[$field]) && $raw !== '' && is_numeric($raw) && (float) $raw < 0) {
            $errors[$field] = ucwords(str_replace('_', ' ', $field)) . ' cannot be negative.';
        }
        return $errors;
    }

    public function dashboard(): void
    {
        $profile = $this->freelancerProfile();
        $freelancerId = (int) $profile['id'];

        $stats = [
            'proposals' => Database::count('freelance_proposals', 'freelancer_id = :id', ['id' => $freelancerId]),
            'accepted' => Database::count('freelance_proposals', "freelancer_id = :id AND status = 'accepted'", ['id' => $freelancerId]),
            'open_projects' => Database::count('freelance_projects', "status = 'open'"),
            'services' => Database::count('freelancer_services', 'freelancer_id = :id', ['id' => $freelancerId]),
        ];

        $recentProposals = Database::all(
            'SELECT fp.*, p.title AS project_title, p.status AS project_status, p.budget, p.currency
             FROM freelance_proposals fp
             JOIN freelance_projects p ON p.id = fp.project_id
             WHERE fp.freelancer_id = :id
             ORDER BY fp.created_at DESC LIMIT 8',
            ['id' => $freelancerId]
        );

        $recommended = Job::recommendations((int) Auth::id(), 8);

        $this->renderWorkspace('freelancer.dashboard', [
            'active' => 'freelancer/dashboard',
            'profile' => $profile,
            'user' => Auth::user(),
            'stats' => $stats,
            'recentProposals' => $recentProposals,
            'recommended' => $recommended,
        ], 'Freelancer overview');
    }

    public function profile(): void
    {
        $profile = $this->freelancerProfile();
        $this->renderWorkspace('freelancer.profile', [
            'active' => 'freelancer/profile',
            'profile' => $profile,
            'user' => Auth::user(),
            'currencies' => self::CURRENCIES,
        ], 'Freelance profile');
    }

    public function updateProfile(): void
    {
        $profile = $this->freelancerProfile();
        $userId = (int) Auth::id();

        $input = [
            'name' => trim((string) $this->request->post('name', '')),
            'phone' => trim((string) $this->request->post('phone', '')),
            'title' => trim((string) $this->request->post('title', '')),
            'bio' => trim((string) $this->request->post('bio', '')),
            'hourly_rate' => trim((string) $this->request->post('hourly_rate', '')),
            'project_rate' => trim((string) $this->request->post('project_rate', '')),
            'currency' => trim((string) $this->request->post('currency', 'RWF')),
            'availability' => trim((string) $this->request->post('availability', 'available')),
            'response_time' => trim((string) $this->request->post('response_time', '')),
        ];

        $errors = Validator::make($input, [
            'name' => 'required|max:150',
            'title' => 'required|min:4|max:180',
            'bio' => 'max:5000',
            'hourly_rate' => 'numeric',
            'project_rate' => 'numeric',
            'currency' => 'in:' . implode(',', self::CURRENCIES),
            'availability' => 'in:available,busy,unavailable',
            'response_time' => 'max:80',
            'phone' => 'max:40',
        ]);
        $errors = $this->rejectNegative($errors, 'hourly_rate', $input['hourly_rate']);
        $errors = $this->rejectNegative($errors, 'project_rate', $input['project_rate']);

        if ($errors) {
            old_input($input);
            flash('error', implode(' ', $errors));
            redirect('freelancer/profile');
        }

        $currency = in_array($input['currency'], self::CURRENCIES, true) ? $input['currency'] : 'RWF';
        $availability = in_array($input['availability'], ['available', 'busy', 'unavailable'], true) ? $input['availability'] : 'available';

        Database::update('freelancer_profiles', [
            'title' => $input['title'],
            'bio' => $input['bio'] !== '' ? $input['bio'] : null,
            'hourly_rate' => $input['hourly_rate'] !== '' ? (float) $input['hourly_rate'] : null,
            'project_rate' => $input['project_rate'] !== '' ? (float) $input['project_rate'] : null,
            'currency' => $currency,
            'availability' => $availability,
            'response_time' => $input['response_time'] !== '' ? $input['response_time'] : null,
        ], 'user_id = :id', ['id' => $userId]);

        $userFields = ['name' => $input['name']];
        if ($input['phone'] !== '') {
            $userFields['phone'] = $input['phone'];
        }
        Database::update('users', $userFields, 'id = :id', ['id' => $userId]);
        Auth::refresh();

        $this->audit('freelancer_profile_updated', 'Updated freelancer profile #' . $profile['id']);
        clear_old();
        flash('success', 'Your freelance profile has been updated.');
        redirect('freelancer/profile');
    }

    public function services(): void
    {
        $profile = $this->freelancerProfile();
        $services = Database::all(
            'SELECT * FROM freelancer_services WHERE freelancer_id = :id ORDER BY id DESC',
            ['id' => $profile['id']]
        );

        $this->renderWorkspace('freelancer.services', [
            'active' => 'freelancer/services',
            'profile' => $profile,
            'services' => $services,
            'currencies' => self::CURRENCIES,
        ], 'My services');
    }

    public function addService(): void
    {
        $profile = $this->freelancerProfile();

        $input = [
            'title' => trim((string) $this->request->post('title', '')),
            'description' => trim((string) $this->request->post('description', '')),
            'price' => trim((string) $this->request->post('price', '')),
            'price_type' => trim((string) $this->request->post('price_type', 'project')),
        ];

        $errors = Validator::make($input, [
            'title' => 'required|min:4|max:180',
            'description' => 'max:5000',
            'price' => 'numeric',
            'price_type' => 'in:hourly,project',
        ]);
        $errors = $this->rejectNegative($errors, 'price', $input['price']);

        if ($errors) {
            old_input($input);
            flash('error', implode(' ', $errors));
            redirect('freelancer/services');
        }

        Database::insert('freelancer_services', [
            'freelancer_id' => $profile['id'],
            'title' => $input['title'],
            'description' => $input['description'] !== '' ? $input['description'] : null,
            'price' => $input['price'] !== '' ? (float) $input['price'] : null,
            'price_type' => $input['price_type'] === 'hourly' ? 'hourly' : 'project',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit('service_added', 'Added freelancer service "' . $input['title'] . '"');
        clear_old();
        flash('success', 'Service added to your catalogue.');
        redirect('freelancer/services');
    }

    public function deleteService(): void
    {
        $profile = $this->freelancerProfile();
        $serviceId = (int) $this->request->route('id');

        $deleted = Database::delete(
            'freelancer_services',
            'id = :id AND freelancer_id = :fid',
            ['id' => $serviceId, 'fid' => $profile['id']]
        );

        if ($deleted > 0) {
            $this->audit('service_deleted', 'Deleted freelancer service #' . $serviceId);
            flash('success', 'Service removed from your catalogue.');
        } else {
            flash('error', 'That service was not found in your catalogue.');
        }
        redirect('freelancer/services');
    }

    public function projects(): void
    {
        $profile = $this->freelancerProfile();
        $filters = [
            'q' => trim((string) $this->request->query('q', '')),
            'category_id' => (int) $this->request->query('category_id', 0),
            'min_budget' => trim((string) $this->request->query('min_budget', '')),
            'max_budget' => trim((string) $this->request->query('max_budget', '')),
            'skills' => trim((string) $this->request->query('skills', '')),
        ];

        $where = ["p.status = 'open'"];
        $params = [];
        if ($filters['q'] !== '') {
            $where[] = '(p.title LIKE :q OR p.description LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if ($filters['category_id'] > 0) {
            $where[] = 'p.category_id = :category';
            $params['category'] = $filters['category_id'];
        }
        if ($filters['min_budget'] !== '' && is_numeric($filters['min_budget'])) {
            $where[] = 'p.budget >= :min_budget';
            $params['min_budget'] = (float) $filters['min_budget'];
        }
        if ($filters['max_budget'] !== '' && is_numeric($filters['max_budget'])) {
            $where[] = 'p.budget <= :max_budget';
            $params['max_budget'] = (float) $filters['max_budget'];
        }
        if ($filters['skills'] !== '') {
            $where[] = 'p.skills_required LIKE :skills';
            $params['skills'] = '%' . $filters['skills'] . '%';
        }

        $projects = Database::all(
            'SELECT p.*, u.name AS client_name, cat.name AS category_name
             FROM freelance_projects p
             LEFT JOIN users u ON u.id = p.client_id
             LEFT JOIN job_categories cat ON cat.id = p.category_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.created_at DESC LIMIT 50',
            $params
        );

        $proposalRows = Database::all(
            'SELECT project_id, status FROM freelance_proposals WHERE freelancer_id = :id',
            ['id' => $profile['id']]
        );
        $proposals = [];
        foreach ($proposalRows as $row) {
            $proposals[(int) $row['project_id']] = $row['status'];
        }

        $this->renderWorkspace('freelancer.projects', [
            'active' => 'freelancer/projects',
            'profile' => $profile,
            'projects' => $projects,
            'proposals' => $proposals,
            'filters' => $filters,
            'categories' => Database::all('SELECT id, name FROM job_categories WHERE active = 1 ORDER BY name'),
        ], 'Projects board');
    }

    public function applyProject(): void
    {
        $profile = $this->freelancerProfile();
        $projectId = (int) $this->request->route('id');
        $project = Database::one('SELECT * FROM freelance_projects WHERE id = :id', ['id' => $projectId]);

        if (!$project || $project['status'] !== 'open') {
            flash('error', 'This project is no longer accepting proposals.');
            redirect('freelancer/projects');
        }

        $existing = Database::scalar(
            'SELECT COUNT(*) FROM freelance_proposals WHERE project_id = :p AND freelancer_id = :f',
            ['p' => $projectId, 'f' => $profile['id']]
        );
        if ($existing) {
            flash('error', 'You have already submitted a proposal for this project.');
            redirect('freelancer/projects');
        }

        $coverLetter = trim((string) $this->request->post('cover_letter', ''));
        $bidAmount = trim((string) $this->request->post('bid_amount', ''));

        $errors = Validator::make(
            ['cover_letter' => $coverLetter, 'bid_amount' => $bidAmount],
            ['cover_letter' => 'required|min:20|max:5000', 'bid_amount' => 'numeric']
        );
        $errors = $this->rejectNegative($errors, 'bid_amount', $bidAmount);
        if ($errors) {
            old_input(['cover_letter' => $coverLetter, 'bid_amount' => $bidAmount]);
            flash('error', implode(' ', $errors));
            redirect('freelancer/projects');
        }

        Database::insert('freelance_proposals', [
            'project_id' => $projectId,
            'freelancer_id' => $profile['id'],
            'cover_letter' => $coverLetter,
            'bid_amount' => $bidAmount !== '' ? (float) $bidAmount : null,
            'status' => 'submitted',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Notifications::create(
            (int) $project['client_id'],
            'proposal',
            'New proposal received',
            'A freelancer submitted a proposal for "' . $project['title'] . '".',
            '/freelancer/projects'
        );

        $this->audit('proposal_submitted', 'Submitted proposal for project #' . $projectId);
        clear_old();
        flash('success', 'Your proposal has been submitted. The client has been notified.');
        redirect('freelancer/projects');
    }
}
