<?php

declare(strict_types=1);

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Import Jamaah (CSV)</div>
      <div class="card-subtitle">Gunakan template agar kolom sesuai</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=jamaah')) ?>">Kembali</a>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=jamaah_import')) ?>" enctype="multipart/form-data">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="jamaah.import" />

    <div class="grid cols-2">
      <div class="field">
        <div class="label">File CSV</div>
        <input class="input" type="file" name="file" accept=".csv,text/csv" required />
        <div class="help">Tanggal lahir: YYYY-MM-DD</div>
      </div>
      <div class="field">
        <div class="label">Template</div>
        <a class="btn" href="<?= h(app_url('/assets/jamaah_import_template.csv')) ?>">Unduh template</a>
        <div class="help">Duplikat NIK akan dilewati</div>
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Import</button>
    </div>
  </form>
</section>
