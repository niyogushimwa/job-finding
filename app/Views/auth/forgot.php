<div class="auth-shell">
  <aside class="auth-aside"><span class="eyebrow">Account recovery</span><h1>We will help you get back in.</h1><p>Enter the email address linked to your account and we will send a secure reset link.</p></aside>
  <main class="auth-main">
    <form class="auth-card" method="post" action="<?= e(url('forgot-password')) ?>">
      <?= csrf_field() ?>
      <h2>Forgot your password?</h2>
      <p class="muted small">Reset links expire after one hour.</p>
      <div class="form-group"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required></div>
      <button class="btn btn-primary btn-block" type="submit">Send reset link</button>
      <p class="small text-center mt-2"><a href="<?= e(url('login')) ?>">Back to login</a></p>
    </form>
  </main>
</div>
