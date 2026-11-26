<?php
/** Reused by the jobs page and the JSON search endpoint. Expects $jobs, $pager, optional $basePath, $showMatch. */
$link = static function (int $page) use ($pager, $basePath): string {
    if (!empty($basePath)) {
        $query = array_merge($_GET, ['page' => $page]);
        unset($query['_csrf']);
        return url($basePath) . '?' . http_build_query($query);
    }
    return $pager->link($page);
};
?>
<p class="small muted mb-2"><strong id="result-count"><?= (int) $pager->total ?></strong> <?= e(t('results')) ?> · <?= e($pager->summary()) ?></p>
<?php if ($jobs): ?>
  <div class="grid grid-2">
    <?php foreach ($jobs as $job): $saved = \App\Models\Job::isSaved((int) $job['id'], \App\Core\Auth::id()); ob_start(); include BASE_PATH . '/app/Views/partials/job_card.php'; echo ob_get_clean(); endforeach; ?>
  </div>
  <?php if ($pager->last > 1): ?>
  <nav class="pagination" aria-label="Pagination">
    <?php if ($pager->current > 1): ?><a href="<?= e($link($pager->current - 1)) ?>">‹</a><?php endif; ?>
    <?php foreach ($pager->pages() as $page): ?>
      <?php if ($page === $pager->current): ?><span class="current" aria-current="page"><?= $page ?></span><?php else: ?><a href="<?= e($link($page)) ?>"><?= $page ?></a><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($pager->current < $pager->last): ?><a href="<?= e($link($pager->current + 1)) ?>">›</a><?php endif; ?>
  </nav>
  <?php endif; ?>
<?php else: ?>
  <?php $emptyTitle = t('no_results'); $emptyText = 'Try a broader keyword, clear filters, or check back later.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
<?php endif; ?>
