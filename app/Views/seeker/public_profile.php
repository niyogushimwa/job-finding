<?php use App\Core\Auth; use App\Core\Upload; ?>
<section class="section">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url()) ?>">Home</a> / Public profile</nav>

    <?php if (empty($profile['user'])): ?>
      <div class="empty-state"><h3>Profile unavailable</h3><p>This profile could not be found.</p></div>
    <?php else: ?>
      <?php $publicUser = $profile['user']; $publicSeeker = $profile['seeker'] ?? []; ?>
      <div class="detail-hero">
        <div class="d-flex gap-2 flex-wrap align-center">
          <div class="company-logo" style="width:72px;height:72px;font-size:1.6rem">
            <?php if (!empty($publicUser['avatar'])): ?><img src="<?= e(Upload::url($publicUser['avatar'])) ?>" alt="Profile photo"><?php else: ?><?= e(mb_substr((string) $publicUser['name'], 0, 1)) ?><?php endif; ?>
          </div>
          <div>
            <h1 class="mt-0"><?= e($publicUser['name']) ?></h1>
            <p style="color:#c9d8ed" class="mb-1"><?= e($publicSeeker['headline'] ?? 'Job seeker on JobLink Rwanda') ?></p>
            <div class="d-flex flex-wrap gap-1">
              <?php if (!empty($publicSeeker['location'])): ?><span class="tag"><?= icon('map', 13) ?> <?= e($publicSeeker['location']) ?></span><?php endif; ?>
              <?php if (!empty($publicSeeker['experience_years'])): ?><span class="tag"><?= icon('briefcase', 13) ?> <?= (int) $publicSeeker['experience_years'] ?> years experience</span><?php endif; ?>
              <?php if (!empty($publicSeeker['education_level'])): ?><span class="tag"><?= icon('graduation', 13) ?> <?= e($publicSeeker['education_level']) ?></span><?php endif; ?>
              <a class="tag tag-green" href="<?= e(url('jobs')) ?>"><?= icon('briefcase', 13) ?> Explore open roles</a>
            </div>
          </div>
        </div>
      </div>

      <div class="detail-grid">
        <div>
          <?php if (!empty($publicSeeker['summary'])): ?>
            <section class="card mb-2 rich-text"><h2 class="mt-0">About</h2><p style="white-space:pre-line"><?= e($publicSeeker['summary']) ?></p></section>
          <?php endif; ?>

          <section class="panel">
            <div class="panel-head"><h3>Skills</h3><span class="small muted">Endorsed by the community</span></div>
            <div class="panel-body">
              <?php if ($skills): ?>
                <?php foreach ($skills as $skill): ?>
                  <div class="d-flex justify-between align-center gap-1 mb-2">
                    <div>
                      <strong><?= e($skill['name']) ?></strong>
                      <span class="small muted"> · <?= e($skill['level']) ?><?= (float) $skill['years'] > 0 ? ' · ' . (float) $skill['years'] . ' yrs' : '' ?></span>
                      <?php if ((int) $skill['endorsements'] > 0): ?><span class="tag tag-green"><?= (int) $skill['endorsements'] ?> endorsement<?= (int) $skill['endorsements'] === 1 ? '' : 's' ?></span><?php endif; ?>
                    </div>
                    <?php if (Auth::check() && !$isOwner): ?>
                      <form method="post" action="<?= e(url('my-skills/' . $skill['user_skill_id'] . '/endorse')) ?>">
                        <?= csrf_field() ?><button class="btn btn-sm btn-outline" type="submit"><?= icon('award', 14) ?> Endorse</button>
                      </form>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
                <?php if (!Auth::check()): ?><p class="small muted mb-0"><a href="<?= e(url('login')) ?>">Log in</a> to endorse a skill.</p><?php endif; ?>
              <?php else: ?><p class="muted mb-0">No skills listed yet.</p><?php endif; ?>
            </div>
          </section>

          <div class="grid grid-2">
            <section class="panel">
              <div class="panel-head"><h3>Education</h3></div>
              <div class="panel-body">
                <?php if ($education): ?>
                  <?php foreach ($education as $row): ?>
                    <div class="cv-item"><strong><?= e($row['degree']) ?></strong><p class="small muted mb-0"><?= e($row['school']) ?><?= !empty($row['field_of_study']) ? ' · ' . e($row['field_of_study']) : '' ?> · <?= e(trim((string) $row['start_year'] . ' – ' . (string) $row['end_year'], ' –')) ?></p></div>
                  <?php endforeach; ?>
                <?php else: ?><p class="muted mb-0">No education listed.</p><?php endif; ?>
              </div>
            </section>

            <section class="panel">
              <div class="panel-head"><h3>Experience</h3></div>
              <div class="panel-body">
                <?php if ($experience): ?>
                  <?php foreach ($experience as $row): ?>
                    <div class="cv-item"><strong><?= e($row['title']) ?> · <?= e($row['company']) ?></strong><span class="small muted"><?= !empty($row['is_current']) ? 'Present' : e(format_date($row['end_date'])) ?></span><?php if (!empty($row['description'])): ?><p class="small muted mb-0 mt-1"><?= e(str_limit($row['description'], 200)) ?></p><?php endif; ?></div>
                  <?php endforeach; ?>
                <?php else: ?><p class="muted mb-0">No experience listed.</p><?php endif; ?>
              </div>
            </section>
          </div>

          <div class="grid grid-2">
            <section class="panel">
              <div class="panel-head"><h3>Certifications</h3></div>
              <div class="panel-body">
                <?php if ($certifications): ?>
                  <?php foreach ($certifications as $row): ?>
                    <div class="cv-item"><strong><?= e($row['name']) ?></strong><p class="small muted mb-0"><?= e($row['issuer'] ?? '') ?> · <?= e($row['issued_year'] ?? '') ?></p>
                      <?php if (!empty($row['credential_url'])): ?><a class="small" href="<?= e($row['credential_url']) ?>" rel="noopener noreferrer" target="_blank">View credential →</a><?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?><p class="muted mb-0">No certifications listed.</p><?php endif; ?>
              </div>
            </section>

            <section class="panel">
              <div class="panel-head"><h3>Portfolio</h3></div>
              <div class="panel-body">
                <?php if ($portfolios): ?>
                  <?php foreach ($portfolios as $row): ?>
                    <div class="cv-item"><strong><?= e($row['title']) ?></strong><p class="small muted mb-1"><?= e(str_limit((string) $row['description'], 180)) ?></p>
                      <?php if (!empty($row['url'])): ?><a class="small" href="<?= e($row['url']) ?>" rel="noopener noreferrer" target="_blank">Live project →</a><?php endif; ?>
                      <?php if (!empty($row['repository_url'])): ?> · <a class="small" href="<?= e($row['repository_url']) ?>" rel="noopener noreferrer" target="_blank">Source code →</a><?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?><p class="muted mb-0">No projects listed.</p><?php endif; ?>
              </div>
            </section>
          </div>
        </div>

        <aside class="side-info">
          <div class="card">
            <h3>At a glance</h3>
            <div class="side-info">
              <div class="row"><span>Profile strength</span><strong><?= (int) $completion ?>%</strong></div>
              <div class="row"><span>Open to work</span><strong><?= !empty($publicSeeker['open_to_work']) ? 'Yes' : 'No' ?></strong></div>
              <?php if (!empty($publicSeeker['availability'])): ?><div class="row"><span>Availability</span><strong><?= e($publicSeeker['availability']) ?></strong></div><?php endif; ?>
              <?php if (!empty($publicSeeker['preferred_location'])): ?><div class="row"><span>Preferred location</span><strong><?= e($publicSeeker['preferred_location']) ?></strong></div><?php endif; ?>
            </div>
          </div>
          <?php if ($canSeeContact): ?>
            <div class="card">
              <h3>Contact</h3>
              <div class="side-info">
                <?php if (!empty($publicUser['email'])): ?><div class="row"><span>Email</span><strong style="word-break:break-all"><?= e($publicUser['email']) ?></strong></div><?php endif; ?>
                <?php if (!empty($publicUser['phone'])): ?><div class="row"><span>Phone</span><strong><?= e($publicUser['phone']) ?></strong></div><?php endif; ?>
                <?php if (!empty($publicSeeker['linkedin_url'])): ?><div class="row"><span>LinkedIn</span><a href="<?= e($publicSeeker['linkedin_url']) ?>" rel="noopener noreferrer" target="_blank">Open →</a></div><?php endif; ?>
                <?php if (!empty($publicSeeker['github_url'])): ?><div class="row"><span>GitHub</span><a href="<?= e($publicSeeker['github_url']) ?>" rel="noopener noreferrer" target="_blank">Open →</a></div><?php endif; ?>
                <?php if (!empty($publicSeeker['website_url'])): ?><div class="row"><span>Website</span><a href="<?= e($publicSeeker['website_url']) ?>" rel="noopener noreferrer" target="_blank">Open →</a></div><?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="card"><h3>Get in touch</h3><p class="small muted mb-0">Contact details are visible to the profile owner and administrators only.</p></div>
          <?php endif; ?>
          <?php if ($isOwner): ?><div class="card"><a class="btn btn-outline btn-block" href="<?= e(url('profile/edit')) ?>"><?= icon('edit', 16) ?> Edit my profile</a></div><?php endif; ?>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
