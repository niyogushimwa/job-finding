<?php
/** Expected: $sideTitle, $active, $links, $profile, $user, $stats, $recentProposals, $recommended */
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>Welcome back, <?= e(explode(' ', (string) ($user['name'] ?? 'Freelancer'))[0]) ?></h1>
        <p>Here is what is happening across your freelance workspace.</p>
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <a class="btn btn-outline" href="<?= e(url('freelancer/profile')) ?>"><?= icon('user', 16) ?> Edit profile</a>
        <a class="btn btn-primary" href="<?= e(url('freelancer/projects')) ?>"><?= icon('rocket', 16) ?> Browse projects</a>
      </div>
    </div>

    <div class="grid grid-4 mb-2">
      <div class="stat-card"><div class="stat-icon"><?= icon('briefcase', 20) ?></div><strong><?= (int) $stats['proposals'] ?></strong><span>Proposals submitted</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('award', 20) ?></div><strong><?= (int) $stats['accepted'] ?></strong><span>Proposals accepted</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('rocket', 20) ?></div><strong><?= (int) $stats['open_projects'] ?></strong><span>Open projects on the board</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('star', 20) ?></div><strong><?= (int) $stats['services'] ?></strong><span>Services in your catalogue</span></div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2>Your freelancer profile</h2>
        <a class="small" href="<?= e(url('freelancer/profile')) ?>">Manage profile →</a>
      </div>
      <div class="panel-body">
        <div class="d-flex gap-2 flex-wrap align-center">
          <div>
            <h3 class="mb-1"><?= e($profile['title']) ?></h3>
            <p class="small muted mb-0"><?= e(str_limit(strip_tags((string) ($profile['bio'] ?? '')), 220) ?: 'Add a short bio so clients understand the value you bring.') ?></p>
          </div>
          <div class="d-flex gap-1 flex-wrap" style="margin-left:auto">
            <span class="tag tag-blue"><?= e(str_replace('_', ' ', (string) $profile['availability'])) ?></span>
            <?php if (!empty($profile['hourly_rate'])): ?><span class="tag"><?= e(format_money((float) $profile['hourly_rate'], $profile['currency'], 'hour')) ?></span><?php endif; ?>
            <?php if (!empty($profile['project_rate'])): ?><span class="tag"><?= e(format_money((float) $profile['project_rate'], $profile['currency'], 'project')) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2>Recent proposals</h2>
        <a class="small" href="<?= e(url('freelancer/projects')) ?>">View projects board →</a>
      </div>
      <div class="panel-body flush">
        <?php if (!$recentProposals): ?>
          <?php $emptyTitle = 'No proposals yet'; $emptyText = 'Browse the projects board and send your first proposal.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('freelancer/projects')) . '">Find projects</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Project</th><th>Your bid</th><th>Status</th><th>Submitted</th></tr></thead>
              <tbody>
                <?php foreach ($recentProposals as $proposal): ?>
                  <?php
                    $statusClass = match ($proposal['status']) {
                        'accepted', 'shortlisted' => 'status-shortlisted',
                        'rejected' => 'status-rejected',
                        default => 'status-submitted',
                    };
                  ?>
                  <tr>
                    <td><strong><?= e($proposal['project_title']) ?></strong><br><span class="small muted">Project #<?= (int) $proposal['project_id'] ?></span></td>
                    <td><?= $proposal['bid_amount'] !== null ? e(format_money((float) $proposal['bid_amount'], $proposal['currency'], 'project')) : '—' ?></td>
                    <td><span class="status <?= e($statusClass) ?>"><?= e(str_replace('_', ' ', $proposal['status'])) ?></span></td>
                    <td class="small muted"><?= e(time_ago($proposal['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="section-head">
      <div>
        <div class="section-kicker">Recommended for you</div>
        <h2>Fresh opportunities</h2>
        <p>Matched against your profile and skills, updated from the live jobs board.</p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('jobs')) ?>">See all jobs</a>
    </div>
    <?php if (!$recommended): ?>
      <?php $emptyTitle = 'No recommendations yet'; $emptyText = 'Complete your profile and add skills to improve your matches.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('freelancer/profile')) . '">Complete profile</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
    <?php else: ?>
      <div class="grid grid-2">
        <?php foreach ($recommended as $jobCard): ?>
          <?php $job = $jobCard; $showMatch = true; include BASE_PATH . '/app/Views/partials/job_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
