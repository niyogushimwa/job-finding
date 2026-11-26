<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Proof of work</span>
        <h1>My portfolio</h1>
        <p>Show employers what you have built. Projects appear on your public profile and CV.</p>
      </div>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Projects</h3><span class="small muted"><?= count($portfolios) ?> added</span></div>
        <div class="panel-body">
          <?php if ($portfolios): ?>
            <?php foreach ($portfolios as $project): ?>
              <article class="card mb-2">
                <div class="d-flex justify-between gap-1 flex-wrap">
                  <div>
                    <h3 class="mb-1"><?= e($project['title']) ?></h3>
                    <div class="d-flex flex-wrap gap-1">
                      <?php if (!empty($project['url'])): ?><a class="tag tag-blue" href="<?= e($project['url']) ?>" rel="noopener noreferrer" target="_blank"><?= icon('globe', 13) ?> Live project</a><?php endif; ?>
                      <?php if (!empty($project['repository_url'])): ?><a class="tag" href="<?= e($project['repository_url']) ?>" rel="noopener noreferrer" target="_blank"><?= icon('code', 13) ?> Code</a><?php endif; ?>
                      <?php if (!empty($project['completed_at'])): ?><span class="tag"><?= e(format_date($project['completed_at'])) ?></span><?php endif; ?>
                    </div>
                  </div>
                  <form method="post" action="<?= e(url('my-portfolio/' . $project['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this project?')">
                    <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 14) ?></button>
                  </form>
                </div>
                <?php if (!empty($project['description'])): ?><p class="small muted mt-2 mb-1"><?= e(str_limit($project['description'], 320)) ?></p><?php endif; ?>
                <?php if (!empty($project['tags'])): ?><p class="small muted mb-0"><?= e($project['tags']) ?></p><?php endif; ?>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <?php $emptyTitle = 'Your portfolio is empty'; $emptyText = 'Add a project, even a small one. Clear descriptions matter more than scale.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Add a project</h3></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('my-portfolio')) ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label" for="portfolio-title">Title</label><input class="form-control" id="portfolio-title" name="title" required maxlength="180" placeholder="e.g. Community jobs board"></div>
            <div class="form-group"><label class="form-label" for="portfolio-description">Description</label><textarea class="form-control" id="portfolio-description" name="description" rows="4" placeholder="What problem did you solve and how?"></textarea></div>
            <div class="form-group"><label class="form-label" for="portfolio-url">Project URL</label><input class="form-control" id="portfolio-url" name="url" type="url" placeholder="https://..."></div>
            <div class="form-group"><label class="form-label" for="portfolio-repo">Repository URL</label><input class="form-control" id="portfolio-repo" name="repository_url" type="url" placeholder="https://..."></div>
            <div class="form-row">
              <div class="form-group"><label class="form-label" for="portfolio-tags">Tags</label><input class="form-control" id="portfolio-tags" name="tags" maxlength="255" placeholder="PHP, MySQL, JavaScript"></div>
              <div class="form-group"><label class="form-label" for="portfolio-date">Completed on</label><input class="form-control" id="portfolio-date" name="completed_at" type="date"></div>
            </div>
            <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Add project</button>
          </form>
        </div>
      </section>
    </div>
  </main>
</div>
