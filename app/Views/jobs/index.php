<?php use App\Core\Auth; ?>
<section class="section-tight" style="background:linear-gradient(125deg,var(--navy),var(--navy-3));color:#fff">
  <div class="container">
    <span class="eyebrow">Opportunity explorer</span>
    <h1 style="color:#fff">Find work that fits your life.</h1>
    <p class="mb-0" style="color:#c9d8ed">Search by keyword, location, category and much more. Every result is a real, moderated opportunity.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <form class="filters mb-3" id="job-filter-form" method="get" action="<?= e(url('jobs')) ?>">
      <div class="filter-row">
        <div class="form-group"><label class="form-label">Keywords</label><input class="form-control" name="q" data-suggestions value="<?= e($filters['q']) ?>" placeholder="e.g. PHP, nurse, marketing"><div id="suggestion-box" class="notif-panel hide" style="top:70px;width:100%"></div></div>
        <div class="form-group"><label class="form-label">Location</label><input class="form-control" name="location" value="<?= e($filters['location']) ?>" placeholder="Kigali or Remote"></div>
        <div class="form-group"><label class="form-label">Category</label><select class="form-control" name="category"><option value="">All categories</option><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (string) $filters['category'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Employment type</label><select class="form-control" name="employment_type"><option value="">Any type</option><?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Contract', 'remote' => 'Remote', 'freelance' => 'Freelance'] as $v => $l): ?><option value="<?= $v ?>" <?= $filters['employment_type'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Work mode</label><select class="form-control" name="work_mode"><option value="">Any</option><option value="onsite" <?= $filters['work_mode'] === 'onsite' ? 'selected' : '' ?>>On-site</option><option value="hybrid" <?= $filters['work_mode'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option><option value="remote" <?= $filters['work_mode'] === 'remote' ? 'selected' : '' ?>>Remote</option></select></div>
        <div class="form-group"><label class="form-label">Experience</label><select class="form-control" name="experience_level"><option value="">Any level</option><?php foreach (['entry' => 'Entry level', 'junior' => 'Junior', 'mid' => 'Mid-level', 'senior' => 'Senior', 'executive' => 'Executive'] as $v => $l): ?><option value="<?= $v ?>" <?= $filters['experience_level'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Min salary (RWF)</label><input class="form-control" type="number" min="0" name="salary_min" value="<?= e($filters['salary_min']) ?>"></div>
        <div class="form-group"><label class="form-label">Published within</label><select class="form-control" name="posted_within"><option value="">Any time</option><option value="7" <?= $filters['posted_within'] === '7' ? 'selected' : '' ?>>Last 7 days</option><option value="30" <?= $filters['posted_within'] === '30' ? 'selected' : '' ?>>Last 30 days</option></select></div>
      </div>
      <div class="filter-row mt-2">
        <div class="form-group"><label class="form-label">Country</label><select class="form-control" name="country"><option value="">All countries</option><?php foreach ($countries as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (string) $filters['country'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Industry</label><select class="form-control" name="industry"><option value="">All industries</option><?php foreach ($industries as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (string) $filters['industry'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Education</label><input class="form-control" name="education_level" value="<?= e($filters['education_level']) ?>" placeholder="Bachelor, Diploma…"></div>
        <div class="form-group"><label class="form-label">Deadline before</label><input class="form-control" type="date" name="deadline_before" value="<?= e($filters['deadline_before']) ?>"></div>
        <div class="form-group"><label class="form-label">Sort by</label><select class="form-control" name="sort"><option value="latest" <?= $filters['sort'] === 'latest' ? 'selected' : '' ?>>Newest</option><option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest</option><option value="salary" <?= $filters['sort'] === 'salary' ? 'selected' : '' ?>>Highest salary</option><option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Most viewed</option><option value="deadline" <?= $filters['sort'] === 'deadline' ? 'selected' : '' ?>>Deadline soonest</option></select></div>
        <div class="form-group d-flex align-end gap-1"><button class="btn btn-primary" type="submit"><?= icon('filter', 16) ?> Apply</button><a class="btn btn-outline" href="<?= e(url('jobs')) ?>">Clear</a></div>
      </div>
    </form>

    <div class="search-layout">
      <div id="job-results" data-url="<?= e(url('api/jobs')) ?>">
        <?php $basePath = ''; include BASE_PATH . '/app/Views/jobs/results.php'; ?>
      </div>
      <aside>
        <?php if (Auth::id()): ?>
          <div class="panel"><div class="panel-head"><h3>Save this search</h3></div><div class="panel-body">
            <form method="post" action="<?= e(url('api/saved-searches')) ?>"><?= csrf_field() ?>
              <input type="hidden" name="query_params" value="<?= e(http_build_query(array_filter($filters))) ?>">
              <div class="form-group"><input class="form-control" name="name" placeholder="e.g. PHP jobs in Kigali" required></div>
              <button class="btn btn-outline btn-sm btn-block" type="submit">Save search</button>
            </form>
            <?php if ($savedSearches): ?><div class="mt-2"><span class="small muted">Your saved searches:</span><?php foreach ($savedSearches as $s): ?><a class="small d-block" href="<?= e(url('jobs?' . $s['query_params'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div><?php endif; ?>
          </div></div>
        <?php endif; ?>
        <?php if ($recent): ?>
          <div class="panel"><div class="panel-head"><h3>Recently viewed</h3></div><div class="panel-body">
            <?php foreach ($recent as $r): ?><a class="small d-block mb-1" href="<?= e(url('jobs/' . $r['id'] . '/' . $r['slug'])) ?>"><?= e(str_limit($r['title'], 55)) ?></a><?php endforeach; ?>
          </div></div>
        <?php endif; ?>
        <div class="panel"><div class="panel-body">
          <h3>Opportunity Radar</h3>
          <p class="small muted">Internships, scholarships, remote roles and more — all in one place.</p>
          <a class="btn btn-outline btn-sm btn-block" href="<?= e(url('opportunity-radar')) ?>">Open Radar</a>
        </div></div>
      </aside>
    </div>
  </div>
</section>
