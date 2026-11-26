<div class="auth-shell">
  <aside class="auth-aside">
    <span class="eyebrow">Welcome back</span>
    <h1>Your next step starts here.</h1>
    <p>Sign in to manage applications, messages, saved opportunities and your professional profile.</p>
    <ul>
      <li><?= icon('briefcase', 18) ?> Track applications in real time</li>
      <li><?= icon('bell', 18) ?> Get notified about matching jobs</li>
      <li><?= icon('chat', 18) ?> Message employers directly</li>
    </ul>
  </aside>
  <main class="auth-main">
    <form class="auth-card" method="post" action="<?= e(url('login')) ?>">
      <?= csrf_field() ?>
      <h2>Log in</h2>
      <p class="muted small mb-2">New here? <a href="<?= e(url('register')) ?>">Create an account</a></p>
      <div class="form-group"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required autocomplete="email" value="<?= e(old('email')) ?>"></div>
      <div class="form-group"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required autocomplete="current-password"></div>
      <div class="d-flex justify-between align-center mb-2">
        <label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <a class="small" href="<?= e(url('forgot-password')) ?>">Forgot password?</a>
      </div>
      <button class="btn btn-primary btn-block btn-lg" type="submit">Log in</button>
    </form>
  </main>
</div>
