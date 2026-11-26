<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Notifications;
use App\Core\Paginator;
use App\Core\Validator;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;

final class JobController extends Controller
{
    public function index(): void
    {
        $filters = $this->searchFilters();
        $pager = new Paginator(0, 12, (int) ($this->request->query('page', 1)));
        $result = Job::search($filters, $pager);
        $pager = new Paginator($result['total'], 12, (int) ($this->request->query('page', 1)));
        $result = Job::search($filters, $pager);
        $recent = [];
        if (Auth::id()) {
            $recent = Database::all(
                'SELECT ' . Job::SELECT . ' ' . Job::FROM . ' JOIN job_views v ON v.job_id = j.id
                 WHERE v.user_id = :id AND j.status = "published" GROUP BY j.id ORDER BY MAX(v.viewed_at) DESC LIMIT 4',
                ['id' => Auth::id()]
            );
        }
        $savedSearches = Auth::id() ? Database::all('SELECT * FROM saved_searches WHERE user_id = :id ORDER BY id DESC LIMIT 5', ['id' => Auth::id()]) : [];
        $this->render('jobs.index', [
            'jobs' => $result['rows'],
            'pager' => $pager,
            'filters' => $filters,
            'categories' => Database::all('SELECT id, name FROM job_categories WHERE active = 1 ORDER BY name'),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
            'industries' => Database::all('SELECT id, name FROM industries WHERE active = 1 ORDER BY name'),
            'recent' => $recent,
            'savedSearches' => $savedSearches,
        ], 'Find Jobs');
    }

    private function searchFilters(): array
    {
        return [
            'q' => trim((string) $this->request->query('q', '')),
            'location' => trim((string) $this->request->query('location', '')),
            'country' => $this->request->query('country', ''),
            'category' => $this->request->query('category', ''),
            'industry' => $this->request->query('industry', ''),
            'employment_type' => $this->request->query('employment_type', ''),
            'work_mode' => $this->request->query('work_mode', ''),
            'experience_level' => $this->request->query('experience_level', ''),
            'education_level' => $this->request->query('education_level', ''),
            'salary_min' => $this->request->query('salary_min', ''),
            'salary_max' => $this->request->query('salary_max', ''),
            'posted_within' => $this->request->query('posted_within', ''),
            'deadline_before' => $this->request->query('deadline_before', ''),
            'featured' => $this->request->query('featured', ''),
            'sort' => $this->request->query('sort', 'latest'),
        ];
    }

    public function show(): void
    {
        $id = (int) $this->request->route('id');
        $job = Job::findFull($id);
        if (!$job || $job['status'] !== 'published') {
            abort(404, 'This opportunity is not available.');
        }
        Job::recordView($id, Auth::id(), $this->request->ip());
        $skills = Job::skills($id);
        $match = Auth::id() && in_array(Auth::role(), ['seeker', 'freelancer'], true) ? Job::matchScore($job, (int) Auth::id()) : null;
        $similar = Database::all(
            'SELECT ' . Job::SELECT . ' ' . Job::FROM . ' WHERE j.status = "published" AND j.id <> :id AND (j.category_id = :cat OR j.location = :loc) ORDER BY j.published_at DESC LIMIT 3',
            ['id' => $id, 'cat' => $job['category_id'], 'loc' => $job['location']]
        );
        $this->render('jobs.show', [
            'job' => $job,
            'skills' => $skills,
            'match' => $match,
            'similar' => $similar,
            'saved' => Job::isSaved($id, Auth::id()),
            'applied' => Job::hasApplied($id, Auth::id()),
        ], $job['title']);
    }

