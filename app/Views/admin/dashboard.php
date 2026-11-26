<?php /** Administration dashboard. Expects $stats, $trend, $categories, $topJobs, $reports, $users, $announcements. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/dashboard';
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
      <div>
        <h1>Administration overview</h1>
        <p>Platform health, moderation queues and recent activity.</p>
      </div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/analytics')) ?>">Full analytics</a>
    </div>

    <?php
    $cards = [
      ['Users', $stats['users'] ?? 0, 'users'],
      ['Active users (30d)', $stats['active_users'] ?? 0, 'check'],
      ['Published jobs', $stats['published_jobs'] ?? 0, 'briefcase'],
      ['Pending jobs', $stats['pending_jobs'] ?? 0, 'clock'],
      ['Companies', $stats['companies'] ?? 0, 'building'],
      ['Verified companies', $stats['verified_companies'] ?? 0, 'shield'],
      ['Open reports', $stats['reported'] ?? 0, 'flag'],
      ['Applications', $stats['applications'] ?? 0, 'chart'],
    ];
    ?>
    <div class="grid grid-4" style="margin-bottom:1.5rem">
      <?php foreach ($cards as $card): ?>
        <div class="stat-card">
          <div class="stat-icon"><?= icon($card[2], 20) ?></div>
          <strong><?= e(number_format((int) $card[1])) ?></strong>
          <span><?= e($card[0]) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h2>Applications — last 14 days</h2><span class="badge"><?= e(count($trend)) ?> days with activity</span></div>
        <div class="panel-body">
          <?php $trendMax = max(1, ...array_column($trend, 'total')); ?>
          <?php if (!$trend): ?>
            <?php $emptyTitle = 'No applications yet'; $emptyText = 'Applications will appear here as job seekers apply.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="side-info">
              <?php foreach ($trend as $row): ?>
                <div class="row" style="display:grid;grid-template-columns:88px 1fr 42px;align-items:center;gap:.75rem;border:0;padding:.35rem 0">
                  <span><?= e(format_date($row['day'] ?? null, 'M j')) ?></span>
                  <span class="progress"><span style="width: <?= (int) round(((int) $row['total'] / $trendMax) * 100) ?>%"></span></span>
                  <strong style="text-align:right"><?= (int) $row['total'] ?></strong>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Popular job categories</h2><a href="<?= e(url('admin/categories')) ?>">Manage</a></div>
        <div class="panel-body">
          <?php $catMax = max(1, ...array_column($categories, 'total')); ?>
          <?php if (!$categories): ?>
            <?php $emptyTitle = 'No categories yet'; $emptyText = 'Add categories to organise job listings.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="side-info">
              <?php foreach ($categories as $row): ?>
                <div class="row" style="display:grid;grid-template-columns:150px 1fr 42px;align-items:center;gap:.75rem;border:0;padding:.4rem 0">
                  <span title="<?= e($row['name']) ?>"><?= e(str_limit((string) $row['name'], 22)) ?></span>
                  <span class="progress"><span style="width: <?= (int) round(((int) $row['total'] / $catMax) * 100) ?>%"></span></span>
                  <strong style="text-align:right"><?= (int) $row['total'] ?></strong>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Most viewed jobs</h2><a href="<?= e(url('admin/jobs')) ?>">All jobs</a></div>
        <div class="panel-body panel-body flush">
          <?php if (!$topJobs): ?>
            <?php $emptyTitle = 'No published jobs'; $emptyText = 'Published jobs and their view counts will show here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Job</th><th>Views</th><th>Applications</th></tr></thead><tbody>
              <?php foreach ($topJobs as $row): ?>
                <tr>
                  <td><a href="<?= e(url('jobs/' . $row['id'] . '/' . $row['slug'])) ?>"><?= e(str_limit((string) $row['title'], 48)) ?></a></td>
                  <td><?= e(number_format((int) $row['views_count'])) ?></td>
                  <td><?= (int) $row['applications_count'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Recent reports</h2><a href="<?= e(url('admin/reports')) ?>">Review</a></div>
        <div class="panel-body panel-body flush">
          <?php if (!$reports): ?>
            <?php $emptyTitle = 'No open reports'; $emptyText = 'Reported content will appear here for review.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Report</th><th>Reason</th><th>Status</th><th>When</th></tr></thead><tbody>
              <?php foreach ($reports as $row): ?>
                <tr>
                  <td><a href="<?= e(url('admin/reports')) ?>">#<?= (int) $row['id'] ?> · <?= e(str_limit((string) $row['type'], 20)) ?></a></td>
                  <td><?= e(str_replace('_', ' ', (string) $row['reason'])) ?></td>
                  <td><span class="status status-<?= e($row['status']) ?>"><?= e(str_replace('_', ' ', (string) $row['status'])) ?></span></td>
                  <td><?= e(time_ago($row['created_at'] ?? null)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Newest accounts</h2><a href="<?= e(url('admin/users')) ?>">All users</a></div>
        <div class="panel-body panel-body flush">
          <?php if (!$users): ?>
            <?php $emptyTitle = 'No accounts'; $emptyText = 'Registered users will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody>
              <?php foreach ($users as $row): ?>
                <tr>
                  <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['email']) ?></small></td>
                  <td><?= e($row['role']) ?></td>
                  <td><span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e(time_ago($row['created_at'] ?? null)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Announcements</h2><a href="<?= e(url('admin/settings')) ?>">Manage</a></div>
        <div class="panel-body">
          <?php if (!$announcements): ?>
            <?php $emptyTitle = 'No announcements'; $emptyText = 'Post an update from the settings page.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <ul class="timeline">
              <?php foreach ($announcements as $row): ?>
                <li>
                  <strong><?= e($row['title']) ?></strong>
                  <p style="margin:.25rem 0;color:var(--muted)"><?= e(str_limit(strip_tags((string) $row['body']), 120)) ?></p>
                  <time><?= e($row['admin_name'] ?: 'Administrator') ?> · <?= e(time_ago($row['created_at'] ?? null)) ?></time>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</div>
