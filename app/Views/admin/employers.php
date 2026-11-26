<?php /** Expects $rows, $status, $q. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/employers';
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
      <div><h1>Employers</h1><p>Review company verification and their published listings.</p></div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/verification')) ?>">Verification queue</a>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/employers')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="employer-q">Search</label>
          <input class="form-control" id="employer-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Company, owner or email">
        </div>
        <div class="form-group">
          <label class="form-label" for="employer-status">Verification</label>
          <select class="form-control" id="employer-status" name="status">
            <option value="">All states</option>
            <?php foreach (['unverified', 'pending', 'verified', 'rejected'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>Registered companies</h2><span class="badge"><?= e(count($rows)) ?> shown</span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No companies found'; $emptyText = 'Try a different search or verification filter.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <?php $verificationStatuses = ['unverified', 'pending', 'verified', 'rejected']; ?>
          <div class="table-wrap"><table>
            <thead><tr><th>Company</th><th>Owner</th><th>Jobs</th><th>Verification</th><th>Verified</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <a href="<?= e(url('companies/' . $row['id'] . '/' . $row['slug'])) ?>"><strong><?= e($row['name']) ?></strong></a><br>
                    <small><?= e($row['location'] ?: 'Location not set') ?></small>
                  </td>
                  <td>
                    <?php if (!empty($row['user_id'])): ?>
                      <?= e($row['owner_name'] ?: '—') ?><br><small><?= e($row['owner_email'] ?: '') ?></small>
                    <?php else: ?>
                      <span class="badge">No linked owner</span>
                    <?php endif; ?>
                  </td>
                  <td><?= (int) $row['jobs_count'] ?></td>
                  <td><span class="status status-<?= e($row['verification_status']) ?>"><?= e($row['verification_status']) ?></span></td>
                  <td><?= e(format_date($row['verified_at'] ?? null)) ?></td>
                  <td>
                    <form method="post" action="<?= e(url('admin/employers/' . $row['id'] . '/verify')) ?>" style="display:flex;gap:.4rem;align-items:center">
                      <?= csrf_field() ?>
                      <select class="form-control" name="status" style="min-width:125px;min-height:34px;padding:.35rem .5rem" aria-label="Verification for <?= e($row['name']) ?>">
                        <?php foreach ($verificationStatuses as $option): ?>
                          <option value="<?= e($option) ?>" <?= $row['verification_status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm btn-outline" type="submit">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
