<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Upload;
use App\Models\Community;

final class CommunityController extends Controller
{
    /** Values accepted by community_posts.category. */
    private const CATEGORIES = ['experience', 'question', 'advice', 'interview', 'resource', 'achievement'];

    /** Reasons offered to members; spam/harassment are mapped to the DB enum below. */
    private const REPORT_REASONS = ['spam', 'inappropriate', 'harassment', 'scam', 'other'];

    public function index(): void
    {
        $category = trim((string) $this->request->query('category', ''));
        if ($category !== '' && !in_array($category, self::CATEGORIES, true)) {
            $category = '';
        }

        $stats = [
            'posts' => (int) Database::scalar("SELECT COUNT(*) FROM community_posts WHERE status = 'published'"),
            'comments' => (int) Database::scalar('SELECT COUNT(*) FROM community_comments'),
            'members' => (int) Database::scalar("SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL"),
        ];

        $this->render('community.index', [
            'categories' => array_merge([''], self::CATEGORIES),
            'activeCategory' => $category,
            'posts' => Community::posts($category, 30),
            'stats' => $stats,
        ], 'Career Community');
    }

    public function show(): void
    {
        $id = (int) $this->request->route('id');
        $post = Community::post($id);
        if (!$post || ($post['status'] ?? '') !== 'published') {
            abort(404, 'This discussion is not available.');
        }

        $comments = Community::comments($id);
        // community_posts has no views column — deliberately do not increment a view counter here.

        $liked = false;
        if (Auth::id()) {
            $liked = (bool) Database::scalar(
                'SELECT COUNT(*) FROM community_likes WHERE post_id = :p AND user_id = :u',
                ['p' => $id, 'u' => Auth::id()]
            );
        }

        $role = (string) ($post['author_role'] ?? '');
        $authorRole = $role !== ''
            ? (string) config('roles.' . $role, ucfirst(str_replace('_', ' ', $role)))
            : 'Community member';

        $this->render('community.show', [
            'post' => $post,
            'comments' => $comments,
            'liked' => $liked,
            'authorRole' => $authorRole,
        ], (string) $post['title']);
    }

    public function create(): void
    {
        $errors = $this->validate([
            'title' => 'required|min:5|max:220',
            'body' => 'required|min:10|max:10000',
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
        ]);
        if ($errors) {
            old_input([
                'title' => (string) $this->request->post('title', ''),
                'body' => (string) $this->request->post('body', ''),
                'category' => (string) $this->request->post('category', ''),
            ]);
            flash('error', implode(' ', $errors));
            $this->safeBack('community');
        }

        $image = null;
        try {
            $image = Upload::handle($this->request->file('image'), 'community', (array) config('upload.allowed_images'));
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->safeBack('community');
        }

        $id = Database::insert('community_posts', [
            'user_id' => Auth::id(),
            'category' => (string) $this->request->post('category'),
            'title' => trim((string) $this->request->post('title')),
            'body' => trim((string) $this->request->post('body')),
            'image' => $image,
            'status' => 'published',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        clear_old();
        flash('success', 'Your post is live in the community.');
        redirect('community/' . $id);
    }

    public function comment(): void
    {
        $id = (int) $this->request->route('id');
        $post = Community::post($id);
        if (!$post || ($post['status'] ?? '') !== 'published') {
            abort(404, 'This discussion is not available.');
        }

        $errors = $this->validate(['body' => 'required|min:2|max:5000']);
        if ($errors) {
            old_input(['comment_body' => (string) $this->request->post('body', '')]);
            flash('error', implode(' ', $errors));
            redirect('community/' . $id);
        }

        $parentId = 0;
        $rawParent = $this->request->post('parent_id', 0);
        if ($rawParent !== '' && filter_var($rawParent, FILTER_VALIDATE_INT) !== false) {
            $candidate = (int) $rawParent;
            $valid = Database::scalar(
                'SELECT COUNT(*) FROM community_comments WHERE id = :id AND post_id = :p',
                ['id' => $candidate, 'p' => $id]
            );
            if ($candidate > 0 && $valid) {
                $parentId = $candidate;
            }
        }

        $data = [
            'post_id' => $id,
            'user_id' => Auth::id(),
            'body' => trim((string) $this->request->post('body')),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($parentId > 0) {
            $data['parent_id'] = $parentId;
        }

        Database::insert('community_comments', $data);
        Database::query('UPDATE community_posts SET comments_count = comments_count + 1 WHERE id = :id', ['id' => $id]);

        clear_old();
        flash('success', 'Your comment has been posted.');
        redirect('community/' . $id);
    }

    public function like(): void
    {
        $id = (int) $this->request->route('id');
        $liked = Community::toggleLike($id, (int) Auth::id());
        flash('success', $liked ? 'Post liked.' : 'Like removed.');
        $this->safeBack('community/' . $id);
    }

    public function report(): void
    {
        $id = (int) $this->request->route('id');
        $errors = $this->validate([
            'reason' => 'required|in:' . implode(',', self::REPORT_REASONS),
            'details' => 'max:1000',
        ]);
        if ($errors) {
            flash('error', implode(' ', $errors));
            $this->safeBack('community/' . $id);
        }

        $reason = (string) $this->request->post('reason', 'other');
        if (in_array($reason, ['spam', 'harassment'], true)) {
            $reason = 'other';
        }
        if (!in_array($reason, ['inappropriate', 'scam', 'other'], true)) {
            $reason = 'other';
        }
        $details = trim((string) $this->request->post('details', ''));

        Database::query(
            'INSERT INTO reports (reporter_id, type, target_id, reason, details, status, created_at)
             VALUES (:uid, "community_post", :tid, :reason, :details, "open", NOW())
             ON DUPLICATE KEY UPDATE reason = :reason2, details = :details2, status = "open"',
            [
                'uid' => Auth::id(),
                'tid' => $id,
                'reason' => $reason,
                'reason2' => $reason,
                'details' => $details,
                'details2' => $details,
            ]
        );

        flash('success', 'Thank you. Our moderation team will review this report.');
        redirect('community/' . $id);
    }

    /** Redirect back only when the referer points inside this application. */
    private function safeBack(string $fallback): void
    {
        $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        if ($referer !== '') {
            $parts = parse_url($referer);
            $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : '';
            $host = is_array($parts) && isset($parts['host']) ? strtolower((string) $parts['host']) : '';
            $currentHost = strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0]);
            $base = rtrim(url(), '/');

            if (
                $path !== ''
                && ($host === '' || ($currentHost !== '' && $host === $currentHost))
                && str_starts_with($path . '/', $base . '/')
            ) {
                redirect(ltrim(substr($path, strlen($base)), '/'));
            }
        }
        redirect($fallback);
    }
}
