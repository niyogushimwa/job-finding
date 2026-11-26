<?php
/** Expected: $sideTitle, $active, $links, $profile, $projects, $proposals, $filters, $categories */
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>Projects board</h1>
        <p>Browse open freelance projects and send a tailored proposal.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('freelancer/dashboard')) ?>"><?= icon('chart', 16) ?> My overview</a>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Filter projects</h2><a class="small" href="<?= e(url('freelancer/projects')) ?>">Clear filters</a></div>
      <div class="panel-body">
        <form class="filters" method="get" action="<?= e(url('freelancer/projects')) ?>">
          <div class="filter-row">
            <div class="form-group">
              <label class="form-label" for="q">Keyword</label>
              <input class="form-control" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Title or description">
            </div>
            <div class="form-group">
              <label class="form-label" for="category_id">Category</label>
              <select class="form-control" id="category_id" name="category_id">
                <option value="0">All categories</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= (int) $category['id'] ?>" <?= $filters['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="min_budget">Min budget</label>
              <input class="form-control" id="min_budget" name="min_budget" type="number" min="0" step="0.01" value="<?= e($filters['min_budget']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="max_budget">Max budget</label>
              <input class="form-control" id="max_budget" name="max_budget" type="number" min="0" step="0.01" value="<?= e($filters['max_budget']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="skills">Skill</label>
              <input class="form-control" id="skills" name="skills" value="<?= e($filters['skills']) ?>" placeholder="e.g. PHP">
            </div>
            <div class="form-group">
              <button class="btn btn-primary btn-block" type="submit"><?= icon('filter', 16) ?> Apply filters</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if (!$projects): ?>
      <?php $emptyTitle = 'No open projects found'; $emptyText = 'Try broadening your filters, or check back soon — new projects are posted regularly.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
    <?php else: ?>
      <p class="small muted mb-2">Showing <?= (int) count($projects) ?> open project<?= count($projects) === 1 ? '' : 's' ?>.</p>
      <div class="grid grid-2">
        <?php foreach ($projects as $project): ?>
          <?php
            $projectId = (int) $project['id'];
            $proposalStatus = $proposals[$projectId] ?? null;
            $statusClass = $proposalStatus === null ? '' : match ($proposalStatus) {
                'accepted', 'shortlisted' => 'status-shortlisted',
                'rejected' => 'status-rejected',
                default => 'status-submitted',
            };
          ?>
          <article class="card card-hover">
            <div class="d-flex justify-between gap-1 flex-wrap">
              <div style="min-width:0">
                <h3 class="mb-1"><?= e($project['title']) ?></h3>
                <span class="small muted">Posted by <?= e($project['client_name'] ?? 'Client') ?> · <?= e(time_ago($project['created_at'])) ?></span>
              </div>
              <span class="status status-open">Open</span>
            </div>
            <div class="job-meta mt-2">
              <span><?= icon('briefcase', 16) ?> Budget: <?= e(format_money($project['budget'] !== null ? (float) $project['budget'] : null, $project['currency'], 'project')) ?></span>
              <span><?= icon('clock', 16) ?> Deadline: <?= e(format_date($project['deadline'])) ?></span>
            </div>
            <div class="d-flex flex-wrap gap-1 mt-2">
              <?php if (!empty($project['category_name'])): ?><span class="tag tag-blue"><?= e($project['category_name']) ?></span><?php endif; ?>
              <?php if (!empty($project['skills_required'])): ?><span class="tag"><?= e($project['skills_required']) ?></span><?php endif; ?>
              <?php if ($proposalStatus !== null): ?><span class="status <?= e($statusClass) ?>">Proposal <?= e(str_replace('_', ' ', $proposalStatus)) ?></span><?php endif; ?>
            </div>
            <p class="small muted mb-0 mt-2"><?= e(str_limit(strip_tags((string) ($project['description'] ?? '')), 180) ?: 'Open the details to read the full brief.') ?></p>

            <?php if ($proposalStatus === null): ?>
              <details class="mt-2">
                <summary class="small" style="cursor:pointer;font-weight:700;color:var(--blue)">View details & submit a proposal</summary>
                <?php if (!empty($project['description'])): ?>
                  <p class="small muted mt-2"><?= nl2br(e($project['description'])) ?></p>
                <?php endif; ?>
                <form method="post" action="<?= e(url('freelancer/projects/' . $projectId . '/apply')) ?>" class="mt-2">
                  <?= csrf_field() ?>
                  <input type="hidden" name="project_id" value="<?= (int) $projectId ?>">
                  <div class="form-group">
                    <label class="form-label" for="cover_letter_<?= (int) $projectId ?>">Cover letter *</label>
                    <textarea class="form-control" id="cover_letter_<?= (int) $projectId ?>" name="cover_letter" minlength="20" maxlength="5000" rows="4" required placeholder="Explain how you would approach this project and why you are a good fit."><?= e(old('cover_letter', '')) ?></textarea>
                    <span class="form-hint">At least 20 characters. Tailor it to this brief.</span>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="bid_amount_<?= (int) $projectId ?>">Your bid (<?= e($project['currency']) ?>)</label>
                    <input class="form-control" id="bid_amount_<?= (int) $projectId ?>" name="bid_amount" type="number" min="0" step="0.01" value="<?= e(old('bid_amount', '')) ?>" placeholder="0.00">
                  </div>
                  <button class="btn btn-primary btn-block" type="submit"><?= icon('rocket', 16) ?> Send proposal</button>
                </form>
              </details>
            <?php else: ?>
              <div class="alert-success small mt-2" style="color:var(--green);font-weight:700">You have already applied for this project.</div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
