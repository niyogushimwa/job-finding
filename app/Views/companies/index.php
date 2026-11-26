<?php use App\Core\Upload; ?>
<section class="section-tight" style="background:var(--surface);border-bottom:1px solid var(--border)">
  <div class="container d-flex justify-between align-center flex-wrap gap-2">
    <div><div class="section-kicker">Directory</div><h1 style="margin:0">Browse companies</h1><p class="muted mb-0">Explore organisations hiring in Rwanda and beyond.</p></div>
    <form class="d-flex gap-1" method="get" action="<?= e(url('companies')) ?>"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search companies"><button class="btn btn-primary">Search</button></form>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if ($companies): ?>
      <div class="grid grid-4">
        <?php foreach ($companies as $company): ?>
          <a class="card card-hover company-card" href="<?= e(url('companies/' . $company['id'] . '/' . $company['slug'])) ?>">
            <span class="company-logo"><?= !empty($company['logo']) ? '<img src="' . e(Upload::url($company['logo'])) . '" alt="">' : e(mb_substr($company['name'], 0, 1)) ?></span>
            <strong><?= e($company['name']) ?></strong>
            <span class="small muted"><?= e($company['industry_name'] ?? 'Various industries') ?></span>
            <span class="small muted"><?= (int) $company['jobs_count'] ?> open opportunities · <?= e($company['location'] ?? '—') ?></span>
            <div class="d-flex gap-1 flex-wrap">
              <?php if ($company['verification_status'] === 'verified'): ?><span class="tag tag-green"><?= icon('shield', 13) ?> Verified</span><?php endif; ?>
              <?php if ($company['featured']): ?><span class="tag tag-gold">Spotlight</span><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>
