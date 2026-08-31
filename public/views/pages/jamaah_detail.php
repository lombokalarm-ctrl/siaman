<section class="split">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Profil Jamaah</div>
        <div class="card-subtitle">Data lengkap (prototype)</div>
      </div>
      <div style="display:flex;gap:8px">
        <?php if ((int)($_GET['id'] ?? 0) > 0): ?>
          <a class="btn" href="<?= h(app_url('/?page=jamaah_edit&id=' . (int)($_GET['id'] ?? 0))) ?>">Edit</a>
        <?php else: ?>
          <span class="btn" style="opacity:.6;pointer-events:none">Edit</span>
        <?php endif; ?>
      </div>
    </div>

    <?php
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

    <?php if (!$jamaah): ?>
      <div class="muted">Data jamaah tidak ditemukan.</div>
    <?php else: ?>
    <div class="row">
      <div class="field">
        <div class="label">ID Jamaah</div>
        <input class="input mono" value="<?= h((string)$jamaah['id_jamaah']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Nomor Pendaftaran</div>
        <input class="input mono" value="<?= h((string)$jamaah['nomor_pendaftaran']) ?>" readonly />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Paket</div>
      <input class="input" value="<?= h((string)($jamaah['paket_nama'] ?? '—')) ?>" readonly />
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Nama Lengkap</div>
        <input class="input" value="<?= h((string)$jamaah['nama_lengkap']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Nama Bapak Kandung</div>
        <input class="input" value="<?= h((string)$jamaah['nama_bapak_kandung']) ?>" readonly />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">NIK</div>
        <input class="input mono" value="<?= h((string)$jamaah['nik']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Nomor KK</div>
        <input class="input mono" value="<?= h((string)$jamaah['nomor_kk']) ?>" readonly />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Tempat Lahir</div>
        <input class="input" value="<?= h((string)$jamaah['tempat_lahir']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Tanggal Lahir</div>
        <input class="input mono" value="<?= h((string)$jamaah['tanggal_lahir']) ?>" readonly />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Jenis Kelamin</div>
        <input class="input" value="<?= h((string)$jamaah['jenis_kelamin']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Status Pernikahan</div>
        <input class="input" value="<?= h((string)$jamaah['status_pernikahan']) ?>" readonly />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Pendidikan</div>
        <input class="input" value="<?= h((string)$jamaah['pendidikan']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Pekerjaan</div>
        <input class="input" value="<?= h((string)$jamaah['pekerjaan']) ?>" readonly />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">No. HP / WA</div>
        <input class="input mono" value="<?= h((string)$jamaah['hp']) ?>" readonly />
      </div>
      <div class="field">
        <div class="label">Email</div>
        <input class="input" value="<?= h((string)($jamaah['email'] ?? '')) ?>" readonly />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Alamat Lengkap</div>
      <textarea class="input" rows="3" readonly><?= h((string)$jamaah['alamat_lengkap']) ?></textarea>
      <div class="help">Saran: validasi minimal dan simpan rapih, tanpa memaksa format.</div>
    </div>

    <div class="hr"></div>

    <div class="card-title">Administrasi Paspor (opsional)</div>
    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">No. Paspor</div>
        <input class="input mono" placeholder="contoh: C1234567" />
      </div>
      <div class="field">
        <div class="label">Masa Berlaku</div>
        <input class="input" placeholder="YYYY-MM-DD" />
        <div class="help">Sesuai preferensi: bisa input manual (tanpa date picker).</div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Invoice</div>
        <div class="card-subtitle">Daftar invoice per jamaah</div>
      </div>
      <?php if ($jamaah): ?>
        <a class="btn primary" href="<?= h(app_url('/?page=invoice_create&jamaah_id=' . (int)$jamaah['id'])) ?>">Buat Invoice</a>
      <?php else: ?>
        <span class="btn primary" style="opacity:.6;pointer-events:none">Buat Invoice</span>
      <?php endif; ?>
    </div>

    <?php if (!$jamaah): ?>
      <div class="muted">Invoice akan muncul setelah jamaah tersedia.</div>
    <?php else: ?>
    <?php
      $invRows = [];
      try {
          $invRows = invoices_by_jamaah((int)$jamaah['id']);
      } catch (Throwable $e) {
          $invRows = [];
      }
    ?>
    <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Status</th>
          <th>Total</th>
          <th>Sisa</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$invRows): ?>
          <tr>
            <td colspan="4" class="muted">Belum ada invoice.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($invRows as $r): ?>
          <?php
            $total = (float)$r['grand_total'];
            $paid = (float)$r['paid_total'];
            $remain = max(0, $total - $paid);
            $status = (string)$r['status'];
          ?>
          <tr>
            <td><a class="mono" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$r['id'])) ?>"><?= h((string)$r['nomor']) ?></a></td>
            <td>
              <?php if ($status === 'paid'): ?>
                <span class="badge success">Paid</span>
              <?php elseif ($status === 'partial'): ?>
                <span class="badge warn">Partial</span>
              <?php else: ?>
                <span class="badge muted"><?= h($status) ?></span>
              <?php endif; ?>
            </td>
            <td class="mono"><?= h(rupiah($total)) ?></td>
            <td class="mono"><?= h(rupiah($remain)) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</section>
