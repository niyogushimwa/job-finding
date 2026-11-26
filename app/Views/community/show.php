<?php
use App\Core\Auth;
use App\Core\Upload;

$categoryLabels = [
    'experience' => 'Experience',
    'question' => 'Question',
    'advice' => 'Advice',
    'interview' => 'Interview',
    'resource' => 'Resource',
    'achievement' => 'Achievement',
];
$commentAuthors = [];
foreach ($comments as $comment) {
    $commentAuthors[(int) $comment['id']] = (string) $comment['author_name'];
}
?>
<section class="section">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url()) ?>">Home</a> / <a href="<?= e(url('community')) ?>">Community</a> / <?= e(str_limit((string) $post['title'], 50)) ?></nav>

    <div class="search-layout">
      <div>
        <?php $full = true; include BASE_PATH . '/app/Views/partials/community_post.php'; ?>

        <div class="panel">
          <div class="panel-head"><h2><?= (int) count($comments) ?> comments</h2></div>
          <div class="panel-body">
            <?php if (!$comments): ?>
              <p class="small muted mb-0">No comments yet. Start the conversation.</p>
            <?php endif; ?>

            <?php foreach ($comments as $comment): ?>
              <div class="comment" <?= !empty($comment['parent_id']) ? 'style="margin-left:1.25rem"' : '' ?>>
                <div class="d-flex align-center gap-1">
                  <?php if (!empty($comment['author_avatar'])): ?>
                    <img class="avatar" src="<?= e(Upload::url((string) $comment['author_avatar'])) ?>" alt="" style="width:28px;height:28px">
                  <?php else: ?>
                    <span class="avatar" style="width:28px;height:28px"><?= e(mb_substr((string) $comment['author_name'], 0, 1)) ?></span>
                  <?php endif; ?>
                  <div>
                    <strong><?= e($comment['author_name']) ?></strong>
                    <span class="small muted"><?= e(time_ago($comment['created_at'])) ?></span>
                  </div>
                </div>
                <?php if (!empty($comment['parent_id'])): ?>
                  <div class="small muted mt-1">In reply to <?= e($commentAuthors[(int) $comment['parent_id']] ?? 'an earlier comment') ?></div>
                <?php endif; ?>
                <div class="mt-1" style="white-space:pre-wrap"><?= e($comment['body']) ?></div>

                <?php if (Auth::check()): ?>
                  <details class="mt-1">
                    <summary class="small">Reply</summary>
                    <form method="post" action="<?= e(url('community/' . (int) $post['id'] . '/comment')) ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="parent_id" value="<?= (int) $comment['id'] ?>">
                      <div class="form-group">
                        <textarea class="form-control" name="body" rows="2" minlength="2" maxlength="5000" required placeholder="Write a reply..."><?= e(old('comment_body')) ?></textarea>
                      </div>
                      <button class="btn btn-outline btn-sm" type="submit">Post reply</button>
                    </form>
                  </details>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <?php if (Auth::check()): ?>
              <form method="post" action="<?= e(url('community/' . (int) $post['id'] . '/comment')) ?>" class="mt-2">
                <?= csrf_field() ?>
                <div class="form-group">
                  <label class="form-label" for="comment-body">Add a comment</label>
                  <textarea class="form-control" id="comment-body" name="body" rows="4" minlength="2" maxlength="5000" required placeholder="Share your thoughts..."><?= e(old('comment_body')) ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><?= icon('chat', 16) ?> Post comment</button>
              </form>
            <?php else: ?>
              <p class="small mt-2"><a href="<?= e(url('login')) ?>">Log in</a> to join the discussion.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <aside>
        <div class="panel"><div class="panel-body">
          <h3 class="mt-0">About this post</h3>
          <div class="side-info">
            <div class="row"><span>Author</span><strong><?= e($post['author_name']) ?></strong></div>
            <div class="row"><span>Role</span><span class="tag tag-blue"><?= e($authorRole) ?></span></div>
            <div class="row"><span>Category</span><strong><?= e($categoryLabels[$post['category']] ?? ucfirst((string) $post['category'])) ?></strong></div>
            <div class="row"><span>Published</span><strong><?= e(format_date($post['created_at'])) ?></strong></div>
            <div class="row"><span>Likes</span><strong><?= (int) $post['likes_count'] ?></strong></div>
          </div>
        </div></div>

        <?php if (Auth::check()): ?>
          <div class="card">
            <h3 class="mt-0">Report this post</h3>
            <p class="small muted">Help moderators keep the community safe.</p>
            <form method="post" action="<?= e(url('community/' . (int) $post['id'] . '/report')) ?>">
              <?= csrf_field() ?>
              <div class="form-group">
                <label class="form-label" for="report-reason">Reason</label>
                <select class="form-control" id="report-reason" name="reason">
                  <option value="spam">Spam</option>
                  <option value="inappropriate">Inappropriate content</option>
                  <option value="harassment">Harassment</option>
                  <option value="scam">Scam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="report-details">Details (optional)</label>
                <textarea class="form-control" id="report-details" name="details" rows="3" maxlength="1000"></textarea>
              </div>
              <button class="btn btn-outline btn-block" type="submit"><?= icon('flag', 15) ?> Submit report</button>
            </form>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>
