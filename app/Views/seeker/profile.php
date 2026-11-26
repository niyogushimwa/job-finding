<?php use App\Core\Upload; ?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Your public presence</span>
        <h1>My profile</h1>
        <p>Employers see this profile when you apply or when they discover you.</p>
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <button class="btn btn-outline" type="button" data-copy="<?= e($publicUrl) ?>"><?= icon('globe', 16) ?> Copy public link</button>
        <a class="btn btn-primary" href="<?= e(url('profile/edit')) ?>"><?= icon('edit', 16) ?> Edit profile</a>
      </div>
    </div>

    <section class="panel">
      <div class="panel-body">
        <div class="d-flex gap-2 flex-wrap align-center">
          <div class="company-logo" style="width:72px;height:72px;font-size:1.6rem">
            <?php if (!empty($profile['user']['avatar'])): ?><img src="<?= e(Upload::url($profile['user']['avatar'])) ?>" alt="Profile photo"><?php else: ?><?= e(mb_substr((string) ($profile['user']['name'] ?? '?'), 0, 1)) ?><?php endif; ?>
          </div>
          <div style="flex:1;min-width:0">
            <h2 class="mb-1"><?= e($profile['user']['name'] ?? 'Job seeker') ?></h2>
            <p class="muted mb-1"><?= e($profile['seeker']['headline'] ?? 'Add a headline to describe what you do.') ?></p>
            <div class="d-flex flex-wrap gap-1">
              <?php if (!empty($profile['seeker']['location'])): ?><span class="tag"><?= icon('map', 13) ?> <?= e($profile['seeker']['location']) ?></span><?php endif; ?>
              <?php if (!empty($profile['seeker']['experience_years'])): ?><span class="tag"><?= icon('briefcase', 13) ?> <?= (int) $profile['seeker']['experience_years'] ?> years experience</span><?php endif; ?>
              <?php if (!empty($profile['seeker']['education_level'])): ?><span class="tag"><?= icon('graduation', 13) ?> <?= e($profile['seeker']['education_level']) ?></span><?php endif; ?>
              <a class="tag tag-blue" href="<?= e($publicUrl) ?>"><?= icon('eye', 13) ?> View public profile</a>
            </div>
          </div>
        </div>
        <div class="mt-2">
          <div class="d-flex justify-between small mb-1"><span>Profile completion</span><strong><?= (int) $completion ?>%</strong></div>
          <div class="progress"><span style="width:<?= min(100, (int) $completion) ?>%"></span></div>
        </div>
      </div>
    </section>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>About</h3><a class="small" href="<?= e(url('profile/edit')) ?>">Edit →</a></div>
        <div class="panel-body">
          <p class="mb-0" style="white-space:pre-line"><?= e($profile['seeker']['summary'] ?? 'Tell employers about your background, strengths and goals.') ?></p>
          <?php if (!empty($profile['seeker']['availability'])): ?><p class="small muted mt-2 mb-0"><strong>Availability:</strong> <?= e($profile['seeker']['availability']) ?></p><?php endif; ?>
          <?php if (!empty($profile['seeker']['employment_types'])): ?>
            <div class="d-flex flex-wrap gap-1 mt-2">
              <?php foreach (explode(',', (string) $profile['seeker']['employment_types']) as $type): if ($type === '') continue; ?>
                <span class="tag"><?= e(str_replace('_', ' ', $type)) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Contact & links</h3></div>
        <div class="panel-body side-info">
          <div class="row"><span>Email</span><strong><?= e($profile['user']['email'] ?? '—') ?></strong></div>
          <div class="row"><span>Phone</span><strong><?= e($profile['user']['phone'] ?? '—') ?></strong></div>
          <div class="row"><span>Preferred location</span><strong><?= e($profile['seeker']['preferred_location'] ?? '—') ?></strong></div>
          <div class="row"><span>LinkedIn</span><strong><?= !empty($profile['seeker']['linkedin_url']) ? e(str_limit($profile['seeker']['linkedin_url'], 42)) : '—' ?></strong></div>
          <div class="row"><span>GitHub</span><strong><?= !empty($profile['seeker']['github_url']) ? e(str_limit($profile['seeker']['github_url'], 42)) : '—' ?></strong></div>
          <div class="row"><span>Website</span><strong><?= !empty($profile['seeker']['website_url']) ? e(str_limit($profile['seeker']['website_url'], 42)) : '—' ?></strong></div>
        </div>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h3>Skills</h3><a class="small" href="<?= e(url('my-skills')) ?>">Manage skills →</a></div>
      <div class="panel-body">
        <?php if ($skills): ?>
          <div class="d-flex flex-wrap gap-1">
            <?php foreach ($skills as $skill): ?>
              <span class="tag tag-blue"><?= e($skill['name']) ?> · <?= e($skill['level']) ?><?= (int) $skill['endorsements'] > 0 ? ' · ' . (int) $skill['endorsements'] . ' endorsement' . ((int) $skill['endorsements'] === 1 ? '' : 's') : '' ?></span>
            <?php endforeach; ?>
          </div>
        <?php else: ?><p class="muted mb-0">No skills yet. <a href="<?= e(url('my-skills')) ?>">Add your first skill →</a></p><?php endif; ?>
      </div>
    </section>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Education</h3></div>
        <div class="panel-body">
          <?php if ($education): ?>
            <?php foreach ($education as $row): ?>
              <div class="cv-item">
                <strong><?= e($row['degree']) ?></strong>
                <p class="small muted mb-1"><?= e($row['school']) ?><?= !empty($row['field_of_study']) ? ' · ' . e($row['field_of_study']) : '' ?></p>
                <span class="small muted"><?= e(trim((string) $row['start_year'] . ' – ' . (string) $row['end_year'], ' –')) ?></span>
              </div>
            <?php endforeach; ?>
          <?php else: ?><p class="muted mb-0">No education entries yet.</p><?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Experience</h3></div>
        <div class="panel-body">
          <?php if ($experience): ?>
            <?php foreach ($experience as $row): ?>
              <div class="cv-item">
                <strong><?= e($row['title']) ?> · <?= e($row['company']) ?></strong>
                <span class="small muted"><?= !empty($row['is_current']) ? 'Present' : e(format_date($row['end_date'])) ?></span>
                <?php if (!empty($row['description'])): ?><p class="small muted mb-0 mt-1"><?= e(str_limit($row['description'], 220)) ?></p><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?><p class="muted mb-0">No experience entries yet.</p><?php endif; ?>
        </div>
      </section>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Certifications</h3></div>
        <div class="panel-body">
          <?php if ($certifications): ?>
            <?php foreach ($certifications as $row): ?>
              <div class="cv-item"><strong><?= e($row['name']) ?></strong><p class="small muted mb-0"><?= e($row['issuer'] ?? '') ?> · <?= e($row['issued_year'] ?? '') ?></p></div>
            <?php endforeach; ?>
          <?php else: ?><p class="muted mb-0">No certifications yet.</p><?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Portfolio</h3><a class="small" href="<?= e(url('my-portfolio')) ?>">Manage →</a></div>
        <div class="panel-body">
          <?php if ($portfolios): ?>
            <?php foreach ($portfolios as $row): ?>
              <div class="cv-item"><strong><?= e($row['title']) ?></strong><p class="small muted mb-0"><?= e(str_limit((string) $row['description'], 160)) ?></p></div>
            <?php endforeach; ?>
          <?php else: ?><p class="muted mb-0">No projects yet. <a href="<?= e(url('my-portfolio')) ?>">Add one →</a></p><?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</div>
