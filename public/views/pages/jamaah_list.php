<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn primary" href="<?= h(app_url('/?page=jamaah_create')) ?>">Tambah Jamaah</a>
      <a class="btn" href="<?= h(app_url('/?page=jamaah_import')) ?>">Import CSV</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="jamaah" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: nama / ID / no daftar / HP / NIK" aria-label="Cari jamaah" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      $rows = jamaah_search((string)($_GET['q'] ?? ''));
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>Jamaah</th>
        <th>Kontak</th>
        <th>Status</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="4" class="muted">Belum ada data jamaah.</td>
        </tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?= h((string)$r['nama_lengkap']) ?>
            <div class="sub">
              ID <?= h((string)$r['id_jamaah']) ?> • Daftar <?= h((string)$r['nomor_pendaftaran']) ?> • NIK <?= h((string)$r['nik']) ?>
            </div>
          </td>
          <td>
            <div class="mono"><?= h((string)$r['hp']) ?></div>
            <div class="sub"><?= $r['email'] ? h((string)$r['email']) : '—' ?></div>
          </td>
          <td>
            <?php if ((string)$r['status'] === 'aktif'): ?>
              <span class="badge success">Aktif</span>
            <?php else: ?>
              <span class="badge muted"><?= h((string)$r['status']) ?></span>
            <?php endif; ?>
          </td>
          <td><a class="btn" href="<?= h(app_url('/?page=jamaah_detail&id=' . (int)$r['id'])) ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
