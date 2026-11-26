<?php
/** Manage employer opportunities. Expected: $ctx, $jobs, $pager, $status, $q, $statuses, $allowedStatuses */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/jobs';
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
        <h1>Manage opportunities</h1>
        <p><?= e($pager->summary()) ?> · Track performance and control which roles are live.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('employer/jobs/create')) ?>"><?= icon('plus', 16) ?> Post a job</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div>
    <?php endif; ?>

    <form class="filters mb-3" method="get" action="<?= e(url('employer/jobs')) ?>">
      <div class="filter-row">
        <div class="form-group"><label class="form-label">Search</label><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Title or location"></div>
        <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><option value="">All statuses</option><?php foreach ($statuses as $value): ?><option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $value))) ?></option><?php endforeach; ?></select></div>
        <div class="form-group d-flex align-end gap-1"><button class="btn btn-primary" type="submit"><?= icon('filter', 16) ?> Filter</button><a class="btn btn-outline" href="<?= e(url('employer/jobs')) ?>">Clear</a></div>
      </div>
    </form>

    <div class="panel">
      <div class="panel-body flush table-wrap">
        <?php if (!$jobs): ?>
          <?php $emptyTitle = 'No opportunities found'; $emptyText = 'Try a different filter or post a new opportunity to get started.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('employer/jobs/create')) . '">Post a job</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <table>
            <thead><tr><th>Opportunity</th><th>Company</th><th>Status</th><th>Applicants</th><th>Views</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($jobs as $job): ?>
                <tr>
                  <td>
                    <a href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>"><?= e(str_limit($job['title'], 52)) ?></a>
                    <div class="small muted"><?= e($job['location']) ?> · <?= e(str_replace('_', ' ', $job['employment_type'])) ?></div>
                  </td>
                  <td><?= e($job['company_name'] ?? '—') ?></td>
                  <td><span class="status status-<?= e($job['status']) ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span></td>
                  <td><a href="<?= e(url('employer/applicants?job_id=' . $job['id'])) ?>"><?= (int) $job['applicants_count'] ?></a></td>
                  <td><?= (int) $job['views_count'] ?></td>
                  <td class="muted"><?= e(format_date($job['published_at'])) ?></td>
                  <td>
                    <div class="d-flex gap-1 flex-wrap">
                      <a class="btn btn-outline btn-sm" href="<?= e(url('employer/jobs/' . $job['id'] . '/edit')) ?>"><?= icon('edit', 14) ?> Edit</a>
                      <form method="post" action="<?= e(url('employer/jobs/' . $job['id'] . '/status')) ?>" class="d-flex gap-1 align-center">
                        <?= csrf_field() ?>
                        <select class="form-control" name="status" style="width:auto;min-height:34px;padding:.35rem .5rem" aria-label="Change status">
                          <?php foreach ($allowedStatuses as $value): ?>
                            <option value="<?= e($value) ?>" <?= $job['status'] === $value ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $value))) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn btn-ghost btn-sm" type="submit">Update</button>
                      </form>
                      <form method="post" action="<?= e(url('employer/jobs/' . $job['id'] . '/delete')) ?>" onsubmit="return confirm('Remove this opportunity from your workspace?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost btn-sm" type="submit" title="Remove opportunity"><?= icon('trash', 14) ?></button>
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
    <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
  </main>
</div>
