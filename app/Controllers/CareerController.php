<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Content;

final class CareerController extends Controller
{
    public function index(): void
    {
        $category = trim((string) $this->request->query('category', ''));
        $categories = Database::all(
            "SELECT category, COUNT(*) AS total FROM articles
             WHERE status = 'published' AND category IS NOT NULL AND category <> ''
             GROUP BY category ORDER BY total DESC, category"
        );

        $this->render('career.index', [
            'articles' => Content::articles($category, 12),
            'tips' => Content::tips(6),
            'stories' => Content::stories(),
            'questions' => Content::questions(),
            'categories' => $categories,
            'activeCategory' => $category,
        ], 'Career Growth Centre');
    }

    public function article(): void
    {
        $slug = trim((string) $this->request->route('slug', ''));
        $article = Content::article($slug);
        if (!$article) {
            abort(404, 'This article is not available.');
        }

        Database::query('UPDATE articles SET views = views + 1 WHERE id = :id', ['id' => (int) $article['id']]);
        $article['views'] = (int) ($article['views'] ?? 0) + 1;

        $related = [];
        if (!empty($article['category'])) {
            $related = Database::all(
                "SELECT * FROM articles
                 WHERE status = 'published' AND category = :cat AND id <> :id
                 ORDER BY published_at DESC LIMIT 3",
                ['cat' => $article['category'], 'id' => (int) $article['id']]
            );
        }

        $this->render('career.article', [
            'article' => $article,
            'related' => $related,
        ], $article['title']);
    }

    public function interviewPrep(): void
    {
        $categories = [
            'common' => 'Common questions',
            'behavioral' => 'Behavioural questions',
            'technical' => 'Technical questions',
            'hr' => 'HR & salary questions',
        ];

        $all = Content::questions();
        $counts = [];
        foreach ($all as $question) {
            $category = (string) ($question['category'] ?? 'common');
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        $active = (string) $this->request->query('category', '');
        if (!array_key_exists($active, $categories)) {
            $active = '';
        }

        $questions = $active !== '' ? Content::questions($active) : $all;
        $grouped = [];
        foreach ($questions as $question) {
            $grouped[(string) ($question['category'] ?? 'common')][] = $question;
        }

        $this->render('career.interview', [
            'grouped' => $grouped,
            'categories' => $categories,
            'counts' => $counts,
            'activeCategory' => $active,
            'tips' => Content::tips(4),
        ], 'Interview Preparation');
    }
}
