<?php

declare(strict_types=1);

$page = (string)($_GET['page'] ?? 'dashboard');
$me = auth_user();

$items = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => app_url('/?page=dashboard')],
];

if (auth_can_access_page('jamaah')) {
    $items[] = ['key' => 'jamaah', 'label' => 'Jamaah', 'href' => app_url('/?page=jamaah')];
}
if (auth_can_access_page('clients')) {
    $items[] = ['key' => 'clients', 'label' => 'Klien', 'href' => app_url('/?page=clients')];
}
if (auth_can_access_page('paket')) {
    $items[] = ['key' => 'paket', 'label' => 'Paket', 'href' => app_url('/?page=paket')];
}
if (auth_can_access_page('invoice')) {
    $items[] = ['key' => 'invoice', 'label' => 'Invoice', 'href' => app_url('/?page=invoice')];
}
if (auth_can_access_page('kuitansi')) {
    $items[] = ['key' => 'kuitansi', 'label' => 'Kuitansi', 'href' => app_url('/?page=kuitansi')];
}
if (auth_can_access_page('rekap_pembayaran')) {
    $items[] = ['key' => 'rekap_pembayaran', 'label' => 'Rekap Pembayaran', 'href' => app_url('/?page=rekap_pembayaran')];
}
if (auth_can_access_page('rekap_piutang')) {
    $items[] = ['key' => 'rekap_piutang', 'label' => 'Piutang', 'href' => app_url('/?page=rekap_piutang')];
}
if (auth_can_access_page('settings')) {
    $items[] = ['key' => 'settings', 'label' => 'Pengaturan', 'href' => app_url('/?page=settings')];
}
if (auth_can_access_page('users')) {
    $items[] = ['key' => 'users', 'label' => 'Users', 'href' => app_url('/?page=users')];
}

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
    <?php if ($me): ?>
      <div class="chip"><?= h((string)$me['username']) ?></div>
      <div class="muted">Role: <?= h((string)$me['role']) ?></div>
      <form method="post" action="<?= h(app_url('/?page=dashboard')) ?>" style="margin-top:8px">
        <?= csrf_input() ?>
        <input type="hidden" name="_action" value="auth.logout" />
        <button class="btn" type="submit" style="width:100%">Logout</button>
      </form>
    <?php endif; ?>
  </div>
</aside>
