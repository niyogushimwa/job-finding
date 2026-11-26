<?php /** Expects $opportunity, $formAction, $opportunityCategories, $countries, $jobCategories, $statuses, $verificationStatuses. */ ?>
<?php
$raw = static function (string $key, mixed $default = '') use ($opportunity): string {
    $oldValue = old($key, null);
    if ($oldValue !== null) {
        return (string) $oldValue;
    }
    return (string) ($opportunity[$key] ?? $default);
};
$isChecked = static function (string $key) use ($opportunity): bool {
    $oldValue = old($key, null);
    if ($oldValue !== null) {
        return (bool) $oldValue;
    }
    return !empty($opportunity[$key]);
};
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/external-opportunities';
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
      <div>
        <h1><?= $opportunity ? 'Edit external opportunity' : 'Add external opportunity' ?></h1>
        <p>Details are used to detect duplicates and to display the opportunity on the radar.</p>
      </div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/external-opportunities')) ?>">Back to list</a>
    </div>

    <form class="panel" method="post" action="<?= e($formAction) ?>">
      <?= csrf_field() ?>
      <div class="panel-head"><h2>Opportunity details</h2><span class="badge">* required</span></div>
      <div class="panel-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="ext-title">Title *</label>
            <input class="form-control" id="ext-title" name="title" maxlength="220" required value="<?= e($raw('title')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-organization">Organisation *</label>
            <input class="form-control" id="ext-organization" name="organization" maxlength="180" required value="<?= e($raw('organization')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="ext-description">Description</label>
          <textarea class="form-control" id="ext-description" name="description" rows="4"><?= e($raw('description')) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label" for="ext-requirements">Requirements</label>
          <textarea class="form-control" id="ext-requirements" name="requirements" rows="3"><?= e($raw('requirements')) ?></textarea>
        </div>

        <div class="form-row three">
          <div class="form-group">
            <label class="form-label" for="ext-location">Location</label>
            <input class="form-control" id="ext-location" name="location" maxlength="150" value="<?= e($raw('location')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-country">Country</label>
            <select class="form-control" id="ext-country" name="country_id">
              <option value="">—</option>
              <?php foreach ($countries as $country): ?>
                <option value="<?= (int) $country['id'] ?>" <?= $raw('country_id') === (string) $country['id'] ? 'selected' : '' ?>><?= e($country['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-deadline">Deadline</label>
            <input class="form-control" id="ext-deadline" name="deadline" type="date" value="<?= e($raw('deadline')) ?>">
          </div>
        </div>

        <div class="form-row three">
          <div class="form-group">
            <label class="form-label" for="ext-opp-category">Opportunity category</label>
            <select class="form-control" id="ext-opp-category" name="opportunity_category_id">
              <option value="">—</option>
              <?php foreach ($opportunityCategories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $raw('opportunity_category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-job-category">Related job category</label>
            <select class="form-control" id="ext-job-category" name="category_id">
              <option value="">—</option>
              <?php foreach ($jobCategories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $raw('category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-salary">Salary / award</label>
            <input class="form-control" id="ext-salary" name="salary" maxlength="150" value="<?= e($raw('salary')) ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="ext-source-name">Source name</label>
            <input class="form-control" id="ext-source-name" name="source_name" maxlength="150" value="<?= e($raw('source_name')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-source-url">Source URL</label>
            <input class="form-control" id="ext-source-url" name="source_url" type="url" maxlength="500" placeholder="https://..." value="<?= e($raw('source_url')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="ext-application-url">Application URL *</label>
          <input class="form-control" id="ext-application-url" name="application_url" type="url" maxlength="500" required placeholder="https://..." value="<?= e($raw('application_url')) ?>">
          <span class="form-hint">A valid http:// or https:// link is required.</span>
        </div>
        <div class="form-group">
          <label class="form-label" for="ext-instructions">Application instructions</label>
          <textarea class="form-control" id="ext-instructions" name="application_instructions" rows="3"><?= e($raw('application_instructions')) ?></textarea>
        </div>

        <div class="form-row three">
          <div class="form-group">
            <label class="form-label" for="ext-status">Status</label>
            <select class="form-control" id="ext-status" name="status">
              <?php foreach ($statuses as $option): ?>
                <option value="<?= e($option) ?>" <?= $raw('status', 'draft') === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-verification">Verification</label>
            <select class="form-control" id="ext-verification" name="verification_status">
              <?php foreach ($verificationStatuses as $option): ?>
                <option value="<?= e($option) ?>" <?= $raw('verification_status', 'unverified') === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ext-currency">Currency</label>
            <input class="form-control" id="ext-currency" name="currency" maxlength="10" value="<?= e($raw('currency')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?= $isChecked('is_featured') ? 'checked' : '' ?>> Feature this opportunity on the radar</label>
        </div>
        <div class="form-group" style="border-top:1px solid var(--border);padding-top:1rem">
          <label class="checkbox"><input type="checkbox" name="force" value="1" <?= $isChecked('force') ? 'checked' : '' ?>> Override duplicate warning and save anyway</label>
          <span class="form-hint">Opportunities are fingerprinted by title, organisation and application URL.</span>
        </div>
      </div>
      <div class="panel-head" style="border-top:1px solid var(--border);border-bottom:0">
        <button class="btn btn-primary" type="submit"><?= $opportunity ? 'Save changes' : 'Create opportunity' ?></button>
        <a class="btn btn-outline" href="<?= e(url('admin/external-opportunities')) ?>">Cancel</a>
      </div>
    </form>
  </main>
</div>
