<?php
$user = $profile['user'] ?? [];
$seeker = $profile['seeker'] ?? [];
$selectedTypes = array_filter(explode(',', (string) ($seeker['employment_types'] ?? '')));
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Keep it current</span>
        <h1>Edit profile</h1>
        <p>A detailed profile helps us find better matches for you.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('profile')) ?>">← Back to profile</a>
    </div>

    <section class="panel">
      <div class="panel-head"><h3>Profile photo</h3></div>
      <div class="panel-body d-flex gap-2 flex-wrap align-center">
        <div class="company-logo" style="width:56px;height:56px"><?= e(mb_substr((string) ($user['name'] ?? '?'), 0, 1)) ?></div>
        <form method="post" action="<?= e(url('profile/avatar')) ?>" enctype="multipart/form-data" class="d-flex gap-1 flex-wrap align-center">
          <?= csrf_field() ?>
          <input class="form-control" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required>
          <button class="btn btn-outline" type="submit"><?= icon('upload', 16) ?> Upload photo</button>
        </form>
        <span class="small muted">JPG, PNG or WEBP, up to 5 MB.</span>
      </div>
    </section>

    <form method="post" action="<?= e(url('profile/edit')) ?>">
      <?= csrf_field() ?>
      <section class="panel">
        <div class="panel-head"><h3>Basics</h3></div>
        <div class="panel-body">
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required minlength="2" maxlength="150" value="<?= e(old('name', $user['name'] ?? '')) ?>"></div>
            <div class="form-group"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" maxlength="40" value="<?= e(old('phone', $user['phone'] ?? '')) ?>"></div>
          </div>
          <div class="form-group"><label class="form-label" for="headline">Professional headline</label><input class="form-control" id="headline" name="headline" maxlength="200" placeholder="e.g. Frontend developer with a focus on accessibility" value="<?= e(old('headline', $seeker['headline'] ?? '')) ?>"></div>
          <div class="form-group"><label class="form-label" for="summary">Summary</label><textarea class="form-control" id="summary" name="summary" rows="6" maxlength="5000" placeholder="Describe your experience, strengths and what you are looking for..."><?= e(old('summary', $seeker['summary'] ?? '')) ?></textarea></div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Location & expectations</h3></div>
        <div class="panel-body">
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="location">Current location</label><input class="form-control" id="location" name="location" maxlength="150" value="<?= e(old('location', $seeker['location'] ?? '')) ?>" placeholder="Kigali"></div>
            <div class="form-group"><label class="form-label" for="preferred_location">Preferred location</label><input class="form-control" id="preferred_location" name="preferred_location" maxlength="150" value="<?= e(old('preferred_location', $seeker['preferred_location'] ?? '')) ?>" placeholder="Kigali or Remote"></div>
          </div>
          <div class="form-row three">
            <div class="form-group"><label class="form-label" for="country_id">Country</label><select class="form-control" id="country_id" name="country_id"><option value="">Select country</option><?php foreach ($countries as $country): ?><option value="<?= (int) $country['id'] ?>" <?= (string) old('country_id', $seeker['country_id'] ?? '') === (string) $country['id'] ? 'selected' : '' ?>><?= e($country['name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label" for="gender">Gender <span class="muted">(optional)</span></label><select class="form-control" id="gender" name="gender"><option value="">Prefer not to say</option><?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label): ?><option value="<?= $value ?>" <?= old('gender', $seeker['gender'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label" for="date_of_birth">Date of birth</label><input class="form-control" id="date_of_birth" name="date_of_birth" type="date" value="<?= e(old('date_of_birth', $seeker['date_of_birth'] ?? '')) ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="expected_salary_min">Expected salary from</label><input class="form-control" id="expected_salary_min" name="expected_salary_min" type="number" min="0" step="1000" value="<?= e(old('expected_salary_min', $seeker['expected_salary_min'] ?? '')) ?>"></div>
            <div class="form-group"><label class="form-label" for="expected_salary_max">Expected salary to</label><input class="form-control" id="expected_salary_max" name="expected_salary_max" type="number" min="0" step="1000" value="<?= e(old('expected_salary_max', $seeker['expected_salary_max'] ?? '')) ?>"></div>
          </div>
          <div class="form-row three">
            <div class="form-group"><label class="form-label" for="salary_period">Salary period</label><select class="form-control" id="salary_period" name="salary_period"><?php foreach (['hourly' => 'Hourly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $value => $label): ?><option value="<?= $value ?>" <?= old('salary_period', $seeker['salary_period'] ?? 'monthly') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label" for="experience_years">Years of experience</label><input class="form-control" id="experience_years" name="experience_years" type="number" min="0" max="60" value="<?= e(old('experience_years', $seeker['experience_years'] ?? 0)) ?>"></div>
            <div class="form-group"><label class="form-label" for="education_level">Highest education</label><input class="form-control" id="education_level" name="education_level" maxlength="80" placeholder="Bachelor, Diploma..." value="<?= e(old('education_level', $seeker['education_level'] ?? '')) ?>"></div>
          </div>
          <div class="form-group"><span class="form-label">Employment types</span><div class="d-flex flex-wrap gap-2"><?php foreach ($employmentTypes as $value => $label): ?>
            <label class="checkbox"><input type="checkbox" name="employment_types[]" value="<?= e($value) ?>" <?= in_array($value, $selectedTypes, true) ? 'checked' : '' ?>><span><?= e($label) ?></span></label>
          <?php endforeach; ?></div></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="availability">Availability</label><input class="form-control" id="availability" name="availability" maxlength="80" placeholder="Immediately, 2 weeks notice..." value="<?= e(old('availability', $seeker['availability'] ?? '')) ?>"></div>
            <div class="form-group"><span class="form-label">Preferences</span>
              <label class="checkbox mb-1"><input type="checkbox" name="remote_only" value="1" <?= !empty($seeker['remote_only']) ? 'checked' : '' ?>><span>Remote roles only</span></label>
              <label class="checkbox"><input type="checkbox" name="open_to_work" value="1" <?= ($seeker['open_to_work'] ?? 1) ? 'checked' : '' ?>><span>Open to work (visible on public profile)</span></label>
            </div>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Links</h3></div>
        <div class="panel-body">
          <div class="form-row three">
            <div class="form-group"><label class="form-label" for="linkedin_url">LinkedIn URL</label><input class="form-control" id="linkedin_url" name="linkedin_url" type="url" value="<?= e(old('linkedin_url', $seeker['linkedin_url'] ?? '')) ?>" placeholder="https://..."></div>
            <div class="form-group"><label class="form-label" for="github_url">GitHub URL</label><input class="form-control" id="github_url" name="github_url" type="url" value="<?= e(old('github_url', $seeker['github_url'] ?? '')) ?>" placeholder="https://..."></div>
            <div class="form-group"><label class="form-label" for="website_url">Website URL</label><input class="form-control" id="website_url" name="website_url" type="url" value="<?= e(old('website_url', $seeker['website_url'] ?? '')) ?>" placeholder="https://..."></div>
          </div>
          <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save profile</button>
        </div>
      </section>
    </form>

    <section class="panel">
      <div class="panel-head"><h3>Education, experience & certifications</h3></div>
      <div class="panel-body">
        <details class="mb-2" <?= $education ? '' : 'open' ?>>
          <summary class="small"><strong>Education</strong> (<?= count($education) ?>)</summary>
          <div class="mt-2">
            <?php foreach ($education as $row): ?>
              <div class="d-flex justify-between align-center gap-1 small mb-1">
                <span><?= e($row['degree']) ?> · <?= e($row['school']) ?></span>
                <form method="post" action="<?= e(url('profile/entries/' . $row['id'] . '/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="type" value="education"><button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Remove this education entry?')"><?= icon('trash', 14) ?></button></form>
              </div>
            <?php endforeach; ?>
            <form method="post" action="<?= e(url('profile/entries')) ?>" class="mt-2">
              <?= csrf_field() ?><input type="hidden" name="type" value="education">
              <div class="form-row">
                <div class="form-group"><label class="form-label">School</label><input class="form-control" name="school" required maxlength="180" value="<?= e(old('school')) ?>"></div>
                <div class="form-group"><label class="form-label">Degree</label><input class="form-control" name="degree" required maxlength="120" value="<?= e(old('degree')) ?>"></div>
              </div>
              <div class="form-row three">
                <div class="form-group"><label class="form-label">Field of study</label><input class="form-control" name="field_of_study" maxlength="120" value="<?= e(old('field_of_study')) ?>"></div>
                <div class="form-group"><label class="form-label">Start year</label><input class="form-control" name="start_year" type="number" min="1900" max="2100"></div>
                <div class="form-group"><label class="form-label">End year</label><input class="form-control" name="end_year" type="number" min="1900" max="2100"></div>
              </div>
              <div class="form-group"><label class="form-label">Grade <span class="muted">(optional)</span></label><input class="form-control" name="grade" maxlength="40" value="<?= e(old('grade')) ?>"></div>
              <button class="btn btn-outline btn-sm" type="submit"><?= icon('plus', 14) ?> Add education</button>
            </form>
          </div>
        </details>

        <details class="mb-2">
          <summary class="small"><strong>Experience</strong> (<?= count($experience) ?>)</summary>
          <div class="mt-2">
            <?php foreach ($experience as $row): ?>
              <div class="d-flex justify-between align-center gap-1 small mb-1">
                <span><?= e($row['title']) ?> · <?= e($row['company']) ?></span>
                <form method="post" action="<?= e(url('profile/entries/' . $row['id'] . '/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="type" value="experience"><button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Remove this experience entry?')"><?= icon('trash', 14) ?></button></form>
              </div>
            <?php endforeach; ?>
            <form method="post" action="<?= e(url('profile/entries')) ?>" class="mt-2">
              <?= csrf_field() ?><input type="hidden" name="type" value="experience">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Company</label><input class="form-control" name="company" required maxlength="180"></div>
                <div class="form-group"><label class="form-label">Job title</label><input class="form-control" name="title" required maxlength="150"></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Start date</label><input class="form-control" name="start_date" type="date"></div>
                <div class="form-group"><label class="form-label">End date</label><input class="form-control" name="end_date" type="date"></div>
              </div>
              <div class="form-group"><label class="checkbox"><input type="checkbox" name="is_current" value="1"><span>I currently work here</span></label></div>
              <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
              <button class="btn btn-outline btn-sm" type="submit"><?= icon('plus', 14) ?> Add experience</button>
            </form>
          </div>
        </details>

        <details>
          <summary class="small"><strong>Certifications</strong> (<?= count($certifications) ?>)</summary>
          <div class="mt-2">
            <?php foreach ($certifications as $row): ?>
              <div class="d-flex justify-between align-center gap-1 small mb-1">
                <span><?= e($row['name']) ?> · <?= e($row['issuer'] ?? '') ?></span>
                <form method="post" action="<?= e(url('profile/entries/' . $row['id'] . '/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="type" value="certification"><button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Remove this certification?')"><?= icon('trash', 14) ?></button></form>
              </div>
            <?php endforeach; ?>
            <form method="post" action="<?= e(url('profile/entries')) ?>" class="mt-2">
              <?= csrf_field() ?><input type="hidden" name="type" value="certification">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Certificate name</label><input class="form-control" name="name" required maxlength="180"></div>
                <div class="form-group"><label class="form-label">Issuing organisation</label><input class="form-control" name="issuer" maxlength="180"></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Year issued</label><input class="form-control" name="issued_year" type="number" min="1900" max="2100"></div>
                <div class="form-group"><label class="form-label">Credential ID</label><input class="form-control" name="credential_id" maxlength="120"></div>
              </div>
              <div class="form-group"><label class="form-label">Credential URL</label><input class="form-control" name="credential_url" type="url" placeholder="https://..."></div>
              <button class="btn btn-outline btn-sm" type="submit"><?= icon('plus', 14) ?> Add certification</button>
            </form>
          </div>
        </details>
      </div>
    </section>
  </main>
</div>
