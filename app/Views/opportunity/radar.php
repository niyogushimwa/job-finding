<?php
/** Opportunity Radar and its focused listing pages. Expects rows, pager, filters, categories, countries, ads, stats. */
$pageHeading = $pageHeading ?? 'Opportunity Radar';
$pageIntro = $pageIntro ?? 'Internships, scholarships, remote roles and more from trusted external sources.';
$activeFilter = $activeFilter ?? '';
$hiddenFilters = $hiddenFilters ?? [];
$filters += [
    'q' => '', 'category' => '', 'country' => '', 'qualification' => '',
    'deadline_before' => '', 'deadline_after' => '', 'verified' => '', 'featured' => '',
];
$quickLinks = [
    ['internships', 'Internships', 'internships'],
    ['remote', 'Remote roles', 'remote-jobs'],
    ['freelance', 'Freelance', 'freelance'],
    ['scholarships', 'Scholarships', 'scholarships'],
];
?>
<section class="section-tight" style="background:linear-gradient(125deg,var(--navy),var(--navy-3));color:#fff">
  <div class="container">
    <span class="eyebrow">Opportunity Radar</span>
    <h1 style="color:#fff"><?= e($pageHeading) ?></h1>
    <p class="mb-0" style="color:#c9d8ed"><?= e($pageIntro) ?></p>
    <div class="d-flex flex-wrap gap-1 mt-3">
      <?php foreach ($quickLinks as [$key, $label, $path]): ?>
        <a class="btn btn-sm <?= $activeFilter === $key ? 'btn-gold' : 'btn-outline' ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <form class="filters mb-3" method="get" action="<?= e(url('opportunity-radar')) ?>" role="search">
      <?php foreach ($hiddenFilters as $hiddenKey => $hiddenValue): ?>
        <input type="hidden" name="<?= e($hiddenKey) ?>" value="<?= e($hiddenValue) ?>">
      <?php endforeach; ?>
      <div class="filter-row">
        <div class="form-group"><label class="form-label">Keywords</label><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="e.g. scholarship, design, nursing"></div>
        <div class="form-group"><label class="form-label">Category</label><select class="form-control" name="category">
          <option value="">All categories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>" <?= (string) $filters['category'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label class="form-label">Country</label><select class="form-control" name="country">
          <option value="">All countries</option>
          <?php foreach ($countries as $country): ?>
            <option value="<?= (int) $country['id'] ?>" <?= (string) $filters['country'] === (string) $country['id'] ? 'selected' : '' ?>><?= e($country['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label class="form-label">Qualification or requirement</label><input class="form-control" name="qualification" value="<?= e($filters['qualification']) ?>" placeholder="e.g. Bachelor, certificate"></div>
      </div>
      <div class="filter-row mt-2">
        <div class="form-group"><label class="form-label">Deadline after</label><input class="form-control" type="date" name="deadline_after" value="<?= e($filters['deadline_after']) ?>"></div>
        <div class="form-group"><label class="form-label">Deadline before</label><input class="form-control" type="date" name="deadline_before" value="<?= e($filters['deadline_before']) ?>"></div>
        <div class="form-group d-flex align-end gap-2">
          <label class="small"><input type="checkbox" name="verified" value="1" <?= !empty($filters['verified']) ? 'checked' : '' ?>> Verified only</label>
          <label class="small"><input type="checkbox" name="featured" value="1" <?= !empty($filters['featured']) ? 'checked' : '' ?>> Featured only</label>
        </div>
        <div class="form-group d-flex align-end gap-1">
          <button class="btn btn-primary" type="submit"><?= icon('filter', 16) ?> Apply filters</button>
          <a class="btn btn-outline" href="<?= e(url('opportunity-radar')) ?>">Clear</a>
        </div>
      </div>
    </form>

    <div class="search-layout">
      <div>
        <p class="small muted mb-2"><strong><?= (int) $pager->total ?></strong> opportunities found · <?= e($pager->summary()) ?></p>
        <?php if ($rows): ?>
          <div class="grid grid-2">
            <?php foreach ($rows as $opportunity): ?>
              <?php include BASE_PATH . '/app/Views/partials/opportunity_card.php'; ?>
            <?php endforeach; ?>
          </div>
          <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
        <?php else: ?>
          <?php
          $emptyTitle = 'No opportunities match these filters';
          $emptyText = 'Try a broader keyword, clear a filter or check back soon — new listings are added regularly.';
          $emptyAction = '<a class="btn btn-outline btn-sm" href="' . e(url('opportunity-radar')) . '">See all opportunities</a>';
          include BASE_PATH . '/app/Views/partials/empty.php';
          ?>
        <?php endif; ?>
      </div>

      <aside>
        <div class="panel">
          <div class="panel-head"><h3>Radar snapshot</h3></div>
          <div class="panel-body">
            <div class="side-info">
              <div class="row"><span>Published</span><strong><?= (int) ($stats['published'] ?? 0) ?></strong></div>
              <div class="row"><span>Verified</span><strong><?= (int) ($stats['verified'] ?? 0) ?></strong></div>
              <div class="row"><span>Closing in 30 days</span><strong><?= (int) ($stats['closing_soon'] ?? 0) ?></strong></div>
            </div>
            <h4 class="mt-2">By category</h4>
            <?php if (!empty($stats['by_category'])): ?>
              <div class="d-flex flex-wrap gap-1">
                <?php foreach ($stats['by_category'] as $stat): ?>
                  <a class="tag" href="<?= e(url('opportunity-radar?category=' . (int) $stat['id'])) ?>"><?= e($stat['name']) ?> · <?= (int) $stat['total'] ?></a>
                <?php endforeach; ?>
              </div>
            <?php else: ?><p class="small muted mb-0">Category counts will appear once opportunities are published.</p><?php endif; ?>
            <?php if (!empty($stats['by_status'])): ?>
              <div class="d-flex flex-wrap gap-1 mt-2">
                <?php foreach ($stats['by_status'] as $stat): ?>
                  <span class="status status-<?= e($stat['status']) ?>"><?= e(str_replace('_', ' ', $stat['status'])) ?> · <?= (int) $stat['total'] ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php foreach ($ads as $ad): ?>
          <?php
          $adLink = trim((string) ($ad['link'] ?? ''));
          $adHref = $adLink !== '' ? url(ltrim($adLink, '/')) : '';
          ?>
          <div class="panel">
            <div class="panel-body">
              <span class="tag tag-gold">Sponsored</span>
              <h3 class="mt-1"><?= e($ad['title']) ?></h3>
              <?php if ($ad['description']): ?><p class="small muted"><?= e($ad['description']) ?></p><?php endif; ?>
              <?php if ($adHref !== ''): ?><a class="btn btn-outline btn-sm btn-block" href="<?= e($adHref) ?>">Learn more <?= icon('arrow', 14) ?></a><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="panel">
          <div class="panel-body">
            <h3>Keep growing</h3>
            <p class="small muted">Prepare while you apply: build your CV, practise interviews and learn from others.</p>
            <div class="d-grid" style="display:grid;gap:.5rem">
              <a class="btn btn-outline btn-sm btn-block" href="<?= e(url('career-centre')) ?>">Career Growth Centre</a>
              <a class="btn btn-outline btn-sm btn-block" href="<?= e(url('interview-prep')) ?>">Interview preparation</a>
              <a class="btn btn-outline btn-sm btn-block" href="<?= e(url('community')) ?>">Join the community</a>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
