<section class="section">
  <div class="container-narrow">
    <nav class="small muted mb-2"><a href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>">← Back to opportunity</a></nav>
    <div class="section-kicker">Application</div>
    <h1>Apply for <?= e($job['title']) ?></h1>
    <p class="muted">At <?= e($job['company_name']) ?> · <?= e($job['location']) ?></p>
    <form class="card" method="post" action="<?= e(url('jobs/' . $job['id'] . '/apply')) ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="resume_id">Choose your CV</label>
        <select class="form-control" name="resume_id" id="resume_id">
          <option value="">Use my profile and CV builder data</option>
          <?php foreach ($resumes as $resume): ?><option value="<?= (int) $resume['id'] ?>" <?= $resume['is_default'] ? 'selected' : '' ?>><?= e($resume['title']) ?> (<?= e($resume['original_name']) ?>)</option><?php endforeach; ?>
        </select>
        <span class="form-hint"><a href="<?= e(url('my-cv')) ?>">Build or upload a CV</a></span>
      </div>
      <div class="form-group">
        <label class="form-label" for="cover_letter">Cover letter / message to the employer</label>
        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="9" minlength="20" required placeholder="Introduce yourself, explain why this role interests you and highlight your most relevant experience..."><?= e(old('cover_letter')) ?></textarea>
        <span class="form-hint">At least 20 characters. Tailoring this message greatly improves your chances.</span>
      </div>
      <?php if (!empty($seeker['headline'])): ?><div class="card mb-2" style="background:var(--surface-2)"><strong class="small">Profile headline:</strong> <span class="small"><?= e($seeker['headline']) ?></span></div><?php endif; ?>
      <div class="d-flex gap-1 flex-wrap"><button class="btn btn-primary btn-lg" type="submit">Submit application</button><a class="btn btn-outline btn-lg" href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>">Cancel</a></div>
    </form>
  </div>
</section>
