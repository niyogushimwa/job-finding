<div class="auth-shell">
  <aside class="auth-aside"><span class="eyebrow">Almost there</span><h1>Choose a strong new password.</h1><p>Use at least 8 characters and something you have not used on other sites.</p></aside>
  <main class="auth-main">
    <form class="auth-card" method="post" action="<?= e(url('reset-password')) ?>">
      <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
      <h2>Reset password</h2>
      <div class="form-group"><label class="form-label" for="password">New password</label><input class="form-control" id="password" name="password" type="password" required minlength="8"></div>
      <div class="form-group"><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Update password</button>
    </form>
  </main>
</div>
