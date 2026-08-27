<?php

declare(strict_types=1);

$page = (string)($_GET['page'] ?? 'dashboard');

$items = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => app_url('/?page=dashboard')],
    ['key' => 'jamaah', 'label' => 'Jamaah', 'href' => app_url('/?page=jamaah')],
    ['key' => 'paket', 'label' => 'Paket', 'href' => app_url('/?page=paket')],
    ['key' => 'invoice', 'label' => 'Invoice', 'href' => app_url('/?page=invoice')],
    ['key' => 'rekap_pembayaran', 'label' => 'Rekap Pembayaran', 'href' => app_url('/?page=rekap_pembayaran')],
    ['key' => 'rekap_piutang', 'label' => 'Piutang', 'href' => app_url('/?page=rekap_piutang')],
    ['key' => 'settings', 'label' => 'Pengaturan', 'href' => app_url('/?page=settings')],
];

?>
<aside class="sidebar" data-shell="sidebar">
  <div class="sidebar-header">
    <div class="brand">
      <div class="brand-mark">S</div>
      <div class="brand-text">
        <div class="brand-title">Siaman</div>
        <div class="brand-subtitle">Manajemen Umroh</div>
      </div>
    </div>
    <button class="icon-btn sidebar-close" type="button" data-action="sidebar-close" aria-label="Tutup menu">
      <span class="icon">✕</span>
    </button>
  </div>

  <nav class="nav">
    <?php foreach ($items as $item): ?>
      <a class="nav-item <?= $page === $item['key'] ? 'is-active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>">
        <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="chip">Prototype UI</div>
    <div class="muted">Tahap awal (tanpa backend)</div>
  </div>
</aside>
