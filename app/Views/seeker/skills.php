<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Show what you know</span>
        <h1>My skills</h1>
        <p>Skills power your match scores and can be endorsed by other JobLink members.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('profile')) ?>"><?= icon('user', 16) ?> View profile</a>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Your skills</h3><span class="small muted"><?= count($skills) ?> added</span></div>
        <div class="panel-body">
          <?php if ($skills): ?>
            <?php foreach ($skills as $skill): ?>
              <div class="d-flex justify-between align-center gap-1 mb-2">
                <div>
                  <strong><?= e($skill['name']) ?></strong>
                  <div class="small muted"><?= e($skill['level']) ?> · <?= (float) $skill['years'] ?> year<?= (float) $skill['years'] === 1.0 ? '' : 's' ?><?= !empty($skill['category']) ? ' · ' . e($skill['category']) : '' ?></div>
                </div>
                <div class="d-flex align-center gap-1">
                  <?php if ((int) $skill['endorsements'] > 0): ?><span class="tag tag-green"><?= (int) $skill['endorsements'] ?> endorsement<?= (int) $skill['endorsements'] === 1 ? '' : 's' ?></span><?php endif; ?>
                  <form method="post" action="<?= e(url('my-skills/' . $skill['user_skill_id'] . '/delete')) ?>" onsubmit="return confirm('Remove this skill?')">
                    <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit" aria-label="Remove <?= e($skill['name']) ?>"><?= icon('trash', 14) ?></button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <?php $emptyTitle = 'No skills listed'; $emptyText = 'Add your strongest skills to improve your recommendations.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Add a skill</h3></div>
        <div class="panel-body">
          <form method="post" action="<?= e(url('my-skills')) ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label" for="skill_id">Skill</label><select class="form-control" id="skill_id" name="skill_id" required><option value="">Choose a skill</option><?php foreach ($allSkills as $skill): ?><option value="<?= (int) $skill['id'] ?>"><?= e($skill['name']) ?><?= !empty($skill['category']) ? ' · ' . e($skill['category']) : '' ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label" for="level">Level</label><select class="form-control" id="level" name="level"><?php foreach ($levels as $value => $label): ?><option value="<?= $value ?>" <?= $value === 'intermediate' ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label" for="years">Years of experience</label><input class="form-control" id="years" name="years" type="number" min="0" max="50" step="0.5" value="0"></div>
            <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Add skill</button>
          </form>
          <p class="small muted mt-2 mb-0">Tip: endorse skills on other members' public profiles. You cannot endorse your own.</p>
        </div>
      </section>
    </div>
  </main>
</div>
