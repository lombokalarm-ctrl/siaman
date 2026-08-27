<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
$paket = null;
if ($id > 0) {
    try {
        $paket = paket_find($id);
    } catch (Throwable $e) {
        $paket = null;
    }
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Edit Paket</div>
      <div class="card-subtitle">Harga paket dipakai sebagai default item invoice (opsional)</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=paket')) ?>">Kembali</a>
  </div>

  <?php if (!$paket): ?>
    <div class="muted">Data paket tidak ditemukan.</div>
  <?php else: ?>
  <form method="post" action="<?= h(app_url('/?page=paket_edit&id=' . (int)$id)) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="paket.update" />
    <input type="hidden" name="id" value="<?= (int)$id ?>" />

    <div class="row">
      <div class="field">
        <div class="label">Nama Paket</div>
        <input class="input" name="nama" value="<?= h((string)$paket['nama']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Kode Paket (opsional)</div>
        <input class="input mono" name="kode" value="<?= h((string)($paket['kode'] ?? '')) ?>" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Durasi (hari)</div>
        <input class="input mono" name="durasi_hari" inputmode="numeric" value="<?= h((string)$paket['durasi_hari']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Tanggal Berangkat (opsional)</div>
        <input class="input mono" name="tanggal_berangkat" value="<?= h((string)($paket['tanggal_berangkat'] ?? '')) ?>" placeholder="YYYY-MM-DD" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Harga</div>
        <input class="input mono" name="harga" inputmode="decimal" value="<?= h((string)$paket['harga']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Mata Uang</div>
        <input class="input mono" name="currency" value="<?= h((string)$paket['currency']) ?>" required />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Deskripsi (opsional)</div>
      <textarea class="input" name="deskripsi" rows="3"><?= h((string)($paket['deskripsi'] ?? '')) ?></textarea>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif" <?= (string)$paket['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= (string)$paket['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>
      <div class="field">
        <div class="label">Catatan</div>
        <input class="input" value="(tahap berikutnya)" readonly />
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Simpan Perubahan</button>
    </div>
  </form>
  <?php endif; ?>
</section>
