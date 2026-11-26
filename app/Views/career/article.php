<?php
/** Admin-authored article. HTML bodies are rendered as HTML; plain text uses nl2br. */
$body = (string) ($article['body'] ?? '');
$hasHtml = preg_match('/<[a-z][^>]*>/i', $body) === 1;
?>
<section class="section-tight">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url()) ?>">Home</a> / <a href="<?= e(url('career-centre')) ?>">Career Growth Centre</a> / <?= e(str_limit($article['title'], 50)) ?></nav>
    <div class="detail-hero">
      <span class="eyebrow"><?= e($article['category'] ?? 'Career advice') ?></span>
      <h1 class="mt-0"><?= e($article['title']) ?></h1>
      <div class="job-meta mt-2">
        <span><?= icon('clock', 16) ?>Published <?= e(format_date($article['published_at'] ?? $article['created_at'])) ?></span>
        <span><?= icon('eye', 16) ?><?= (int) $article['views'] ?> reads</span>
      </div>
      <?php if (!empty($article['excerpt'])): ?><p class="mt-2 mb-0" style="color:#c9d8ed"><?= e($article['excerpt']) ?></p><?php endif; ?>
    </div>

    <div class="detail-grid mt-3">
      <div>
        <div class="card rich-text">
          <?php if ($hasHtml): ?>
            <?= $body ?>
          <?php else: ?>
            <?= nl2br(e($body)) ?>
          <?php endif; ?>
        </div>
        <div class="card mt-2">
          <h3>Put this into practice</h3>
          <p class="small muted">Turn advice into action with a strong CV and focused applications.</p>
          <div class="d-flex flex-wrap gap-1">
            <a class="btn btn-primary btn-sm" href="<?= e(url('my-cv')) ?>">Build my CV <?= icon('arrow', 14) ?></a>
            <a class="btn btn-outline btn-sm" href="<?= e(url('jobs')) ?>">Browse jobs</a>
            <a class="btn btn-outline btn-sm" href="<?= e(url('interview-prep')) ?>">Practise interviews</a>
          </div>
        </div>
      </div>

      <aside class="side-info">
        <div class="card">
          <h3>More from the centre</h3>
          <?php if ($related): ?>
            <div class="side-info">
              <?php foreach ($related as $item): ?>
                <a class="small d-block mb-1" href="<?= e(url('career-centre/' . $item['slug'])) ?>"><?= e(str_limit($item['title'], 70)) ?></a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="small muted mb-1">No other articles in this topic yet.</p>
          <?php endif; ?>
          <a class="btn btn-outline btn-sm btn-block mt-2" href="<?= e(url('career-centre')) ?>">All articles</a>
        </div>
        <div class="card">
          <h3>Keep learning together</h3>
          <p class="small muted mb-2">Swap tips and ask questions with other Rwandan job seekers.</p>
          <a class="btn btn-outline btn-block" href="<?= e(url('community')) ?>">Visit the community <?= icon('arrow', 14) ?></a>
        </div>
      </aside>
    </div>
  </div>
</section>
