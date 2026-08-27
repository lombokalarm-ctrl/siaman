<?php

declare(strict_types=1);

$me = auth_user();

?>
<header class="topbar">
  <div class="topbar-left">
    <button class="icon-btn sidebar-open" type="button" data-action="sidebar-open" aria-label="Buka menu">
      <span class="icon">☰</span>
    </button>
    <div class="page-title"><?= htmlspecialchars($title) ?></div>
  </div>
  <div class="topbar-right">
    <div class="search">
      <input class="input" type="search" placeholder="Cari jamaah, invoice, paket…" aria-label="Cari" />
    </div>
    <?php if ($me): ?>
      <div class="user">
        <div class="avatar" aria-hidden="true"><?= h(strtoupper(substr((string)$me['username'], 0, 1))) ?></div>
        <div class="user-meta">
          <div class="user-name"><?= h((string)$me['username']) ?></div>
          <div class="user-role"><?= h((string)$me['role']) ?></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</header>
