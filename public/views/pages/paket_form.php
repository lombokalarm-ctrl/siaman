<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Tambah Paket</div>
      <div class="card-subtitle">Paket menjadi referensi untuk pembuatan invoice</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=paket')) ?>">Kembali</a>
  </div>

  <form method="post" action="<?= h(app_url('/?page=paket_create')) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="paket.create" />

    <div class="row">
      <div class="field">
        <div class="label">Nama Paket</div>
        <input class="input" name="nama" required />
      </div>
      <div class="field">
        <div class="label">Kode Paket (opsional)</div>
        <input class="input mono" name="kode" placeholder="contoh: REG-12H" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Durasi (hari)</div>
        <input class="input mono" name="durasi_hari" inputmode="numeric" placeholder="contoh: 12" required />
      </div>
      <div class="field">
        <div class="label">Tanggal Berangkat (opsional)</div>
        <input class="input mono" name="tanggal_berangkat" placeholder="YYYY-MM-DD" />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Harga</div>
        <input class="input mono" name="harga" inputmode="decimal" placeholder="contoh: 35000000" required />
      </div>
      <div class="field">
        <div class="label">Mata Uang</div>
        <input class="input mono" name="currency" value="IDR" required />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Deskripsi (opsional)</div>
      <textarea class="input" name="deskripsi" rows="3"></textarea>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
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
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
