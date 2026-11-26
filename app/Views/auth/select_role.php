<section class="section">
  <div class="container-narrow">
    <div class="section-kicker">One account, many possibilities</div>
    <h1>How would you like to use JobLink?</h1>
    <p class="muted">You can change this later from the same page. Each account type has its own dashboard.</p>
    <form method="post" action="<?= e(url('select-role')) ?>">
      <?= csrf_field() ?>
      <div class="grid grid-3 mt-2">
        <?php foreach ([
          ['seeker', 'user', 'Job seeker', 'Apply for jobs, save opportunities and build your career.'],
          ['employer', 'building', 'Employer', 'Publish jobs, review applicants and hire your team.'],
          ['freelancer', 'code', 'Freelancer', 'Showcase services and bid for projects.'],
        ] as [$value, $ic, $label, $hint]): ?>
          <label class="card role-card"><input type="radio" name="role" value="<?= e($value) ?>" required><?= icon($ic, 24) ?><strong><?= e($label) ?></strong><small><?= e($hint) ?></small></label>
        <?php endforeach; ?>
      </div>
      <button class="btn btn-primary mt-3" type="submit">Continue</button>
    </form>
  </div>
</section>
