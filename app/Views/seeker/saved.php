<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Your shortlist</span>
        <h1>Saved jobs</h1>
        <p>Roles you bookmarked while browsing. Remove the bookmark from any card when you are done.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('jobs')) ?>"><?= icon('search', 16) ?> Browse jobs</a>
    </div>

    <section class="panel">
      <div class="panel-body">
        <?php if ($jobs): ?>
          <div class="grid grid-2">
            <?php foreach ($jobs as $job): include BASE_PATH . '/app/Views/partials/job_card.php'; endforeach; ?>
          </div>
        <?php else: ?>
          <?php $emptyTitle = 'Nothing saved yet'; $emptyText = 'Use the bookmark button on any job to keep it here for later.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('jobs')) . '">Find jobs</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
