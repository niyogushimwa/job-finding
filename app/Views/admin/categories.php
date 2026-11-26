<?php /** Expects $jobCategories, $opportunityCategories, $parents. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/categories';
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
      <div><h1>Categories</h1><p>Organise jobs and external opportunities into clear, searchable groups.</p></div>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h2>Add a category</h2></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('admin/categories')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="cat-type">Type</label>
              <select class="form-control" id="cat-type" name="type">
                <option value="job" <?= old('type', 'job') === 'job' ? 'selected' : '' ?>>Job category</option>
                <option value="opportunity" <?= old('type') === 'opportunity' ? 'selected' : '' ?>>Opportunity category</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="cat-name">Name *</label>
              <input class="form-control" id="cat-name" name="name" maxlength="120" required value="<?= e(old('name')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="cat-slug">Slug</label>
              <input class="form-control" id="cat-slug" name="slug" maxlength="140" placeholder="Generated from name if empty" value="<?= e(old('slug')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="cat-parent">Parent (job categories only)</label>
              <select class="form-control" id="cat-parent" name="parent_id">
                <option value="">None</option>
                <?php foreach ($parents as $parent): ?>
                  <option value="<?= (int) $parent['id'] ?>" <?= (string) old('parent_id', '') === (string) $parent['id'] ? 'selected' : '' ?>><?= e($parent['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="cat-icon">Icon name (job categories only)</label>
              <input class="form-control" id="cat-icon" name="icon" maxlength="60" placeholder="e.g. code" value="<?= e(old('icon')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="cat-description">Description</label>
              <textarea class="form-control" id="cat-description" name="description" rows="2"><?= e(old('description')) ?></textarea>
            </div>
            <span class="form-hint">New categories are created as active.</span>
            <div style="margin-top:1.2rem"><button class="btn btn-primary" type="submit">Create category</button></div>
          </form>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Category counts</h2></div>
        <div class="panel-body">
          <div class="side-info">
            <div class="row"><span>Job categories</span><strong><?= e(count($jobCategories)) ?></strong></div>
            <div class="row"><span>Opportunity categories</span><strong><?= e(count($opportunityCategories)) ?></strong></div>
            <div class="row"><span>Jobs categorised</span><strong><?= e(array_sum(array_column($jobCategories, 'jobs_count'))) ?></strong></div>
            <div class="row"><span>Opportunities categorised</span><strong><?= e(array_sum(array_column($opportunityCategories, 'opportunities_count'))) ?></strong></div>
          </div>
          <p style="color:var(--muted);font-size:.85rem;margin-top:1rem">Deleting a category keeps its jobs and opportunities; their category link is cleared.</p>
        </div>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h2>Job categories</h2><span class="badge"><?= e(count($jobCategories)) ?></span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$jobCategories): ?>
          <?php $emptyTitle = 'No job categories'; $emptyText = 'Create your first job category using the form above.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Jobs</th><th>Active</th><th></th></tr></thead><tbody>
            <?php foreach ($jobCategories as $row): ?>
              <tr>
                <td><strong><?= e($row['name']) ?></strong></td>
                <td><code><?= e($row['slug']) ?></code></td>
                <td><?= e($row['parent_name'] ?: '—') ?></td>
                <td><?= (int) $row['jobs_count'] ?></td>
                <td><span class="status <?= (int) $row['active'] === 1 ? 'status-published' : '' ?>"><?= (int) $row['active'] === 1 ? 'Active' : 'Hidden' ?></span></td>
                <td>
                  <form method="post" action="<?= e(url('admin/categories/' . $row['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this category? Linked records will be kept.')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="job">
                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Opportunity categories</h2><span class="badge"><?= e(count($opportunityCategories)) ?></span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$opportunityCategories): ?>
          <?php $emptyTitle = 'No opportunity categories'; $emptyText = 'Create your first opportunity category using the form above.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Name</th><th>Slug</th><th>Opportunities</th><th>Active</th><th></th></tr></thead><tbody>
            <?php foreach ($opportunityCategories as $row): ?>
              <tr>
                <td><strong><?= e($row['name']) ?></strong></td>
                <td><code><?= e($row['slug']) ?></code></td>
                <td><?= (int) $row['opportunities_count'] ?></td>
                <td><span class="status <?= (int) $row['active'] === 1 ? 'status-published' : '' ?>"><?= (int) $row['active'] === 1 ? 'Active' : 'Hidden' ?></span></td>
                <td>
                  <form method="post" action="<?= e(url('admin/categories/' . $row['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this category? Linked records will be kept.')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="opportunity">
                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
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
