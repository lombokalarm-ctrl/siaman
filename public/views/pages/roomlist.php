<?php

declare(strict_types=1);

auth_require_admin_or_staff();

$paketRows = [];
try {
    $paketRows = paket_search('', 200);
} catch (Throwable $e) {
    $paketRows = [];
}
$paketId = (int)($_GET['paket_id'] ?? 0);

$roomlists = [];
if ($paketId > 0) {
    try {
        $roomlists = roomlists_by_paket($paketId);
    } catch (Throwable $e) {
        $roomlists = [];
    }
}

?>
<section class="card">
  <div class="toolbar">
    <div class="toolbar-left">
      <div>
        <div class="card-title">Roomlist</div>
        <div class="card-subtitle">Buat roomlist per hotel untuk paket tertentu</div>
      </div>
    </div>
    <div class="toolbar-right">
      <form method="get" action="<?= h(app_url('/')) ?>" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="page" value="roomlist" />
        <select class="input" name="paket_id" required style="width:360px">
          <option value="">Pilih paket dulu...</option>
          <?php foreach ($paketRows as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $paketId === (int)$p['id'] ? 'selected' : '' ?>>
              <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Buka</button>
      </form>
    </div>
  </div>

  <div class="hr"></div>

  <?php if ($paketId <= 0): ?>
    <div class="muted">Pilih paket dulu untuk melihat/menambah roomlist.</div>
  <?php else: ?>
    <div class="card-title">Daftar Hotel</div>
    <div class="card-subtitle">Satu paket bisa punya lebih dari satu hotel</div>

    <div class="hr"></div>

    <form method="post" action="<?= h(app_url('/?page=roomlist&paket_id=' . $paketId)) ?>" style="display:flex;gap:8px;align-items:end">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="roomlist.create" />
      <input type="hidden" name="paket_id" value="<?= (int)$paketId ?>" />
      <div class="field" style="flex:1">
        <div class="label">Nama Hotel</div>
        <input class="input" name="hotel_nama" placeholder="mis. Hotel Madinah" required />
      </div>
      <button class="btn primary" type="submit">Tambah</button>
    </form>

    <div style="margin-top:12px">
      <table class="table">
        <thead>
          <tr>
            <th>Hotel</th>
            <th style="width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$roomlists): ?>
            <tr>
              <td colspan="2" class="muted">Belum ada roomlist untuk paket ini.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($roomlists as $rl): ?>
            <tr>
              <td><?= h((string)$rl['hotel_nama']) ?></td>
              <td style="display:flex;gap:8px;justify-content:flex-end">
                <a class="btn" href="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$rl['id'])) ?>">Kelola</a>
                <a class="btn" href="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$rl['id'] . '&tab=setup')) ?>">Edit</a>
                <form method="post" action="<?= h(app_url('/?page=roomlist&paket_id=' . $paketId)) ?>" onsubmit="return confirm('Hapus hotel ini dari roomlist? Semua kamar & penghuni di hotel ini akan ikut terhapus.');">
                  <?= csrf_input() ?>
                  <input type="hidden" name="_action" value="roomlist.delete" />
                  <input type="hidden" name="id" value="<?= (int)$rl['id'] ?>" />
                  <input type="hidden" name="paket_id" value="<?= (int)$paketId ?>" />
                  <button class="btn danger" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
