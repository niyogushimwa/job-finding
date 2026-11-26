<?php use App\Core\Auth; use App\Models\Application; ?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">Track your progress</span>
        <h1>My applications</h1>
        <p>Every role you have applied for, with its latest status and full history.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(url('recommended-jobs')) ?>"><?= icon('star', 16) ?> Find more matches</a>
    </div>

    <nav class="tabs">
      <a class="tab <?= $activeStatus === '' ? 'active' : '' ?>" href="<?= e(url('applications')) ?>">All (<?= count(Application::forSeeker((int) Auth::id())) ?>)</a>
      <?php foreach ($statuses as $key => $label): ?>
        <?php
        $count = count(Application::forSeeker((int) Auth::id(), (string) $key));
        if ($count === 0 && $activeStatus !== (string) $key) continue;
        ?>
        <a class="tab <?= $activeStatus === (string) $key ? 'active' : '' ?>" href="<?= e(url('applications?status=' . $key)) ?>"><?= e($label) ?> (<?= $count ?>)</a>
      <?php endforeach; ?>
    </nav>

    <?php if ($detail): ?>
      <section class="panel">
        <div class="panel-head">
          <div><h3><?= e($detail['title']) ?> · <?= e($detail['company_name'] ?? 'Employer') ?></h3><span class="small muted">Applied <?= e(format_date($detail['created_at'])) ?></span></div>
          <a class="small" href="<?= e(url('applications')) ?>">Close details</a>
        </div>
        <div class="panel-body">
          <div class="grid grid-2">
            <div>
              <p class="small muted"><strong>Status:</strong> <span class="status status-<?= e($detail['status']) ?>"><?= e($statuses[$detail['status']] ?? $detail['status']) ?></span></p>
              <?php if (!empty($detail['match_score'])): ?><p class="small muted"><strong>Match score:</strong> <?= (int) $detail['match_score'] ?>%</p><?php endif; ?>
              <?php if (!empty($detail['cover_letter'])): ?><p class="small" style="white-space:pre-line"><?= e($detail['cover_letter']) ?></p><?php endif; ?>
              <?php if (in_array($detail['status'], $withdrawable, true)): ?>
                <form method="post" action="<?= e(url('applications/' . $detail['id'] . '/withdraw')) ?>" onsubmit="return confirm('Withdraw this application? This cannot be undone.')">
                  <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit"><?= icon('close', 14) ?> Withdraw application</button>
                </form>
              <?php else: ?>
                <p class="small muted">This application can no longer be withdrawn.</p>
              <?php endif; ?>
            </div>
            <div>
              <strong class="small">Status history</strong>
              <?php if ($history): ?>
                <ul class="timeline mt-2">
                  <?php foreach ($history as $event): ?>
                    <li><strong><?= e($statuses[$event['to_status']] ?? $event['to_status']) ?></strong><time><?= e(format_date($event['created_at'], 'M j, Y · H:i')) ?> · <?= e($event['changed_by_name'] ?? 'System') ?></time><p class="small muted mb-0"><?= e($event['note'] ?? '') ?></p></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?><p class="small muted mt-2">No history recorded yet.</p><?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <section class="panel">
      <div class="panel-head"><h3>All applications</h3><span class="small muted"><?= count($applications) ?> result<?= count($applications) === 1 ? '' : 's' ?></span></div>
      <div class="panel-body flush">
        <?php if ($applications): ?>
          <div class="table-wrap"><table><thead><tr><th>Opportunity</th><th>Status</th><th>Match</th><th>Applied</th><th></th></tr></thead><tbody>
            <?php foreach ($applications as $app): ?>
              <tr>
                <td>
                  <a href="<?= e(url('jobs/' . $app['job_id'] . '/' . $app['slug'])) ?>"><?= e(str_limit($app['title'], 55)) ?></a>
                  <br><span class="small muted"><?= e($app['company_name'] ?? 'Employer') ?> · <?= e($app['location']) ?></span>
                </td>
                <td><span class="status status-<?= e($app['status']) ?>"><?= e($statuses[$app['status']] ?? $app['status']) ?></span></td>
                <td><?= !empty($app['match_score']) ? (int) $app['match_score'] . '%' : '—' ?></td>
                <td class="small muted"><?= e(format_date($app['created_at'])) ?></td>
                <td><a class="btn btn-sm btn-outline" href="<?= e(url('applications?view=' . $app['id']) . ($activeStatus !== '' ? '&status=' . $activeStatus : '')) ?>">Details</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php else: ?>
          <?php $emptyTitle = 'No applications here'; $emptyText = 'Browse recommended roles and send your first application.'; $emptyAction = '<a class="btn btn-primary btn-sm" href="' . e(url('recommended-jobs')) . '">See recommendations</a>'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
