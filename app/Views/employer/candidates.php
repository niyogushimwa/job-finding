<?php
/** Candidate search. Expected: $ctx, $candidates, $pager, $filters */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/candidates';
  $links = [
      ['Overview', 'employer/dashboard', 'chart'],
      ['Company profile', 'employer/company', 'building'],
      ['Post a job', 'employer/jobs/create', 'plus'],
      ['Manage jobs', 'employer/jobs', 'briefcase'],
      ['Applicants', 'employer/applicants', 'users'],
      ['Find candidates', 'employer/candidates', 'search'],
      ['Shortlisted', 'employer/shortlisted', 'star'],
      ['Interviews', 'employer/interviews', 'clock'],
      ['Analytics', 'employer/analytics', 'chart'],
  ];
  include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php';
  ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>Find candidates</h1>
        <p><?= e($pager->summary()) ?> · Search profiles of candidates actively open to work.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/applicants')) ?>"><?= icon('users', 16) ?> Applicants</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <form class="filters mb-3" method="get" action="<?= e(url('employer/candidates')) ?>">
      <div class="filter-row">
        <div class="form-group"><label class="form-label">Keywords</label><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Name, headline or summary"></div>
        <div class="form-group"><label class="form-label">Location</label><input class="form-control" name="location" value="<?= e($filters['location']) ?>" placeholder="Kigali or Remote"></div>
        <div class="form-group"><label class="form-label">Skill</label><input class="form-control" name="skill" value="<?= e($filters['skill']) ?>" placeholder="e.g. React"></div>
        <div class="form-group"><label class="form-label">Education</label><input class="form-control" name="education" value="<?= e($filters['education']) ?>" placeholder="e.g. Bachelor"></div>
        <div class="form-group"><label class="form-label">Minimum years</label><input class="form-control" name="experience" type="number" min="0" max="60" value="<?= e($filters['experience']) ?>" placeholder="Any"></div>
        <div class="form-group d-flex align-end gap-1"><button class="btn btn-primary" type="submit"><?= icon('search', 16) ?> Search</button><a class="btn btn-outline" href="<?= e(url('employer/candidates')) ?>">Clear</a></div>
      </div>
    </form>

    <?php if (!$candidates): ?>
      <?php $emptyTitle = 'No candidates found'; $emptyText = 'Try broader keywords or remove a filter to see more profiles.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
    <?php else: ?>
      <div class="grid grid-2">
        <?php foreach ($candidates as $candidate): ?>
          <div class="card">
            <div class="d-flex align-center gap-2">
              <span class="company-logo"><?= e(mb_substr((string) $candidate['name'], 0, 1)) ?></span>
              <div style="min-width:0;flex:1">
                <h3 class="mb-1"><?= e($candidate['name']) ?></h3>
                <div class="small muted"><?= e($candidate['headline'] ?? 'Professional') ?></div>
              </div>
              <span class="badge"><?= (int) $candidate['experience_years'] ?> yrs</span>
            </div>
            <p class="small muted mt-2"><?= e(str_limit($candidate['summary'] ?? 'No profile summary provided.', 180)) ?></p>
            <div class="row"><span>Location</span><strong><?= e($candidate['location'] ?? '—') ?></strong></div>
            <div class="row"><span>Education</span><strong><?= e($candidate['education_level'] ?? '—') ?></strong></div>
            <div class="d-flex flex-wrap gap-1 mt-2">
              <?php if (!empty($candidate['skills'])): ?>
                <?php foreach (explode(', ', (string) $candidate['skills']) as $skill): ?><span class="tag tag-blue"><?= e($skill) ?></span><?php endforeach; ?>
              <?php else: ?><span class="small muted">No skills listed</span><?php endif; ?>
            </div>
            <div class="d-flex gap-1 mt-2">
              <a class="btn btn-outline btn-sm" href="<?= e(url('u/' . $candidate['user_id'])) ?>">View public profile</a>
              <a class="btn btn-ghost btn-sm" href="mailto:<?= e($candidate['email']) ?>">Contact</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    <?php endif; ?>
  </main>
</div>
