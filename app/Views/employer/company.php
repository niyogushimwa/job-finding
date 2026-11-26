<?php
/** Company profile form. Expected: $ctx, $company, $industries, $countries */
use App\Core\Upload;
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = 'employer/company';
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
        <h1>Company profile</h1>
        <p>This information appears on your public company page and on every opportunity you post.</p>
      </div>
      <?php if ($company): ?><a class="btn btn-outline" href="<?= e(url('companies/' . $company['id'] . '/' . $company['slug'])) ?>">View public page</a><?php endif; ?>
    </div>

    <?php if (!empty($ctx['notice'])): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div>
    <?php endif; ?>

    <?php if (!$company): ?>
      <div class="empty-state">
        <div class="empty-icon"><?= icon('building', 28) ?></div>
        <h3>No company profile linked</h3>
        <p>Your administrator account is not linked to an employer company profile. A company profile is created automatically when an employer account registers.</p>
        <a class="btn btn-outline" href="<?= e(url('employer/dashboard')) ?>">Back to overview</a>
      </div>
    <?php else: ?>
      <?php $verification = (string) ($company['verification_status'] ?? 'unverified'); ?>
      <div class="card mb-3 d-flex justify-between align-center flex-wrap gap-2">
        <div>
          <strong><?= icon('shield', 16) ?> Verification: <span class="status status-<?= e($verification) ?>"><?= e(str_replace('_', ' ', $verification)) ?></span></strong>
          <p class="small muted mb-0">Verified companies display a trust badge to job seekers.</p>
        </div>
        <?php if ($verification !== 'verified'): ?><span class="small muted">Administrators review profiles after you save your details.</span><?php endif; ?>
      </div>

      <form class="card" method="post" action="<?= e(url('employer/company')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="name">Company name</label>
            <input class="form-control" id="name" name="name" maxlength="180" required value="<?= e(old('name', $company['name'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="industry_id">Industry</label>
            <select class="form-control" id="industry_id" name="industry_id">
              <option value="">Choose an industry</option>
              <?php foreach ($industries as $industry): ?>
                <option value="<?= (int) $industry['id'] ?>" <?= (string) old('industry_id', $company['industry_id'] ?? '') === (string) $industry['id'] ? 'selected' : '' ?>><?= e($industry['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="website">Website</label>
            <input class="form-control" id="website" name="website" type="url" maxlength="255" placeholder="https://example.com" value="<?= e(old('website', $company['website'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Contact email</label>
            <input class="form-control" id="email" name="email" type="email" maxlength="190" value="<?= e(old('email', $company['email'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone</label>
            <input class="form-control" id="phone" name="phone" maxlength="40" value="<?= e(old('phone', $company['phone'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="country_id">Country</label>
            <select class="form-control" id="country_id" name="country_id">
              <option value="">Choose a country</option>
              <?php foreach ($countries as $country): ?>
                <option value="<?= (int) $country['id'] ?>" <?= (string) old('country_id', $company['country_id'] ?? '') === (string) $country['id'] ? 'selected' : '' ?>><?= e($country['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="location">City / location</label>
            <input class="form-control" id="location" name="location" maxlength="150" value="<?= e(old('location', $company['location'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="address">Address</label>
            <input class="form-control" id="address" name="address" maxlength="255" value="<?= e(old('address', $company['address'])) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="company_size">Company size</label>
            <select class="form-control" id="company_size" name="company_size">
              <?php foreach (['' => 'Select size', '1-10' => '1–10 employees', '11-50' => '11–50 employees', '51-200' => '51–200 employees', '201-500' => '201–500 employees', '501-1000' => '501–1,000 employees', '1000+' => '1,000+ employees'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= (string) old('company_size', $company['company_size'] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="founded_year">Founded year</label>
            <input class="form-control" id="founded_year" name="founded_year" type="number" min="1800" max="<?= (int) date('Y') + 1 ?>" value="<?= e(old('founded_year', $company['founded_year'])) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">About the company</label>
          <textarea class="form-control" id="description" name="description" rows="6" maxlength="5000" placeholder="Describe what your organisation does and what makes it a great place to work."><?= e(old('description', $company['description'])) ?></textarea>
        </div>

        <div class="form-row mb-2">
          <div class="form-group">
            <label class="form-label" for="logo">Company logo</label>
            <?php if (!empty($company['logo'])): ?><div class="mb-1"><img src="<?= e(Upload::url($company['logo'])) ?>" alt="Current company logo" style="max-height:64px;max-width:160px;border-radius:8px"></div><?php endif; ?>
            <input class="form-control" id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
            <span class="form-hint">JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current logo.</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="banner">Cover banner</label>
            <?php if (!empty($company['banner'])): ?><div class="mb-1"><img src="<?= e(Upload::url($company['banner'])) ?>" alt="Current company banner" style="max-height:64px;max-width:220px;border-radius:8px"></div><?php endif; ?>
            <input class="form-control" id="banner" name="banner" type="file" accept="image/jpeg,image/png,image/webp">
            <span class="form-hint">Used on your public company page. Leave empty to keep the current banner.</span>
          </div>
        </div>

        <div class="d-flex gap-1 flex-wrap">
          <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save company profile</button>
          <a class="btn btn-outline" href="<?= e(url('employer/dashboard')) ?>">Cancel</a>
        </div>
      </form>
    <?php endif; ?>
  </main>
</div>
