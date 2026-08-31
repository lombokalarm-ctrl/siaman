<?php

declare(strict_types=1);

auth_require_admin();

$id = (int)($_GET['id'] ?? 0);
$invoice = null;
if ($id > 0) {
    try {
        $invoice = invoice_find($id);
    } catch (Throwable $e) {
        $invoice = null;
    }
}

if (!$invoice) {
    flash_set('error', 'Invoice tidak ditemukan.');
    redirect(app_url('/?page=invoice'));
}

$paketRows = [];
try {
    $paketRows = paket_search('', 100);
} catch (Throwable $e) {
    $paketRows = [];
}

$isClient = (string)($invoice['target_type'] ?? '') === 'client';
$tanggal = (string)($invoice['tanggal'] ?? '');
$notes = (string)($invoice['notes'] ?? '');
$diskon = (string)($invoice['diskon'] ?? '0');
$pajak = (string)($invoice['pajak'] ?? '0');
$paidTotal = (float)($invoice['paid_total'] ?? 0);

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Edit Invoice <span class="mono"><?= h((string)$invoice['nomor']) ?></span></div>
      <div class="card-subtitle">
        <?= $isClient ? 'Klien' : 'Jamaah' ?>: <?= h((string)$invoice['target_nama']) ?>
        • Sudah dibayar: <span class="mono"><?= h(rupiah($paidTotal)) ?></span>
      </div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$invoice['id'])) ?>">Kembali</a>
  </div>

  <form method="post" action="<?= h(app_url('/?page=invoice_edit&id=' . (int)$invoice['id'])) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="invoice.update" />
    <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>" />

    <div class="row">
      <div class="field">
        <div class="label">Ditagihkan ke</div>
        <input class="input" value="<?= h($isClient ? 'Klien' : 'Jamaah') ?>" readonly />
      </div>
      <div class="field" style="flex:2">
        <div class="label"><?= h($isClient ? 'Klien' : 'Jamaah') ?></div>
        <input class="input" value="<?= h((string)$invoice['target_nama']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Tanggal Invoice</div>
        <input class="input mono" name="tanggal" value="<?= h($tanggal) ?>" placeholder="YYYY-MM-DD" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <?php if (!$isClient): ?>
        <div class="field">
          <div class="label">Paket (opsional)</div>
          <select class="input" name="paket_id" data-invoice-paket="select">
            <option value="">(Tanpa paket)</option>
            <?php foreach ($paketRows as $p): ?>
              <option value="<?= (int)$p['id'] ?>"
                data-paket-nama="<?= h((string)$p['nama']) ?>"
                data-paket-harga="<?= h((string)$p['harga']) ?>"
                <?= (int)($invoice['paket_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>
              >
                <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="help">Jika paket berubah, item paket akan disesuaikan (otomatis).</div>
        </div>
      <?php endif; ?>
      <div class="field" style="<?= $isClient ? 'flex:1' : 'flex:2' ?>">
        <div class="label">Catatan (opsional)</div>
        <input class="input" name="notes" value="<?= h($notes) ?>" placeholder="catatan internal" />
      </div>
    </div>

    <div class="hr"></div>

    <div class="toolbar">
      <div class="toolbar-left">
        <div>
          <div class="card-title">Item Invoice</div>
          <div class="card-subtitle">Kamu bisa tambah/hapus item. Total akan dihitung ulang.</div>
        </div>
      </div>
      <div class="toolbar-right">
        <button class="btn" type="button" data-action="invoice-item-add">Tambah Item</button>
      </div>
    </div>

    <div style="margin-top:10px">
      <table class="table" data-invoice-items="table">
        <thead>
          <tr>
            <th>Label</th>
            <th style="width:110px">Qty</th>
            <th style="width:160px">Harga</th>
            <th style="width:160px">Total</th>
            <th style="width:90px">Aksi</th>
          </tr>
        </thead>
        <tbody data-invoice-items="body">
          <?php $items = (array)($invoice['items'] ?? []); ?>
          <?php if (!$items): ?>
            <tr data-invoice-item="row">
              <td><textarea class="input" name="item_label[]" rows="2"></textarea></td>
              <td><input class="input mono" name="item_qty[]" inputmode="decimal" value="1" data-invoice-item="qty" /></td>
              <td><input class="input mono" name="item_price[]" inputmode="decimal" data-invoice-item="price" /></td>
              <td><input class="input mono" value="0" readonly data-invoice-item="total" /></td>
              <td><button class="btn danger" type="button" data-action="invoice-item-remove">Hapus</button></td>
            </tr>
          <?php endif; ?>
          <?php foreach ($items as $it): ?>
            <tr data-invoice-item="row">
              <td><textarea class="input" name="item_label[]" rows="2"><?= h((string)$it['label']) ?></textarea></td>
              <td><input class="input mono" name="item_qty[]" inputmode="decimal" value="<?= h((string)$it['qty']) ?>" data-invoice-item="qty" /></td>
              <td><input class="input mono" name="item_price[]" inputmode="decimal" value="<?= h((string)$it['price']) ?>" data-invoice-item="price" /></td>
              <td><input class="input mono" value="<?= h((string)$it['total']) ?>" readonly data-invoice-item="total" /></td>
              <td><button class="btn danger" type="button" data-action="invoice-item-remove">Hapus</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Diskon</div>
        <input class="input mono" name="diskon" inputmode="decimal" value="<?= h($diskon) ?>" data-invoice-summary="diskon" />
      </div>
      <div class="field">
        <div class="label">Pajak</div>
        <input class="input mono" name="pajak" inputmode="decimal" value="<?= h($pajak) ?>" data-invoice-summary="pajak" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Grand Total (preview)</div>
        <input class="input mono" value="<?= h((string)$invoice['grand_total']) ?>" readonly data-invoice-summary="grand_total" />
      </div>
      <div class="field">
        <div class="label">Sisa (estimasi)</div>
        <input class="input mono" value="<?= h(rupiah((float)$invoice['remaining_total'])) ?>" readonly />
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$invoice['id'])) ?>">Batal</a>
      <button class="btn primary" type="submit">Simpan Perubahan</button>
    </div>
  </form>
</section>

