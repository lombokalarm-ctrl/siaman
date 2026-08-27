<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn primary" href="<?= h(app_url('/?page=invoice_create')) ?>">Buat Invoice</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="invoice" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: nomor / jamaah / id / daftar" aria-label="Cari invoice" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      $rows = invoice_search((string)($_GET['q'] ?? ''));
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>No Invoice</th>
        <th>Jamaah</th>
        <th>Status</th>
        <th>Total</th>
        <th>Sisa</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="6" class="muted">Belum ada data invoice.</td>
        </tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php
          $total = (float)$r['grand_total'];
          $paid = (float)$r['paid_total'];
          $remain = max(0, $total - $paid);
          $status = (string)$r['status'];
        ?>
        <tr>
          <td class="mono"><?= h((string)$r['nomor']) ?></td>
          <td>
            <?= h((string)$r['jamaah_nama']) ?>
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
          <td class="mono"><?= h(rupiah($total)) ?></td>
          <td class="mono"><?= h(rupiah($remain)) ?></td>
          <td><a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$r['id'])) ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
