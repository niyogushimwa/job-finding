<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Job seeker workspace</span>
        <h1>Welcome back, <?= e($profile['user']['name'] ?? 'there') ?></h1>
        <p>Here is what is happening with your job search today.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('jobs')) ?>"><?= icon('search', 17) ?> Find jobs</a>
    </div>

    <div class="grid grid-4 mb-3">
      <div class="stat-card"><div class="stat-icon"><?= icon('briefcase', 20) ?></div><strong><?= (int) ($stats['applications'] ?? 0) ?></strong><span>Applications sent</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('bookmark', 20) ?></div><strong><?= (int) $savedCount ?></strong><span>Saved jobs</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('award', 20) ?></div><strong><?= (int) ($stats['skills'] ?? 0) ?></strong><span>Skills listed</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('chart', 20) ?></div><strong><?= (int) $completion ?>%</strong><span>Profile complete</span></div>
    </div>

    <div class="mb-3">
      <div class="d-flex justify-between small mb-1"><span>Profile strength</span><strong><?= (int) $completion ?>%</strong></div>
      <div class="progress"><span style="width:<?= min(100, (int) $completion) ?>%"></span></div>
      <?php if ((int) $completion < 100): ?><p class="small muted mt-1">A complete profile gets better matches. <a href="<?= e(url('profile/edit')) ?>">Finish your profile →</a></p><?php endif; ?>
    </div>

    <div class="grid grid-2">
      <section class="panel">
        <div class="panel-head"><h3>Recent applications</h3><a class="small" href="<?= e(url('applications')) ?>">View all →</a></div>
        <div class="panel-body flush">
          <?php if ($applications): ?>
            <div class="table-wrap"><table><thead><tr><th>Opportunity</th><th>Status</th><th>Applied</th></tr></thead><tbody>
              <?php foreach ($applications as $app): ?>
                <tr>
                  <td><a href="<?= e(url('jobs/' . $app['job_id'] . '/' . $app['slug'])) ?>"><?= e(str_limit($app['title'], 48)) ?></a><br><span class="small muted"><?= e($app['company_name'] ?? 'Employer') ?></span></td>
                  <td><span class="status status-<?= e($app['status']) ?>"><?= e(config('application_statuses.' . $app['status'], $app['status'])) ?></span></td>
                  <td class="small muted"><?= e(time_ago($app['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php else: ?>
            <?php $emptyTitle = 'No applications yet'; $emptyText = 'When you apply for a role, you can track every step here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h3>Upcoming interviews</h3><a class="small" href="<?= e(url('applications')) ?>">Applications →</a></div>
        <div class="panel-body">
          <?php if ($interviews): ?>
            <ul class="timeline">
              <?php foreach ($interviews as $interview): ?>
                <li>
                  <strong><?= e($interview['job_title']) ?></strong>
                  <time><?= e(format_date($interview['scheduled_at'], 'M j, Y · H:i')) ?> · <?= (int) $interview['duration_minutes'] ?> min</time>
                  <p class="small muted mb-0"><?= e($interview['location'] ?: ($interview['meeting_link'] ? 'Online meeting' : 'Details to be confirmed')) ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="muted mb-0">No interviews scheduled right now. Keep applying and improving your profile.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h3>Recommended for you</h3><a class="small" href="<?= e(url('recommended-jobs')) ?>">See all matches →</a></div>
      <div class="panel-body">
        <?php if ($recommended): ?>
          <div class="grid grid-2">
            <?php foreach ($recommended as $job): include BASE_PATH . '/app/Views/partials/job_card.php'; endforeach; ?>
          </div>
        <?php else: ?>
          <?php $emptyTitle = 'No matches yet'; $emptyText = 'Add skills and preferences to your profile to unlock tailored recommendations.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h3>Latest notifications</h3><a class="small" href="<?= e(url('notifications')) ?>">All notifications →</a></div>
      <div class="panel-body">
        <?php if ($notifications): ?>
          <?php foreach ($notifications as $notification): ?>
            <a class="thread-item" href="<?= e($notification['href']) ?>">
              <strong><?= icon('bell', 14) ?> <?= e($notification['title']) ?> <?= empty($notification['is_read']) ? '<span class="tag tag-blue">New</span>' : '' ?></strong>
              <span><?= e(str_limit($notification['body'], 110)) ?> · <?= e(time_ago($notification['created_at'])) ?></span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="muted mb-0">You are all caught up. New updates will appear here.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
