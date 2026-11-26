<?php /** Expects $counts, $articles, $questions. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/content';
  $links = [
    ['Overview', 'admin/dashboard', 'chart'],
    ['Users', 'admin/users', 'users'],
    ['Employers', 'admin/employers', 'building'],
    ['Jobs', 'admin/jobs', 'briefcase'],
    ['External opportunities', 'admin/external-opportunities', 'globe'],
    ['Categories', 'admin/categories', 'list'],
    ['Verification', 'admin/verification', 'shield'],
    ['Reports', 'admin/reports', 'flag'],
    ['Content', 'admin/content', 'edit'],
    ['Ads', 'admin/ads', 'star'],
    ['Analytics', 'admin/analytics', 'chart'],
    ['Settings', 'admin/settings', 'settings'],
    ['Audit logs', 'admin/audit-logs', 'clock'],
  ];
  include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php';
  ?>
  <main class="dash-main">
    <div class="dash-head">
      <div><h1>Content</h1><p>Publish career articles and manage interview preparation questions.</p></div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('career-centre')) ?>">View career centre</a>
    </div>

    <div class="grid grid-4" style="margin-bottom:1.5rem">
      <?php foreach ([['Articles', 'articles', 'edit'], ['Interview questions', 'questions', 'chat'], ['Career tips', 'tips', 'info'], ['Success stories', 'stories', 'award'], ['Newsletter subscribers', 'newsletters', 'mail']] as $card): ?>
        <div class="stat-card"><div class="stat-icon"><?= icon($card[2], 20) ?></div><strong><?= e(number_format((int) ($counts[$card[1]] ?? 0))) ?></strong><span><?= e($card[0]) ?></span></div>
      <?php endforeach; ?>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h2>New article</h2></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('admin/content/articles')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="article-title">Title *</label>
              <input class="form-control" id="article-title" name="title" maxlength="220" required value="<?= e(old('title')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="article-slug">Slug</label>
              <input class="form-control" id="article-slug" name="slug" maxlength="240" placeholder="Generated from title if empty" value="<?= e(old('slug')) ?>">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="article-category">Category</label>
                <input class="form-control" id="article-category" name="category" maxlength="80" value="<?= e(old('category')) ?>">
              </div>
              <div class="form-group">
                <label class="form-label" for="article-status">Status</label>
                <select class="form-control" id="article-status" name="status">
                  <?php foreach (['draft', 'published', 'archived'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= old('status', 'draft') === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="article-excerpt">Excerpt</label>
              <textarea class="form-control" id="article-excerpt" name="excerpt" rows="2"><?= e(old('excerpt')) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="article-body">Body *</label>
              <textarea class="form-control" id="article-body" name="body" rows="5" required><?= e(old('body')) ?></textarea>
              <span class="form-hint">Basic HTML such as &lt;h2&gt; and &lt;p&gt; is supported.</span>
            </div>
            <label class="checkbox"><input type="checkbox" name="featured" value="1" <?= old('featured') ? 'checked' : '' ?>> Feature this article</label>
            <div style="margin-top:1.2rem"><button class="btn btn-primary" type="submit">Publish / save article</button></div>
          </form>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>New interview question</h2></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('admin/content/questions')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="question-category">Category</label>
              <select class="form-control" id="question-category" name="category">
                <?php foreach (['common', 'technical', 'hr', 'behavioral'] as $option): ?>
                  <option value="<?= e($option) ?>" <?= old('category', 'common') === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="question-text">Question *</label>
              <textarea class="form-control" id="question-text" name="question" rows="2" required><?= e(old('question')) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="question-answer">Sample answer</label>
              <textarea class="form-control" id="question-answer" name="sample_answer" rows="4"><?= e(old('sample_answer')) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="question-tips">Tips</label>
              <textarea class="form-control" id="question-tips" name="tips" rows="2"><?= e(old('tips')) ?></textarea>
            </div>
            <label class="checkbox"><input type="checkbox" name="active" value="1" <?= old('active', '1') ? 'checked' : '' ?>> Active</label>
            <div style="margin-top:1.2rem"><button class="btn btn-primary" type="submit">Add question</button></div>
          </form>
        </div>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h2>Articles</h2><span class="badge"><?= e(count($articles)) ?> recent</span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$articles): ?>
          <?php $emptyTitle = 'No articles yet'; $emptyText = 'Use the form above to publish your first article.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Author</th><th>Published</th><th></th></tr></thead><tbody>
            <?php foreach ($articles as $row): ?>
              <tr>
                <td>
                  <a href="<?= e(url('career-centre/' . $row['slug'])) ?>"><strong><?= e(str_limit((string) $row['title'], 50)) ?></strong></a><br>
                  <small><code><?= e($row['slug']) ?></code></small>
                </td>
                <td><?= e($row['category'] ?: '—') ?></td>
                <td><span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                <td><?= e($row['author_name'] ?: '—') ?></td>
                <td><?= e(format_date($row['published_at'] ?? null)) ?></td>
                <td>
                  <form method="post" action="<?= e(url('admin/content/articles/' . $row['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this article permanently?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Interview questions</h2><span class="badge"><?= e(count($questions)) ?> recent</span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$questions): ?>
          <?php $emptyTitle = 'No questions yet'; $emptyText = 'Add common interview questions for the preparation centre.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Category</th><th>Question</th><th>Active</th><th>Added</th></tr></thead><tbody>
            <?php foreach ($questions as $row): ?>
              <tr>
                <td><span class="badge"><?= e($row['category']) ?></span></td>
                <td><?= e(str_limit(strip_tags((string) $row['question']), 130)) ?></td>
                <td><span class="status <?= (int) $row['active'] === 1 ? 'status-published' : '' ?>"><?= (int) $row['active'] === 1 ? 'Active' : 'Hidden' ?></span></td>
                <td><?= e(format_date($row['created_at'] ?? null)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
