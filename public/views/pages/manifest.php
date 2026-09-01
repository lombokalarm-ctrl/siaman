<?php

declare(strict_types=1);

$paketRows = [];
try {
    $paketRows = paket_search('', 200);
} catch (Throwable $e) {
    $paketRows = [];
}
$paketId = (int)($_GET['paket_id'] ?? 0);
$paketNama = '';
if ($paketId > 0) {
    try {
        $p = paket_find($paketId);
        $paketNama = (string)($p['nama'] ?? '');
    } catch (Throwable $e) {
        $paketNama = '';
    }
}

$rows = [];
if ($paketId > 0) {
    try {
        $rows = jamaah_manifest_by_paket($paketId);
    } catch (Throwable $e) {
        $rows = [];
    }
}

?>
<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <a class="btn" href="<?= h(app_url('/?page=jamaah')) ?>">Kembali</a>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="manifest" />
        <select class="input" name="paket_id" required style="width:320px">
          <option value="">Pilih paket dulu...</option>
          <?php foreach ($paketRows as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $paketId === (int)$p['id'] ? 'selected' : '' ?>>
              <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Tampilkan</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <div class="toolbar">
    <div class="toolbar-left">
      <div>
        <div class="card-title">Manifest Jamaah</div>
        <div class="card-subtitle"><?= $paketId > 0 ? ('Paket: ' . h($paketNama)) : 'Pilih paket untuk menampilkan data.' ?></div>
      </div>
    </div>
    <div class="toolbar-right" style="display:flex;gap:8px">
      <?php if ($paketId > 0): ?>
        <a class="btn" href="<?= h(app_url('/?page=manifest_csv&paket_id=' . $paketId)) ?>">Export CSV</a>
        <a class="btn" href="<?= h(app_url('/?page=manifest_pdf&paket_id=' . $paketId)) ?>" target="_blank" rel="noopener">Export PDF</a>
      <?php endif; ?>
    </div>
  </div>

  <div style="margin-top:10px">
    <table class="table">
      <thead>
        <tr>
          <th style="width:60px">No</th>
          <th>Nama</th>
          <th>Nama Bapak</th>
          <th style="width:140px">Jenis Kelamin</th>
          <th style="width:130px">Tanggal Lahir</th>
          <th style="width:170px">No KTP</th>
          <th style="width:170px">No Paspor</th>
          <th style="width:130px">Expire Paspor</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($paketId <= 0): ?>
          <tr>
            <td colspan="8" class="muted">Pilih paket dulu untuk melihat manifest.</td>
          </tr>
        <?php elseif (!$rows): ?>
          <tr>
            <td colspan="8" class="muted">Belum ada jamaah untuk paket ini.</td>
          </tr>
        <?php endif; ?>
        <?php $no = 0; ?>
        <?php foreach ($rows as $r): ?>
          <?php $no++; ?>
          <tr>
            <td class="mono"><?= (int)$no ?></td>
            <td><?= h((string)$r['nama_lengkap']) ?></td>
            <td><?= h((string)$r['nama_bapak_kandung']) ?></td>
            <td><?= h((string)$r['jenis_kelamin']) ?></td>
            <td class="mono"><?= h((string)$r['tanggal_lahir']) ?></td>
            <td class="mono"><?= h((string)$r['nik']) ?></td>
            <td class="mono"><?= h((string)($r['passport_no'] ?? '')) ?></td>
            <td class="mono"><?= h((string)($r['passport_expire_date'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($paketId > 0): ?>
    <div class="hr"></div>
    <div class="muted">Total jamaah: <span class="mono"><?= (int)count($rows) ?></span></div>
  <?php endif; ?>
</section>
