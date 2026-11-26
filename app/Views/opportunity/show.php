<?php
/** Public detail page for a third-party opportunity. Expects opportunity, related, safety. */
$verified = ($opportunity['verification_status'] ?? '') === 'verified';
$applicationUrl = trim((string) ($opportunity['application_url'] ?? ''));
$sourceUrl = trim((string) ($opportunity['source_url'] ?? ''));
?>
<section class="section-tight">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url()) ?>">Home</a> / <a href="<?= e(url('opportunity-radar')) ?>">Opportunity Radar</a> / <?= e(str_limit($opportunity['title'], 50)) ?></nav>

    <div class="detail-hero">
      <div class="d-flex align-center gap-2 flex-wrap">
        <span class="company-logo"><?= e(mb_substr((string) $opportunity['organization'], 0, 1)) ?></span>
        <div>
          <div class="small" style="color:#e6b34a"><?= icon('building', 14) ?> <?= e($opportunity['organization']) ?></div>
          <h1 class="mt-0"><?= e($opportunity['title']) ?></h1>
        </div>
      </div>
      <div class="job-meta mt-2">
        <span><?= icon('map', 16) ?><?= e($opportunity['location'] ?: ($opportunity['country_name'] ?? 'Location not specified')) ?></span>
        <span><?= icon('clock', 16) ?>Deadline: <?= $opportunity['deadline'] ? e(format_date($opportunity['deadline'])) : 'Not specified' ?></span>
        <span><?= icon('globe', 16) ?>Source: <?= e($opportunity['source_name'] ?? 'External source') ?></span>
        <span><?= icon('list', 16) ?><?= e($opportunity['opportunity_category_name'] ?? 'Opportunity') ?></span>
      </div>
      <div class="d-flex gap-1 flex-wrap mt-3">
        <?php if ($verified): ?>
          <span class="tag tag-green" title="Organisation checked by administrators"><?= icon('shield', 13) ?> Verified listing</span>
        <?php else: ?>
          <span class="tag">Not yet verified</span>
        <?php endif; ?>
        <?php if (!empty($opportunity['is_featured'])): ?><span class="tag tag-gold"><?= icon('star', 13) ?> Featured</span><?php endif; ?>
        <span class="tag"><?= e(time_ago($opportunity['published_at'] ?? $opportunity['discovered_at'] ?? $opportunity['created_at'])) ?></span>
      </div>
    </div>

    <div class="detail-grid mt-3">
      <div>
        <div class="card rich-text mb-2">
          <h2>About this opportunity</h2>
          <div><?= nl2br(e((string) ($opportunity['description'] ?? ''))) ?></div>
          <?php if (!empty($opportunity['requirements'])): ?>
            <h2>Who can apply</h2>
            <div><?= nl2br(e((string) $opportunity['requirements'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($opportunity['application_instructions'])): ?>
            <h2>How to apply</h2>
            <div><?= nl2br(e((string) $opportunity['application_instructions'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($opportunity['salary'])): ?>
            <h2>Compensation</h2>
            <div><?= e($opportunity['salary']) ?></div>
          <?php endif; ?>
        </div>

        <div class="card">
          <h3>Before you apply</h3>
          <p class="small muted">This listing was discovered on an external site. JobLink Rwanda does not receive your application and cannot change the organisation's process.</p>
          <ul class="small" style="list-style:none;padding:0;display:grid;gap:.6rem">
            <?php foreach ($safety as $guidance): ?>
              <li><?= icon('check', 15) ?> <?= e($guidance) ?></li>
            <?php endforeach; ?>
          </ul>
          <p class="small mb-0">Something suspicious or a possible duplicate? <a href="<?= e(url('contact')) ?>">Contact our team</a> with the listing title and the official link.</p>
        </div>
      </div>

      <aside class="side-info">
        <div class="card">
          <h3>Apply directly</h3>
          <p class="small muted">You will be taken to the organisation's official site.</p>
          <?php if ($applicationUrl !== ''): ?>
            <a class="btn btn-primary btn-block btn-lg" href="<?= e($applicationUrl) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('arrow', 17) ?> Apply on official site</a>
          <?php else: ?>
            <p class="small muted mb-0">No application link is available. Please contact the source directly.</p>
          <?php endif; ?>
          <?php if ($sourceUrl !== ''): ?>
            <a class="btn btn-outline btn-block btn-sm mt-1" href="<?= e($sourceUrl) ?>" target="_blank" rel="noopener noreferrer nofollow">Visit source website</a>
          <?php endif; ?>
          <a class="btn btn-ghost btn-block btn-sm mt-1" href="<?= e(url('opportunity-radar')) ?>">Back to Opportunity Radar</a>
        </div>

        <div class="card">
          <h3>Listing details</h3>
          <div class="side-info">
            <div class="row"><span>Organisation</span><strong><?= e($opportunity['organization']) ?></strong></div>
            <div class="row"><span>Category</span><strong><?= e($opportunity['opportunity_category_name'] ?? '—') ?></strong></div>
            <div class="row"><span>Location</span><strong><?= e($opportunity['location'] ?: ($opportunity['country_name'] ?? '—')) ?></strong></div>
            <div class="row"><span>Deadline</span><strong><?= $opportunity['deadline'] ? e(format_date($opportunity['deadline'])) : '—' ?></strong></div>
            <div class="row"><span>Source</span><strong><?= e($opportunity['source_name'] ?? '—') ?></strong></div>
            <div class="row"><span>Verification</span><strong><?= $verified ? 'Verified' : 'Unverified' ?></strong></div>
          </div>
        </div>

        <div class="card">
          <h3>Trust &amp; safety</h3>
          <p class="small muted mb-0"><?= icon('shield', 15) ?> Check the official site for duplicate or expired postings before you apply again. Never pay a fee for an offer, and report concerns to us through the <a href="<?= e(url('contact')) ?>">contact page</a>.</p>
        </div>
      </aside>
    </div>

    <?php if ($related): ?>
      <div class="mt-3">
        <div class="section-head"><div><h2>More in this category</h2><p>Similar opportunities currently published on the radar.</p></div><a class="btn btn-outline btn-sm" href="<?= e(url('opportunity-radar')) ?>">View all</a></div>
        <div class="grid grid-3">
          <?php foreach ($related as $opportunityCard): ?>
            <?php $opportunity = $opportunityCard; include BASE_PATH . '/app/Views/partials/opportunity_card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
