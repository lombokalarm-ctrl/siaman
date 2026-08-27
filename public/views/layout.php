<?php

declare(strict_types=1);

if (!isset($title, $contentView)) {
    http_response_code(500);
    echo 'Layout variables missing.';
    exit;
}

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($title) ?> — Siaman</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= h(app_url('/assets/app.css')) ?>" />
  </head>
  <body>
    <div class="app-shell" data-shell="root">
      <?php require __DIR__ . '/partials/sidebar.php'; ?>
      <div class="app-main">
        <?php require __DIR__ . '/partials/topbar.php'; ?>
        <main class="app-content">
          <?php require __DIR__ . '/partials/flash.php'; ?>
          <?php require $contentView; ?>
        </main>
      </div>
    </div>
    <script src="<?= h(app_url('/assets/app.js')) ?>"></script>
  </body>
</html>
