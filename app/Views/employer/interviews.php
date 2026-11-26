<?php
/** Interview scheduling and list. Expected: $ctx, $interviews, $applications, $statuses */
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/interviews';
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
        <h1>Interviews</h1>
        <p>Schedule conversations with candidates and keep your pipeline up to date.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/shortlisted')) ?>"><?= icon('star', 16) ?> Shortlisted</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h2>Schedule an interview</h2><span class="small muted">The candidate is notified automatically</span></div>
      <div class="panel-body">
        <?php if (!$applications): ?>
          <p class="small muted">No applications available yet. Once candidates apply, you can schedule interviews here.</p>
        <?php else: ?>
          <form method="post" action="<?= e(url('employer/interviews')) ?>">
            <?= csrf_field() ?>
            <div class="form-row three">
              <div class="form-group">
                <label class="form-label" for="application_id">Applicant</label>
                <select class="form-control" id="application_id" name="application_id" required>
                  <option value="">Choose an applicant</option>
                  <?php foreach ($applications as $app): ?>
                    <option value="<?= (int) $app['id'] ?>"><?= e($app['applicant_name']) ?> — <?= e(str_limit($app['job_title'], 42)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="scheduled_at">Date &amp; time</label>
                <input class="form-control" id="scheduled_at" name="scheduled_at" type="datetime-local" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="duration_minutes">Duration (minutes)</label>
                <input class="form-control" id="duration_minutes" name="duration_minutes" type="number" min="15" max="240" value="45" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="location">Location</label>
                <input class="form-control" id="location" name="location" placeholder="e.g. Head office or Kigali">
              </div>
              <div class="form-group">
                <label class="form-label" for="meeting_link">Meeting link</label>
                <input class="form-control" id="meeting_link" name="meeting_link" type="url" placeholder="https://meet.example.com/...">
              </div>
              <div class="form-group">
                <label class="form-label" for="notes">Notes</label>
                <input class="form-control" id="notes" name="notes" maxlength="2000" placeholder="Optional agenda or instructions">
              </div>
            </div>
            <p class="small muted mb-1">Provide a location or a meeting link (or both). Duration must be between 15 and 240 minutes.</p>
            <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Schedule interview</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Scheduled interviews</h2><span class="small muted"><?= count($interviews) ?> shown</span></div>
      <div class="panel-body flush table-wrap">
        <?php if (!$interviews): ?>
          <?php $emptyTitle = 'No interviews yet'; $emptyText = 'Interviews you schedule will appear here with their current status.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <table>
            <thead><tr><th>Candidate</th><th>Opportunity</th><th>When</th><th>Duration</th><th>Where</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
              <?php foreach ($interviews as $interview): ?>
                <tr>
                  <td><?= e($interview['applicant_name']) ?></td>
                  <td><?= e(str_limit($interview['job_title'], 42)) ?></td>
                  <td><?= e(format_date($interview['scheduled_at'], 'M j, Y · H:i')) ?></td>
                  <td><?= (int) $interview['duration_minutes'] ?> min</td>
                  <td class="small">
                    <?= e($interview['location'] ?: '—') ?>
                    <?php if (!empty($interview['meeting_link'])): ?><br><a href="<?= e($interview['meeting_link']) ?>" target="_blank" rel="noopener noreferrer">Join meeting</a><?php endif; ?>
                  </td>
                  <td><span class="status status-<?= e($interview['status']) ?>"><?= e(ucfirst($interview['status'])) ?></span></td>
                  <td>
                    <form method="post" action="<?= e(url('employer/interviews/' . $interview['id'] . '/status')) ?>" class="d-flex gap-1 align-center">
                      <?= csrf_field() ?>
                      <select class="form-control" name="status" style="width:auto;min-height:34px;padding:.35rem .5rem" aria-label="Interview status">
                        <?php foreach ($statuses as $value): ?><option value="<?= e($value) ?>" <?= $interview['status'] === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option><?php endforeach; ?>
                      </select>
                      <button class="btn btn-ghost btn-sm" type="submit">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
