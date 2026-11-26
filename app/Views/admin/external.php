<?php /** Expects $rows, $pager, $status, $verification, $q. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/external-opportunities';
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
      <div><h1>External opportunities</h1><p>Curate scholarships, fellowships and opportunities from trusted sources.</p></div>
      <a class="btn btn-primary btn-sm" href="<?= e(url('admin/external-opportunities/create')) ?>">Add opportunity</a>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/external-opportunities')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="ext-q">Search</label>
          <input class="form-control" id="ext-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Title or organisation">
        </div>
        <div class="form-group">
          <label class="form-label" for="ext-status">Status</label>
          <select class="form-control" id="ext-status" name="status">
            <option value="">All statuses</option>
            <?php foreach (['draft', 'published', 'archived', 'expired'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="ext-verification">Verification</label>
          <select class="form-control" id="ext-verification" name="verification">
            <option value="">All states</option>
            <?php foreach (['unverified', 'verified', 'rejected'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $verification === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>Opportunity radar entries</h2><span class="badge"><?= e($pager->summary()) ?></span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No opportunities found'; $emptyText = 'Add an opportunity or adjust the filters above.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <?php $statuses = ['draft', 'published', 'archived', 'expired']; $verifications = ['unverified', 'verified', 'rejected']; ?>
          <div class="table-wrap"><table>
            <thead><tr><th>Opportunity</th><th>Category</th><th>Deadline</th><th>Status</th><th>Verification</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <a href="<?= e(url('opportunity-radar/' . $row['id'])) ?>"><strong><?= e(str_limit((string) $row['title'], 44)) ?></strong></a><br>
                    <small><?= e($row['organization']) ?></small>
                  </td>
                  <td><?= e($row['opportunity_category_name'] ?: $row['category_name'] ?: '—') ?></td>
                  <td><?= e(format_date($row['deadline'] ?? null)) ?></td>
                  <td>
                    <span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span>
                    <form method="post" action="<?= e(url('admin/external-opportunities/' . $row['id'] . '/status')) ?>" style="display:flex;gap:.35rem;margin-top:.4rem">
                      <?= csrf_field() ?>
                      <select class="form-control" name="status" style="min-width:115px;min-height:32px;padding:.3rem .45rem" aria-label="Status for <?= e($row['title']) ?>">
                        <?php foreach ($statuses as $option): ?>
                          <option value="<?= e($option) ?>" <?= $row['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm btn-outline" type="submit">Save</button>
                    </form>
                  </td>
                  <td>
                    <span class="status status-<?= e($row['verification_status']) ?>"><?= e($row['verification_status']) ?></span>
                    <form method="post" action="<?= e(url('admin/external-opportunities/' . $row['id'] . '/verify')) ?>" style="display:flex;gap:.35rem;margin-top:.4rem">
                      <?= csrf_field() ?>
                      <select class="form-control" name="status" style="min-width:125px;min-height:32px;padding:.3rem .45rem" aria-label="Verification for <?= e($row['title']) ?>">
                        <?php foreach ($verifications as $option): ?>
                          <option value="<?= e($option) ?>" <?= $row['verification_status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm btn-outline" type="submit">Save</button>
                    </form>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-outline" href="<?= e(url('admin/external-opportunities/' . $row['id'] . '/edit')) ?>">Edit</a>
                    <form method="post" action="<?= e(url('admin/external-opportunities/' . $row['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Delete this opportunity permanently?')">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-danger" type="submit">Delete</button>
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
