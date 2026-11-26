<?php $role = $selectedRole ?: 'seeker'; ?>
<div class="auth-shell">
  <aside class="auth-aside">
    <span class="eyebrow">Free forever</span>
    <h1>Your skills deserve an opportunity.</h1>
    <p>Build a profile that gets noticed, discover opportunities that fit and track every application.</p>
    <ul>
      <li><?= icon('chart', 18) ?> Honest compatibility scores</li>
      <li><?= icon('shield', 18) ?> Verified opportunities and scam protection</li>
      <li><?= icon('download', 18) ?> CV builder and PDF downloads</li>
    </ul>
  </aside>
  <main class="auth-main">
    <form class="auth-card wide" method="post" action="<?= e(url('register')) ?>">
      <?= csrf_field() ?>
      <h2>Create your account</h2>
      <p class="muted small mb-2">Already registered? <a href="<?= e(url('login')) ?>">Log in</a></p>
      <div class="form-group">
        <span class="form-label">I am here as a…</span>
        <div class="role-grid">
          <?php foreach ([
            ['seeker', 'user', 'Job seeker', 'Find jobs, internships and grow'],
            ['employer', 'building', 'Employer', 'Hire talent and manage vacancies'],
            ['freelancer', 'code', 'Freelancer', 'Offer services and find projects'],
          ] as [$value, $ic, $label, $hint]): ?>
            <label class="role-card">
              <input type="radio" name="role" value="<?= e($value) ?>" <?= $role === $value ? 'checked' : '' ?> onchange="document.querySelectorAll('.employer-only').forEach(function(el){el.style.display=this.value==='employer'?'block':'none'},this)">
              <?= icon($ic, 22) ?><strong><?= e($label) ?></strong><small><?= e($hint) ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required minlength="2" value="<?= e(old('name')) ?>"></div>
        <div class="form-group"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required value="<?= e(old('email')) ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required minlength="8"><span class="form-hint">At least 8 characters.</span></div>
        <div class="form-group"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="phone">Phone <span class="muted">(optional)</span></label><input class="form-control" id="phone" name="phone" placeholder="+250 788 000 000" value="<?= e(old('phone')) ?>"></div>
        <div class="form-group"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" placeholder="Kigali" value="<?= e(old('location', 'Kigali')) ?>"></div>
      </div>
      <div id="employer-fields" class="employer-only" style="display:<?= $role === 'employer' ? 'block' : 'none' ?>">
        <div class="form-group"><label class="form-label" for="company_name">Company / organisation name</label><input class="form-control" id="company_name" name="company_name" value="<?= e(old('company_name')) ?>"></div>
      </div>
      <label class="checkbox form-group"><input type="checkbox" name="terms" value="1" required><span>I agree to the <a href="<?= e(url('terms')) ?>">Terms</a> and <a href="<?= e(url('privacy')) ?>">Privacy Policy</a>.</span></label>
      <button class="btn btn-primary btn-block btn-lg" type="submit">Create free account</button>
    </form>
  </main>
</div>
