<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn primary" href="<?= h(app_url('/?page=paket_create')) ?>">Tambah Paket</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="paket" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: nama / kode" aria-label="Cari paket" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      $rows = paket_search((string)($_GET['q'] ?? ''));
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>Paket</th>
        <th>Durasi</th>
        <th>Harga</th>
        <th>Status</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="5" class="muted">Belum ada data paket.</td>
        </tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?= h((string)$r['nama']) ?>
            <div class="sub">Kode: <?= $r['kode'] ? h((string)$r['kode']) : '—' ?></div>
          </td>
          <td class="mono"><?= (int)$r['durasi_hari'] ?> hari</td>
          <td class="mono">
            <?php if ((string)$r['currency'] === 'IDR'): ?>
              <?= h(rupiah((string)$r['harga'])) ?>
            <?php else: ?>
              <?= h((string)$r['currency']) ?> <?= h((string)$r['harga']) ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if ((string)$r['status'] === 'aktif'): ?>
              <span class="badge success">Aktif</span>
            <?php else: ?>
              <span class="badge muted"><?= h((string)$r['status']) ?></span>
            <?php endif; ?>
          </td>
          <td><a class="btn" href="<?= h(app_url('/?page=paket_edit&id=' . (int)$r['id'])) ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
