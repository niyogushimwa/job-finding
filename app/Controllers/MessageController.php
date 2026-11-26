<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Message;

final class MessageController extends Controller
{
    public function index(): void
    {
        $userId = (int) Auth::id();
        $threads = Message::threads($userId);
        $activeThread = null;
        $messages = [];

        $threadId = (int) $this->request->query('thread', 0);
        if ($threadId > 0) {
            $activeThread = Message::thread($threadId, $userId);
            if ($activeThread) {
                Message::markRead($threadId, $userId);
                $messages = Message::messages($threadId);
            }
        }

        $this->render('messages.index', $this->viewData($threads, $activeThread, $messages, $userId), 'Messages');
    }

    public function thread(): void
    {
        $userId = (int) Auth::id();
        $threadId = (int) $this->request->route('thread');
        $activeThread = Message::thread($threadId, $userId);
        if (!$activeThread) {
            abort(404, 'Conversation not found.');
        }

        Message::markRead($threadId, $userId);
        $this->render('messages.index', $this->viewData(
            Message::threads($userId),
            $activeThread,
            Message::messages($threadId),
            $userId
        ), 'Messages');
    }

    public function send(): void
    {
        $userId = (int) Auth::id();
        $threadId = (int) $this->request->route('thread');
        if (!Message::thread($threadId, $userId)) {
            abort(404, 'Conversation not found.');
        }

        $errors = $this->validate(['body' => 'required|min:1|max:5000']);
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('messages/' . $threadId);
        }

        $recipientId = (int) Database::scalar(
            'SELECT user_id FROM thread_participants WHERE thread_id = :t AND user_id <> :u ORDER BY user_id LIMIT 1',
            ['t' => $threadId, 'u' => $userId]
        );
        if ($recipientId <= 0) {
            flash('error', 'This conversation has no recipient.');
            redirect('messages/' . $threadId);
        }

        Message::send($threadId, $userId, $recipientId, trim((string) $this->request->post('body')));
        flash('success', 'Message sent.');
        redirect('messages/' . $threadId);
    }

    public function start(): void
    {
        $userId = (int) Auth::id();
        $errors = $this->validate([
            'recipient_id' => 'required|integer',
            'subject' => 'max:200',
            'body' => 'required|min:1|max:5000',
        ]);

        $recipientId = (int) $this->request->post('recipient_id', 0);
        if ($recipientId <= 0 || $recipientId === $userId) {
            $errors['recipient_id'] = 'Choose a valid recipient.';
        } else {
            $recipient = Database::one('SELECT id, status, deleted_at FROM users WHERE id = :id', ['id' => $recipientId]);
            if (!$recipient || $recipient['status'] !== 'active' || !empty($recipient['deleted_at'])) {
                $errors['recipient_id'] = 'Choose an active member.';
            }
        }

        if ($errors) {
            old_input([
                'recipient_id' => $recipientId,
                'subject' => (string) $this->request->post('subject', ''),
                'body' => (string) $this->request->post('body', ''),
            ]);
            flash('error', implode(' ', $errors));
            redirect('messages');
        }

        $subject = trim((string) $this->request->post('subject', ''));
        if ($subject === '') {
            $subject = 'New conversation';
        }

        $threadId = Message::startThread(
            $userId,
            $recipientId,
            mb_substr($subject, 0, 200),
            trim((string) $this->request->post('body'))
        );

        clear_old();
        flash('success', 'Conversation started.');
        redirect('messages/' . $threadId);
    }

    /** Shared view payload so ?thread= and /messages/{thread} render the same page. */
    private function viewData(array $threads, ?array $activeThread, array $messages, int $userId): array
    {
        return [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'messages' => $messages,
            'users' => Database::all(
                'SELECT id, name, role FROM users
                 WHERE status = "active" AND deleted_at IS NULL AND id <> :id
                 ORDER BY name LIMIT 100',
                ['id' => $userId]
            ),
        ];
    }
}
