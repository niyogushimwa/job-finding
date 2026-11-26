<?php /** Expects $companies, $externals, $jobs, $certifications. */ ?>
<div class="dashboard">
  <?php
  $sideTitle = 'Administration';
  $active = 'admin/verification';
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
      <div><h1>Verification queue</h1><p>Review companies, external sources and jobs awaiting a decision.</p></div>
      <a class="btn btn-outline btn-sm" href="<?= e(url('admin/reports')) ?>">User reports</a>
    </div>

    <div class="grid grid-4" style="margin-bottom:1.5rem">
      <div class="stat-card"><div class="stat-icon"><?= icon('building', 20) ?></div><strong><?= e(count($companies)) ?></strong><span>Companies pending</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('globe', 20) ?></div><strong><?= e(count($externals)) ?></strong><span>External sources</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('briefcase', 20) ?></div><strong><?= e(count($jobs)) ?></strong><span>Jobs for review</span></div>
      <div class="stat-card"><div class="stat-icon"><?= icon('award', 20) ?></div><strong><?= e(count($certifications)) ?></strong><span>Certifications (info)</span></div>
    </div>

    <section class="panel">
      <div class="panel-head"><h2>Companies awaiting verification</h2><a href="<?= e(url('admin/employers')) ?>">All employers</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$companies): ?>
          <?php $emptyTitle = 'Company queue is clear'; $emptyText = 'No companies are currently unverified or pending.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Company</th><th>Owner</th><th>State</th><th>Submitted</th><th>Decision</th></tr></thead><tbody>
            <?php foreach ($companies as $row): ?>
              <tr>
                <td><a href="<?= e(url('companies/' . $row['id'])) ?>"><strong><?= e($row['name']) ?></strong></a><br><small><?= e($row['location'] ?: 'Location not set') ?></small></td>
                <td><?= e($row['owner_name'] ?: '—') ?><br><small><?= e($row['owner_email'] ?: '') ?></small></td>
                <td><span class="status status-<?= e($row['verification_status']) ?>"><?= e($row['verification_status']) ?></span></td>
                <td><?= e(time_ago($row['created_at'] ?? null)) ?></td>
                <td>
                  <form method="post" action="<?= e(url('admin/employers/' . $row['id'] . '/verify')) ?>" style="display:flex;gap:.4rem">
                    <?= csrf_field() ?>
                    <select class="form-control" name="status" style="min-width:120px;min-height:34px;padding:.35rem .5rem" aria-label="Decision for <?= e($row['name']) ?>">
                      <option value="verified">Verify</option>
                      <option value="rejected">Reject</option>
                      <option value="pending">Keep pending</option>
                    </select>
                    <button class="btn btn-sm btn-outline" type="submit">Apply</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Unverified external opportunities</h2><a href="<?= e(url('admin/external-opportunities')) ?>">All opportunities</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$externals): ?>
          <?php $emptyTitle = 'External queue is clear'; $emptyText = 'Every external opportunity has been reviewed.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Opportunity</th><th>Source URL</th><th>Discovered</th><th>Decision</th></tr></thead><tbody>
            <?php foreach ($externals as $row): ?>
              <tr>
                <td><a href="<?= e(url('opportunity-radar/' . $row['id'])) ?>"><strong><?= e(str_limit((string) $row['title'], 44)) ?></strong></a><br><small><?= e($row['organization']) ?> · <?= e($row['status']) ?></small></td>
                <td><?php if (!empty($row['application_url'])): ?><a href="<?= e($row['application_url']) ?>" target="_blank" rel="noopener noreferrer">Open link</a><?php else: ?>—<?php endif; ?></td>
                <td><?= e(time_ago($row['discovered_at'] ?? null)) ?></td>
                <td>
                  <form method="post" action="<?= e(url('admin/external-opportunities/' . $row['id'] . '/verify')) ?>" style="display:flex;gap:.4rem">
                    <?= csrf_field() ?>
                    <select class="form-control" name="status" style="min-width:115px;min-height:34px;padding:.35rem .5rem" aria-label="Decision for <?= e($row['title']) ?>">
                      <option value="verified">Verify</option>
                      <option value="rejected">Reject</option>
                      <option value="unverified">Keep unverified</option>
                    </select>
                    <button class="btn btn-sm btn-outline" type="submit">Apply</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Jobs pending review</h2><a href="<?= e(url('admin/jobs')) ?>">All jobs</a></div>
      <div class="panel-body panel-body flush">
        <?php if (!$jobs): ?>
          <?php $emptyTitle = 'No jobs awaiting review'; $emptyText = 'New employer submissions will appear here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Job</th><th>Company</th><th>Submitted</th><th>Decision</th></tr></thead><tbody>
            <?php foreach ($jobs as $row): ?>
              <tr>
                <td><a href="<?= e(url('jobs/' . $row['id'])) ?>"><strong><?= e(str_limit((string) $row['title'], 46)) ?></strong></a></td>
                <td><?= e($row['company_name'] ?: '—') ?></td>
                <td><?= e(time_ago($row['created_at'] ?? null)) ?></td>
                <td>
                  <form method="post" action="<?= e(url('admin/jobs/' . $row['id'] . '/moderate')) ?>" style="display:flex;gap:.4rem">
                    <?= csrf_field() ?>
                    <select class="form-control" name="status" style="min-width:135px;min-height:34px;padding:.35rem .5rem" aria-label="Decision for <?= e($row['title']) ?>">
                      <option value="published">Publish</option>
                      <option value="rejected">Reject</option>
                      <option value="draft">Return to draft</option>
                    </select>
                    <button class="btn btn-sm btn-outline" type="submit">Apply</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Certifications awaiting review</h2><span class="badge">Informational only</span></div>
      <div class="panel-body">
        <p style="color:var(--muted);font-size:.85rem;margin-top:0">
          There is no dedicated skill or portfolio verification request table yet. These unverified certifications are shown for context only — use the queues above to take action.
        </p>
        <?php if (!$certifications): ?>
          <?php $emptyTitle = 'No pending certifications'; $emptyText = 'Unverified certification claims will be listed here.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
        <?php else: ?>
          <div class="table-wrap"><table><thead><tr><th>Certification</th><th>Member</th><th>Issuer</th><th>Added</th></tr></thead><tbody>
            <?php foreach ($certifications as $row): ?>
              <tr>
                <td><strong><?= e($row['name']) ?></strong></td>
                <td><?= e($row['user_name']) ?><br><small><?= e($row['user_email']) ?></small></td>
                <td><?= e($row['issuer'] ?: '—') ?></td>
                <td><?= e(time_ago($row['created_at'] ?? null)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>
