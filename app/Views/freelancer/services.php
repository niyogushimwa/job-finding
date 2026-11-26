<?php
/** Expected: $sideTitle, $active, $links, $profile, $services, $currencies */
$currentPriceType = (string) old('price_type', 'project');
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <h1>My services</h1>
        <p>Package your work into clear, bookable services that clients can understand at a glance.</p>
      </div>
      <span class="tag tag-blue"><?= (int) count($services) ?> listed</span>
    </div>

    <div class="detail-grid">
      <div>
        <div class="panel">
          <div class="panel-head"><h2>Service catalogue</h2></div>
          <div class="panel-body flush">
            <?php if (!$services): ?>
              <?php $emptyTitle = 'No services yet'; $emptyText = 'Add your first service using the form to describe what you offer and how you charge.'; include BASE_PATH . '/app/Views/partials/empty.php'; ?>
            <?php else: ?>
              <div class="table-wrap">
                <table>
                  <thead><tr><th>Service</th><th>Price</th><th>Type</th><th style="text-align:right">Actions</th></tr></thead>
                  <tbody>
                    <?php foreach ($services as $service): ?>
                      <tr>
                        <td>
                          <strong><?= e($service['title']) ?></strong>
                          <?php if (!empty($service['description'])): ?><br><span class="small muted"><?= e(str_limit(strip_tags((string) $service['description']), 110)) ?></span><?php endif; ?>
                        </td>
                        <td><?= $service['price'] !== null ? e(format_money((float) $service['price'], $profile['currency'], $service['price_type'])) : 'Price on request' ?></td>
                        <td><span class="tag <?= $service['price_type'] === 'hourly' ? 'tag-blue' : '' ?>"><?= e($service['price_type']) ?></span></td>
                        <td style="text-align:right">
                          <form method="post" action="<?= e(url('freelancer/services/' . $service['id'] . '/delete')) ?>" onsubmit="return confirm('Remove this service?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-sm" type="submit" aria-label="Delete <?= e($service['title']) ?>"><?= icon('trash', 16) ?></button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <aside class="side-info">
        <div class="card">
          <h3>Add a service</h3>
          <p class="small muted">Give it a short, searchable title and explain exactly what is included.</p>
          <form method="post" action="<?= e(url('freelancer/services')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="title">Title *</label>
              <input class="form-control" id="title" name="title" minlength="4" maxlength="180" required value="<?= e(old('title', '')) ?>" placeholder="e.g. Logo design package">
            </div>
            <div class="form-group">
              <label class="form-label" for="description">Description</label>
              <textarea class="form-control" id="description" name="description" maxlength="5000" rows="4" placeholder="What will the client receive?"><?= e(old('description', '')) ?></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="price">Price</label>
                <input class="form-control" id="price" name="price" type="number" min="0" step="0.01" value="<?= e(old('price', '')) ?>" placeholder="0.00">
              </div>
              <div class="form-group">
                <label class="form-label" for="price_type">Charged</label>
                <select class="form-control" id="price_type" name="price_type">
                  <option value="project" <?= $currentPriceType === 'project' ? 'selected' : '' ?>>Per project</option>
                  <option value="hourly" <?= $currentPriceType === 'hourly' ? 'selected' : '' ?>>Per hour</option>
                </select>
              </div>
            </div>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('plus', 16) ?> Add service</button>
          </form>
        </div>
        <div class="card">
          <h3>Tip</h3>
          <p class="small muted mb-0">Use your profile currency (<?= e($profile['currency']) ?>) when setting prices so clients see consistent figures.</p>
        </div>
      </aside>
    </div>
  </main>
</div>
