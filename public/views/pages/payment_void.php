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

$supported = payments_void_supported();

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Batalkan Pembayaran</div>
      <div class="card-subtitle">Menandai pembayaran sebagai void (tidak dihitung di status invoice)</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=invoice')) ?>">Kembali</a>
  </div>

  <?php if (!$supported): ?>
    <div class="alert danger">
      Fitur void pembayaran butuh migrasi database.
      <div class="sub" style="margin-top:6px">Jalankan: database/migrations/003_payments_void.sql</div>
    </div>
  <?php endif; ?>

  <?php if (!$payment): ?>
    <div class="muted">Data pembayaran tidak ditemukan.</div>
  <?php else: ?>
    <div class="hr"></div>

    <section class="grid cols-2">
      <div>
        <div class="label">No Kuitansi</div>
        <div class="mono" style="font-weight:750;margin-top:6px"><?= h((string)$payment['nomor_kuitansi']) ?></div>
        <div class="muted mono" style="margin-top:3px"><?= h((string)$payment['tanggal']) ?></div>
      </div>
      <div>
        <div class="label">Jumlah</div>
        <div class="mono" style="font-weight:750;margin-top:6px"><?= h(rupiah((string)$payment['amount'])) ?></div>
        <div class="muted" style="margin-top:3px"><?= h((string)$payment['metode']) ?></div>
      </div>
    </section>

    <div class="hr"></div>

    <form method="post" action="<?= h(app_url('/?page=payment_void&id=' . (int)$payment['id'])) ?>">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="payment.void" />
      <input type="hidden" name="id" value="<?= (int)$payment['id'] ?>" />
      <input type="hidden" name="invoice_id" value="<?= (int)$payment['invoice_id'] ?>" />

      <div class="field">
        <div class="label">Alasan void</div>
        <input class="input" name="reason" placeholder="contoh: salah input nominal" required />
      </div>

      <div class="hr"></div>

      <div style="display:flex;gap:8px;justify-content:flex-end">
        <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$payment['invoice_id'])) ?>">Batal</a>
        <button class="btn danger" type="submit" <?= $supported ? '' : 'disabled' ?>>Void Pembayaran</button>
      </div>
    </form>
  <?php endif; ?>
</section>

