<?php

declare(strict_types=1);

$today = (new DateTimeImmutable('now'))->format('Y-m-d');
$q = (string)($_GET['q'] ?? '');
$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');
$paketId = (int)($_GET['paket_id'] ?? 0);
$statusFilter = strtolower(trim((string)($_GET['status'] ?? '')));

$report = null;
try {
    $report = invoices_piutang_report([
        'q' => $q,
        'from' => $from,
        'to' => $to,
        'paket_id' => $paketId,
        'status' => $statusFilter,
        'limit' => 300,
    ]);
} catch (Throwable $e) {
    $report = ['rows' => [], 'summary' => ['total_piutang' => 0, 'count' => 0]];
    flash_set('error', 'Gagal memuat piutang.');
}

$rows = $report['rows'] ?? [];
$sum = $report['summary'] ?? ['total_piutang' => 0, 'count' => 0];

$paketRows = [];
$paketName = 'Semua Paket';
try {
    $paketRows = paket_search('', 200);
} catch (Throwable $e) {
    $paketRows = [];
}
if ($paketId > 0) {
    try {
        $p = paket_find($paketId);
        if ($p) $paketName = (string)$p['nama'];
    } catch (Throwable $e) {
    }
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Piutang</div>
      <div class="card-subtitle">Invoice yang belum lunas (status unpaid/partial, sisa &gt; 0)</div>
    </div>
  </div>

  <form method="get" action="<?= h(app_url('/')) ?>">
    <input type="hidden" name="page" value="rekap_piutang" />
    <section class="grid cols-3">
      <div class="field">
        <div class="label">Cari (opsional)</div>
        <input class="input" name="q" value="<?= h($q) ?>" placeholder="nomor invoice / nama jamaah / id / daftar" />
      </div>
      <div class="field">
        <div class="label">Dari tanggal (opsional)</div>
        <input class="input mono" name="from" placeholder="YYYY-MM-DD" value="<?= h($from) ?>" />
      </div>
      <div class="field">
        <div class="label">Sampai tanggal (opsional)</div>
        <input class="input mono" name="to" placeholder="YYYY-MM-DD" value="<?= h($to) ?>" />
      </div>
    </section>

    <section class="grid cols-3" style="margin-top:10px">
      <div class="field">
        <div class="label">Paket (opsional)</div>
        <select class="input" name="paket_id">
          <option value="0" <?= $paketId <= 0 ? 'selected' : '' ?>>Semua Paket</option>
          <?php foreach ($paketRows as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === $paketId ? 'selected' : '' ?>>
              <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <div class="label">Status (opsional)</div>
        <select class="input" name="status">
          <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>Semua</option>
          <option value="unpaid" <?= $statusFilter === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
          <option value="partial" <?= $statusFilter === 'partial' ? 'selected' : '' ?>>Partial</option>
        </select>
      </div>
      <div class="field"></div>
    </section>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <a class="btn" href="<?= h(app_url('/?page=rekap_piutang')) ?>">Reset</a>
      <button class="btn primary" type="submit">Terapkan</button>
    </div>
  </form>

  <div class="hr"></div>

  <section class="grid cols-3">
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h(rupiah((float)($sum['total_piutang'] ?? 0))) ?></div>
        <div class="kpi-label">Total piutang (<?= h($paketName) ?>)</div>
      </div>
    </div>
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h((string)($sum['count'] ?? 0)) ?></div>
        <div class="kpi-label">Jumlah invoice (<?= h($paketName) ?>)</div>
      </div>
    </div>
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h($today) ?></div>
        <div class="kpi-label">Tanggal</div>
      </div>
    </div>
  </section>

  <div class="hr"></div>

  <table class="table">
    <thead>
      <tr>
        <th>No Invoice</th>
        <th>Tanggal</th>
        <th>Umur</th>
        <th>Jamaah</th>
        <th>Status</th>
        <th>Total</th>
        <th>Sudah dibayar</th>
        <th>Sisa</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="9" class="muted">Tidak ada piutang.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php $status = (string)$r['status']; ?>
        <tr>
          <td class="mono"><?= h((string)$r['nomor']) ?></td>
          <td class="mono"><?= h((string)$r['tanggal']) ?></td>
          <td class="mono"><?= h((string)max(0, (int)($r['age_days'] ?? 0))) ?> hari</td>
          <td>
            <?= h((string)$r['jamaah_nama']) ?>
            <div class="sub"><?= h((string)$r['jamaah_kode']) ?> • <?= h((string)$r['jamaah_daftar']) ?></div>
            <div class="sub"><?= $r['paket_nama'] ? h((string)$r['paket_nama']) : '—' ?></div>
          </td>
          <td>
            <?php if ($status === 'paid'): ?>
              <span class="badge success">Paid</span>
            <?php elseif ($status === 'partial'): ?>
              <span class="badge warn">Partial</span>
            <?php else: ?>
              <span class="badge muted"><?= h($status) ?></span>
            <?php endif; ?>
          </td>
          <td class="mono"><?= h(rupiah((string)$r['grand_total'])) ?></td>
          <td class="mono"><?= h(rupiah((string)$r['paid_total'])) ?></td>
          <td class="mono"><?= h(rupiah((string)$r['remaining_total'])) ?></td>
          <td><a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$r['id'])) ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
