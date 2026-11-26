<?php
/** Employer analytics. Expected: $ctx, $stats, $byStatus, $statuses, $monthly, $daily, $topJobs, $topSkills */
$maxMonthly = max(1, ...array_column($monthly, 'total'));
$maxDaily = max(1, ...array_column($daily, 'total'));
$maxSkills = max(1, ...array_column($topSkills, 'total'));
$totalApplications = array_sum($byStatus);
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/analytics';
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
        <h1>Analytics</h1>
        <p>Understand how your opportunities perform and where your candidates come from.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/dashboard')) ?>">Overview</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <div class="grid grid-4 mb-3">
      <div class="stat-card"><div class="stat-icon"><?= icon('briefcase', 20) ?></div><strong><?= (int) ($stats['jobs'] ?? 0) ?></strong><span>Opportunities</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('users', 20) ?></div><strong><?= (int) $totalApplications ?></strong><span>Applications</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('eye', 20) ?></div><strong><?= (int) ($stats['views'] ?? 0) ?></strong><span>Total views</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('clock', 20) ?></div><strong><?= (int) ($stats['interviews'] ?? 0) ?></strong><span>Interviews</span></div>
    </div>

    <div class="grid grid-2">
      <div class="panel">
        <div class="panel-head"><h2>Applications by status</h2><span class="small muted"><?= (int) $totalApplications ?> total</span></div>
        <div class="panel-body">
          <?php if (!$byStatus): ?><p class="small muted">No applications recorded yet.</p><?php else: ?>
            <?php foreach ($statuses as $key => $label): ?>
              <?php $count = (int) ($byStatus[$key] ?? 0); ?>
              <div class="d-flex justify-between small mb-1"><span><?= e($label) ?></span><strong><?= $count ?></strong></div>
              <div class="progress mb-2"><span style="width:<?= $totalApplications > 0 ? (int) round($count / $totalApplications * 100) : 0 ?>%"></span></div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Monthly application trend</h2><span class="small muted">Last 6 months</span></div>
        <div class="panel-body">
          <?php if (!$monthly): ?><p class="small muted">No application activity in the last six months.</p><?php else: ?>
            <?php foreach ($monthly as $row): ?>
              <div class="d-flex justify-between small mb-1"><span><?= e(date('M Y', strtotime((string) $row['period'] . '-01'))) ?></span><strong><?= (int) $row['total'] ?></strong></div>
              <div class="progress mb-2"><span style="width:<?= (int) round(((int) $row['total'] / $maxMonthly) * 100) ?>%"></span></div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Daily application trend</h2><span class="small muted">Last 14 days</span></div>
        <div class="panel-body">
          <?php if (!$daily): ?><p class="small muted">No application activity in the last two weeks.</p><?php else: ?>
            <?php foreach ($daily as $row): ?>
              <div class="d-flex justify-between small mb-1"><span><?= e(format_date($row['period'], 'M j')) ?></span><strong><?= (int) $row['total'] ?></strong></div>
              <div class="progress mb-2"><span style="width:<?= (int) round(((int) $row['total'] / $maxDaily) * 100) ?>%"></span></div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Top skills among applicants</h2><span class="small muted">From candidate profiles</span></div>
        <div class="panel-body">
          <?php if (!$topSkills): ?><p class="small muted">No skills recorded for your applicants yet.</p><?php else: ?>
            <?php foreach ($topSkills as $row): ?>
              <div class="d-flex justify-between small mb-1"><span><?= e($row['name']) ?></span><strong><?= (int) $row['total'] ?></strong></div>
              <div class="progress mb-2"><span style="width:<?= (int) round(((int) $row['total'] / $maxSkills) * 100) ?>%"></span></div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Top performing opportunities</h2><a class="small" href="<?= e(url('employer/jobs')) ?>">Manage jobs →</a></div>
      <div class="panel-body flush table-wrap">
        <?php if (!$topJobs): ?><p class="small muted" style="padding:1.4rem">No opportunities to analyse yet.</p><?php else: ?>
          <table>
            <thead><tr><th>Opportunity</th><th>Views</th><th>Applications</th><th>Conversion</th></tr></thead>
            <tbody>
              <?php foreach ($topJobs as $job): ?>
                <?php $views = (int) $job['views_count']; $apps = (int) $job['applicants_count']; ?>
                <tr>
                  <td><a href="<?= e(url('employer/jobs/' . $job['id'] . '/edit')) ?>"><?= e(str_limit($job['title'], 55)) ?></a></td>
                  <td><?= $views ?></td>
                  <td><?= $apps ?></td>
                  <td><?= $views > 0 ? number_format($apps / $views * 100, 1) . '%' : '—' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
