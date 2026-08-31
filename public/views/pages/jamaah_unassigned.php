<?php

declare(strict_types=1);

$rows = [];
try {
    $rows = jamaah_search_unassigned((string)($_GET['q'] ?? ''), 500);
} catch (Throwable $e) {
    $rows = [];
}

?>
<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn" href="<?= h(app_url('/?page=jamaah')) ?>">Kembali</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="jamaah_unassigned" />
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: nama / ID / no daftar / HP / NIK" aria-label="Cari jamaah" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <div class="card-title">Jamaah Tanpa Paket</div>
  <div class="card-subtitle">Isi paket via Edit Jamaah agar masuk ke daftar per paket</div>

  <div class="hr"></div>

  <table class="table">
    <thead>
      <tr>
        <th style="width:70px">No</th>
        <th>Jamaah</th>
        <th>Kontak</th>
        <th>Status</th>
        <th style="width:120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="5" class="muted">Tidak ada jamaah tanpa paket.</td>
        </tr>
      <?php endif; ?>
      <?php $no = 0; ?>
      <?php foreach ($rows as $r): ?>
        <?php $no++; ?>
        <tr>
          <td class="mono"><?= (int)$no ?></td>
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
          <td><a class="btn" href="<?= h(app_url('/?page=jamaah_edit&id=' . (int)$r['id'])) ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="hr"></div>
  <div class="muted">Total jamaah tanpa paket: <span class="mono"><?= (int)count($rows) ?></span></div>
</section>
