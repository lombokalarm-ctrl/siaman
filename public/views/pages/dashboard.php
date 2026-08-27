<section class="grid cols-3">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Jamaah</div>
        <div class="card-subtitle">Total terdaftar</div>
      </div>
      <span class="badge muted">MVP</span>
    </div>
    <div class="kpi">
      <div class="kpi-value mono">128</div>
      <div class="kpi-label">Aktif 120 • Nonaktif 8</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Invoice</div>
        <div class="card-subtitle">Status outstanding</div>
      </div>
      <span class="badge warn">Partial</span>
    </div>
    <div class="kpi">
      <div class="kpi-value mono">34</div>
      <div class="kpi-label">Unpaid 19 • Partial 15</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Pembayaran</div>
        <div class="card-subtitle">Hari ini</div>
      </div>
      <span class="badge success">Masuk</span>
    </div>
    <div class="kpi">
      <div class="kpi-value mono">Rp 42.500.000</div>
      <div class="kpi-label">8 transaksi • 3 kuitansi dicetak</div>
    </div>
  </div>
</section>

<section class="grid cols-2">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Aktivitas Terakhir</div>
        <div class="card-subtitle">Contoh timeline internal</div>
      </div>
      <button class="btn">Lihat semua</button>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Aktivitas</th>
          <th>Referensi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="mono">10:42</td>
          <td>
            Pembayaran diterima
            <div class="sub">Transfer • Rp 5.000.000</div>
          </td>
          <td><a href="<?= h(app_url('/?page=invoice_detail')) ?>">INV-2026-00081</a></td>
        </tr>
        <tr>
          <td class="mono">09:05</td>
          <td>
            Invoice dibuat
            <div class="sub">Paket: Umroh Reguler 12H</div>
          </td>
          <td><a href="<?= h(app_url('/?page=jamaah_detail')) ?>">JMH-000212</a></td>
        </tr>
        <tr>
          <td class="mono">08:10</td>
          <td>
            Jamaah ditambahkan
            <div class="sub">Input manual via form</div>
          </td>
          <td><a href="<?= h(app_url('/?page=jamaah_detail')) ?>">JMH-000213</a></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Outstanding Prioritas</div>
        <div class="card-subtitle">Invoice jatuh tempo dekat</div>
      </div>
      <button class="btn primary">Buat invoice</button>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Jamaah</th>
          <th>Status</th>
          <th>Sisa</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            Ahmad F.
            <div class="sub">Umroh Reguler 12H</div>
          </td>
          <td><span class="badge warn">Partial</span></td>
          <td class="mono">Rp 8.000.000</td>
        </tr>
        <tr>
          <td>
            Siti N.
            <div class="sub">Umroh Plus Turki</div>
          </td>
          <td><span class="badge muted">Unpaid</span></td>
          <td class="mono">Rp 35.000.000</td>
        </tr>
        <tr>
          <td>
            Budi R.
            <div class="sub">Umroh Ramadhan</div>
          </td>
          <td><span class="badge muted">Unpaid</span></td>
          <td class="mono">Rp 12.500.000</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>