    public function toggleSave(): void
    {
        $jobId = (int) $this->request->route('id');
        $userId = Auth::id();
        if (!$userId) {
            $this->json(['ok' => false, 'loginRequired' => true]);
        }
        if (!Job::findFull($jobId)) {
            $this->json(['ok' => false, 'message' => 'Job not found'], 404);
        }
        $existing = Database::one('SELECT id FROM saved_jobs WHERE user_id = :u AND job_id = :j', ['u' => $userId, 'j' => $jobId]);
        if ($existing) {
            Database::delete('saved_jobs', 'id = :id', ['id' => $existing['id']]);
            $saved = false;
        } else {
            Database::insert('saved_jobs', ['user_id' => $userId, 'job_id' => $jobId, 'created_at' => date('Y-m-d H:i:s')]);
            $saved = true;
        }
        $this->json(['ok' => true, 'saved' => $saved]);
    }

    public function applyForm(): void
    {
        $job = Job::findFull((int) $this->request->route('id'));
        if (!$job || $job['status'] !== 'published') { abort(404); }
        $resumes = Database::all('SELECT * FROM resumes WHERE user_id = :id ORDER BY is_default DESC, id DESC', ['id' => Auth::id()]);
        $cvDoc = Database::one('SELECT * FROM cv_documents WHERE user_id = :id', ['id' => Auth::id()]);
        $seeker = Database::one('SELECT * FROM job_seekers WHERE user_id = :id', ['id' => Auth::id()]) ?? [];
        $this->render('jobs.apply', ['job' => $job, 'resumes' => $resumes, 'cvDoc' => $cvDoc, 'seeker' => $seeker], 'Apply for ' . $job['title']);
    }

    public function apply(): void
    {
        $jobId = (int) $this->request->route('id');
        $job = Job::findFull($jobId);
        if (!$job || $job['status'] !== 'published') { abort(404); }
        if (Job::hasApplied($jobId, Auth::id())) {
            flash('error', 'You have already applied for this opportunity.');
            redirect('jobs/' . $jobId . '/' . $job['slug']);
        }
        $errors = Validator::make($this->request->post, ['cover_letter' => 'required|min:20|max:5000']);
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('jobs/' . $jobId . '/apply');
        }
        $resumeId = (int) $this->request->post('resume_id', 0) ?: null;
        if ($resumeId && !Database::scalar('SELECT COUNT(*) FROM resumes WHERE id = :id AND user_id = :uid', ['id' => $resumeId, 'uid' => Auth::id()])) {
            $resumeId = null;
        }
        $score = Job::matchScore($job, (int) Auth::id())['score'];
        try {
            Application::apply($jobId, (int) Auth::id(), trim((string) $this->request->post('cover_letter')), $resumeId, $score);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('jobs/' . $jobId . '/' . $job['slug']);
        }
        User::logAudit((int) Auth::id(), 'apply', 'Applied to job #' . $jobId);
        Notifications::create((int) Auth::id(), 'application', 'Application submitted',
            'Your application for "' . $job['title'] . '" has been received.', '/applications');
        clear_old();
        flash('success', 'Your application has been submitted. Track it in My Applications.');
        redirect('applications');
    }

    public function report(): void
    {
        $jobId = (int) $this->request->route('id');
        $reason = (string) $this->request->post('reason', 'other');
        if (!in_array($reason, ['scam', 'fake_job', 'payment_request', 'misleading_salary', 'inappropriate', 'fraud', 'other'], true)) {
            $reason = 'other';
        }
        Database::query(
            'INSERT INTO reports (reporter_id, type, target_id, reason, details, status, created_at)
             VALUES (:uid, "job", :jid, :reason, :details, "open", NOW())
             ON DUPLICATE KEY UPDATE reason = :reason2, status = "open"',
            ['uid' => Auth::id(), 'jid' => $jobId, 'reason' => $reason, 'reason2' => $reason, 'details' => trim((string) $this->request->post('details', ''))]
        );
        User::logAudit((int) Auth::id(), 'report', 'Reported job #' . $jobId . ' (' . $reason . ')');
        flash('success', 'Thank you. Our moderation team will investigate this report.');
        redirect('jobs/' . $jobId);
    }
}
