<?php use App\Core\Auth; use App\Core\Upload; ?>
<section class="section-tight">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url()) ?>">Home</a> / <a href="<?= e(url('jobs')) ?>">Find Jobs</a> / <?= e(str_limit($job['title'], 50)) ?></nav>
    <div class="detail-hero">
      <div class="d-flex align-center gap-2 flex-wrap">
        <span class="company-logo"><?= !empty($job['company_logo']) ? '<img src="' . e(Upload::url($job['company_logo'])) . '" alt="">' : e(mb_substr((string) $job['company_name'], 0, 1)) ?></span>
        <div>
          <a class="small" style="color:#e6b34a" href="<?= e(url('companies/' . $job['company_id'] . '/' . $job['company_slug'])) ?>"><?= e($job['company_name'] ?? 'Employer') ?></a>
          <h1 class="mt-0"><?= e($job['title']) ?></h1>
        </div>
      </div>
      <div class="job-meta mt-2">
        <span><?= icon('map', 16) ?><?= e($job['location']) ?> · <?= e(ucfirst($job['work_mode'])) ?></span>
        <span><?= icon('briefcase', 16) ?><?= e(str_replace('_', ' ', $job['employment_type'])) ?></span>
        <span><?= icon('clock', 16) ?>Deadline: <?= $job['deadline'] ? e(format_date($job['deadline'])) : 'Open until closed' ?></span>
        <span><?= icon('eye', 16) ?><?= (int) $job['views_count'] ?> views</span>
      </div>
      <div class="d-flex gap-1 flex-wrap mt-3">
        <?php if ($job['featured']): ?><span class="tag tag-gold"><?= icon('star', 13) ?> Featured</span><?php endif; ?>
        <?php if ($job['company_verification'] === 'verified'): ?><span class="tag tag-green"><?= icon('shield', 13) ?> Verified company</span><?php endif; ?>
        <span class="tag">Published <?= e(time_ago($job['published_at'] ?? $job['created_at'])) ?></span>
      </div>
    </div>

    <div class="detail-grid">
      <div>
        <?php if ($match): ?>
          <div class="card mb-2">
            <div class="d-flex align-center gap-2">
              <div class="score-ring" style="--score:<?= (int) $match['score'] ?>"><span><?= (int) $match['score'] ?>%</span></div>
              <div><h3 class="mb-1">Your profile matches <?= (int) $match['score'] ?>% of this opportunity</h3>
                <p class="small muted mb-0">Calculated from your skills, experience, education, location and job type — not a random number.</p>
              </div>
            </div>
            <div class="mt-2">
              <?php foreach ($match['breakdown'] as $label => $points): ?>
                <div class="d-flex justify-between small mb-1"><span><?= e($label) ?></span><strong><?= (int) $points ?> pts</strong></div>
                <div class="progress mb-1"><span style="width:<?= min(100, (int) $points * 3) ?>%"></span></div>
              <?php endforeach; ?>
            </div>
            <div class="grid grid-2 mt-2">
              <div><strong class="small" style="color:var(--green)">You have:</strong><p class="small muted mb-0"><?= $match['matched'] ? e(implode(', ', $match['matched'])) : 'No listed job skills yet' ?></p></div>
              <div><strong class="small" style="color:var(--amber)">Missing / to develop:</strong><p class="small muted mb-0"><?= $match['missing'] ? e(implode(', ', $match['missing'])) : 'Nothing missing — great fit!' ?></p></div>
            </div>
            <a class="small" href="<?= e(url('my-skills')) ?>">Improve your match →</a>
          </div>
        <?php endif; ?>

        <div class="card rich-text mb-2">
          <h2>About the role</h2><div><?= nl2br($job['description']) ?></div>
          <?php if ($job['responsibilities']): ?><h2>What you will do</h2><div><?= nl2br($job['responsibilities']) ?></div><?php endif; ?>
          <?php if ($job['requirements']): ?><h2>What we are looking for</h2><div><?= nl2br($job['requirements']) ?></div><?php endif; ?>
          <?php if ($job['benefits']): ?><h2>Benefits</h2><div><?= nl2br($job['benefits']) ?></div><?php endif; ?>
          <?php if ($skills): ?>
            <h2>Skills</h2><div class="d-flex flex-wrap gap-1"><?php foreach ($skills as $s): ?><span class="tag <?= $s['is_required'] ? 'tag-blue' : '' ?>"><?= e($s['name']) ?><?= $s['is_required'] ? ' · required' : '' ?></span><?php endforeach; ?></div>
          <?php endif; ?>
        </div>

        <div class="card">
          <h3>Report this opportunity</h3>
          <p class="small muted">Saw something suspicious? Help keep the community safe. Reports are reviewed by administrators.</p>
          <?php if (Auth::id()): ?>
          <form method="post" action="<?= e(url('jobs/' . $job['id'] . '/report')) ?>" class="form-row">
            <?= csrf_field() ?>
            <div class="form-group"><select class="form-control" name="reason"><option value="scam">Possible scam</option><option value="fake_job">Fake job</option><option value="payment_request">Requests payment</option><option value="misleading_salary">Misleading salary</option><option value="inappropriate">Inappropriate content</option><option value="other">Other</option></select></div>
            <div class="form-group"><button class="btn btn-outline btn-block" type="submit">Submit report</button></div>
          </form>
          <?php else: ?><a class="btn btn-outline btn-sm" href="<?= e(url('login')) ?>">Log in to report</a><?php endif; ?>
        </div>
      </div>

      <aside class="side-info">
        <div class="card">
          <h3>Opportunity details</h3>
          <div class="side-info">
            <div class="row"><span>Salary</span><strong><?= e(format_money($job['salary_min'] ? (float) $job['salary_min'] : null, $job['salary_currency'], $job['salary_period'])) ?></strong></div>
            <?php if ($job['salary_min'] && $job['salary_max'] && $job['salary_min'] != $job['salary_max']): ?><div class="row"><span>Range</span><strong><?= e(format_money((float) $job['salary_max'], $job['salary_currency'], $job['salary_period'])) ?></strong></div><?php endif; ?>
            <div class="row"><span>Level</span><strong><?= e(ucfirst($job['experience_level'])) ?></strong></div>
            <div class="row"><span>Education</span><strong><?= e($job['education_level'] ?? 'Not specified') ?></strong></div>
            <div class="row"><span>Category</span><strong><?= e($job['category_name'] ?? '—') ?></strong></div>
            <div class="row"><span>Posted</span><strong><?= e(format_date($job['published_at'] ?? $job['created_at'])) ?></strong></div>
          </div>
          <div class="mt-2">
            <?php if ($applied): ?>
              <div class="alert-success small" style="color:var(--green);font-weight:700">You have applied for this role.</div>
            <?php elseif (!Auth::id()): ?>
              <a class="btn btn-primary btn-block" href="<?= e(url('login')) ?>">Log in to apply</a>
            <?php elseif (!in_array(Auth::role(), ['seeker', 'freelancer'], true)): ?>
              <a class="btn btn-outline btn-block" href="<?= e(url('select-role')) ?>">Switch to job seeker to apply</a>
            <?php else: ?>
              <a class="btn btn-primary btn-block btn-lg" href="<?= e(url('jobs/' . $job['id'] . '/apply')) ?>"><?= icon('rocket', 17) ?> Apply now</a>
            <?php endif; ?>
            <?php if (Auth::id()): ?><button class="btn btn-outline btn-block mt-1 save-btn <?= $saved ? 'saved' : '' ?>" type="button" data-save-url="<?= e(url('jobs/' . $job['id'] . '/save')) ?>" style="width:100%"><?= icon('bookmark', 16) ?> <?= $saved ? 'Saved' : 'Save for later' ?></button><?php endif; ?>
          </div>
          <button class="btn btn-ghost btn-block btn-sm mt-1" data-copy="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>" type="button">Copy share link</button>
        </div>
        <div class="card">
          <h3>Trust & safety</h3>
          <p class="small muted mb-0"><?= icon('shield', 15) ?> Never pay a fee for a job offer. Verify all details with the organisation before accepting.</p>
        </div>
      </aside>
    </div>

    <?php if ($similar): ?>
      <div class="mt-3">
        <h2>Similar opportunities</h2>
        <div class="grid grid-3"><?php foreach ($similar as $jobCard): $saved = \App\Models\Job::isSaved((int) $jobCard['id'], Auth::id()); include BASE_PATH . '/app/Views/partials/job_card.php'; endforeach; ?></div>
      </div>
    <?php endif; ?>
  </div>
</section>
