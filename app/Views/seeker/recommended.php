<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Curated for you</span>
        <h1>Recommended jobs</h1>
        <p>Matched using your skills, experience, education, location and job-type preferences.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('my-skills')) ?>"><?= icon('award', 16) ?> Improve matches</a>
    </div>

    <section class="panel">
      <div class="panel-body">
        <h3 class="mt-0">How the score works</h3>
        <div class="grid grid-3">
          <p class="small muted mb-0"><strong>Skills (42 pts)</strong> — weighted overlap with the skills each employer asks for.</p>
          <p class="small muted mb-0"><strong>Experience & education (24 pts)</strong> — your years and qualification against the role requirements.</p>
          <p class="small muted mb-0"><strong>Location, job type & category (34 pts)</strong> — where and how you want to work.</p>
        </div>
        <p class="small muted mb-0 mt-2">Scores are a guide, not a decision: a lower score can still be a great opportunity if the role interests you.</p>
      </div>
    </section>

    <?php if ($jobs): ?>
      <?php foreach (array_slice($jobs, 0, 3) as $job): ?>
        <?php if (empty($job['match']['breakdown'])) continue; ?>
        <section class="panel">
          <div class="panel-head"><h3><?= e(str_limit($job['title'], 60)) ?> · match breakdown</h3><span class="match-badge"><?= (int) $job['match_score'] ?>% overall</span></div>
          <div class="panel-body">
            <div class="grid grid-3">
              <?php foreach ($job['match']['breakdown'] as $label => $points): ?>
                <div>
                  <div class="d-flex justify-between small mb-1"><span><?= e($label) ?></span><strong><?= (int) $points ?> pts</strong></div>
                  <div class="progress"><span style="width:<?= min(100, (int) $points * 3) ?>%"></span></div>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="small muted mb-0 mt-2">
              <strong style="color:var(--green)">You have:</strong> <?= e($job['match']['matched'] ? implode(', ', $job['match']['matched']) : 'No listed job skills yet') ?>
              · <strong style="color:var(--amber)">To develop:</strong> <?= e($job['match']['missing'] ? implode(', ', $job['match']['missing']) : 'Nothing missing') ?>
            </p>
          </div>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>

    <section class="panel">
      <div class="panel-head"><h3>All matches</h3><span class="small muted"><?= count($jobs) ?> opportunities</span></div>
      <div class="panel-body">
        <?php if ($jobs): ?>
          <div class="grid grid-2">
            <?php foreach ($jobs as $job): include BASE_PATH . '/app/Views/partials/job_card.php'; endforeach; ?>
          </div>
        <?php else: ?>
          <?php $emptyTitle = 'No recommendations yet'; $emptyText = 'Complete your profile and add skills so we can calculate meaningful matches.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('profile/edit')) . '">Complete profile</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
