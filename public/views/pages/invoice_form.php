<?php

declare(strict_types=1);

$jamaahRows = [];
$paketRows = [];
try {
    $jamaahRows = jamaah_search('', 50);
    $paketRows = paket_search('', 100);
} catch (Throwable $e) {
    $jamaahRows = [];
    $paketRows = [];
}

$today = (new DateTimeImmutable('now'))->format('Y-m-d');
$prefJamaahId = (int)($_GET['jamaah_id'] ?? 0);

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Buat Invoice</div>
      <div class="card-subtitle">Pilih jamaah, lalu pilih paket (opsional) atau item manual</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=invoice')) ?>">Kembali</a>
  </div>

  <?php if (!$jamaahRows): ?>
    <div class="muted">Belum ada jamaah. Buat jamaah dulu.</div>
  <?php else: ?>
  <form method="post" action="<?= h(app_url('/?page=invoice_create')) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="invoice.create" />

    <div class="row">
      <div class="field">
        <div class="label">Jamaah</div>
        <select class="input" name="jamaah_id" required>
          <option value="">Pilih jamaah</option>
          <?php foreach ($jamaahRows as $j): ?>
            <option value="<?= (int)$j['id'] ?>" <?= $prefJamaahId === (int)$j['id'] ? 'selected' : '' ?>>
              <?= h((string)$j['nama_lengkap']) ?> (<?= h((string)$j['id_jamaah']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <div class="label">Tanggal Invoice</div>
        <input class="input mono" name="tanggal" value="<?= h($today) ?>" placeholder="YYYY-MM-DD" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Paket (opsional)</div>
        <select class="input" name="paket_id" data-invoice-paket="select">
          <option value="">(Tanpa paket)</option>
          <?php foreach ($paketRows as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-paket-nama="<?= h((string)$p['nama']) ?>" data-paket-harga="<?= h((string)$p['harga']) ?>">
              <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="help">Jika dipilih, sistem akan menambahkan 1 item paket, dan kamu masih bisa menambah item tambahan.</div>
      </div>
      <div class="field">
        <div class="label">Catatan (opsional)</div>
        <input class="input" name="notes" placeholder="catatan internal" />
      </div>
    </div>

    <div class="hr"></div>

    <div class="toolbar">
      <div class="toolbar-left">
        <div>
          <div class="card-title">Item Invoice</div>
          <div class="card-subtitle">Mendukung multi item. Jika memilih paket, item otomatis mengikuti paket.</div>
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
          <tr data-invoice-item="row">
            <td>
              <input class="input" name="item_label[]" placeholder="contoh: DP Umroh" />
            </td>
            <td>
              <input class="input mono" name="item_qty[]" inputmode="decimal" value="1" data-invoice-item="qty" />
            </td>
            <td>
              <input class="input mono" name="item_price[]" inputmode="decimal" placeholder="contoh: 35000000" data-invoice-item="price" />
            </td>
            <td>
              <input class="input mono" value="0" readonly data-invoice-item="total" />
            </td>
            <td>
              <button class="btn danger" type="button" data-action="invoice-item-remove">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Diskon</div>
        <input class="input mono" name="diskon" inputmode="decimal" value="0" data-invoice-summary="diskon" />
      </div>
      <div class="field">
        <div class="label">Pajak</div>
        <input class="input mono" name="pajak" inputmode="decimal" value="0" data-invoice-summary="pajak" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <input class="input" value="unpaid (otomatis)" readonly />
      </div>
      <div class="field">
        <div class="label">Grand Total (preview)</div>
        <input class="input mono" value="0" readonly data-invoice-summary="grand_total" />
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Buat Invoice</button>
    </div>
  </form>
  <?php endif; ?>
</section>
