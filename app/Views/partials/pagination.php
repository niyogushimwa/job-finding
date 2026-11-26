<?php /** Expected: $pager (Paginator) */ if ($pager->last > 1): ?>
<nav class="pagination" aria-label="Pagination">
  <?php if ($pager->current > 1): ?><a href="<?= e($pager->link($pager->current - 1)) ?>" aria-label="Previous page">‹</a><?php endif; ?>
  <?php foreach ($pager->pages() as $page): ?>
    <?php if ($page === $pager->current): ?><span class="current" aria-current="page"><?= $page ?></span><?php else: ?><a href="<?= e($pager->link($page)) ?>"><?= $page ?></a><?php endif; ?>
  <?php endforeach; ?>
  <?php if ($pager->current < $pager->last): ?><a href="<?= e($pager->link($pager->current + 1)) ?>" aria-label="Next page">›</a><?php endif; ?>
</nav>
<?php endif; ?>
