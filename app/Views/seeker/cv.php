<?php
$fields = [
    'experience' => [
        ['company', 'Company'], ['title', 'Job title'], ['start_date', 'Start date'], ['end_date', 'End date'], ['description', 'What you did'],
    ],
    'education' => [
        ['school', 'School'], ['degree', 'Degree'], ['field_of_study', 'Field of study'], ['start_year', 'Start year'], ['end_year', 'End year'], ['grade', 'Grade'],
    ],
    'skills' => [['name', 'Skill'], ['level', 'Level']],
    'projects' => [['title', 'Project'], ['description', 'Description'], ['url', 'Link'], ['tags', 'Tags']],
    'languages' => [['name', 'Language'], ['level', 'Level']],
];
$labels = [
    'experience' => 'Work experience', 'education' => 'Education', 'skills' => 'Skills',
    'projects' => 'Projects', 'languages' => 'Languages',
];
$json = json_encode($cv, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<div class="dashboard">
  <?php include BASE_PATH . '/app/Views/partials/dashboard_sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <span class="eyebrow">CV builder</span>
        <h1>My CV</h1>
        <p>Edit on the left, preview on the right. Save, then download a print-ready copy.</p>
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <a class="btn btn-outline" href="<?= e(url('my-cv/download')) ?>"><?= icon('download', 16) ?> Download</a>
        <button class="btn btn-primary" type="submit" form="cv-form"><?= icon('check', 16) ?> Save CV</button>
      </div>
    </div>

    <div class="cv-builder">
      <form id="cv-form" method="post" action="<?= e(url('my-cv/save')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="data" id="cv-data" value="<?= e($json) ?>">
        <section class="panel">
          <div class="panel-head"><h3>Document</h3></div>
          <div class="panel-body">
            <div class="form-row">
              <div class="form-group"><label class="form-label" for="cv-title">CV title</label><input class="form-control" id="cv-title" name="title" maxlength="150" value="<?= e($cvTitle) ?>"></div>
              <div class="form-group"><label class="form-label" for="cv-template">Template</label><select class="form-control" id="cv-template" name="template"><option value="modern" <?= $template === 'modern' ? 'selected' : '' ?>>Modern</option><option value="classic" <?= $template === 'classic' ? 'selected' : '' ?>>Classic</option><option value="creative" <?= $template === 'creative' ? 'selected' : '' ?>>Creative</option></select></div>
            </div>
            <div class="form-group"><label class="form-label" for="cv-headline">Headline</label><input class="form-control" id="cv-headline" name="headline" maxlength="200" value="<?= e($cv['headline'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Contact</label>
              <div class="form-row">
                <?php foreach (['name' => 'Full name', 'email' => 'Email', 'phone' => 'Phone', 'location' => 'Location', 'linkedin' => 'LinkedIn', 'github' => 'GitHub', 'website' => 'Website'] as $key => $label): ?>
                  <div class="form-group"><input class="form-control" data-contact="<?= $key ?>" name="contact[<?= $key ?>]" placeholder="<?= e($label) ?>" value="<?= e($cv['contact'][$key] ?? '') ?>"></div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="form-group"><label class="form-label" for="cv-summary">Profile summary</label><textarea class="form-control" id="cv-summary" name="summary" rows="5"><?= e($cv['summary'] ?? '') ?></textarea></div>
          </div>
        </section>

        <?php foreach ($fields as $section => $definition): ?>
          <section class="panel" data-repeater>
            <div class="panel-head">
              <h3><?= e($labels[$section]) ?></h3>
              <button class="btn btn-outline btn-sm" type="button" data-repeater-add><?= icon('plus', 14) ?> Add</button>
            </div>
            <div class="panel-body">
              <div data-repeater-items>
                <?php foreach ($cv[$section] ?? [] as $item): ?>
                  <div class="repeater-item" data-section="<?= e($section) ?>">
                    <?php foreach ($definition as [$field, $placeholder]): ?>
                      <div class="form-group"><label class="form-label small"><?= e($placeholder) ?></label>
                        <?php if ($field === 'description'): ?>
                          <textarea class="form-control" data-field="<?= e($field) ?>" rows="3" placeholder="<?= e($placeholder) ?>"><?= e($item[$field] ?? '') ?></textarea>
                        <?php else: ?>
                          <input class="form-control" data-field="<?= e($field) ?>" value="<?= e($item[$field] ?? '') ?>" placeholder="<?= e($placeholder) ?>">
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                    <button class="btn btn-danger btn-sm" type="button" data-repeater-remove><?= icon('trash', 14) ?> Remove</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <template>
                <div class="repeater-item" data-section="<?= e($section) ?>">
                  <?php foreach ($definition as [$field, $placeholder]): ?>
                    <div class="form-group"><label class="form-label small"><?= e($placeholder) ?></label>
                      <?php if ($field === 'description'): ?>
                        <textarea class="form-control" data-field="<?= e($field) ?>" rows="3" placeholder="<?= e($placeholder) ?>"></textarea>
                      <?php else: ?>
                        <input class="form-control" data-field="<?= e($field) ?>" placeholder="<?= e($placeholder) ?>">
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                  <button class="btn btn-danger btn-sm" type="button" data-repeater-remove><?= icon('trash', 14) ?> Remove</button>
                </div>
              </template>
            </div>
          </section>
        <?php endforeach; ?>
      </form>

      <aside>
        <div class="panel">
          <div class="panel-head"><h3>Live preview</h3><span class="small muted">Updates as you type</span></div>
          <div class="panel-body" id="cv-preview"><?php include BASE_PATH . '/app/Views/partials/cv_paper.php'; ?></div>
        </div>
      </aside>
    </div>

    <section class="panel">
      <div class="panel-head"><h3>Uploaded resumes (PDF)</h3></div>
      <div class="panel-body">
        <form method="post" action="<?= e(url('resumes')) ?>" enctype="multipart/form-data" class="form-row align-end">
          <?= csrf_field() ?>
          <div class="form-group"><label class="form-label" for="resume-title">Resume title</label><input class="form-control" id="resume-title" name="title" maxlength="150" placeholder="e.g. CV - Frontend developer"></div>
          <div class="form-group"><label class="form-label" for="resume-file">PDF file</label><input class="form-control" id="resume-file" type="file" name="resume" accept="application/pdf" required></div>
          <div class="form-group"><button class="btn btn-outline" type="submit"><?= icon('upload', 16) ?> Upload resume</button></div>
        </form>
        <?php if ($resumes): ?>
          <div class="table-wrap"><table><thead><tr><th>Title</th><th>File</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($resumes as $resume): ?>
              <tr>
                <td><?= e($resume['title']) ?></td>
                <td class="small muted"><?= e($resume['original_name'] ?: basename((string) $resume['file_path'])) ?></td>
                <td><?= !empty($resume['is_default']) ? '<span class="tag tag-green">Default</span>' : '<span class="tag">Backup</span>' ?></td>
                <td class="d-flex gap-1">
                  <a class="btn btn-sm btn-outline" href="<?= e(url('resumes/' . $resume['id'] . '/download')) ?>"><?= icon('eye', 14) ?> Open</a>
                  <?php if (empty($resume['is_default'])): ?>
                    <form method="post" action="<?= e(url('resumes/' . $resume['id'] . '/default')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" type="submit">Make default</button></form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php else: ?><p class="small muted mb-0">No PDF resume uploaded yet. You can apply using the online CV in the meantime.</p><?php endif; ?>
      </div>
    </section>
  </main>
</div>

<script>
(function () {
  var form = document.getElementById('cv-form');
  if (!form) return;
  var hidden = document.getElementById('cv-data');
  var preview = document.getElementById('cv-preview');
  var sections = ['experience', 'education', 'skills', 'projects', 'languages'];
  var esc = function (value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
    });
  };
  function valueOf(el) { return el ? String(el.value || '').trim() : ''; }
  function collect() {
    var data = {contact: {}, headline: '', summary: '', experience: [], education: [], skills: [], projects: [], languages: [], template: 'modern'};
    Array.prototype.forEach.call(form.querySelectorAll('[data-contact]'), function (el) {
      data.contact[el.getAttribute('data-contact')] = valueOf(el);
    });
    data.full_name = data.contact.name || '';
    data.headline = valueOf(form.querySelector('[name="headline"]'));
    data.summary = valueOf(form.querySelector('[name="summary"]'));
    data.template = valueOf(form.querySelector('[name="template"]')) || 'modern';
    sections.forEach(function (section) {
      var selector = '.repeater-item[data-section="' + section + '"]';
      Array.prototype.forEach.call(form.querySelectorAll(selector), function (item) {
        var row = {};
        Array.prototype.forEach.call(item.querySelectorAll('[data-field]'), function (el) {
          row[el.getAttribute('data-field')] = valueOf(el);
        });
        data[section].push(row);
      });
    });
    return data;
  }
  function render(data) {
    var contact = data.contact || {};
    var cls = data.template === 'classic' ? ' cv-template-classic' : (data.template === 'creative' ? ' cv-template-creative' : '');
    var html = '<article class="cv-paper' + cls + '">';
    html += '<h1>' + esc(contact.name || data.full_name || 'Your name') + '</h1>';
    if (data.headline) html += '<p class="cv-muted">' + esc(data.headline) + '</p>';
    var labels = {email: 'Email', phone: 'Phone', location: 'Location', linkedin: 'LinkedIn', github: 'GitHub', website: 'Website'};
    var bits = [];
    Object.keys(labels).forEach(function (key) { if (contact[key]) bits.push('<span><strong>' + labels[key] + ':</strong> ' + esc(contact[key]) + '</span>'); });
    if (bits.length) html += '<div class="cv-grid cv-muted" style="font-size:.85rem">' + bits.join('') + '</div>';
    if (data.summary) html += '<h2>Profile</h2><p style="white-space:pre-line">' + esc(data.summary) + '</p>';
    function items(title, list, build) {
      if (!list || !list.length) return '';
      var out = '<h2>' + title + '</h2>';
      list.forEach(function (row) { out += '<div class="cv-item">' + build(row) + '</div>'; });
      return out;
    }
    html += items('Experience', data.experience, function (r) {
      return '<strong>' + esc(r.title) + (r.company ? ' · ' + esc(r.company) : '') + '</strong>' +
        ((r.start_date || r.end_date) ? '<span class="cv-muted small"> ' + esc(r.start_date) + ' – ' + esc(r.end_date) + '</span>' : '') +
        (r.description ? '<p style="white-space:pre-line">' + esc(r.description) + '</p>' : '');
    });
    html += items('Education', data.education, function (r) {
      return '<strong>' + esc(r.degree) + (r.school ? ' · ' + esc(r.school) : '') + '</strong>' +
        (r.field_of_study ? '<span class="cv-muted small"> ' + esc(r.field_of_study) + '</span>' : '');
    });
    html += items('Skills', data.skills.length ? [{names: data.skills.map(function (s) { return s.name + (s.level ? ' (' + s.level + ')' : ''); }).join(', ')}] : [], function (r) { return esc(r.names); });
    html += items('Projects', data.projects, function (r) {
      return '<strong>' + esc(r.title) + '</strong>' + (r.description ? '<p style="white-space:pre-line">' + esc(r.description) + '</p>' : '');
    });
    html += items('Languages', data.languages.length ? [{names: data.languages.map(function (l) { return l.name + (l.level ? ' — ' + l.level : ''); }).join(', ')}] : [], function (r) { return esc(r.names); });
    html += '</article>';
    if (preview) preview.innerHTML = html;
  }
  function sync() {
    var data = collect();
    if (hidden) hidden.value = JSON.stringify(data);
    render(data);
  }
  form.addEventListener('input', sync);
  form.addEventListener('change', sync);
  form.addEventListener('click', function (e) {
    if (e.target.closest('[data-repeater-add]') || e.target.closest('[data-repeater-remove]')) {
      window.setTimeout(sync, 0);
    }
  });
  sync();
})();
</script>
