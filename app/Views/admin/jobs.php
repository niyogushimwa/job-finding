<?php /** Expects $rows, $pager, $status, $q. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/jobs';
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
      <div><h1>Jobs</h1><p>Moderate listings and control which jobs are visible.</p></div>
      <span class="badge"><?= e($pager->summary()) ?></span>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/jobs')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="job-q">Search</label>
          <input class="form-control" id="job-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Job title, company or employer">
        </div>
        <div class="form-group">
          <label class="form-label" for="job-status">Status</label>
          <select class="form-control" id="job-status" name="status">
            <option value="">All statuses</option>
            <?php foreach (['draft', 'pending_review', 'published', 'closed', 'archived', 'rejected'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>All listings</h2><a href="<?= e(url('admin/jobs')) ?>">Clear filters</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No jobs found'; $emptyText = 'Try another status filter or search term.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <?php $jobStatuses = ['draft', 'pending_review', 'published', 'closed', 'archived', 'rejected']; ?>
          <div class="table-wrap"><table>
            <thead><tr><th>Job</th><th>Company</th><th>Category</th><th>Views</th><th>Status</th><th>Moderate</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <a href="<?= e(url('jobs/' . $row['id'] . '/' . $row['slug'])) ?>"><strong><?= e(str_limit((string) $row['title'], 46)) ?></strong></a><br>
                    <small><?= e($row['location']) ?> · <?= e(format_date($row['deadline'] ?? null)) ?></small>
                  </td>
                  <td><?= e($row['company_name'] ?: $row['employer_name'] ?: '—') ?></td>
                  <td><?= e($row['category_name'] ?: '—') ?></td>
                  <td><?= e(number_format((int) $row['views_count'])) ?></td>
                  <td><span class="status status-<?= e($row['status']) ?>"><?= e(str_replace('_', ' ', (string) $row['status'])) ?></span></td>
                  <td>
                    <form method="post" action="<?= e(url('admin/jobs/' . $row['id'] . '/moderate')) ?>" style="display:flex;gap:.4rem;align-items:center">
                      <?= csrf_field() ?>
                      <select class="form-control" name="status" style="min-width:140px;min-height:34px;padding:.35rem .5rem" aria-label="Status for <?= e($row['title']) ?>">
                        <?php foreach ($jobStatuses as $option): ?>
                          <option value="<?= e($option) ?>" <?= $row['status'] === $option ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $option)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm btn-outline" type="submit">Save</button>
                    </form>
                  </td>
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
