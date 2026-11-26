<?php /** Expects $ads, $positions. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/ads';
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
      <div><h1>Advertisements</h1><p>Manage promotional banners shown across the site.</p></div>
      <span class="badge"><?= e(count($ads)) ?> total</span>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h2>New advertisement</h2></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('admin/ads')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="ad-title">Title *</label>
              <input class="form-control" id="ad-title" name="title" maxlength="180" required value="<?= e(old('title')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="ad-description">Description</label>
              <textarea class="form-control" id="ad-description" name="description" rows="2"><?= e(old('description')) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="ad-link">Link URL</label>
              <input class="form-control" id="ad-link" name="link" type="url" maxlength="500" placeholder="https://..." value="<?= e(old('link')) ?>">
              <span class="form-hint">Optional. Leave empty if the advert has no destination.</span>
            </div>
            <div class="form-group">
              <label class="form-label" for="ad-position">Position</label>
              <select class="form-control" id="ad-position" name="position">
                <?php foreach ($positions as $option): ?>
                  <option value="<?= e($option) ?>" <?= old('position', 'sidebar') === $option ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $option)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="ad-starts">Starts at</label>
                <input class="form-control" id="ad-starts" name="starts_at" type="datetime-local" value="<?= e(old('starts_at')) ?>">
              </div>
              <div class="form-group">
                <label class="form-label" for="ad-ends">Ends at</label>
                <input class="form-control" id="ad-ends" name="ends_at" type="datetime-local" value="<?= e(old('ends_at')) ?>">
              </div>
            </div>
            <label class="checkbox"><input type="checkbox" name="active" value="1" <?= old('active') ? 'checked' : '' ?>> Active immediately</label>
            <div style="margin-top:1.2rem"><button class="btn btn-primary" type="submit">Create advertisement</button></div>
          </form>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Placement guide</h2></div>
        <div class="panel-body">
          <div class="side-info">
            <?php foreach ($positions as $option): ?>
              <div class="row"><span><?= e(ucwords(str_replace('_', ' ', $option))) ?></span><span class="badge"><?= e($option) ?></span></div>
            <?php endforeach; ?>
          </div>
          <p style="color:var(--muted);font-size:.85rem;margin-top:1rem">Only active adverts inside their date window are shown to visitors.</p>
        </div>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h2>Existing advertisements</h2></div>
      <div class="panel-body panel-body flush">
        <?php if (!$ads): ?>
          <?php $emptyTitle = 'No advertisements'; $emptyText = 'Create a banner using the form above.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Title</th><th>Position</th><th>Schedule</th><th>Link</th><th>Status</th><th></th></tr></thead><tbody>
            <?php foreach ($ads as $row): ?>
              <tr>
                <td><strong><?= e($row['title']) ?></strong><br><small><?= e(str_limit(strip_tags((string) ($row['description'] ?: '')), 80)) ?></small></td>
                <td><span class="badge"><?= e(str_replace('_', ' ', (string) $row['position'])) ?></span></td>
                <td><small><?= e(format_date($row['starts_at'] ?? null, 'M j, Y H:i')) ?><br>→ <?= e(format_date($row['ends_at'] ?? null, 'M j, Y H:i')) ?></small></td>
                <td><?php if (!empty($row['link'])): ?><a href="<?= e($row['link']) ?>" target="_blank" rel="noopener noreferrer">Open</a><?php else: ?>—<?php endif; ?></td>
                <td><span class="status <?= (int) $row['active'] === 1 ? 'status-published' : 'status-dismissed' ?>"><?= (int) $row['active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                <td>
                  <form method="post" action="<?= e(url('admin/ads/' . $row['id'] . '/toggle')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline" type="submit"><?= (int) $row['active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
