<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn primary" href="<?= h(app_url('/?page=client_create')) ?>">Tambah Klien</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="clients" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: perusahaan / PIC / telp / email" aria-label="Cari klien" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      $rows = client_search((string)($_GET['q'] ?? ''));
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>Perusahaan</th>
        <th>PIC</th>
        <th>Kontak</th>
        <th>Status</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="5" class="muted">Belum ada data klien.</td>
        </tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?= h((string)$r['nama_perusahaan']) ?>
          </td>
          <td><?= h((string)$r['nama_pic']) ?></td>
          <td>
            <div class="mono"><?= h((string)$r['no_tlp']) ?></div>
            <div class="sub"><?= $r['email'] ? h((string)$r['email']) : '—' ?></div>
          </td>
          <td>
            <?php if ((string)$r['status'] === 'aktif'): ?>
              <span class="badge success">Aktif</span>
            <?php else: ?>
              <span class="badge muted"><?= h((string)$r['status']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <a class="btn" href="<?= h(app_url('/?page=client_edit&id=' . (int)$r['id'])) ?>">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
