<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Tambah Klien</div>
      <div class="card-subtitle">Data rekan travel (perusahaan)</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=clients')) ?>">Kembali</a>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=client_create')) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="client.create" />

    <div class="row">
      <div class="field">
        <div class="label">Nama Perusahaan</div>
        <input class="input" name="nama_perusahaan" required />
      </div>
      <div class="field">
        <div class="label">Nama PIC</div>
        <input class="input" name="nama_pic" required />
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">No Tlp</div>
        <input class="input mono" name="no_tlp" required />
      </div>
      <div class="field">
        <div class="label">Email</div>
        <input class="input" name="email" required />
      </div>
    </div>

    <div class="field" style="margin-top:10px">
      <div class="label">Alamat</div>
      <textarea class="input" name="alamat" rows="3" required></textarea>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
      <div class="field"></div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
