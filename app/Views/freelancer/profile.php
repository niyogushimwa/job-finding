<?php
/** Expected: $sideTitle, $active, $links, $profile, $user, $currencies */
$currentCurrency = (string) old('currency', $profile['currency'] ?? 'RWF');
$currentAvailability = (string) old('availability', $profile['availability'] ?? 'available');
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>Freelance profile</h1>
        <p>This is what clients see when you send a proposal. Keep it clear and current.</p>
      </div>
      <span class="tag tag-blue"><?= icon('user', 14) ?> Freelancer account</span>
    </div>

    <div class="detail-grid">
      <div>
        <div class="panel">
          <div class="panel-head"><h2>Profile details</h2><span class="small muted">All fields marked * are required</span></div>
          <div class="panel-body">
            <form method="post" action="<?= e(url('freelancer/profile')) ?>">
              <?= csrf_field() ?>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="name">Full name *</label>
                  <input class="form-control" id="name" name="name" maxlength="150" required value="<?= e(old('name', $user['name'] ?? '')) ?>">
                </div>
                <div class="form-group">
                  <label class="form-label" for="phone">Phone</label>
                  <input class="form-control" id="phone" name="phone" maxlength="40" value="<?= e(old('phone', $user['phone'] ?? '')) ?>" placeholder="+250788000000">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="title">Professional title *</label>
                <input class="form-control" id="title" name="title" minlength="4" maxlength="180" required value="<?= e(old('title', $profile['title'] ?? '')) ?>" placeholder="e.g. Brand Designer & Web Developer">
                <span class="form-hint">Lead with the outcome you deliver and your specialism.</span>
              </div>

              <div class="form-group">
                <label class="form-label" for="bio">About you</label>
                <textarea class="form-control" id="bio" name="bio" maxlength="5000" rows="6" placeholder="Describe your experience, process and the kinds of projects you enjoy."><?= e(old('bio', $profile['bio'] ?? '')) ?></textarea>
              </div>

              <div class="form-row three">
                <div class="form-group">
                  <label class="form-label" for="hourly_rate">Hourly rate</label>
                  <input class="form-control" id="hourly_rate" name="hourly_rate" type="number" min="0" step="0.01" value="<?= e(old('hourly_rate', $profile['hourly_rate'] ?? '')) ?>" placeholder="0.00">
                </div>
                <div class="form-group">
                  <label class="form-label" for="project_rate">Typical project rate</label>
                  <input class="form-control" id="project_rate" name="project_rate" type="number" min="0" step="0.01" value="<?= e(old('project_rate', $profile['project_rate'] ?? '')) ?>" placeholder="0.00">
                </div>
                <div class="form-group">
                  <label class="form-label" for="currency">Currency</label>
                  <select class="form-control" id="currency" name="currency">
                    <?php foreach ($currencies as $currency): ?>
                      <option value="<?= e($currency) ?>" <?= $currentCurrency === $currency ? 'selected' : '' ?>><?= e($currency) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="availability">Availability</label>
                  <select class="form-control" id="availability" name="availability">
                    <option value="available" <?= $currentAvailability === 'available' ? 'selected' : '' ?>>Available for new projects</option>
                    <option value="busy" <?= $currentAvailability === 'busy' ? 'selected' : '' ?>>Busy — limited capacity</option>
                    <option value="unavailable" <?= $currentAvailability === 'unavailable' ? 'selected' : '' ?>>Not available</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label" for="response_time">Response time</label>
                  <input class="form-control" id="response_time" name="response_time" maxlength="80" value="<?= e(old('response_time', $profile['response_time'] ?? '')) ?>" placeholder="Within 24 hours">
                </div>
              </div>

              <button class="btn btn-primary" type="submit"><?= icon('check', 17) ?> Save profile</button>
            </form>
          </div>
        </div>
      </div>

      <aside class="side-info">
        <div class="card">
          <h3>Profile snapshot</h3>
          <div class="side-info">
            <div class="row"><span>Status</span><strong class="status status-<?= e($profile['availability']) ?>"><?= e(str_replace('_', ' ', (string) $profile['availability'])) ?></strong></div>
            <div class="row"><span>Hourly rate</span><strong><?= !empty($profile['hourly_rate']) ? e(format_money((float) $profile['hourly_rate'], $profile['currency'], 'hour')) : 'Not set' ?></strong></div>
            <div class="row"><span>Project rate</span><strong><?= !empty($profile['project_rate']) ? e(format_money((float) $profile['project_rate'], $profile['currency'], 'project')) : 'Not set' ?></strong></div>
            <div class="row"><span>Member since</span><strong><?= e(format_date($user['created_at'] ?? null)) ?></strong></div>
          </div>
        </div>
        <div class="card">
          <h3>Make your profile stand out</h3>
          <ul class="small muted mb-0" style="padding-left:1.1rem">
            <li>Use a specific title clients can search for.</li>
            <li>Describe past projects and measurable results.</li>
            <li>List your services separately so clients can book them.</li>
          </ul>
          <a class="small" href="<?= e(url('freelancer/services')) ?>">Manage my services →</a>
        </div>
      </aside>
    </div>
  </main>
</div>
