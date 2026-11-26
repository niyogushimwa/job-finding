<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Never miss a role</span>
        <h1>Job alerts</h1>
        <p>Create alerts for the roles you want and we will watch new opportunities for you.</p>
      </div>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Your alerts</h3></div>
        <div class="panel-body">
          <?php if ($alerts): ?>
            <?php foreach ($alerts as $alert): ?>
              <div class="card mb-2">
                <div class="d-flex justify-between gap-1 flex-wrap">
                  <div>
                    <strong><?= e($alert['name']) ?></strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                      <span class="tag <?= !empty($alert['active']) ? 'tag-green' : '' ?>"><?= !empty($alert['active']) ? 'Active' : 'Paused' ?></span>
                      <span class="tag"><?= e(ucfirst($alert['frequency'])) ?></span>
                    </div>
                  </div>
                  <form method="post" action="<?= e(url('job-alerts/' . $alert['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this alert?')">
                    <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit"><?= icon('trash', 14) ?></button>
                  </form>
                </div>
                <p class="small muted mb-0 mt-2">
                  <?php
                  $parts = [];
                  if (!empty($alert['keywords'])) $parts[] = 'Keywords: ' . $alert['keywords'];
                  if (!empty($alert['location'])) $parts[] = 'Location: ' . $alert['location'];
                  if (!empty($alert['category_name'])) $parts[] = 'Category: ' . $alert['category_name'];
                  if (!empty($alert['employment_type'])) $parts[] = 'Type: ' . str_replace('_', ' ', $alert['employment_type']);
                  echo e($parts ? implode(' · ', $parts) : 'All published opportunities');
                  ?>
                </p>
                <?php if (!empty($alert['last_sent_at'])): ?><p class="small muted mb-0 mt-1">Last sent: <?= e(time_ago($alert['last_sent_at'])) ?></p><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <?php $emptyTitle = 'No alerts yet'; $emptyText = 'Create an alert on the right to hear about relevant opportunities.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Create an alert</h3></div>
        <div class="panel-body">
          <?php $values = $_SESSION['_old'] ?? []; ?>
          <form method="post" action="<?= e(url('job-alerts')) ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label" for="alert-name">Alert name</label><input class="form-control" id="alert-name" name="name" required maxlength="120" placeholder="e.g. PHP jobs in Kigali" value="<?= e($values['name'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label" for="alert-keywords">Keywords</label><input class="form-control" id="alert-keywords" name="keywords" maxlength="255" placeholder="PHP, React, nurse..." value="<?= e($values['keywords'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label" for="alert-location">Location</label><input class="form-control" id="alert-location" name="location" maxlength="150" placeholder="Kigali or Remote" value="<?= e($values['location'] ?? '') ?>"></div>
            <div class="form-row">
              <div class="form-group"><label class="form-label" for="alert-category">Category</label><select class="form-control" id="alert-category" name="category_id"><option value="">Any category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) ($values['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
              <div class="form-group"><label class="form-label" for="alert-type">Employment type</label><select class="form-control" id="alert-type" name="employment_type"><option value="">Any type</option><?php foreach ($employmentTypes as $value => $label): ?><option value="<?= $value ?>" <?= ($values['employment_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label class="form-label" for="alert-frequency">Frequency</label><select class="form-control" id="alert-frequency" name="frequency"><option value="daily" <?= ($values['frequency'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option><option value="weekly" <?= ($values['frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option></select></div>
            <div class="form-group"><input type="hidden" name="active" value="0"><label class="checkbox"><input type="checkbox" name="active" value="1" checked><span>Alert is active</span></label></div>
            <button class="btn btn-primary" type="submit"><?= icon('bell', 16) ?> Create alert</button>
          </form>
        </div>
      </section>
    </div>
  </main>
</div>
