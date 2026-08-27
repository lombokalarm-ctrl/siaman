<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
$payment = null;
if ($id > 0) {
    try {
        $payment = payment_find($id);
    } catch (Throwable $e) {
        $payment = null;
    }
}

$printTime = (new DateTimeImmutable('now'))->format('Y-m-d H:i');

?>
<div class="receipt">
  <section class="card">
    <?php if (!$payment): ?>
      <div class="muted">Kuitansi tidak ditemukan.</div>
    <?php else: ?>
    <?php $isVoid = payments_void_supported() && isset($payment['voided_at']) && $payment['voided_at']; ?>
    <div class="receipt-header">
      <div>
        <div class="receipt-title">Kuitansi Pembayaran</div>
        <div class="muted">Bukti pembayaran internal • Print-friendly</div>
      </div>
      <div style="text-align:right">
        <?php if ($isVoid): ?>
          <div class="badge danger">VOID</div>
        <?php else: ?>
          <div class="badge muted"><?= h((string)$payment['metode']) ?></div>
        <?php endif; ?>
        <div class="muted mono" style="margin-top:6px"><?= h((string)$payment['nomor_kuitansi']) ?></div>
      </div>
    </div>

    <div class="hr"></div>

    <section class="grid cols-2">
      <div>
        <div class="label">Jamaah</div>
        <div style="font-weight:650;margin-top:6px"><?= h((string)$payment['jamaah_nama']) ?></div>
        <div class="muted mono" style="margin-top:3px"><?= h((string)$payment['jamaah_hp']) ?></div>
      </div>
      <div>
        <div class="label">Invoice</div>
        <div style="font-weight:650;margin-top:6px" class="mono"><?= h((string)$payment['invoice_nomor']) ?></div>
        <div class="muted" style="margin-top:3px">Paket: <?= $payment['paket_nama'] ? h((string)$payment['paket_nama']) : '—' ?></div>
      </div>
    </section>

    <div class="hr"></div>

    <section class="grid cols-3">
      <div class="kpi">
        <div class="kpi-value mono"><?= h(rupiah((string)$payment['amount'])) ?></div>
        <div class="kpi-label">Jumlah dibayar</div>
      </div>
      <div class="kpi">
        <div class="kpi-value mono"><?= h((string)$payment['tanggal']) ?></div>
        <div class="kpi-label">Tanggal</div>
      </div>
      <div class="kpi">
        <div class="kpi-value"><?= h((string)$payment['metode']) ?></div>
        <div class="kpi-label">Metode</div>
      </div>
    </section>

    <div class="hr"></div>

    <div class="muted">Terbilang: <span style="font-weight:650"><?= h(terbilang_rupiah((string)$payment['amount'])) ?></span></div>

    <div class="hr"></div>

    <?php if ($isVoid && isset($payment['void_reason']) && $payment['void_reason']): ?>
      <div class="alert danger">Alasan void: <?= h((string)$payment['void_reason']) ?></div>
      <div class="hr"></div>
    <?php endif; ?>

    <table class="table">
      <thead>
        <tr>
          <th>Field</th>
          <th>Nilai</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Pengirim</td>
          <td class="mono"><?= h((string)($payment['pengirim'] ?? '')) ?></td>
        </tr>
        <tr>
          <td>Outlet</td>
          <td class="mono"><?= h((string)($payment['outlet'] ?? '')) ?></td>
        </tr>
        <tr>
          <td>Sales</td>
          <td class="mono"><?= h((string)($payment['sales'] ?? '')) ?></td>
        </tr>
        <tr>
          <td>Referensi</td>
          <td class="mono"><?= $payment['reference'] ? h((string)$payment['reference']) : '—' ?></td>
        </tr>
      </tbody>
    </table>

    <div class="hr"></div>

    <div class="muted">Waktu cetak: <span class="mono"><?= h($printTime) ?></span></div>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
      <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$payment['invoice_id'])) ?>">Kembali</a>
      <a class="btn" href="<?= h(app_url('/?page=kuitansi_print&id=' . (int)$payment['id'])) ?>" target="_blank" rel="noopener">Cetak 1/2 A4 / PDF</a>
      <a class="btn" href="<?= h(app_url('/?page=kuitansi_pdf&id=' . (int)$payment['id'])) ?>" target="_blank" rel="noopener">PDF (dompdf)</a>
      <button class="btn primary" onclick="window.print()">Cetak</button>
    </div>
    <?php endif; ?>
  </section>
</div>
