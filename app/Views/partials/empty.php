<?php /** Expected: $emptyTitle, $emptyText, $emptyAction (optional) */ ?>
<div class="empty-state">
  <div class="empty-icon"><?= icon('briefcase', 28) ?></div>
  <h3><?= e($emptyTitle ?? t('empty_title')) ?></h3>
  <p><?= e($emptyText ?? t('empty_text')) ?></p>
  <?php if (!empty($emptyAction)): ?><?= $emptyAction ?><?php endif; ?>
</div>
