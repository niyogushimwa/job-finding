<?php /** Expects $settings, $announcements. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/settings';
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
      <div><h1>Platform settings</h1><p>Control branding, registration rules and safety switches.</p></div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/audit-logs')) ?>">Audit logs</a>
    </div>

    <section class="panel">
      <form method="post" action="<?= e(url('admin/settings')) ?>">
        <?= csrf_field() ?>
        <div class="panel-head"><h2>General settings</h2><button class="btn btn-primary btn-sm" type="submit">Save settings</button></div>
        <div class="panel-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="set-site-name">Site name</label>
              <input class="form-control" id="set-site-name" name="site_name" maxlength="150" value="<?= e($settings['site_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="set-tagline">Tagline</label>
              <input class="form-control" id="set-tagline" name="tagline" maxlength="250" value="<?= e($settings['tagline'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="set-email">Contact email</label>
              <input class="form-control" id="set-email" name="contact_email" type="email" maxlength="190" value="<?= e($settings['contact_email'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="set-password-length">Minimum password length</label>
              <input class="form-control" id="set-password-length" name="min_password_length" type="number" min="8" max="72" value="<?= e($settings['min_password_length'] ?? '8') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="set-radar-note">Opportunity radar note</label>
            <textarea class="form-control" id="set-radar-note" name="radar_note" rows="3" maxlength="500"><?= e($settings['radar_note'] ?? '') ?></textarea>
            <span class="form-hint">Shown to visitors above external opportunities.</span>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="checkbox"><input type="checkbox" name="maintenance_mode" value="1" <?= (string) ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>> Maintenance mode</label>
            </div>
            <div class="form-group">
              <label class="checkbox"><input type="checkbox" name="jobs_require_approval" value="1" <?= (string) ($settings['jobs_require_approval'] ?? '0') === '1' ? 'checked' : '' ?>> Jobs require admin approval</label>
            </div>
          </div>
        </div>
      </form>
    </section>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h2>Post an announcement</h2></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('admin/announcements')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="ann-title">Title *</label>
              <input class="form-control" id="ann-title" name="title" maxlength="200" required value="<?= e(old('title')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="ann-body">Message *</label>
              <textarea class="form-control" id="ann-body" name="body" rows="4" required><?= e(old('body')) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="ann-role">Audience</label>
              <select class="form-control" id="ann-role" name="target_role">
                <option value="">Everyone</option>
                <?php foreach (['seeker', 'employer', 'freelancer', 'admin'] as $option): ?>
                  <option value="<?= e($option) ?>" <?= old('target_role') === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <label class="checkbox"><input type="checkbox" name="active" value="1" <?= old('active', '1') ? 'checked' : '' ?>> Active</label>
            <div style="margin-top:1.2rem"><button class="btn btn-primary" type="submit">Post announcement</button></div>
          </form>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Recent announcements</h2><span class="badge"><?= e(count($announcements)) ?></span></div>
        <div class="panel-body">
          <?php if (!$announcements): ?>
            <?php $emptyTitle = 'No announcements'; $emptyText = 'Post an update using the form.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php else: ?>
            <ul class="timeline">
              <?php foreach ($announcements as $row): ?>
                <li>
                  <strong><?= e($row['title']) ?></strong>
                  <p style="margin:.25rem 0;color:var(--muted)"><?= e(str_limit(strip_tags((string) $row['body']), 130)) ?></p>
                  <time><?= e($row['target_role'] ?: 'Everyone') ?> · <?= e($row['admin_name'] ?: 'Administrator') ?> · <?= e(time_ago($row['created_at'] ?? null)) ?></time>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</div>
