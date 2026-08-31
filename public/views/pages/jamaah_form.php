<?php

declare(strict_types=1);

$format = settings_get_pendaftaran_format();
$prefix = strtoupper((string)$format['prefix']);
$separator = (string)$format['separator'];
$yearToken = (string)$format['year_token'];
$seqLength = (int)$format['seq_length'];
$resetPolicy = (string)$format['reset_policy'];
$yearPart = '';

$paketRows = [];
try {
    $paketRows = paket_search('', 200);
} catch (Throwable $e) {
    $paketRows = [];
}

$now = new DateTimeImmutable('now');
if ($yearToken === '{YYYY}') {
    $yearPart = $now->format('Y');
} elseif ($yearToken === '{YY}') {
    $yearPart = $now->format('y');
}

$parts = [];
if ($prefix !== '') $parts[] = $prefix;
if ($yearPart !== '') $parts[] = $yearPart;
$parts[] = str_repeat('0', max(1, $seqLength - 1)) . '1';
$preview = implode($separator, $parts);

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Tambah Jamaah</div>
      <div class="card-subtitle">ID Jamaah & Nomor Pendaftaran akan dibuat otomatis saat simpan</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=jamaah')) ?>">Kembali</a>
  </div>

  <div class="grid cols-2">
    <div class="field">
      <div class="label">ID Jamaah (auto)</div>
      <input class="input mono" value="JMH-000001" readonly />
      <div class="help">Contoh format. Nomor asli dibuat saat simpan.</div>
    </div>
    <div class="field">
      <div class="label">Nomor Pendaftaran (auto)</div>
      <input class="input mono" value="<?= h($preview) ?>" readonly />
      <div class="help">Reset: <?= h($resetPolicy) ?> • Setting ada di menu Pengaturan.</div>
    </div>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=jamaah_create')) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="jamaah.create" />

    <div class="field">
      <div class="label">Paket</div>
      <select class="input" name="paket_id" required>
        <option value="">Pilih paket...</option>
        <?php foreach ($paketRows as $p): ?>
          <option value="<?= (int)$p['id'] ?>">
            <?= h((string)$p['nama']) ?><?= $p['kode'] ? ' • ' . h((string)$p['kode']) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row">
      <div class="field">
        <div class="label">Nama Lengkap</div>
        <input class="input" name="nama_lengkap" required />
      </div>
      <div class="field">
        <div class="label">Nama Bapak Kandung</div>
        <input class="input" name="nama_bapak_kandung" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">NIK</div>
        <input class="input mono" name="nik" required />
      </div>
      <div class="field">
        <div class="label">Nomor KK</div>
        <input class="input mono" name="nomor_kk" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Tempat Lahir</div>
        <input class="input" name="tempat_lahir" required />
      </div>
      <div class="field">
        <div class="label">Tanggal Lahir</div>
        <input class="input mono" name="tanggal_lahir" placeholder="YYYY-MM-DD" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Jenis Kelamin</div>
        <input class="input" name="jenis_kelamin" placeholder="Laki-laki / Perempuan" required />
      </div>
      <div class="field">
        <div class="label">Status Pernikahan</div>
        <input class="input" name="status_pernikahan" placeholder="Belum menikah / Menikah / Cerai" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Pendidikan</div>
        <input class="input" name="pendidikan" required />
      </div>
      <div class="field">
        <div class="label">Pekerjaan</div>
        <input class="input" name="pekerjaan" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Nomor HP/WhatsApp</div>
        <input class="input mono" name="hp" required />
      </div>
      <div class="field">
        <div class="label">Email</div>
        <input class="input" name="email" />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Alamat Lengkap</div>
      <textarea class="input" name="alamat_lengkap" rows="3" required></textarea>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
