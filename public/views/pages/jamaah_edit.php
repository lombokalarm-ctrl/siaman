<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
$jamaah = null;
if ($id > 0) {
    try {
        $jamaah = jamaah_find($id);
    } catch (Throwable $e) {
        $jamaah = null;
    }
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Edit Jamaah</div>
      <div class="card-subtitle">Perubahan akan tercatat di updated_at</div>
    </div>
    <div style="display:flex;gap:8px">
      <a class="btn" href="<?= h(app_url('/?page=jamaah_detail&id=' . (int)$id)) ?>">Kembali</a>
    </div>
  </div>

  <?php if (!$jamaah): ?>
    <div class="muted">Data jamaah tidak ditemukan.</div>
  <?php else: ?>
  <form method="post" action="<?= h(app_url('/?page=jamaah_edit&id=' . (int)$id)) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="jamaah.update" />
    <input type="hidden" name="id" value="<?= (int)$id ?>" />

    <div class="grid cols-2">
      <div class="field">
        <div class="label">ID Jamaah</div>
        <input class="input mono" value="<?= h((string)$jamaah['id_jamaah']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Nomor Pendaftaran</div>
        <input class="input mono" value="<?= h((string)$jamaah['nomor_pendaftaran']) ?>" readonly />
      </div>
    </div>

    <div class="hr"></div>

    <div class="row">
      <div class="field">
        <div class="label">Nama Lengkap</div>
        <input class="input" name="nama_lengkap" value="<?= h((string)$jamaah['nama_lengkap']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Nama Bapak Kandung</div>
        <input class="input" name="nama_bapak_kandung" value="<?= h((string)$jamaah['nama_bapak_kandung']) ?>" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">NIK</div>
        <input class="input mono" name="nik" value="<?= h((string)$jamaah['nik']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Nomor KK</div>
        <input class="input mono" name="nomor_kk" value="<?= h((string)$jamaah['nomor_kk']) ?>" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Tempat Lahir</div>
        <input class="input" name="tempat_lahir" value="<?= h((string)$jamaah['tempat_lahir']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Tanggal Lahir</div>
        <input class="input mono" name="tanggal_lahir" value="<?= h((string)$jamaah['tanggal_lahir']) ?>" placeholder="YYYY-MM-DD" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Jenis Kelamin</div>
        <input class="input" name="jenis_kelamin" value="<?= h((string)$jamaah['jenis_kelamin']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Status Pernikahan</div>
        <input class="input" name="status_pernikahan" value="<?= h((string)$jamaah['status_pernikahan']) ?>" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Pendidikan</div>
        <input class="input" name="pendidikan" value="<?= h((string)$jamaah['pendidikan']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Pekerjaan</div>
        <input class="input" name="pekerjaan" value="<?= h((string)$jamaah['pekerjaan']) ?>" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Nomor HP/WhatsApp</div>
        <input class="input mono" name="hp" value="<?= h((string)$jamaah['hp']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Email</div>
        <input class="input" name="email" value="<?= h((string)($jamaah['email'] ?? '')) ?>" />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Alamat Lengkap</div>
      <textarea class="input" name="alamat_lengkap" rows="3" required><?= h((string)$jamaah['alamat_lengkap']) ?></textarea>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif" <?= (string)$jamaah['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= (string)$jamaah['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
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
