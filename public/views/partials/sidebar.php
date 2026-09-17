<?php

declare(strict_types=1);

$page = (string)($_GET['page'] ?? 'dashboard');
$me = auth_user();

$sections = [];

$sections[] = [
    'label' => 'Utama',
    'items' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => app_url('/?page=dashboard')],
    ],
];

$masterItems = [];
if (auth_can_access_page('jamaah')) {
    $masterItems[] = ['key' => 'jamaah', 'label' => 'Jamaah', 'href' => app_url('/?page=jamaah')];
}
if (auth_can_access_page('paket')) {
    $masterItems[] = ['key' => 'paket', 'label' => 'Paket', 'href' => app_url('/?page=paket')];
}
if (auth_can_access_page('clients')) {
    $masterItems[] = ['key' => 'clients', 'label' => 'Klien', 'href' => app_url('/?page=clients')];
}
if ($masterItems) {
    $sections[] = ['label' => 'Master Data', 'items' => $masterItems];
}

$trxItems = [];
if (auth_can_access_page('invoice')) {
    $trxItems[] = ['key' => 'invoice', 'label' => 'Invoice Jamaah', 'href' => app_url('/?page=invoice')];
}
if (auth_can_access_page('invoice_clients')) {
    $trxItems[] = ['key' => 'invoice_clients', 'label' => 'Invoice Klien', 'href' => app_url('/?page=invoice_clients')];
}
if (auth_can_access_page('kuitansi')) {
    $trxItems[] = ['key' => 'kuitansi', 'label' => 'Kuitansi', 'href' => app_url('/?page=kuitansi')];
}
if ($trxItems) {
    $sections[] = ['label' => 'Transaksi', 'items' => $trxItems];
}

$reportItems = [];
if (auth_can_access_page('manifest')) {
    $reportItems[] = ['key' => 'manifest', 'label' => 'Manifest', 'href' => app_url('/?page=manifest')];
}
if (auth_can_access_page('roomlist')) {
    $reportItems[] = ['key' => 'roomlist', 'label' => 'Roomlist', 'href' => app_url('/?page=roomlist')];
}
if (auth_can_access_page('rekap_pembayaran')) {
    $reportItems[] = ['key' => 'rekap_pembayaran', 'label' => 'Rekap Pembayaran', 'href' => app_url('/?page=rekap_pembayaran')];
}
if (auth_can_access_page('rekap_piutang')) {
    $reportItems[] = ['key' => 'rekap_piutang', 'label' => 'Piutang', 'href' => app_url('/?page=rekap_piutang')];
}
if ($reportItems) {
    $sections[] = ['label' => 'Laporan', 'items' => $reportItems];
}

$settingItems = [];
if (auth_can_access_page('settings')) {
    $settingItems[] = ['key' => 'settings', 'label' => 'Pengaturan', 'href' => app_url('/?page=settings')];
}
if (auth_can_access_page('users')) {
    $settingItems[] = ['key' => 'users', 'label' => 'Users', 'href' => app_url('/?page=users')];
}
if ($settingItems) {
    $sections[] = ['label' => 'Pengaturan', 'items' => $settingItems];
}

$openDefault = [
    'Utama' => true,
    'Master Data' => false,
    'Transaksi' => false,
    'Laporan' => false,
    'Pengaturan' => false,
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
    <?php foreach ($sections as $section): ?>
      <?php
        $items = (array)($section['items'] ?? []);
        $isActiveSection = false;
        foreach ($items as $it) {
            if ($page === (string)($it['key'] ?? '')) {
                $isActiveSection = true;
                break;
            }
        }
        $sectionLabel = (string)($section['label'] ?? '');
        $isOpen = $isActiveSection || (bool)($openDefault[$sectionLabel] ?? false);
      ?>
      <details class="nav-group" <?= $isOpen ? 'open' : '' ?>>
        <summary class="nav-group-title"><?= h($sectionLabel) ?></summary>
        <div class="nav-sub">
          <?php foreach ($items as $item): ?>
            <a class="nav-item <?= $page === $item['key'] ? 'is-active' : '' ?>" href="<?= htmlspecialchars((string)$item['href']) ?>">
              <span class="nav-label"><?= htmlspecialchars((string)$item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </details>
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
