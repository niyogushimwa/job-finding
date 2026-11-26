<?php
/** Applicant detail. Expected: $ctx, $application, $seeker, $resumes, $skills, $education, $experience, $history, $statuses */
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
        <h1><?= e($application['applicant_name'] ?? 'Applicant') ?></h1>
        <p>Applied for <a href="<?= e(url('jobs/' . $application['job_id'] . '/' . $application['job_slug'])) ?>"><?= e($application['job_title']) ?></a> · <?= e(time_ago($application['created_at'])) ?></p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/applicants')) ?>">← All applicants</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?><div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div><?php endif; ?>

    <div class="grid grid-2">
      <div>
        <div class="card mb-2">
          <div class="panel-head" style="padding:0 0 1rem"><h2>Application</h2><span class="status status-<?= e($application['status']) ?>"><?= e($statuses[$application['status']] ?? str_replace('_', ' ', $application['status'])) ?></span></div>
          <?php if ($application['match_score'] !== null): ?>
            <div class="d-flex align-center gap-2 mb-2">
              <div class="score-ring" style="--score:<?= (int) $application['match_score'] ?>"><span><?= (int) $application['match_score'] ?>%</span></div>
              <p class="small muted mb-0">Profile match calculated from the candidate's skills and experience against this opportunity.</p>
            </div>
          <?php endif; ?>
          <h3>Cover letter</h3>
          <div class="rich-text small"><?= nl2br(e($application['cover_letter'] ?: 'No cover letter submitted.')) ?></div>
        </div>

        <div class="card mb-2">
          <h2>Update status</h2>
          <form method="post" action="<?= e(url('employer/applications/' . $application['id'] . '/status')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="status">Status</label>
              <select class="form-control" id="status" name="status">
                <?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>" <?= $application['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="note">Note to the candidate</label>
              <textarea class="form-control" id="note" name="note" rows="3" maxlength="2000" placeholder="Optional feedback that will be recorded in the application history"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Save update</button>
          </form>
        </div>

        <div class="card">
          <h2>Status history</h2>
          <?php if (!$history): ?><p class="small muted">No history recorded yet.</p><?php else: ?>
            <div class="side-info">
              <?php foreach ($history as $entry): ?>
                <div class="row"><span><?= e(format_date($entry['created_at'], 'M j, Y · H:i')) ?></span><strong><?= e($statuses[$entry['to_status']] ?? str_replace('_', ' ', $entry['to_status'])) ?></strong></div>
                <?php if (!empty($entry['note'])): ?><p class="small muted mb-1"><?= e($entry['note']) ?> <?= !empty($entry['changed_by_name']) ? '· ' . e($entry['changed_by_name']) : '' ?></p><?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <aside>
        <div class="card mb-2">
          <h2>Candidate profile</h2>
          <div class="side-info">
            <div class="row"><span>Name</span><strong><?= e($application['applicant_name']) ?></strong></div>
            <div class="row"><span>Email</span><strong><?= e($application['applicant_email']) ?></strong></div>
            <div class="row"><span>Headline</span><strong><?= e($seeker['headline'] ?? '—') ?></strong></div>
            <div class="row"><span>Location</span><strong><?= e($seeker['location'] ?? '—') ?></strong></div>
            <div class="row"><span>Experience</span><strong><?= (int) ($seeker['experience_years'] ?? 0) ?> years</strong></div>
            <div class="row"><span>Education</span><strong><?= e($seeker['education_level'] ?? '—') ?></strong></div>
          </div>
          <p class="small muted mt-2"><?= e(str_limit($seeker['summary'] ?? 'This candidate has not added a profile summary yet.', 320)) ?></p>
          <a class="btn btn-outline btn-sm btn-block mt-2" href="<?= e(url('u/' . $application['user_id'])) ?>">View public profile</a>
          <?php if (!empty($application['applicant_phone'])): ?><a class="btn btn-outline btn-sm btn-block mt-1" href="tel:<?= e($application['applicant_phone']) ?>">Call candidate</a><?php endif; ?>
        </div>

        <div class="card mb-2">
          <h2>Skills</h2>
          <?php if (!$skills): ?><p class="small muted">No skills listed.</p><?php else: ?>
            <div class="d-flex flex-wrap gap-1"><?php foreach ($skills as $skill): ?><span class="tag tag-blue"><?= e($skill['name']) ?> · <?= e($skill['level']) ?></span><?php endforeach; ?></div>
          <?php endif; ?>
        </div>

        <div class="card mb-2">
          <h2>CV &amp; resumes</h2>
          <?php if (!$resumes): ?><p class="small muted">No uploaded CV file. The candidate may use the profile CV builder.</p><?php else: ?>
            <?php foreach ($resumes as $resume): ?>
              <div class="row">
                <span><?= e($resume['title']) ?> <?= !empty($resume['is_default']) ? '<span class="badge">Default</span>' : '' ?></span>
                <?php if (!empty($resume['file_path'])): ?>
                  <a class="small" href="<?= e(url('resumes/' . $resume['id'] . '/download')) ?>"><?= icon('download', 14) ?> <?= e($resume['original_name'] ?? 'Download') ?></a>
                <?php else: ?><span class="small muted">Profile CV</span><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($education || $experience): ?>
          <div class="card">
            <h2>Background</h2>
            <?php foreach ($education as $entry): ?>
              <p class="small mb-1"><strong><?= e($entry['degree']) ?></strong> · <?= e($entry['school']) ?> <span class="muted">(<?= e((string) ($entry['end_year'] ?? '')) ?>)</span></p>
            <?php endforeach; ?>
            <?php foreach ($experience as $entry): ?>
              <p class="small mb-1"><strong><?= e($entry['title']) ?></strong> · <?= e($entry['company']) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </main>
</div>
