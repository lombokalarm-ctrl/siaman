<?php

declare(strict_types=1);

$paketRows = [];
try {
    $paketRows = paket_search('', 200);
} catch (Throwable $e) {
    $paketRows = [];
}
$paketId = (int)($_GET['paket_id'] ?? 0);

?>
<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn primary" href="<?= h(app_url('/?page=jamaah_create')) ?>">Tambah Jamaah</a>
      <a class="btn" href="<?= h(app_url('/?page=jamaah_import')) ?>">Import CSV</a>
      <a class="btn" href="<?= h(app_url('/?page=jamaah_unassigned')) ?>">Jamaah Tanpa Paket</a>
      <?php if (isset($paketId) && (int)$paketId > 0): ?>
        <a class="btn" href="<?= h(app_url('/?page=manifest&paket_id=' . (int)$paketId)) ?>">Manifest Paket</a>
        <a class="btn" href="<?= h(app_url('/?page=roomlist&paket_id=' . (int)$paketId)) ?>">Roomlist Paket</a>
      <?php else: ?>
        <span class="btn" style="opacity:.6;pointer-events:none">Manifest Paket</span>
        <span class="btn" style="opacity:.6;pointer-events:none">Roomlist Paket</span>
      <?php endif; ?>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="jamaah" />
        <select class="input" name="paket_id" required style="width:260px">
          <option value="">Pilih paket dulu...</option>
          <?php foreach ($paketRows as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $paketId === (int)$p['id'] ? 'selected' : '' ?>>
              <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input class="input" style="width:260px" type="text" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" placeholder="Cari: nama / ID / no daftar / HP / NIK" aria-label="Cari jamaah" />
        <button class="btn" type="submit">Cari</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php
  $rows = [];
  try {
      if ($paketId > 0) {
          $rows = jamaah_search((string)($_GET['q'] ?? ''), 500, $paketId);
      } else {
          $rows = [];
      }
  } catch (Throwable $e) {
      $rows = [];
  }
  ?>

  <table class="table">
    <thead>
      <tr>
        <th style="width:70px">No</th>
        <th>Jamaah</th>
        <th>Paket</th>
        <th>Kontak</th>
        <th>Status</th>
        <th style="width:360px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="6" class="muted"><?= $paketId > 0 ? 'Belum ada data jamaah untuk paket ini.' : 'Pilih paket dulu untuk melihat daftar jamaah.' ?></td>
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
          <td><?= h((string)($r['paket_nama'] ?? '—')) ?></td>
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
          <td style="display:flex;gap:8px;justify-content:flex-end;align-items:center;flex-wrap:wrap">
            <a class="btn" href="<?= h(app_url('/?page=jamaah_detail&id=' . (int)$r['id'])) ?>">Detail</a>
            <form method="post" action="<?= h(app_url('/?page=jamaah&paket_id=' . $paketId)) ?>" style="display:flex;gap:8px;align-items:center">
              <?= csrf_input() ?>
              <input type="hidden" name="_action" value="jamaah.paket.move" />
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
              <input type="hidden" name="return_paket_id" value="<?= (int)$paketId ?>" />
              <input type="hidden" name="q" value="<?= h((string)($_GET['q'] ?? '')) ?>" />
              <select class="input" name="paket_id" required style="width:210px">
                <option value="">Pindahkan ke...</option>
                <option value="0">Tanpa Paket</option>
                <?php foreach ($paketRows as $p): ?>
                  <?php if ((int)$p['id'] === (int)$paketId) continue; ?>
                  <option value="<?= (int)$p['id'] ?>"><?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn" type="submit" onclick="return confirm('Pindahkan jamaah ini?');">Pindah</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($paketId > 0): ?>
    <div class="hr"></div>
    <div class="muted">Total jamaah paket ini: <span class="mono"><?= (int)count($rows) ?></span></div>
  <?php endif; ?>
</section>
