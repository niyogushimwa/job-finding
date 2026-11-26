<?php
/** Employer dashboard. Expected: $ctx, $stats, $recentJobs, $recentApplications, $upcoming, $unverified, $statuses */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/dashboard';
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
        <h1>Employer overview</h1>
        <p>Welcome back, <?= e(\App\Core\Auth::user()['name'] ?? 'there') ?>. Here is what is happening across your opportunities.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('employer/jobs/create')) ?>"><?= icon('plus', 16) ?> Post a job</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)">
        <strong>Administrator view</strong>
        <p class="small mb-0"><?= e($ctx['notice']) ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($unverified)): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)">
        <strong><?= icon('shield', 16) ?> Company verification pending</strong>
        <p class="small mb-0">Your company is not yet verified. Complete your <a href="<?= e(url('employer/company')) ?>">company profile</a> so administrators can review it — verified companies attract more applicants.</p>
      </div>
    <?php endif; ?>

    <div class="grid grid-3 mb-3">
      <div class="stat-card"><div class="stat-icon"><?= icon('briefcase', 20) ?></div><strong><?= (int) ($stats['jobs'] ?? 0) ?></strong><span>Total opportunities</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('check', 20) ?></div><strong><?= (int) ($stats['published'] ?? 0) ?></strong><span>Published</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('users', 20) ?></div><strong><?= (int) ($stats['applications'] ?? 0) ?></strong><span>Applications received</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('star', 20) ?></div><strong><?= (int) ($stats['shortlisted'] ?? 0) ?></strong><span>Shortlisted candidates</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('clock', 20) ?></div><strong><?= (int) ($stats['interviews'] ?? 0) ?></strong><span>Interviews</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('eye', 20) ?></div><strong><?= (int) ($stats['views'] ?? 0) ?></strong><span>Opportunity views</span></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Recent opportunities</h2><a class="small" href="<?= e(url('employer/jobs')) ?>">Manage all →</a></div>
      <div class="panel-body flush table-wrap">
        <?php if (!$recentJobs): ?>
          <?php $emptyTitle = 'No opportunities yet'; $emptyText = 'Post your first opportunity to start receiving applications from matched candidates.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('employer/jobs/create')) . '">Post a job</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <table>
            <thead><tr><th>Opportunity</th><th>Company</th><th>Status</th><th>Applicants</th><th>Views</th><th>Created</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($recentJobs as $job): ?>
                <tr>
                  <td><a href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>"><?= e(str_limit($job['title'], 55)) ?></a></td>
                  <td><?= e($job['company_name'] ?? '—') ?></td>
                  <td><span class="status status-<?= e($job['status']) ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span></td>
                  <td><?= (int) $job['applicants_count'] ?></td>
                  <td><?= (int) $job['views_count'] ?></td>
                  <td class="muted"><?= e(time_ago($job['created_at'])) ?></td>
                  <td><a class="small" href="<?= e(url('employer/jobs/' . $job['id'] . '/edit')) ?>">Edit</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid grid-2">
      <div class="panel">
        <div class="panel-head"><h2>Latest applications</h2><a class="small" href="<?= e(url('employer/applicants')) ?>">View all →</a></div>
        <div class="panel-body flush table-wrap">
          <?php if (!$recentApplications): ?>
            <?php $emptyTitle = 'No applications yet'; $emptyText = 'Applications will appear here as soon as candidates apply to your opportunities.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <table>
              <thead><tr><th>Candidate</th><th>Opportunity</th><th>Status</th><th>Applied</th></tr></thead>
              <tbody>
                <?php foreach ($recentApplications as $app): ?>
                  <tr>
                    <td><a href="<?= e(url('employer/applicants/' . $app['id'])) ?>"><?= e($app['applicant_name'] ?? 'Candidate') ?></a></td>
                    <td><?= e(str_limit($app['job_title'] ?? '', 40)) ?></td>
                    <td><span class="status status-<?= e($app['status']) ?>"><?= e(str_replace('_', ' ', $app['status'])) ?></span></td>
                    <td class="muted"><?= e(time_ago($app['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Upcoming interviews</h2><a class="small" href="<?= e(url('employer/interviews')) ?>">Schedule →</a></div>
        <div class="panel-body flush table-wrap">
          <?php if (!$upcoming): ?>
            <?php $emptyTitle = 'Nothing scheduled'; $emptyText = 'Shortlist a candidate and schedule an interview to see it here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <table>
              <thead><tr><th>Candidate</th><th>Opportunity</th><th>When</th><th>Length</th></tr></thead>
              <tbody>
                <?php foreach ($upcoming as $interview): ?>
                  <tr>
                    <td><?= e($interview['applicant_name']) ?></td>
                    <td><?= e(str_limit($interview['job_title'], 40)) ?></td>
                    <td><?= e(format_date($interview['scheduled_at'], 'M j, Y · H:i')) ?></td>
                    <td><?= (int) $interview['duration_minutes'] ?> min</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>
