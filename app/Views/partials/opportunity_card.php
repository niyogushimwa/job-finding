<?php
/** Expected: $opportunity row with opportunity_category_name and country_name. Used only by opportunity views. */
$oppId = (int) ($opportunity['id'] ?? 0);
$oppLocation = (string) ($opportunity['location'] ?? '');
$oppCountry = (string) ($opportunity['country_name'] ?? '');
$oppDeadline = $opportunity['deadline'] ?? null;
?>
<article class="card card-hover opportunity-card">
  <div class="d-flex justify-between align-start gap-2">
    <div style="min-width:0">
      <h3 class="mb-1"><a href="<?= e(url('opportunity-radar/' . $oppId)) ?>"><?= e($opportunity['title']) ?></a></h3>
      <div class="small muted"><?= icon('building', 14) ?> <?= e($opportunity['organization']) ?></div>
    </div>
    <?php if (!empty($opportunity['is_featured'])): ?><span class="tag tag-gold"><?= icon('star', 13) ?> Featured</span><?php endif; ?>
  </div>
  <p class="small muted mb-0"><?= e(str_limit(strip_tags((string) ($opportunity['description'] ?? '')), 130)) ?></p>
  <div class="job-meta">
    <span><?= icon('map', 15) ?><?= e($oppLocation !== '' ? $oppLocation : ($oppCountry !== '' ? $oppCountry : 'Location not specified')) ?></span>
    <span><?= icon('clock', 15) ?>Deadline: <?= $oppDeadline ? e(format_date($oppDeadline)) : 'Not specified' ?></span>
  </div>
  <div class="d-flex flex-wrap gap-1">
    <span class="tag"><?= e($opportunity['opportunity_category_name'] ?? 'Opportunity') ?></span>
    <?php if (($opportunity['verification_status'] ?? '') === 'verified'): ?>
      <span class="tag tag-green" title="Organisation checked by administrators"><?= icon('shield', 13) ?> Verified</span>
    <?php else: ?>
      <span class="tag">Unverified</span>
    <?php endif; ?>
    <?php if ($oppCountry !== '' && strcasecmp($oppLocation, $oppCountry) !== 0): ?><span class="tag tag-blue"><?= e($oppCountry) ?></span><?php endif; ?>
  </div>
  <div class="d-flex justify-between align-center gap-2 mt-auto">
    <span class="small muted"><?= e($opportunity['source_name'] ?? 'External source') ?></span>
    <a class="btn btn-primary btn-sm" href="<?= e(url('opportunity-radar/' . $oppId)) ?>">View details <?= icon('arrow', 14) ?></a>
  </div>
</article>
