<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Account</span>
        <h1>Settings</h1>
        <p>Manage your account details, password and notification preferences.</p>
      </div>
    </div>

    <form method="post" action="<?= e(url('settings')) ?>">
      <?= csrf_field() ?>
      <section class="panel">
        <div class="panel-head"><h3>Account details</h3></div>
        <div class="panel-body">
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="settings-name">Full name</label><input class="form-control" id="settings-name" name="name" required minlength="2" maxlength="150" value="<?= e(old('name', $user['name'] ?? '')) ?>"></div>
            <div class="form-group"><label class="form-label" for="settings-email">Email address</label><input class="form-control" id="settings-email" name="email" type="email" required maxlength="190" value="<?= e(old('email', $user['email'] ?? '')) ?>"></div>
          </div>
          <div class="form-group"><label class="form-label" for="settings-phone">Phone</label><input class="form-control" id="settings-phone" name="phone" maxlength="40" value="<?= e(old('phone', $user['phone'] ?? '')) ?>"></div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Notification preferences</h3></div>
        <div class="panel-body">
          <div class="grid grid-2">
            <?php foreach ([
                'email_enabled' => 'Email notifications',
                'in_app_enabled' => 'In-app notifications',
                'match_enabled' => 'New job matches',
                'deadline_enabled' => 'Application deadline reminders',
                'message_enabled' => 'New messages',
                'application_enabled' => 'Application status changes',
            ] as $field => $label): ?>
              <label class="checkbox mb-2"><input type="hidden" name="<?= $field ?>" value="0"><input type="checkbox" name="<?= $field ?>" value="1" <?= !empty($prefs[$field]) ? 'checked' : '' ?>><span><?= e($label) ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Change password</h3><span class="small muted">Leave blank to keep your current password</span></div>
        <div class="panel-body">
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="current_password">Current password</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password"></div>
            <div class="form-group"><label class="form-label" for="new_password">New password</label><input class="form-control" id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password"></div>
          </div>
          <div class="form-group"><label class="form-label" for="new_password_confirmation">Confirm new password</label><input class="form-control" id="new_password_confirmation" name="new_password_confirmation" type="password" minlength="8" autocomplete="new-password"></div>
          <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save settings</button>
        </div>
      </section>
    </form>

    <section class="panel">
      <div class="panel-head"><h3>Deactivate account</h3></div>
      <div class="panel-body">
        <p class="small muted">Deactivating signs you out and hides your profile from employers. Contact support to reactivate later.</p>
        <form method="post" action="<?= e(url('account/deactivate')) ?>" onsubmit="return confirm('Deactivate your account? You will be signed out immediately.')">
          <?= csrf_field() ?><button class="btn btn-danger" type="submit"><?= icon('close', 16) ?> Deactivate my account</button>
        </form>
      </div>
    </section>
  </main>
</div>
