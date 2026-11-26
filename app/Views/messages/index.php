<?php
use App\Core\Auth;

$active = 'messages';
$sideTitle = 'Communication';
$links = [
    ['Dashboard', 'dashboard', 'chart'],
    ['Jobs', 'jobs', 'briefcase'],
    ['Applications', 'applications', 'list'],
    ['Saved jobs', 'saved-jobs', 'bookmark'],
    ['My CV', 'my-cv', 'user'],
    ['Skills', 'my-skills', 'star'],
    ['Portfolio', 'my-portfolio', 'award'],
    ['Community', 'community', 'users'],
];
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>Messages</h1>
        <p>Chat with employers, candidates and community members.</p>
      </div>
      <span class="status status-open"><?= (int) count($threads) ?> conversations</span>
    </div>

    <div class="panel">
      <div class="panel-body flush">
        <div class="msg-layout">
          <div class="thread-list">
            <details style="padding:.9rem 1rem;border-bottom:1px solid var(--border)">
              <summary class="small"><strong><?= icon('plus', 14) ?> New conversation</strong></summary>
              <?php if ($users): ?>
                <form method="post" action="<?= e(url('messages/start')) ?>" class="mt-2">
                  <?= csrf_field() ?>
                  <div class="form-group">
                    <label class="form-label" for="recipient-id">Recipient</label>
                    <select class="form-control" id="recipient-id" name="recipient_id" required>
                      <option value="">Choose a member</option>
                      <?php foreach ($users as $user): ?>
                        <option value="<?= (int) $user['id'] ?>" <?= (string) old('recipient_id') === (string) $user['id'] ? 'selected' : '' ?>><?= e($user['name']) ?> · <?= e($user['role']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="new-subject">Subject</label>
                    <input class="form-control" id="new-subject" name="subject" maxlength="200" value="<?= e(old('subject')) ?>" placeholder="New conversation">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="new-body">Message</label>
                    <textarea class="form-control" id="new-body" name="body" rows="3" minlength="1" maxlength="5000" required><?= e(old('body')) ?></textarea>
                  </div>
                  <button class="btn btn-primary btn-sm btn-block" type="submit">Start conversation</button>
                </form>
              <?php else: ?>
                <p class="small muted mb-0">No members available yet.</p>
              <?php endif; ?>
            </details>

            <?php if (!$threads): ?>
              <p class="small muted" style="padding:1rem">No conversations yet.</p>
            <?php endif; ?>

            <?php foreach ($threads as $thread): ?>
              <a class="thread-item <?= $activeThread && (int) $activeThread['id'] === (int) $thread['id'] ? 'active' : '' ?>" href="<?= e(url('messages/' . (int) $thread['id'])) ?>">
                <strong><?= e($thread['subject'] ?: 'Conversation') ?></strong>
                <span><?= e(str_limit((string) ($thread['participants'] ?? ''), 46)) ?></span>
                <span><?= e(str_limit((string) ($thread['last_message'] ?? ''), 55)) ?></span>
                <?php if ((int) ($thread['unread'] ?? 0) > 0): ?>
                  <span class="status status-open mt-1"><?= (int) $thread['unread'] ?> unread</span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>

          <div class="msg-main">
            <?php if (!$activeThread): ?>
              <div class="msg-scroll">
                <div class="empty-state">
                  <div class="empty-icon"><?= icon('chat', 28) ?></div>
                  <h3>Select a conversation</h3>
                  <p class="small muted">Choose a thread on the left or start a new conversation.</p>
                </div>
              </div>
            <?php else: ?>
              <div class="panel-head">
                <div>
                  <h3><?= e($activeThread['subject'] ?: 'Conversation') ?></h3>
                  <span class="small muted">Started <?= e(format_date($activeThread['created_at'])) ?></span>
                </div>
              </div>
              <div class="msg-scroll">
                <?php if (!$messages): ?>
                  <p class="small muted">No messages in this conversation yet.</p>
                <?php endif; ?>
                <?php foreach ($messages as $message): ?>
                  <div class="bubble <?= (int) $message['sender_id'] === (int) Auth::id() ? 'mine' : '' ?>">
                    <div style="white-space:pre-wrap"><?= e($message['body']) ?></div>
                    <small><?= e($message['sender_name'] ?? 'Member') ?> · <?= e(time_ago($message['created_at'])) ?></small>
                  </div>
                <?php endforeach; ?>
              </div>
              <form class="msg-compose" method="post" action="<?= e(url('messages/' . (int) $activeThread['id'] . '/send')) ?>">
                <?= csrf_field() ?>
                <textarea class="form-control" name="body" rows="2" minlength="1" maxlength="5000" required placeholder="Write a message..." aria-label="Message"></textarea>
                <button class="btn btn-primary" type="submit"><?= icon('arrow', 16) ?> Send</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
