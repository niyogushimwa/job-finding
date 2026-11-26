<?php
/** Shortlisted pipeline. Expected: $ctx, $applications, $statuses */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/shortlisted';
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
        <h1>Shortlisted candidates</h1>
        <p>Your active hiring pipeline — shortlisted, interviewing and selected candidates.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('employer/interviews')) ?>"><?= icon('clock', 16) ?> Schedule interview</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <div class="panel">
      <div class="panel-body flush table-wrap">
        <?php if (!$applications): ?>
          <?php $emptyTitle = 'Nobody shortlisted yet'; $emptyText = 'Move promising applicants to shortlisted from the applicants page to build your pipeline.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('employer/applicants')) . '">Review applicants</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <table>
            <thead><tr><th>Candidate</th><th>Opportunity</th><th>Match</th><th>Stage</th><th>Updated</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($applications as $app): ?>
                <tr>
                  <td><a href="<?= e(url('employer/applicants/' . $app['id'])) ?>"><?= e($app['applicant_name']) ?></a><div class="small muted"><?= e(str_limit($app['headline'] ?? '', 48)) ?></div></td>
                  <td><?= e(str_limit($app['job_title'] ?? '', 45)) ?></td>
                  <td><?= $app['match_score'] !== null ? (int) $app['match_score'] . '%' : '—' ?></td>
                  <td><span class="status status-<?= e($app['status']) ?>"><?= e($statuses[$app['status']] ?? str_replace('_', ' ', $app['status'])) ?></span></td>
                  <td class="muted"><?= e(time_ago($app['updated_at'] ?? $app['created_at'])) ?></td>
                  <td>
                    <div class="d-flex gap-1">
                      <a class="btn btn-outline btn-sm" href="<?= e(url('employer/applicants/' . $app['id'])) ?>">Review</a>
                      <form method="post" action="<?= e(url('employer/applications/' . $app['id'] . '/status')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="interview_scheduled">
                        <input type="hidden" name="note" value="Moved to interview stage">
                        <button class="btn btn-ghost btn-sm" type="submit">Interview</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
