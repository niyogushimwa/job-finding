<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Stay informed</span>
        <h1>Notifications</h1>
        <p>Application updates, deadline reminders and new matches in one place.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('settings')) ?>"><?= icon('settings', 16) ?> Notification settings</a>
    </div>

    <section class="panel">
      <div class="panel-head"><h3>All notifications</h3><span class="small muted"><?= e($pager->summary()) ?></span></div>
      <div class="panel-body">
        <?php if ($notifications): ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="card mb-2 <?= empty($notification['is_read']) ? '' : '' ?>">
              <div class="d-flex justify-between gap-1 flex-wrap align-start">
                <div style="min-width:0;flex:1">
                  <strong><?= icon(empty($notification['is_read']) ? 'bell' : 'check', 15) ?> <?= e($notification['title']) ?> <?php if (empty($notification['is_read'])): ?><span class="tag tag-blue">New</span><?php endif; ?></strong>
                  <p class="small muted mb-1" style="white-space:pre-line"><?= e($notification['body']) ?></p>
                  <div class="d-flex gap-1 align-center">
                    <span class="small muted"><?= e(time_ago($notification['created_at'])) ?></span>
                    <?php if ($notification['href'] !== ''): ?><a class="small" href="<?= e($notification['href']) ?>">Open →</a><?php endif; ?>
                  </div>
                </div>
                <?php if (empty($notification['is_read'])): ?>
                  <form method="post" action="<?= e(url('api/notifications/read')) ?>">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                    <button class="btn btn-sm btn-outline" type="submit"><?= icon('check', 14) ?> Mark as read</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
        <?php else: ?>
          <?php $emptyTitle = 'No notifications'; $emptyText = 'Updates about your applications and new matches will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
