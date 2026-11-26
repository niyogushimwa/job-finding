<?php use App\Core\Auth; use App\Core\Upload; ?>
<section class="section">
  <div class="container">
    <nav class="small muted mb-2"><a href="<?= e(url('companies')) ?>">Companies</a> / <?= e($company['name']) ?></nav>
    <div class="detail-hero d-flex justify-between align-center flex-wrap gap-2">
      <div class="d-flex align-center gap-2">
        <span class="company-logo" style="width:72px;height:72px"><?= !empty($company['logo']) ? '<img src="' . e(Upload::url($company['logo'])) . '" alt="">' : e(mb_substr($company['name'], 0, 1)) ?></span>
        <div><h1 class="mb-1" style="color:#fff"><?= e($company['name']) ?></h1><span style="color:#c9d8ed"><?= e($company['industry_name'] ?? 'Employer') ?> · <?= e($company['location'] ?? 'Rwanda') ?></span></div>
      </div>
      <?php if (Auth::id() && in_array(Auth::role(), ['seeker', 'freelancer'], true)): ?>
        <form method="post" action="<?= e(url('companies/' . $company['id'] . '/follow')) ?>"><?= csrf_field() ?><button class="btn btn-gold" type="submit"><?= icon($following ? 'check' : 'plus', 16) ?> <?= $following ? 'Following' : 'Follow company' ?></button></form>
      <?php endif; ?>
    </div>
    <div class="detail-grid">
      <div>
        <div class="card rich-text mb-2"><h2>About <?= e($company['name']) ?></h2><p><?= nl2br(e($company['description'] ?: 'This company has not added a description yet.')) ?></p></div>
        <div class="card">
          <div class="panel-head" style="padding:0 0 1rem"><h2>Open opportunities</h2><span class="small muted"><?= count($jobs) ?> found</span></div>
          <?php if ($jobs): ?>
            <div class="grid" style="gap:1rem">
              <?php foreach ($jobs as $job): $saved = \App\Models\Job::isSaved((int) $job['id'], Auth::id()); include BASE_PATH . '/app/Views/partials/job_card.php'; endforeach; ?>
            </div>
          <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
        </div>
      </div>
      <aside class="card side-info">
        <h3>Company details</h3>
        <div class="row"><span>Location</span><strong><?= e($company['location'] ?? '—') ?></strong></div>
        <div class="row"><span>Industry</span><strong><?= e($company['industry_name'] ?? '—') ?></strong></div>
        <div class="row"><span>Company size</span><strong><?= e($company['company_size'] ?? '—') ?></strong></div>
        <div class="row"><span>Founded</span><strong><?= e($company['founded_year'] ?: '—') ?></strong></div>
        <div class="row"><span>Followers</span><strong><?= (int) $followers ?></strong></div>
        <div class="row"><span>Verification</span><strong><?= $company['verification_status'] === 'verified' ? '<span class="verified">Verified by admin</span>' : 'Not verified' ?></strong></div>
        <?php if ($company['website']): ?><a class="btn btn-outline btn-sm btn-block mt-2" target="_blank" rel="noopener noreferrer" href="<?= e($company['website']) ?>">Visit website</a><?php endif; ?>
        <?php if ($company['email']): ?><a class="btn btn-outline btn-sm btn-block mt-1" href="mailto:<?= e($company['email']) ?>">Email company</a><?php endif; ?>
      </aside>
    </div>
  </div>
</section>
