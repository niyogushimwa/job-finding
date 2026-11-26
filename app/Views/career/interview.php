<?php
/** Interview preparation — JS-free category filter and expandable sample answers. */
$activeCategory = $activeCategory ?? '';
?>
<section class="section-tight" style="background:linear-gradient(125deg,var(--navy),var(--navy-3));color:#fff">
  <div class="container">
    <span class="eyebrow">Interview Preparation</span>
    <h1 style="color:#fff">Walk into your interview ready.</h1>
    <p class="mb-0" style="color:#c9d8ed">Common, behavioural, technical and HR questions with sample answers you can adapt to your own story.</p>
    <div class="d-flex flex-wrap gap-1 mt-3">
      <a class="btn btn-gold" href="<?= e(url('my-cv')) ?>">Update my CV</a>
      <a class="btn btn-outline" href="<?= e(url('career-centre')) ?>">Read career guides</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Choose a focus</div><h2>Practice by category</h2><p>Open a question to see a sample answer and the thinking behind it.</p></div></div>
    <div class="d-flex flex-wrap gap-1 mb-3">
      <a class="tag <?= $activeCategory === '' ? 'tag-blue' : '' ?>" href="<?= e(url('interview-prep')) ?>">All questions</a>
      <?php foreach ($categories as $key => $label): ?>
        <a class="tag <?= $activeCategory === $key ? 'tag-blue' : '' ?>" href="<?= e(url('interview-prep?category=' . $key)) ?>"><?= e($label) ?> · <?= (int) ($counts[$key] ?? 0) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($grouped): ?>
      <?php foreach ($grouped as $category => $questions): ?>
        <div class="panel">
          <div class="panel-head"><h3><?= e($categories[$category] ?? ucfirst((string) $category)) ?></h3><span class="tag"><?= count($questions) ?> questions</span></div>
          <div class="panel-body">
            <?php foreach ($questions as $index => $question): ?>
              <details style="padding:.6rem 0;<?= $index > 0 ? 'border-top:1px solid var(--border);' : '' ?>">
                <summary style="cursor:pointer;font-weight:650"><?= ($index + 1) . '. ' . e($question['question']) ?></summary>
                <?php if (!empty($question['sample_answer'])): ?>
                  <p class="small mt-2 mb-1"><strong>Sample answer:</strong> <?= nl2br(e((string) $question['sample_answer'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($question['tips'])): ?>
                  <p class="small muted mb-0"><?= icon('info', 14) ?> <?= e($question['tips']) ?></p>
                <?php endif; ?>
              </details>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php
      $emptyTitle = 'No questions in this category yet';
      $emptyText = 'Choose another category or explore the full list.';
      $emptyAction = '<a class="btn btn-outline btn-sm" href="' . e(url('interview-prep')) . '">All questions</a>';
      include BASE_PATH . '/app/Views/partials/empty.php';
      ?>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container grid grid-2" style="align-items:start">
    <div>
      <div class="section-kicker">Coach's notes</div>
      <h2>Tips before the big day</h2>
      <?php if ($tips): ?>
        <?php foreach ($tips as $tip): ?>
          <div class="card tip-card mb-2">
            <span class="tag tag-green"><?= e($tip['category'] ?? 'Interview tip') ?></span>
            <strong><?= e($tip['title']) ?></strong>
            <p class="small muted mb-0"><?= e(str_limit(strip_tags((string) $tip['body']), 240)) ?></p>
          </div>
        <?php endforeach; ?>
      <?php else:
        $emptyTitle = 'No coaching tips yet';
        $emptyText = 'Check back soon for more interview advice.';
        $emptyAction = '';
        include BASE_PATH . '/app/Views/partials/empty.php';
      endif; ?>
    </div>
    <div>
      <div class="panel" style="margin:0">
        <div class="panel-body">
          <div class="section-kicker">Your next move</div>
          <h2>Prepare, apply, repeat.</h2>
          <p class="muted">A clear CV and targeted applications make every interview count. Bring your questions, know your story and follow up within 24 hours.</p>
          <ul class="small" style="list-style:none;padding:0;display:grid;gap:.6rem">
            <li><?= icon('check', 16) ?> Tailor your CV to the exact role</li>
            <li><?= icon('check', 16) ?> Research the organisation before the call</li>
            <li><?= icon('check', 16) ?> Track what you are asked after each interview</li>
          </ul>
          <div class="d-flex flex-wrap gap-1 mt-2">
            <a class="btn btn-primary btn-sm" href="<?= e(url('jobs')) ?>">Find jobs</a>
            <a class="btn btn-outline btn-sm" href="<?= e(url('my-cv')) ?>">My CV</a>
            <a class="btn btn-outline btn-sm" href="<?= e(url('community')) ?>">Ask the community</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
