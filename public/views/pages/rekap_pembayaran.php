<?php

declare(strict_types=1);

$today = (new DateTimeImmutable('now'))->format('Y-m-d');
$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');
$paketId = (int)($_GET['paket_id'] ?? 0);
$outlet = (string)($_GET['outlet'] ?? '');
$sales = (string)($_GET['sales'] ?? '');
$metode = (string)($_GET['metode'] ?? '');
$includeVoid = (string)($_GET['include_void'] ?? '') === '1';

if ($from === '') $from = $today;
if ($to === '') $to = $today;

$report = null;
try {
    $report = payments_report([
        'from' => $from,
        'to' => $to,
        'paket_id' => $paketId,
        'outlet' => $outlet,
        'sales' => $sales,
        'metode' => $metode,
        'include_void' => $includeVoid,
        'limit' => 300,
    ]);
} catch (Throwable $e) {
    $report = ['rows' => [], 'summary' => ['total_ok' => 0, 'total_void' => 0, 'count_ok' => 0, 'count_void' => 0]];
    flash_set('error', 'Gagal memuat rekap pembayaran.');
}

$rows = $report['rows'] ?? [];
$sum = $report['summary'] ?? ['total_ok' => 0, 'total_void' => 0, 'count_ok' => 0, 'count_void' => 0];
$voidSupported = payments_void_supported();

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

$outstanding = ['invoice_count' => 0, 'total_tagihan' => 0, 'total_paid' => 0, 'total_outstanding' => 0];
try {
    $outstanding = paket_outstanding_summary($paketId);
} catch (Throwable $e) {
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Rekap Pembayaran</div>
      <div class="card-subtitle">Ringkasan pembayaran (filter tanggal / paket / outlet / sales / metode)</div>
    </div>
  </div>

  <form method="get" action="<?= h(app_url('/')) ?>">
    <input type="hidden" name="page" value="rekap_pembayaran" />
    <section class="grid cols-3">
      <div class="field">
        <div class="label">Dari</div>
        <input class="input mono" name="from" placeholder="YYYY-MM-DD" value="<?= h($from) ?>" />
      </div>
      <div class="field">
        <div class="label">Sampai</div>
        <input class="input mono" name="to" placeholder="YYYY-MM-DD" value="<?= h($to) ?>" />
      </div>
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
    </section>

    <section class="grid cols-3" style="margin-top:10px">
      <div class="field">
        <div class="label">Metode (opsional)</div>
        <input class="input" name="metode" value="<?= h($metode) ?>" placeholder="cash / transfer / edc" data-lowercase="true" />
      </div>
      <div class="field">
        <div class="label">Outlet (opsional)</div>
        <input class="input" name="outlet" value="<?= h($outlet) ?>" placeholder="outlet" data-lowercase="true" />
      </div>
      <div class="field">
        <div class="label">Sales (opsional)</div>
        <input class="input" name="sales" value="<?= h($sales) ?>" placeholder="sales" data-lowercase="true" />
      </div>
      <div class="field">
        <div class="label">Opsi</div>
        <label class="muted" style="display:flex;gap:8px;align-items:center">
          <input type="checkbox" name="include_void" value="1" <?= $includeVoid ? 'checked' : '' ?> />
          Tampilkan VOID
        </label>
      </div>
    </section>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <a class="btn" href="<?= h(app_url('/?page=rekap_pembayaran')) ?>">Reset</a>
      <button class="btn primary" type="submit">Terapkan</button>
    </div>
  </form>

  <div class="hr"></div>

    <section class="grid cols-3">
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h(rupiah((float)($sum['total_ok'] ?? 0))) ?></div>
        <div class="kpi-label">Total masuk</div>
      </div>
    </div>
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h((string)($sum['count_ok'] ?? 0)) ?></div>
        <div class="kpi-label">Jumlah transaksi</div>
      </div>
    </div>
    <div class="card" style="box-shadow:none">
      <div class="kpi">
        <div class="kpi-value mono"><?= h(rupiah((float)($sum['total_void'] ?? 0))) ?></div>
        <div class="kpi-label">VOID</div>
      </div>
    </div>
  </section>

    <section class="grid cols-3" style="margin-top:10px">
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((float)$outstanding['total_tagihan'])) ?></div>
          <div class="kpi-label">Total tagihan (<?= h($paketName) ?> • <?= h((string)$outstanding['invoice_count']) ?> invoice)</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((float)$outstanding['total_paid'])) ?></div>
          <div class="kpi-label">Total paid (<?= h($paketName) ?>)</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((float)$outstanding['total_outstanding'])) ?></div>
          <div class="kpi-label">Outstanding (<?= h($paketName) ?>)</div>
        </div>
      </div>
    </section>

  <div class="hr"></div>

  <table class="table">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>No Kuitansi</th>
        <th>Invoice</th>
        <th>Jamaah</th>
        <th>Paket</th>
        <th>Metode</th>
        <th>Jumlah</th>
        <?php if ($voidSupported): ?>
          <th>Status</th>
        <?php endif; ?>
        <th>Outlet / Sales</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= $voidSupported ? 9 : 8 ?>" class="muted">Tidak ada data.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php $isVoid = $voidSupported && isset($r['voided_at']) && $r['voided_at']; ?>
        <tr>
          <td class="mono"><?= h((string)$r['tanggal']) ?></td>
          <td class="mono"><a href="<?= h(app_url('/?page=kuitansi_detail&id=' . (int)$r['id'])) ?>"><?= h((string)$r['nomor_kuitansi']) ?></a></td>
          <td class="mono"><a href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$r['invoice_id'])) ?>"><?= h((string)$r['invoice_nomor']) ?></a></td>
          <td><?= h((string)$r['jamaah_nama']) ?></td>
          <td><?= $r['paket_nama'] ? h((string)$r['paket_nama']) : '—' ?></td>
          <td><?= h((string)$r['metode']) ?></td>
          <td class="mono"><?= h(rupiah((string)$r['amount'])) ?></td>
          <?php if ($voidSupported): ?>
            <td><?= $isVoid ? '<span class="badge danger">VOID</span>' : '<span class="badge success">OK</span>' ?></td>
          <?php endif; ?>
          <td>
            <div class="mono"><?= h((string)($r['outlet'] ?? '')) ?></div>
            <div class="sub">Sales: <span class="mono"><?= h((string)($r['sales'] ?? '')) ?></span></div>
            <?php if ($isVoid && isset($r['void_reason']) && $r['void_reason']): ?>
              <div class="sub">Alasan: <?= h((string)$r['void_reason']) ?></div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
