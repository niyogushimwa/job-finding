<?php /** Expects $rows, $pager, $role, $status, $q. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/users';
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
      <div><h1>Users</h1><p>Manage roles and account status across the platform.</p></div>
      <span class="badge"><?= e($pager->summary()) ?></span>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/users')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="user-q">Search</label>
          <input class="form-control" id="user-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name or email">
        </div>
        <div class="form-group">
          <label class="form-label" for="user-role">Role</label>
          <select class="form-control" id="user-role" name="role">
            <option value="">All roles</option>
            <?php foreach (['seeker', 'employer', 'freelancer', 'admin'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $role === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="user-status">Status</label>
          <select class="form-control" id="user-status" name="status">
            <option value="">All statuses</option>
            <?php foreach (['active', 'pending', 'suspended', 'deactivated'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>Accounts</h2><a href="<?= e(url('admin/users')) ?>">Clear filters</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No matching users'; $emptyText = 'Try changing the filters or search terms.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <?php $statuses = ['active', 'pending', 'suspended', 'deactivated']; $adminId = (int) \App\Core\Auth::id(); ?>
          <div class="table-wrap"><table>
            <thead><tr><th>User</th><th>Role</th><th>Jobs</th><th>Joined</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <strong><?= e($row['name']) ?></strong><br>
                    <small><?= e($row['email']) ?></small>
                  </td>
                  <td><span class="badge"><?= e($row['role']) ?></span></td>
                  <td><?= (int) ($row['jobs_count'] ?? 0) ?></td>
                  <td><?= e(format_date($row['created_at'] ?? null)) ?></td>
                  <td><span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td>
                    <?php if ((int) $row['id'] === $adminId): ?>
                      <span class="badge" title="You cannot change your own status">You</span>
                    <?php else: ?>
                      <form method="post" action="<?= e(url('admin/users/' . $row['id'] . '/status')) ?>" style="display:flex;gap:.4rem;align-items:center">
                        <?= csrf_field() ?>
                        <select class="form-control" name="status" style="min-width:130px;min-height:34px;padding:.35rem .5rem" aria-label="Status for <?= e($row['name']) ?>">
                          <?php foreach ($statuses as $option): ?>
                            <option value="<?= e($option) ?>" <?= $row['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-outline" type="submit">Update</button>
                      </form>
                    <?php endif; ?>
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
