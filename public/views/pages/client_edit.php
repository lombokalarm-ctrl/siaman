<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'ID tidak valid.');
    redirect(app_url('/?page=clients'));
}

$client = null;
try {
    $client = client_find($id);
} catch (Throwable $e) {
    $client = null;
}
if (!$client) {
    flash_set('error', 'Klien tidak ditemukan.');
    redirect(app_url('/?page=clients'));
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Edit Klien</div>
      <div class="card-subtitle"><?= h((string)$client['nama_perusahaan']) ?></div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=clients')) ?>">Kembali</a>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=client_edit&id=' . $id)) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="client.update" />
    <input type="hidden" name="id" value="<?= (int)$id ?>" />

    <div class="row">
      <div class="field">
        <div class="label">Nama Perusahaan</div>
        <input class="input" name="nama_perusahaan" value="<?= h((string)$client['nama_perusahaan']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Nama PIC</div>
        <input class="input" name="nama_pic" value="<?= h((string)$client['nama_pic']) ?>" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">No Tlp</div>
        <input class="input mono" name="no_tlp" value="<?= h((string)$client['no_tlp']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Email</div>
        <input class="input" name="email" value="<?= h((string)($client['email'] ?? '')) ?>" required />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Alamat</div>
      <textarea class="input" name="alamat" rows="3" required><?= h((string)$client['alamat']) ?></textarea>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif" <?= (string)$client['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= (string)$client['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>
      <div class="field"></div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
