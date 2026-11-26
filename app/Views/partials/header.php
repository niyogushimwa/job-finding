<?php
use App\Core\Auth;
use App\Core\I18n;
use App\Models\Content;
$user = Auth::user();
$unread = $user ? (int) App\Core\Database::scalar('SELECT COUNT(*) FROM notifications WHERE user_id = :id AND is_read = 0', ['id' => $user['id']]) : 0;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isActive = static fn(string $path): string => str_contains($currentPath, $path) ? ' active' : '';
?>
<header class="site-header">
  <div class="container nav-container">
    <a class="brand" href="<?= e(url()) ?>" aria-label="JobLink Rwanda home">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 13h20"/></svg></span>
      <span>JobLink<small>Rwanda</small></span>
    </a>
    <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" onclick="this.setAttribute('aria-expanded',this.getAttribute('aria-expanded')==='true'?'false':'true')"><?= icon('menu', 22) ?></button>
    <nav class="nav-links" aria-label="Main navigation">
      <a class="nav-link<?= $currentPath === rtrim(BASE_URL, '/') . '/' || $currentPath === '/' ? ' active' : '' ?>" href="<?= e(url()) ?>"><?= t('nav_home') ?></a>
      <a class="nav-link<?= $isActive('/jobs') ?>" href="<?= e(url('jobs')) ?>"><?= t('nav_jobs') ?></a>
      <a class="nav-link<?= $isActive('/companies') ?>" href="<?= e(url('companies')) ?>"><?= t('nav_companies') ?></a>
      <a class="nav-link<?= $isActive('/opportunity-radar') ?>" href="<?= e(url('opportunity-radar')) ?>"><?= t('nav_opportunities') ?></a>
      <a class="nav-link<?= $isActive('/career-centre') ?>" href="<?= e(url('career-centre')) ?>"><?= t('nav_career') ?></a>
      <a class="nav-link<?= $isActive('/community') ?>" href="<?= e(url('community')) ?>"><?= t('nav_community') ?></a>
    </nav>
    <div class="nav-actions">
      <button class="theme-toggle" type="button" aria-label="Toggle dark mode"><?= icon('moon', 18) ?></button>
      <form method="get" action="<?= e(url()) ?>" class="hide" id="lang-form"><input name="lang" value="<?= e(I18n::locale()) ?>"></form>
      <select class="lang-picker" aria-label="<?= e(t('language')) ?>" onchange="window.location.href='<?= e(url()) ?>?lang='+this.value">
        <?php foreach (I18n::supported() as $code => $name): ?>
          <option value="<?= e($code) ?>" <?= I18n::locale() === $code ? 'selected' : '' ?>><?= e($name) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if ($user): ?>
        <?php if ($unread > 0): ?>
        <div class="notif-wrap">
          <a id="notif-toggle" class="btn btn-ghost btn-sm" href="<?= e(url('notifications')) ?>" aria-label="Notifications"><?= icon('bell', 20) ?><span class="notif-dot"></span></a>
          <div id="notif-panel" class="notif-panel hide"><div id="notif-list" class="small muted" style="padding:.8rem">Loading…</div></div>
        </div>
        <?php else: ?>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('notifications')) ?>" aria-label="Notifications"><?= icon('bell', 20) ?></a>
        <?php endif; ?>
        <a class="btn btn-outline btn-sm" href="<?= e(url(match ($user['role']) {
          'admin' => 'admin/dashboard', 'employer' => 'employer/dashboard',
          'freelancer' => 'freelancer/dashboard', default => 'dashboard',
        })) ?>"><?= e($user['name']) ?></a>
        <form method="post" action="<?= e(url('logout')) ?>" class="m-0"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit" title="<?= e(t('logout')) ?>"><?= icon('arrow', 18) ?></button></form>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('login')) ?>"><?= e(t('nav_login')) ?></a>
        <a class="btn btn-outline btn-sm" href="<?= e(url('register')) ?>"><?= e(t('nav_register')) ?></a>
        <a class="btn btn-primary btn-sm" href="<?= e(url('employer/jobs/create')) ?>"><?= icon('plus', 16) ?><?= e(t('nav_post_job')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
