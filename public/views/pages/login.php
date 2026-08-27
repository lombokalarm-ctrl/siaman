<?php

declare(strict_types=1);

$next = (string)($_GET['next'] ?? '');
$next = trim($next);
if ($next === '') {
    $next = app_url('/?page=dashboard');
}

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login — Siaman</title>
    <link rel="stylesheet" href="<?= h(app_url('/assets/app.css')) ?>" />
  </head>
  <body>
    <div style="max-width:440px;margin:64px auto;padding:0 16px">
      <?php require __DIR__ . '/../partials/flash.php'; ?>
      <section class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Login</div>
            <div class="card-subtitle">Siaman</div>
          </div>
        </div>
        <div class="hr"></div>
        <form method="post" action="<?= h(app_url('/?page=login')) ?>">
          <?= csrf_input() ?>
          <input type="hidden" name="_action" value="auth.login" />
          <input type="hidden" name="next" value="<?= h($next) ?>" />
          <div class="field">
            <div class="label">Username</div>
            <input class="input" name="username" autocomplete="username" required />
          </div>
          <div class="field" style="margin-top:10px">
            <div class="label">Password</div>
            <input class="input" type="password" name="password" autocomplete="current-password" required />
          </div>
          <div class="hr"></div>
          <div style="display:flex;gap:8px;justify-content:flex-end">
            <button class="btn primary" type="submit">Masuk</button>
          </div>
        </form>
      </section>
    </div>
  </body>
</html>
