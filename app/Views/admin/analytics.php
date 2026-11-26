<?php /** Expects $stats, $trend, $categories, $topJobs, $companies, $registrations, $views. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/analytics';
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
      <div><h1>Platform analytics</h1><p>Thirty-day trends in registrations, applications and job views.</p></div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/dashboard')) ?>">Overview</a>
    </div>

    <?php
    $cards = [
      ['Total users', $stats['users'] ?? 0, 'users'],
      ['Active users (30d)', $stats['active_users'] ?? 0, 'check'],
      ['Employers', $stats['employers'] ?? 0, 'building'],
      ['Published jobs', $stats['published_jobs'] ?? 0, 'briefcase'],
      ['External opportunities', $stats['external_opportunities'] ?? 0, 'globe'],
      ['Applications', $stats['applications'] ?? 0, 'chart'],
      ['Newsletter', $stats['newsletter'] ?? 0, 'mail'],
      ['Community posts', $stats['community_posts'] ?? 0, 'chat'],
    ];
    ?>
    <div class="grid grid-4" style="margin-bottom:1.5rem">
      <?php foreach ($cards as $card): ?>
        <div class="stat-card"><div class="stat-icon"><?= icon($card[2], 20) ?></div><strong><?= e(number_format((int) $card[1])) ?></strong><span><?= e($card[0]) ?></span></div>
      <?php endforeach; ?>
    </div>

    <?php
    $drawBars = static function (array $rows, string $emptyTitle, string $emptyText): void {
        if (!$rows) {
            include BASE_PATH . '/app/Views/partials/empty.php';
            return;
        }
        $max = max(1, ...array_column($rows, 'total'));
        echo '<div class="side-info">';
        foreach ($rows as $row) {
            $width = (int) round(((int) $row['total'] / $max) * 100);
            echo '<div class="row" style="display:grid;grid-template-columns:88px 1fr 42px;align-items:center;gap:.75rem;border:0;padding:.3rem 0">';
            echo '<span>' . e(format_date($row['day'] ?? null, 'M j')) . '</span>';
            echo '<span class="progress"><span style="width: ' . $width . '%"></span></span>';
            echo '<strong style="text-align:right">' . (int) $row['total'] . '</strong>';
            echo '</div>';
        }
        echo '</div>';
    };
    ?>

    <div class="grid grid-3">
      <section class="panel">
        <div class="panel-head"><h2>Applications / day</h2><span class="badge">30 days</span></div>
        <div class="panel-body"><?php $drawBars($trend, 'No applications', 'Application activity will appear here.'); ?></div>
      </section>
      <section class="panel">
        <div class="panel-head"><h2>Registrations / day</h2><span class="badge">30 days</span></div>
        <div class="panel-body"><?php $drawBars($registrations, 'No registrations', 'New accounts will appear here.'); ?></div>
      </section>
      <section class="panel">
        <div class="panel-head"><h2>Job views / day</h2><span class="badge">30 days</span></div>
        <div class="panel-body"><?php $drawBars($views, 'No job views', 'Job view activity will appear here.'); ?></div>
      </section>
    </div>

    <div class="grid grid-3">
      <section class="panel">
        <div class="panel-head"><h2>Popular categories</h2></div>
        <div class="panel-body">
          <?php $catMax = max(1, ...array_column($categories, 'total')); ?>
          <?php if (!$categories): ?>
            <?php $emptyTitle = 'No categories'; $emptyText = 'Category usage will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="side-info">
              <?php foreach ($categories as $row): ?>
                <div class="row" style="display:grid;grid-template-columns:130px 1fr 35px;align-items:center;gap:.6rem;border:0;padding:.35rem 0">
                  <span title="<?= e($row['name']) ?>"><?= e(str_limit((string) $row['name'], 18)) ?></span>
                  <span class="progress"><span style="width: <?= (int) round(((int) $row['total'] / $catMax) * 100) ?>%"></span></span>
                  <strong style="text-align:right"><?= (int) $row['total'] ?></strong>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Most viewed jobs</h2></div>
        <div class="panel-body panel-body flush">
          <?php if (!$topJobs): ?>
            <?php $emptyTitle = 'No job views'; $emptyText = 'Published jobs will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Job</th><th>Views</th></tr></thead><tbody>
              <?php foreach ($topJobs as $row): ?>
                <tr><td><?= e(str_limit((string) $row['title'], 38)) ?></td><td><?= e(number_format((int) $row['views_count'])) ?></td></tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Most active companies</h2></div>
        <div class="panel-body panel-body flush">
          <?php if (!$companies): ?>
            <?php $emptyTitle = 'No companies yet'; $emptyText = 'Company job activity will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Company</th><th>Jobs</th><th>State</th></tr></thead><tbody>
              <?php foreach ($companies as $row): ?>
                <tr>
                  <td><?= e(str_limit((string) $row['name'], 30)) ?></td>
                  <td><?= (int) $row['jobs'] ?></td>
                  <td><span class="status status-<?= e($row['verification_status']) ?>"><?= e($row['verification_status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</div>
