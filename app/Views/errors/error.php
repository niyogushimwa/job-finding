<?php
/** Standalone error page used by abort(); no layout dependency so it always renders. */
$code = (int) ($code ?? 404);
$title = $code === 403 ? 'Access denied' : ($code === 500 ? 'Something went wrong' : 'Page not found');
$message = $message ?? 'The page you requested could not be found.';
$css = function_exists('asset') ? asset('css/style.css') : '/assets/css/style.css';
$home = function_exists('url') ? url() : '/';
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0b1f3a">
  <title><?= e($code) ?> · <?= e($title) ?> · JobLink Rwanda</title>
  <link rel="icon" href="<?= e($css ? str_replace('css/style.css', 'img/icon.svg', $css) : '/assets/img/icon.svg') ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e($css) ?>">
</head>
<body class="text-center" style="display:grid;place-items:center;min-height:100vh;padding:1.5rem">
  <div style="max-width:620px">
    <div class="empty-state">
      <div class="empty-icon" style="font-size:1.6rem;font-weight:800;color:var(--gold)"><?= e($code) ?></div>
      <h1 style="font-size:2rem"><?= e($title) ?></h1>
      <p><?= e($message) ?></p>
      <div class="d-flex gap-1 justify-center" style="justify-content:center;flex-wrap:wrap">
        <a class="btn btn-primary" href="<?= e($home) ?>">Back to home</a>
        <a class="btn btn-outline" href="<?= e($home . 'jobs') ?>">Browse jobs</a>
      </div>
    </div>
    <p class="small muted mt-2">JobLink Rwanda · Your Skills. Your Opportunity. Your Future.</p>
  </div>
</body>
</html>
