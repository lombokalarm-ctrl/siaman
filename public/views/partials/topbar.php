<?php

declare(strict_types=1);

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
    <div class="user">
      <div class="avatar" aria-hidden="true">A</div>
      <div class="user-meta">
        <div class="user-name">Admin</div>
        <div class="user-role">Internal</div>
      </div>
    </div>
  </div>
</header>

