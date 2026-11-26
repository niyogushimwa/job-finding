<?php
/** Expected: $active, $links = [[label, path, icon], ...], $sideTitle */
?>
<aside class="sidebar" aria-label="Dashboard navigation">
  <div class="side-label"><?= e($sideTitle ?? 'Workspace') ?></div>
  <?php foreach ($links as $link): ?>
    <a class="side-link <?= $active === $link[1] ? 'active' : '' ?>" href="<?= e(url($link[1])) ?>"><?= icon($link[2], 18) ?><?= e($link[0]) ?></a>
  <?php endforeach; ?>
  <div class="side-label">Account</div>
  <a class="side-link" href="<?= e(url('settings')) ?>"><?= icon('settings', 18) ?>Settings</a>
  <a class="side-link" href="<?= e(url('messages')) ?>"><?= icon('chat', 18) ?>Messages</a>
  <a class="side-link" href="<?= e(url('notifications')) ?>"><?= icon('bell', 18) ?>Notifications</a>
</aside>
