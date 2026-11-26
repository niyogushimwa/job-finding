<?php
/** Expected: $cv (normalised CV array) and optional $template. All output escaped. */
$template = $template ?? ($cv['template'] ?? 'modern');
if (!in_array($template, ['classic', 'modern', 'creative'], true)) {
    $template = 'modern';
}
$paperClass = 'cv-paper' . ($template === 'classic' ? ' cv-template-classic' : ($template === 'creative' ? ' cv-template-creative' : ''));
$contact = $cv['contact'] ?? [];
$cvName = (string) ($contact['name'] ?? ($cv['full_name'] ?? 'Your name'));
$cvHeadline = (string) ($cv['headline'] ?? '');
$cvSummary = (string) ($cv['summary'] ?? '');
$bits = [];
foreach (['email' => 'Email', 'phone' => 'Phone', 'location' => 'Location', 'linkedin' => 'LinkedIn', 'github' => 'GitHub', 'website' => 'Website'] as $key => $label) {
    if (!empty($contact[$key])) {
        $bits[$label] = (string) $contact[$key];
    }
}
?>
<article class="<?= e($paperClass) ?>" id="cv-paper-preview">
  <h1><?= e($cvName) ?></h1>
  <?php if ($cvHeadline !== ''): ?><p class="cv-muted" style="margin:.3rem 0 1rem"><?= e($cvHeadline) ?></p><?php endif; ?>
  <?php if ($bits): ?>
    <div class="cv-grid cv-muted" style="font-size:.85rem">
      <?php foreach ($bits as $label => $value): ?><span><strong><?= e($label) ?>:</strong> <?= e($value) ?></span><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($cvSummary !== ''): ?>
    <h2>Profile</h2>
    <p style="white-space:pre-line;line-height:1.65"><?= e($cvSummary) ?></p>
  <?php endif; ?>

  <?php if (!empty($cv['experience'])): ?>
    <h2>Experience</h2>
    <?php foreach ($cv['experience'] as $item): ?>
      <div class="cv-item">
        <strong><?= e($item['title'] ?? '') ?><?= !empty($item['company']) ? ' · ' . e($item['company']) : '' ?></strong>
        <?php if (!empty($item['start_date']) || !empty($item['end_date'])): ?>
          <span class="cv-muted small"><?= e(trim((string) ($item['start_date'] ?? '') . ' – ' . (string) ($item['end_date'] ?? ''), ' –')) ?></span>
        <?php endif; ?>
        <?php if (!empty($item['description'])): ?><p style="white-space:pre-line;line-height:1.55;margin:.4rem 0 0"><?= e($item['description']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($cv['education'])): ?>
    <h2>Education</h2>
    <?php foreach ($cv['education'] as $item): ?>
      <div class="cv-item">
        <strong><?= e($item['degree'] ?? '') ?><?= !empty($item['school']) ? ' · ' . e($item['school']) : '' ?></strong>
        <?php if (!empty($item['field_of_study']) || !empty($item['start_year']) || !empty($item['end_year'])): ?>
          <span class="cv-muted small"><?= e(trim(implode(' · ', array_filter([$item['field_of_study'] ?? '', (string) ($item['start_year'] ?? ''), (string) ($item['end_year'] ?? '')])), ' ·')) ?></span>
        <?php endif; ?>
        <?php if (!empty($item['grade'])): ?><p class="cv-muted small" style="margin:.3rem 0 0">Grade: <?= e($item['grade']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($cv['skills'])): ?>
    <h2>Skills</h2>
    <p style="line-height:1.9"><?php foreach ($cv['skills'] as $item): ?><span style="display:inline-block;border:1px solid #d1d5db;border-radius:6px;padding:.15rem .55rem;margin:0 .35rem .35rem 0"><?= e($item['name'] ?? '') ?><?= !empty($item['level']) ? ' (' . e($item['level']) . ')' : '' ?></span><?php endforeach; ?></p>
  <?php endif; ?>

  <?php if (!empty($cv['projects'])): ?>
    <h2>Projects</h2>
    <?php foreach ($cv['projects'] as $item): ?>
      <div class="cv-item">
        <strong><?= e($item['title'] ?? '') ?></strong>
        <?php if (!empty($item['url'])): ?><span class="cv-muted small"> — <?= e($item['url']) ?></span><?php endif; ?>
        <?php if (!empty($item['description'])): ?><p style="white-space:pre-line;line-height:1.55;margin:.4rem 0 0"><?= e($item['description']) ?></p><?php endif; ?>
        <?php if (!empty($item['tags'])): ?><p class="cv-muted small" style="margin:.3rem 0 0"><?= e($item['tags']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($cv['languages'])): ?>
    <h2>Languages</h2>
    <p><?php foreach ($cv['languages'] as $item): ?><span style="margin-right:1rem"><?= e($item['name'] ?? '') ?><?= !empty($item['level']) ? ' — ' . e($item['level']) : '' ?></span><?php endforeach; ?></p>
  <?php endif; ?>
</article>
