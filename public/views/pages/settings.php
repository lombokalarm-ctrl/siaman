<section class="grid cols-2">
  <?php
  $dbError = null;
  $format = [
      'prefix' => 'REG',
      'year_token' => '{YYYY}',
      'separator' => '-',
      'seq_length' => 5,
      'reset_policy' => 'yearly',
  ];
  $kop = [
      'nama' => 'Siaman',
      'alamat' => '',
      'kontak' => '',
      'kota' => '',
      'penanggung_jawab' => '',
      'logo_path' => '',
  ];
  try {
      $format = settings_get_pendaftaran_format();
      $kop = settings_get_invoice_kop();
  } catch (Throwable $e) {
      $dbError = $e->getMessage();
  }

  $prefix = strtoupper((string)(($format['prefix'] ?? null) ?: 'REG'));
  $separator = (string)(($format['separator'] ?? null) ?: '-');
  $yearToken = (string)(($format['year_token'] ?? null) ?: '{YYYY}');
  $seqLength = (int)(($format['seq_length'] ?? null) ?: 5);
  $resetPolicy = (string)(($format['reset_policy'] ?? null) ?: 'yearly');

  $now = new DateTimeImmutable('now');
  $yearPart = '';
  if ($yearToken === '{YYYY}') {
      $yearPart = $now->format('Y');
  } elseif ($yearToken === '{YY}') {
      $yearPart = $now->format('y');
  }

  $previewParts = [];
  if ($prefix !== '') $previewParts[] = $prefix;
  if ($yearPart !== '') $previewParts[] = $yearPart;
  $previewParts[] = str_repeat('0', max(1, $seqLength - 1)) . '1';
  $preview = implode($separator, $previewParts);
  ?>

  <?php if ($dbError): ?>
    <div class="alert danger" style="grid-column:1 / -1">
      Database belum siap. Buat database dan import schema, atau sesuaikan konfigurasi DB.
      <div class="sub" style="margin-top:6px">Detail: <?= h($dbError) ?></div>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Nomor Pendaftaran</div>
        <div class="card-subtitle">Tempat setting format nomor pendaftaran (prototype)</div>
      </div>
      <span class="badge muted">MVP</span>
    </div>

    <form method="post" action="<?= h(app_url('/?page=settings')) ?>">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="settings.pendaftaran.update" />

      <div class="field">
        <div class="label">Prefix</div>
        <input class="input mono" name="prefix" value="<?= h($prefix) ?>" />
        <div class="help">Contoh: REG / VIP / JKT. Disimpan uppercase.</div>
      </div>

      <div class="row" style="margin-top:10px">
        <div class="field">
          <div class="label">Tahun</div>
          <input class="input mono" name="year_token" value="<?= h($yearToken) ?>" />
          <div class="help">Placeholder: {YYYY} / {YY} / kosong.</div>
        </div>
        <div class="field">
          <div class="label">Panjang Urutan</div>
          <input class="input mono" name="seq_length" value="<?= h((string)$seqLength) ?>" inputmode="numeric" />
          <div class="help">Contoh 5 → 00001.</div>
        </div>
      </div>

      <div class="row" style="margin-top:10px">
        <div class="field">
          <div class="label">Pemisah</div>
          <input class="input mono" name="separator" value="<?= h($separator) ?>" />
          <div class="help">Contoh: - atau / atau kosong.</div>
        </div>
        <div class="field">
          <div class="label">Reset Urutan</div>
          <select class="input" name="reset_policy">
            <option value="yearly" <?= $resetPolicy === 'yearly' ? 'selected' : '' ?>>Tahunan</option>
            <option value="monthly" <?= $resetPolicy === 'monthly' ? 'selected' : '' ?>>Bulanan</option>
            <option value="never" <?= $resetPolicy === 'never' ? 'selected' : '' ?>>Tidak reset</option>
          </select>
          <div class="help">Default: tahunan.</div>
        </div>
      </div>

    <div class="hr"></div>

    <div class="field">
      <div class="label">Preview Format</div>
      <input class="input mono" value="<?= h($preview) ?>" readonly />
      <div class="help">Preview berubah sesuai setting (akan dibuat dinamis saat backend).</div>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
      <button class="btn" type="reset">Batal</button>
      <button class="btn primary" type="submit">Simpan</button>
    </div>
    </form>
  </div>

  <div class="card" style="grid-column:1 / -1">
    <div class="card-header">
      <div>
        <div class="card-title">Kop Surat Invoice</div>
        <div class="card-subtitle">Logo + informasi header untuk Invoice (untuk cetak / save as PDF)</div>
      </div>
      <span class="badge muted">MVP</span>
    </div>

    <?php
    $kopNama = (string)($kop['nama'] ?? '');
    $kopAlamat = (string)($kop['alamat'] ?? '');
    $kopKontak = (string)($kop['kontak'] ?? '');
    $kopKota = (string)($kop['kota'] ?? '');
    $kopPj = (string)($kop['penanggung_jawab'] ?? '');
    $kopLogo = (string)($kop['logo_path'] ?? '');
    ?>

    <form method="post" action="<?= h(app_url('/?page=settings')) ?>" enctype="multipart/form-data">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="settings.kop.update" />

      <section class="grid cols-2">
        <div class="field">
          <div class="label">Nama Instansi</div>
          <input class="input" name="kop_nama" value="<?= h($kopNama) ?>" />
          <div class="help">Akan tampil paling atas (bold) pada invoice.</div>
        </div>
        <div class="field">
          <div class="label">Kota (opsional)</div>
          <input class="input" name="kop_kota" value="<?= h($kopKota) ?>" />
          <div class="help">Dipakai untuk tempat/tanggal (opsional).</div>
        </div>
      </section>

      <div class="field" style="margin-top:10px">
        <div class="label">Alamat (multi-line)</div>
        <textarea class="input" name="kop_alamat" rows="3" style="resize:vertical"><?= h($kopAlamat) ?></textarea>
        <div class="help">Contoh: Jalan, kelurahan/kecamatan, kota, kode pos.</div>
      </div>

      <div class="field" style="margin-top:10px">
        <div class="label">Kontak</div>
        <input class="input" name="kop_kontak" value="<?= h($kopKontak) ?>" />
        <div class="help">Contoh: +62xxx • email@domain.com</div>
      </div>

      <section class="grid cols-2" style="margin-top:10px">
        <div class="field">
          <div class="label">Penanggung Jawab (opsional)</div>
          <input class="input" name="kop_penanggung_jawab" value="<?= h($kopPj) ?>" />
          <div class="help">Untuk area tanda tangan di invoice.</div>
        </div>
        <div class="field">
          <div class="label">Logo (opsional)</div>
          <input class="input" type="file" name="kop_logo" accept="image/png,image/jpeg,image/webp" />
          <div class="help">PNG/JPG/WEBP. Rekomendasi: PNG background transparan.</div>
        </div>
      </section>

      <?php if ($kopLogo !== ''): ?>
        <div class="hr"></div>
        <div class="field">
          <div class="label">Logo saat ini</div>
          <div style="display:flex;align-items:center;gap:10px">
            <img src="<?= h(app_url($kopLogo)) ?>" alt="Logo" style="height:44px;max-width:220px;object-fit:contain;border:1px solid var(--border);border-radius:10px;padding:6px;background:#fff" />
            <div class="muted mono"><?= h($kopLogo) ?></div>
          </div>
        </div>
      <?php endif; ?>

      <div class="hr"></div>

      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button class="btn" type="reset">Batal</button>
        <button class="btn primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">ID Jamaah</div>
        <div class="card-subtitle">Generate otomatis</div>
      </div>
      <span class="badge success">Fixed</span>
    </div>

    <div class="field">
      <div class="label">Format</div>
      <input class="input mono" value="JMH-{SEQ6}" readonly />
      <div class="help">Urutan 6 digit, contoh: JMH-000001.</div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Contoh ID berikutnya</div>
      <input class="input mono" value="JMH-000004" readonly />
    </div>

    <div class="hr"></div>

    <div class="muted">Pengaturan ID Jamaah tidak dibuka di MVP untuk menjaga konsistensi data.</div>
  </div>
</section>
