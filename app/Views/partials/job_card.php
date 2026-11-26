<?php
use App\Core\Auth;
use App\Core\Upload;
use App\Models\Job;
/** Expected: $job row with company fields, optional $showMatch. */
$saved = Job::isSaved((int) $job['id'], Auth::id());
$logo = !empty($job['company_logo']) ? Upload::url($job['company_logo']) : '';
?>
<article class="card card-hover job-card">
  <div class="job-card-top">
    <div class="company-logo">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($job['company_name'] ?? '') ?> logo"><?php else: ?><?= e(mb_substr((string) ($job['company_name'] ?? '?'), 0, 1)) ?><?php endif; ?>
    </div>
    <div style="min-width:0;flex:1">
      <h3><a href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>"><?= e($job['title']) ?></a></h3>
      <div class="company-name">
        <?= e($job['company_name'] ?? 'Employer') ?>
        <?php if (($job['company_verification'] ?? '') === 'verified'): ?><span class="verified" title="Company verified by administrators"><?= icon('shield', 14) ?></span><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="job-meta">
    <span><?= icon('map', 16) ?><?= e($job['location']) ?></span>
    <span><?= icon('briefcase', 16) ?><?= e(str_replace('_', ' ', $job['employment_type'])) ?></span>
    <span><?= icon('clock', 16) ?><?= e(time_ago($job['published_at'] ?? $job['created_at'])) ?></span>
  </div>
  <div class="d-flex flex-wrap gap-1">
    <?php if (!empty($job['featured'])): ?><span class="tag tag-gold"><?= icon('star', 13) ?> Featured</span><?php endif; ?>
    <?php if ($job['work_mode'] === 'remote'): ?><span class="tag tag-blue">Remote</span><?php endif; ?>
    <?php if (!empty($job['category_name'])): ?><span class="tag"><?= e($job['category_name']) ?></span><?php endif; ?>
    <?php if (isset($showMatch) && $showMatch && isset($job['match_score'])): ?><span class="match-badge"><?= icon('chart', 13) ?> <?= (int) $job['match_score'] ?>% match</span><?php endif; ?>
  </div>
  <div class="job-card-foot">
    <span class="small muted"><?= e(str_limit(strip_tags((string) $job['description']), 90)) ?></span>
    <div class="d-flex align-center gap-1">
      <?php if (Auth::id()): ?>
        <button class="save-btn <?= $saved ? 'saved' : '' ?>" type="button" data-save-url="<?= e(url('jobs/' . $job['id'] . '/save')) ?>" aria-label="<?= $saved ? 'Remove saved job' : 'Save job' ?>" aria-pressed="<?= $saved ? 'true' : 'false' ?>"><?= icon('bookmark', 17) ?></button>
      <?php endif; ?>
      <a class="btn btn-primary btn-sm" href="<?= e(url('jobs/' . $job['id'] . '/' . $job['slug'])) ?>"><?= e(t('details')) ?></a>
    </div>
  </div>
</article>
