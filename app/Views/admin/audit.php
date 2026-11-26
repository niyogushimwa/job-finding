<?php /** Expects $rows, $pager, $action, $q. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/audit-logs';
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
      <div><h1>Audit logs</h1><p>A chronological record of administrative and account actions.</p></div>
      <span class="badge"><?= e($pager->summary()) ?></span>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/audit-logs')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="audit-action">Action</label>
          <input class="form-control" id="audit-action" type="search" name="action" value="<?= e($action) ?>" placeholder="e.g. user_status">
        </div>
        <div class="form-group">
          <label class="form-label" for="audit-q">Search</label>
          <input class="form-control" id="audit-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Description, user or IP">
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>Activity</h2><a href="<?= e(url('admin/audit-logs')) ?>">Clear filters</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No audit entries'; $emptyText = 'Actions will be recorded here as the platform is used.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table>
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><small><?= e(format_date($row['created_at'] ?? null, 'M j, Y H:i')) ?></small></td>
                  <td>
                    <?php if (!empty($row['user_id'])): ?>
                      <?= e($row['user_name'] ?: 'User #' . $row['user_id']) ?><br><small><?= e($row['user_email'] ?: '') ?></small>
                    <?php else: ?>
                      <span class="badge">System</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge"><?= e($row['action']) ?></span></td>
                  <td><?= e(str_limit(strip_tags((string) ($row['description'] ?: '')), 150)) ?></td>
                  <td><small><?= e($row['ip'] ?: '—') ?></small></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
          <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
