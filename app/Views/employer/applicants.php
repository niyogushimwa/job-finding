<?php
/** Applicant list with filters. Expected: $ctx, $applications, $pager, $filters, $jobs, $statuses */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/applicants';
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
        <h1>Applicants</h1>
        <p><?= e($pager->summary()) ?> · Review candidates and move them through your hiring pipeline.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/shortlisted')) ?>"><?= icon('star', 16) ?> Shortlisted</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <form class="filters mb-3" method="get" action="<?= e(url('employer/applicants')) ?>">
      <div class="filter-row">
        <div class="form-group"><label class="form-label">Opportunity</label><select class="form-control" name="job_id"><option value="">All opportunities</option><?php foreach ($jobs as $job): ?><option value="<?= (int) $job['id'] ?>" <?= (int) $filters['job_id'] === (int) $job['id'] ? 'selected' : '' ?>><?= e(str_limit($job['title'], 60)) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><option value="">All statuses</option><?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Skill</label><input class="form-control" name="skill" value="<?= e($filters['skill']) ?>" placeholder="e.g. PHP"></div>
        <div class="form-group"><label class="form-label">Education</label><input class="form-control" name="education" value="<?= e($filters['education']) ?>" placeholder="e.g. Bachelor"></div>
        <div class="form-group d-flex align-end gap-1"><button class="btn btn-primary" type="submit"><?= icon('filter', 16) ?> Filter</button><a class="btn btn-outline" href="<?= e(url('employer/applicants')) ?>">Clear</a></div>
      </div>
    </form>

    <div class="panel">
      <div class="panel-body flush table-wrap">
        <?php if (!$applications): ?>
          <?php $emptyTitle = 'No applications match'; $emptyText = 'Adjust your filters or check back once candidates start applying.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <table>
            <thead><tr><th>Candidate</th><th>Opportunity</th><th>Skills</th><th>Match</th><th>Applied</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
              <?php foreach ($applications as $app): ?>
                <tr>
                  <td><a href="<?= e(url('employer/applicants/' . $app['id'])) ?>"><?= e($app['applicant_name'] ?? 'Candidate') ?></a><div class="small muted"><?= e(str_limit($app['headline'] ?? '', 48)) ?></div></td>
                  <td><?= e(str_limit($app['job_title'] ?? '', 42)) ?></td>
                  <td class="small"><?= e(str_limit($app['applicant_skills'] ?? '', 48) ?: '—') ?></td>
                  <td><?= $app['match_score'] !== null ? (int) $app['match_score'] . '%' : '—' ?></td>
                  <td class="muted"><?= e(time_ago($app['created_at'])) ?></td>
                  <td><span class="status status-<?= e($app['status']) ?>"><?= e($statuses[$app['status']] ?? str_replace('_', ' ', $app['status'])) ?></span></td>
                  <td>
                    <div class="d-flex gap-1 align-center">
                      <form method="post" action="<?= e(url('employer/applications/' . $app['id'] . '/status')) ?>" class="d-flex gap-1 align-center">
                        <?= csrf_field() ?>
                        <select class="form-control" name="status" style="width:auto;min-height:34px;padding:.35rem .5rem" aria-label="Application status">
                          <?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>" <?= $app['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                        </select>
                        <input class="form-control" name="note" maxlength="2000" placeholder="Note (optional)" style="width:130px;min-height:34px">
                        <button class="btn btn-ghost btn-sm" type="submit">Save</button>
                      </form>
                      <a class="btn btn-outline btn-sm" href="<?= e(url('employer/applicants/' . $app['id'])) ?>">View</a>
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
