<?php
/** Job create/edit form. Expected: $ctx, $job, $selectedSkills, $formAction, $categories, $industries, $countries, $skills, $company, enums */
$chosen = old('skills', array_keys($selectedSkills ?? []));
$chosen = is_array($chosen) ? array_map('intval', $chosen) : [];
$requiredSkills = old('skills_required', array_keys(array_filter($selectedSkills ?? [])));
$requiredSkills = is_array($requiredSkills) ? array_map('intval', $requiredSkills) : [];
$statusValue = (string) old('status', $job['status'] ?? 'pending_review');
?>
<div class="dashboard">
  <?php
  $sideTitle = 'Employer workspace';
  $active = !empty($job) ? 'employer/jobs' : 'employer/jobs/create';
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
        <h1><?= $job ? 'Edit opportunity' : 'Post a new opportunity' ?></h1>
        <p><?= $job ? 'Update the details below. Changes to published roles are visible immediately.' : 'Describe the role clearly to attract well-matched candidates.' ?></p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('employer/jobs')) ?>">← Manage opportunities</a>
    </div>

    <?php if (!empty($ctx['notice'])): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)"><p class="small mb-0"><?= e($ctx['notice']) ?></p></div>
    <?php endif; ?>

    <?php if (!$job && empty($ctx['company'])): ?>
      <div class="card mb-3" style="border-color:var(--amber);background:var(--amber-soft)">
        <p class="small mb-0">You do not have a company profile linked yet. <a href="<?= e(url('employer/company')) ?>">Complete your company profile</a> before posting.</p>
      </div>
    <?php endif; ?>

    <form class="card" method="post" action="<?= e(url($formAction)) ?>">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="title">Job title <span style="color:var(--red)">*</span></label>
        <input class="form-control" id="title" name="title" minlength="5" maxlength="200" required value="<?= e(old('title', $job['title'] ?? '')) ?>" placeholder="e.g. Frontend Developer (React)">
        <span class="form-hint">At least 5 characters. Include the role and, where useful, the specialism.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Role description <span style="color:var(--red)">*</span></label>
        <textarea class="form-control" id="description" name="description" rows="8" minlength="30" required placeholder="Explain the role, the team and why a candidate should apply..."><?= e(old('description', $job['description'] ?? '')) ?></textarea>
        <span class="form-hint">At least 30 characters. Be specific and avoid generic wording.</span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="responsibilities">Responsibilities</label>
          <textarea class="form-control" id="responsibilities" name="responsibilities" rows="5" placeholder="One responsibility per line"><?= e(old('responsibilities', $job['responsibilities'] ?? '')) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label" for="requirements">Requirements</label>
          <textarea class="form-control" id="requirements" name="requirements" rows="5" placeholder="Skills, qualifications and experience needed"><?= e(old('requirements', $job['requirements'] ?? '')) ?></textarea>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="benefits">Benefits</label>
        <textarea class="form-control" id="benefits" name="benefits" rows="3" placeholder="Salary-related benefits, learning budget, flexible hours..."><?= e(old('benefits', $job['benefits'] ?? '')) ?></textarea>
      </div>

      <div class="form-row three">
        <div class="form-group">
          <label class="form-label" for="category_id">Category</label>
          <select class="form-control" id="category_id" name="category_id">
            <option value="">Choose a category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= (int) $category['id'] ?>" <?= (string) old('category_id', $job['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="industry_id">Industry</label>
          <select class="form-control" id="industry_id" name="industry_id">
            <option value="">Choose an industry</option>
            <?php foreach ($industries as $industry): ?>
              <option value="<?= (int) $industry['id'] ?>" <?= (string) old('industry_id', $job['industry_id'] ?? '') === (string) $industry['id'] ? 'selected' : '' ?>><?= e($industry['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="country_id">Country</label>
          <select class="form-control" id="country_id" name="country_id">
            <option value="">Choose a country</option>
            <?php foreach ($countries as $country): ?>
              <option value="<?= (int) $country['id'] ?>" <?= (string) old('country_id', $job['country_id'] ?? '') === (string) $country['id'] ? 'selected' : '' ?>><?= e($country['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="location">Location <span style="color:var(--red)">*</span></label>
          <input class="form-control" id="location" name="location" maxlength="150" required value="<?= e(old('location', $job['location'] ?? 'Kigali')) ?>" placeholder="Kigali or Remote">
        </div>
        <div class="form-group">
          <label class="form-label" for="deadline">Application deadline</label>
          <input class="form-control" id="deadline" name="deadline" type="date" min="<?= e(date('Y-m-d')) ?>" value="<?= e(old('deadline', $job['deadline'] ?? '')) ?>">
          <span class="form-hint">Leave empty for an open-ended opportunity. Must be today or later.</span>
        </div>
        <div class="form-group">
          <label class="form-label" for="employment_type">Employment type</label>
          <select class="form-control" id="employment_type" name="employment_type">
            <?php foreach ($employmentTypes as $value): ?>
              <option value="<?= e($value) ?>" <?= old('employment_type', $job['employment_type'] ?? 'full_time') === $value ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $value))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="work_mode">Work mode</label>
          <select class="form-control" id="work_mode" name="work_mode">
            <?php foreach ($workModes as $value): ?>
              <option value="<?= e($value) ?>" <?= old('work_mode', $job['work_mode'] ?? 'onsite') === $value ? 'selected' : '' ?>><?= e($value === 'onsite' ? 'On-site' : ucfirst($value)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="experience_level">Experience level</label>
          <select class="form-control" id="experience_level" name="experience_level">
            <?php foreach ($experienceLevels as $value): ?>
              <option value="<?= e($value) ?>" <?= old('experience_level', $job['experience_level'] ?? 'entry') === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="education_level">Education level</label>
          <input class="form-control" id="education_level" name="education_level" maxlength="80" value="<?= e(old('education_level', $job['education_level'] ?? '')) ?>" placeholder="e.g. Bachelor, Diploma">
        </div>
      </div>

      <div class="form-row three">
        <div class="form-group">
          <label class="form-label" for="salary_min">Minimum salary</label>
          <input class="form-control" id="salary_min" name="salary_min" type="number" min="0" step="1" value="<?= e(old('salary_min', $job['salary_min'] ?? '')) ?>" placeholder="Optional">
        </div>
        <div class="form-group">
          <label class="form-label" for="salary_max">Maximum salary</label>
          <input class="form-control" id="salary_max" name="salary_max" type="number" min="0" step="1" value="<?= e(old('salary_max', $job['salary_max'] ?? '')) ?>" placeholder="Optional">
        </div>
        <div class="form-group">
          <label class="form-label" for="salary_currency">Currency / period</label>
          <div class="d-flex gap-1">
            <select class="form-control" name="salary_currency" aria-label="Salary currency">
              <?php foreach ($currencies as $currency): ?>
                <option value="<?= e($currency) ?>" <?= old('salary_currency', $job['salary_currency'] ?? 'RWF') === $currency ? 'selected' : '' ?>><?= e($currency) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-control" name="salary_period" aria-label="Salary period">
              <?php foreach ($salaryPeriods as $period): ?>
                <option value="<?= e($period) ?>" <?= old('salary_period', $job['salary_period'] ?? 'monthly') === $period ? 'selected' : '' ?>><?= e(ucfirst($period)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Skills required</label>
        <?php if ($skills): ?>
          <div class="grid grid-3" style="gap:.5rem">
            <?php foreach ($skills as $skill): ?>
              <?php $skillId = (int) $skill['id']; ?>
              <div class="role-card" style="display:flex;align-items:center;gap:.6rem">
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                  <input type="checkbox" name="skills[]" value="<?= $skillId ?>" <?= in_array($skillId, $chosen, true) ? 'checked' : '' ?>>
                  <span><strong class="small"><?= e($skill['name']) ?></strong><br><small class="muted"><?= e($skill['category'] ?? 'Skill') ?></small></span>
                </label>
                <label class="small" style="margin-left:auto;cursor:pointer">Required
                  <input type="checkbox" name="skills_required[]" value="<?= $skillId ?>" <?= in_array($skillId, $requiredSkills, true) ? 'checked' : '' ?>>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?><p class="small muted">No skills have been configured yet.</p><?php endif; ?>
        <span class="form-hint">Tick a skill to include it in matching. Tick “Required” for must-have skills.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="status">Save as</label>
        <select class="form-control" id="status" name="status">
          <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft (only visible to you)</option>
          <option value="pending_review" <?= $statusValue === 'pending_review' ? 'selected' : '' ?>>Submit for review</option>
          <?php if (!empty($ctx['is_admin'])): ?>
            <option value="published" <?= $statusValue === 'published' ? 'selected' : '' ?>>Publish now (administrators only)</option>
          <?php elseif ($job && $job['status'] === 'published'): ?>
            <option value="published" selected>Published (current status)</option>
          <?php endif; ?>
        </select>
        <span class="form-hint">Employer-submitted opportunities are reviewed by administrators before they go live.</span>
      </div>

      <div class="d-flex gap-1 flex-wrap">
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('check', 17) ?> <?= $job ? 'Save changes' : 'Create opportunity' ?></button>
        <a class="btn btn-outline btn-lg" href="<?= e(url('employer/jobs')) ?>">Cancel</a>
      </div>
    </form>
  </main>
</div>
