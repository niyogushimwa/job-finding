<?php
/** Career Growth Centre — articles, tips, stories and interview practice. */
$activeCategory = $activeCategory ?? '';
$bodyHasHtml = static fn(string $text): bool => preg_match('/<[a-z][^>]*>/i', $text) === 1;
?>
<section class="section-tight" style="background:linear-gradient(125deg,var(--navy),var(--navy-3));color:#fff">
  <div class="container">
    <span class="eyebrow">Career Growth Centre</span>
    <h1 style="color:#fff">Build a career with confidence.</h1>
    <p class="mb-0" style="color:#c9d8ed">Practical, Rwanda-first guidance for writing your CV, finding work, preparing for interviews and growing after you are hired.</p>
    <div class="d-flex flex-wrap gap-1 mt-3">
      <a class="btn btn-gold" href="<?= e(url('jobs')) ?>">Browse jobs <?= icon('arrow', 16) ?></a>
      <a class="btn btn-outline" href="<?= e(url('interview-prep')) ?>">Practise interviews</a>
      <a class="btn btn-outline" href="<?= e(url('my-cv')) ?>">Build my CV</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div><div class="section-kicker">Learn from experts</div><h2>Guides and articles</h2><p>Written for the Rwandan and African job market — short, practical and backed by real experience.</p></div>
    </div>
    <?php if ($categories): ?>
      <div class="d-flex flex-wrap gap-1 mb-3">
        <a class="tag <?= $activeCategory === '' ? 'tag-blue' : '' ?>" href="<?= e(url('career-centre')) ?>">All topics</a>
        <?php foreach ($categories as $category): ?>
          <a class="tag <?= $activeCategory === $category['category'] ? 'tag-blue' : '' ?>" href="<?= e(url('career-centre?category=' . rawurlencode((string) $category['category']))) ?>"><?= e($category['category']) ?> · <?= (int) $category['total'] ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($articles): ?>
      <div class="grid grid-3">
        <?php foreach ($articles as $article): ?>
          <a class="card card-hover article-card" href="<?= e(url('career-centre/' . $article['slug'])) ?>">
            <div class="article-image"><?= icon('graduation', 34) ?></div>
            <span class="tag tag-blue"><?= e($article['category'] ?? 'Career advice') ?></span>
            <strong><?= e($article['title']) ?></strong>
            <span class="small muted"><?= e(str_limit((string) ($article['excerpt'] ?? ''), 130)) ?></span>
            <span class="small">Read article <?= icon('arrow', 14) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?php
      $emptyTitle = 'No articles in this topic yet';
      $emptyText = 'Choose another topic or browse all career guidance.';
      $emptyAction = '<a class="btn btn-outline btn-sm" href="' . e(url('career-centre')) . '">All articles</a>';
      include BASE_PATH . '/app/Views/partials/empty.php';
      ?>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Small steps, big difference</div><h2>Career tips you can use today</h2><p>Practical habits that improve every application and interview.</p></div></div>
    <?php if ($tips): ?>
      <div class="grid grid-3">
        <?php foreach ($tips as $tip): ?>
          <div class="card tip-card">
            <span class="tag tag-green"><?= e($tip['category'] ?? 'Career tip') ?></span>
            <strong><?= e($tip['title']) ?></strong>
            <p class="small muted mb-0"><?= $bodyHasHtml((string) $tip['body']) ? nl2br(e(strip_tags((string) $tip['body']))) : nl2br(e((string) $tip['body'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else:
      $emptyTitle = 'No career tips yet';
      $emptyText = 'New practical tips are added regularly.';
      $emptyAction = '';
      include BASE_PATH . '/app/Views/partials/empty.php';
    endif; ?>
  </div>
</section>

<section class="section">
  <div class="container grid grid-2" style="align-items:start">
    <div>
      <div class="section-kicker">Practise out loud</div>
      <h2>Interview questions, answered</h2>
      <p class="muted">Work through real questions with sample answers. No sign-up needed.</p>
      <?php if ($questions): ?>
        <div class="card">
          <?php foreach (array_slice($questions, 0, 4) as $question): ?>
            <details style="padding:.75rem 0;border-bottom:1px solid var(--border)">
              <summary style="cursor:pointer;font-weight:650"><?= e($question['question']) ?></summary>
              <p class="small muted mb-1"><strong>Sample answer:</strong> <?= e(str_limit((string) ($question['sample_answer'] ?? ''), 260)) ?></p>
              <?php if (!empty($question['tips'])): ?><p class="small muted mb-0"><strong>Tip:</strong> <?= e($question['tips']) ?></p><?php endif; ?>
            </details>
          <?php endforeach; ?>
          <a class="btn btn-outline btn-sm mt-2" href="<?= e(url('interview-prep')) ?>">See all questions <?= icon('arrow', 14) ?></a>
        </div>
      <?php else:
        $emptyTitle = 'No interview questions yet';
        $emptyText = 'Visit the interview preparation page for more practice.';
        $emptyAction = '';
        include BASE_PATH . '/app/Views/partials/empty.php';
      endif; ?>
    </div>
    <div>
      <div class="section-kicker">Real journeys</div>
      <h2>Success stories</h2>
      <p class="muted">Proof that structured applications, practice and persistence pay off.</p>
      <?php if ($stories): foreach ($stories as $story): ?>
        <div class="card story-card mb-2">
          <span class="tag tag-gold">Success story</span>
          <h3><?= e($story['headline']) ?></h3>
          <p class="small muted mb-1"><?= e(str_limit(strip_tags((string) $story['story']), 220)) ?></p>
          <span class="small">— <?= e($story['name']) ?></span>
        </div>
      <?php endforeach; else:
        $emptyTitle = 'No success stories yet';
        $emptyText = 'Community stories will appear here as members share their journeys.';
        $emptyAction = '';
        include BASE_PATH . '/app/Views/partials/empty.php';
      endif; ?>
      <a class="btn btn-outline btn-block" href="<?= e(url('community')) ?>">Share your journey with the community</a>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--navy);color:#fff">
  <div class="container d-flex justify-between align-center flex-wrap gap-2">
    <div><h2 style="color:#fff;margin:0">Ready to put this into practice?</h2><p class="mb-0" style="color:#c6d5ea">Update your CV, apply to matched roles and learn alongside other job seekers.</p></div>
    <div class="d-flex flex-wrap gap-1">
      <a class="btn btn-gold" href="<?= e(url('jobs')) ?>">Find opportunities</a>
      <a class="btn btn-outline" href="<?= e(url('my-cv')) ?>">Open my CV</a>
      <a class="btn btn-outline" href="<?= e(url('community')) ?>">Community</a>
    </div>
  </div>
</section>
