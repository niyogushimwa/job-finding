<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Admin;
use App\Models\Content;
use App\Models\Job;

final class HomeController extends Controller
{
    public function index(): void
    {
        $sql = 'SELECT ' . Job::SELECT . ' ' . Job::FROM . " WHERE j.status = 'published' AND (j.deadline IS NULL OR j.deadline >= CURDATE())";
        $featured = Database::all($sql . ' ORDER BY j.featured DESC, j.published_at DESC LIMIT 4');
        $latest = Database::all($sql . ' ORDER BY j.published_at DESC LIMIT 4');
        $remote = Database::all($sql . " AND j.work_mode = 'remote' ORDER BY j.published_at DESC LIMIT 3");
        $internships = Database::all($sql . " AND j.employment_type = 'internship' ORDER BY j.published_at DESC LIMIT 3");
        $graduate = Database::all($sql . " AND j.experience_level = 'entry' ORDER BY j.published_at DESC LIMIT 3");
        $categories = Database::all('SELECT cat.*, (SELECT COUNT(*) FROM jobs j WHERE j.category_id = cat.id AND j.status = "published") AS jobs_count FROM job_categories cat WHERE cat.active = 1 ORDER BY jobs_count DESC, cat.name LIMIT 8');
        $companies = Admin::activeCompanies(4);
        $articles = Content::articles('', 3);
        $tips = Content::tips(2);
        $stories = Content::stories();
        $stats = Admin::platformStats();
        $ad = Content::ads('home_banner')[0] ?? null;

        $this->render('home.index', [
            'featured' => $featured,
            'latest' => $latest,
            'remote' => $remote,
            'internships' => $internships,
            'graduate' => $graduate,
            'categories' => $categories,
            'companies' => $companies,
            'articles' => $articles,
            'tips' => $tips,
            'stories' => $stories,
            'stats' => $stats,
            'ad' => $ad,
        ], 'Find Your Next Opportunity');
    }
}
