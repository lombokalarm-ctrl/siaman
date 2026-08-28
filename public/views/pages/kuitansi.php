<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <div>
        <div class="card-title">Kuitansi</div>
        <div class="card-subtitle">Daftar kuitansi pembayaran (hasil dari pembayaran invoice)</div>
      </div>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="kuitansi" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: no kuitansi / invoice / jamaah" aria-label="Cari kuitansi" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      $rows = payments_search((string)($_GET['q'] ?? ''));
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>No Kuitansi</th>
        <th>Tanggal</th>
        <th>Pelanggan</th>
        <th>Invoice</th>
        <th>Metode</th>
        <th>Jumlah</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="7" class="muted">Belum ada kuitansi.</td>
        </tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php $isVoid = isset($r['voided_at']) && $r['voided_at']; ?>
        <tr>
          <td class="mono">
            <?= h((string)$r['nomor_kuitansi']) ?>
            <?php if ($isVoid): ?>
              <span class="badge danger" style="margin-left:6px">VOID</span>
            <?php endif; ?>
          </td>
          <td class="mono"><?= h((string)$r['tanggal']) ?></td>
          <td>
            <?= h((string)$r['target_nama']) ?>
            <div class="sub"><?= (string)($r['target_type'] ?? '') === 'client' ? 'Klien' : 'Jamaah' ?></div>
          </td>
          <td class="mono"><?= h((string)$r['invoice_nomor']) ?></td>
          <td><?= h((string)$r['metode']) ?></td>
          <td class="mono"><?= h(rupiah((string)$r['amount'])) ?></td>
          <td><a class="btn" href="<?= h(app_url('/?page=kuitansi_detail&id=' . (int)$r['id'])) ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
