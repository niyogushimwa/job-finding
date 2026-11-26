<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Models\Job;
use App\Models\Opportunity;

final class ApiController extends Controller
{
    public function jobs(): void
    {
        $filters = [];
        foreach (['q', 'location', 'country', 'category', 'industry', 'employment_type', 'work_mode', 'experience_level', 'education_level', 'salary_min', 'salary_max', 'posted_within', 'deadline_before', 'featured', 'sort'] as $key) {
            $filters[$key] = trim((string) $this->request->query($key, ''));
        }
        $limit = min(24, max(1, (int) $this->request->query('limit', 12)));
        $page = max(1, (int) $this->request->query('page', 1));
        $pager = new Paginator(0, $limit, $page);
        $pager = new Paginator(Job::search($filters, $pager)['total'], $limit, $page);
        $result = Job::search($filters, $pager);
        $basePath = 'jobs';
        $jobs = $result['rows'];
        ob_start();
        include BASE_PATH . '/app/Views/jobs/results.php';
        $html = (string) ob_get_clean();
        $rows = array_map(static fn($job) => [
            'id' => (int) $job['id'],
            'title' => $job['title'],
            'slug' => $job['slug'],
            'location' => $job['location'],
            'url' => url('jobs/' . $job['id'] . '/' . $job['slug']),
        ], $result['rows']);
        $this->json(['ok' => true, 'total' => $pager->total, 'rows' => $rows, 'html' => $html]);
    }

    public function opportunities(): void
    {
        $pager = new Paginator(0, 12, 1);
        $pager = new Paginator(Opportunity::external(['q' => $this->request->query('q', '')], $pager)['total'], 12, 1);
        $rows = Opportunity::external(['q' => $this->request->query('q', '')], $pager)['rows'];
        $data = array_map(static fn($r) => [
            'id' => (int) $r['id'], 'title' => $r['title'], 'organization' => $r['organization'],
            'location' => $r['location'], 'url' => url('opportunity-radar/' . $r['id']),
        ], $rows);
        $this->json(['ok' => true, 'rows' => $data]);
    }

    public function notifications(): void
    {
        $rows = Database::all('SELECT id, type, title, body, link, is_read, created_at FROM notifications WHERE user_id = :id ORDER BY id DESC LIMIT 20', ['id' => Auth::id()]);
        foreach ($rows as &$row) {
            $row['link'] = $row['link'] ? url($row['link']) : '#';
            $row['is_read'] = (bool) $row['is_read'];
        }
        $this->json(['ok' => true, 'notifications' => $rows]);
    }

    public function readNotification(): void
    {
        $id = (int) $this->request->post('id', 0);
        Database::update('notifications', ['is_read' => 1], 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => Auth::id()]);
        $this->json(['ok' => true]);
    }

    public function match(): void
    {
        $job = Job::findFull((int) $this->request->route('id'));
        if (!$job) { $this->json(['ok' => false], 404); }
        if (!in_array(Auth::role(), ['seeker', 'freelancer'], true)) { $this->json(['ok' => false, 'message' => 'Only job seekers can request a match.'], 403); }
        $this->json(['ok' => true] + Job::matchScore($job, (int) Auth::id()));
    }

    public function saveSearch(): void
    {
        $name = trim((string) $this->request->post('name', ''));
        $params = trim((string) $this->request->post('query_params', ''));
        if ($name === '') { $this->json(['ok' => false, 'message' => 'Name is required.'], 422); }
        Database::insert('saved_searches', ['user_id' => Auth::id(), 'name' => $name, 'query_params' => $params, 'created_at' => date('Y-m-d H:i:s')]);
        if (!$this->request->isAjax() && !str_contains(strtolower($this->request->header('Accept', '')), 'application/json')) {
            flash('success', 'Search saved. Find it again from the jobs page.');
            redirect('jobs' . ($params !== '' ? '?' . ltrim($params, '?&') : ''));
        }
        $this->json(['ok' => true, 'message' => 'Search saved.']);
    }

    public function health(): void
    {
        $this->json([
            'ok' => true,
            'app' => config('app.name'),
            'version' => config('app.version'),
            'database' => Database::scalar('SELECT 1') === 1 ? 'connected' : 'error',
            'time' => date('c'),
        ]);
    }
}
