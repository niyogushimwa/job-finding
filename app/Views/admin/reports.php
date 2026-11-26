<?php /** Expects $rows, $pager, $messages, $type, $reason, $status. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/reports';
  $links = [
    ['Overview', 'admin/dashboard', 'chart'],
    ['Users', 'admin/users', 'users'],
    ['Employers', 'admin/employers', 'building'],
    ['Jobs', 'admin/jobs', 'briefcase'],
    ['External opportunities', 'admin/external-opportunities', 'globe'],
    ['Categories', 'admin/categories', 'list'],
    ['Verification', 'admin/verification', 'shield'],
    ['Reports', 'admin/reports', 'flag'],
    ['Content', 'admin/content', 'edit'],
    ['Ads', 'admin/ads', 'star'],
    ['Analytics', 'admin/analytics', 'chart'],
    ['Settings', 'admin/settings', 'settings'],
    ['Audit logs', 'admin/audit-logs', 'clock'],
  ];
  include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php';
  ?>
  <main class="dash-main">
    <div class="dash-head">
      <div><h1>Reports &amp; messages</h1><p>Resolve user reports and read inbound contact messages.</p></div>
      <span class="badge"><?= e($pager->summary()) ?></span>
    </div>

    <form class="filters" method="get" action="<?= e(url('admin/reports')) ?>" style="margin-bottom:1.5rem">
      <div class="filter-row">
        <div class="form-group">
          <label class="form-label" for="report-type">Type</label>
          <select class="form-control" id="report-type" name="type">
            <option value="">All types</option>
            <?php foreach (['job', 'employer', 'external_opportunity', 'user', 'community_post'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $type === $option ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="report-reason">Reason</label>
          <select class="form-control" id="report-reason" name="reason">
            <option value="">All reasons</option>
            <?php foreach (['scam', 'fake_job', 'payment_request', 'misleading_salary', 'inappropriate', 'fraud', 'other'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $reason === $option ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="report-status">Status</label>
          <select class="form-control" id="report-status" name="status">
            <option value="">All statuses</option>
            <?php foreach (['open', 'investigating', 'resolved', 'dismissed'] as $option): ?>
              <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><button class="btn btn-primary" type="submit">Apply filters</button></div>
      </div>
    </form>

    <section class="panel">
      <div class="panel-head"><h2>User reports</h2><a href="<?= e(url('admin/reports')) ?>">Clear filters</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$rows): ?>
          <?php $emptyTitle = 'No reports found'; $emptyText = 'Adjust the filters, or reports will appear here when submitted.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table>
            <thead><tr><th>Report</th><th>Reported by</th><th>Reason</th><th>Status</th><th>Details</th><th>Resolve</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong>#<?= (int) $row['id'] ?></strong><br><small><?= e(str_replace('_', ' ', (string) $row['type'])) ?> · target <?= (int) $row['target_id'] ?></small></td>
                  <td><?= e($row['reporter_name'] ?: 'Guest') ?></td>
                  <td><?= e(str_replace('_', ' ', (string) $row['reason'])) ?></td>
                  <td><span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td>
                    <?= e(str_limit(strip_tags((string) ($row['details'] ?: '')), 90) ?: '—') ?><br>
                    <small><?= e(time_ago($row['created_at'] ?? null)) ?></small>
                    <?php if (!empty($row['resolution'])): ?><br><small><em><?= e(str_limit((string) $row['resolution'], 90)) ?></em></small><?php endif; ?>
                  </td>
                  <td>
                    <details>
                      <summary style="cursor:pointer">Update</summary>
                      <form method="post" action="<?= e(url('admin/reports/' . $row['id'] . '/resolve')) ?>" style="display:grid;gap:.4rem;min-width:210px;margin-top:.5rem">
                        <?= csrf_field() ?>
                        <select class="form-control" name="status" style="min-height:34px;padding:.35rem .5rem" aria-label="Resolution for report <?= (int) $row['id'] ?>">
                          <?php foreach (['investigating', 'resolved', 'dismissed'] as $option): ?>
                            <option value="<?= e($option) ?>" <?= $row['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <textarea class="form-control" name="resolution" maxlength="2000" rows="2" placeholder="Resolution note (optional)"><?= e($row['resolution'] ?? '') ?></textarea>
                        <button class="btn btn-sm btn-outline" type="submit">Save</button>
                      </form>
                    </details>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
          <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Contact messages</h2><span class="badge">Read only</span></div>
      <div class="panel-body panel-body flush">
        <?php if (!$messages): ?>
          <?php $emptyTitle = 'No contact messages'; $emptyText = 'Messages sent through the contact form appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Status</th><th>Received</th></tr></thead><tbody>
            <?php foreach ($messages as $message): ?>
              <tr>
                <td><strong><?= e($message['name']) ?></strong><br><small><?= e($message['email']) ?></small></td>
                <td><?= e($message['subject'] ?: '—') ?></td>
                <td><?= e(str_limit(strip_tags((string) $message['message']), 140)) ?></td>
                <td><span class="status status-<?= e($message['status']) ?>"><?= e($message['status']) ?></span></td>
                <td><?= e(time_ago($message['created_at'] ?? null)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
