<?php
/** Main HTML layout. All dynamic output is escaped in partials and views. */
$pageTitle = $title ?? config('app.name');
$description = $description ?? 'Your Skills Deserve an Opportunity. Discover jobs, internships, remote opportunities and career resources across Rwanda and Africa.';
?>
<!doctype html>
<html lang="<?= e(\App\Core\I18n::locale()) ?>" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($description) ?>">
  <meta name="theme-color" content="#0b1f3a">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= e(url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '')) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <title><?= e($pageTitle) ?> · <?= e(config('app.name')) ?></title>
  <link rel="manifest" href="<?= e(url('manifest.json')) ?>">
  <link rel="icon" href="<?= e(asset('img/icon.svg')) ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <script>
    (function(){try{var t=localStorage.getItem('joblink-theme');if(!t&&matchMedia('(prefers-color-scheme: dark)').matches)t='dark';document.documentElement.setAttribute('data-theme',t||'light')}catch(e){}})();
  </script>
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Organization","name":"JobLink Rwanda","url":"<?= e(url()) ?>","description":"Your Skills Deserve an Opportunity."}
  </script>
</head>
<body>
  <?php include BASE_PATH . '/app/Views/partials/header.php'; ?>
  <main id="main-content">
    <?= $content ?>
  </main>
  <?php include BASE_PATH . '/app/Views/partials/footer.php'; ?>
  <div class="flash-stack" aria-live="polite">
    <?php foreach (take_flash() as $flash): ?>
      <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
  </div>
  <script>
    const METABASE = <?= json_encode(defined('BASE_URL') ? BASE_URL : '', JSON_UNESCAPED_SLASHES) ?>;
    const META_CSRF = <?= json_encode(csrf_token()) ?>;
  </script>
  <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
