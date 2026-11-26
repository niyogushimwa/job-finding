<?php
use App\Core\Auth;

$categoryLabels = [
    '' => 'All posts',
    'experience' => 'Experience',
    'question' => 'Questions',
    'advice' => 'Advice',
    'interview' => 'Interviews',
    'resource' => 'Resources',
    'achievement' => 'Achievements',
];
?>
<section class="section">
  <div class="container">
    <div class="d-flex justify-between align-center gap-2 flex-wrap mb-3">
      <div>
        <div class="section-kicker">Learn together</div>
        <h1 class="mb-1">Career Community</h1>
        <p class="muted mb-0">Share experience, ask questions and help each other grow.</p>
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <span class="badge"><?= (int) $stats['posts'] ?> posts</span>
        <span class="badge"><?= (int) $stats['comments'] ?> comments</span>
        <span class="badge"><?= (int) $stats['members'] ?> members</span>
      </div>
    </div>

    <nav class="tabs" aria-label="Community categories">
      <?php foreach ($categories as $category): ?>
        <a class="tab <?= $activeCategory === $category ? 'active' : '' ?>" href="<?= e(url('community' . ($category !== '' ? '?category=' . $category : ''))) ?>"><?= e($categoryLabels[$category] ?? ucfirst($category)) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="search-layout">
      <div>
        <?php if ($posts): ?>
          <?php foreach ($posts as $post): ?>
            <?php $liked = false; $full = false; include BASE_PATH . '/app/Views/partials/community_post.php'; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <?php
          $emptyTitle = 'No discussions yet';
          $emptyText = 'Be the first to share something in this category.';
          include BASE_PATH . '/app/Views/partials/empty.php';
          ?>
        <?php endif; ?>
      </div>

      <aside>
        <?php if (Auth::check()): ?>
          <div class="card">
            <h3 class="mt-0">Share with the community</h3>
            <form method="post" action="<?= e(url('community')) ?>" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <div class="form-group">
                <label class="form-label" for="community-title">Title</label>
                <input class="form-control" id="community-title" name="title" minlength="5" maxlength="220" required value="<?= e(old('title')) ?>" placeholder="What would you like to share?">
              </div>
              <div class="form-group">
                <label class="form-label" for="community-category">Category</label>
                <select class="form-control" id="community-category" name="category">
                  <?php foreach ($categories as $category): ?>
                    <?php if ($category === '') { continue; } ?>
                    <option value="<?= e($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>><?= e($categoryLabels[$category] ?? ucfirst($category)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="community-body">Your post</label>
                <textarea class="form-control" id="community-body" name="body" rows="6" minlength="10" maxlength="10000" required placeholder="Add enough detail to help others..."><?= e(old('body')) ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label" for="community-image">Image (optional)</label>
                <input class="form-control" id="community-image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
              </div>
              <button class="btn btn-primary btn-block" type="submit"><?= icon('plus', 16) ?> Publish post</button>
            </form>
          </div>
        <?php else: ?>
          <div class="panel"><div class="panel-body">
            <h3 class="mt-0">Join the conversation</h3>
            <p class="small muted">Log in to publish posts, comment and like discussions.</p>
            <a class="btn btn-primary btn-block" href="<?= e(url('login')) ?>">Log in</a>
          </div></div>
        <?php endif; ?>
        <div class="panel"><div class="panel-body">
          <h3 class="mt-0">Community guidelines</h3>
          <p class="small muted mb-0">Be respectful, protect your personal information and report anything suspicious.</p>
        </div></div>
      </aside>
    </div>
  </div>
</section>
