<section class="split">
  <div class="card">
    <div class="card-header">
      <?php
      $id = (int)($_GET['id'] ?? 0);
      $invoice = null;
      $voidSupported = false;
      $canVoid = false;
      $canPaymentCreate = auth_can_do_action('payment.create');
      if ($id > 0) {
          try {
              $invoice = invoice_find($id);
          } catch (Throwable $e) {
              $invoice = null;
          }
      }
      $voidSupported = payments_void_supported();
      $canVoid = $voidSupported && auth_can_access_page('payment_void');
      ?>
      <div>
        <div class="card-title">Invoice <span class="mono"><?= $invoice ? h((string)$invoice['nomor']) : '—' ?></span></div>
        <?php if ($invoice): ?>
          <div class="card-subtitle">
            <?= (string)($invoice['target_type'] ?? '') === 'client' ? 'Klien' : 'Jamaah' ?>:
            <?php if ((string)($invoice['target_type'] ?? '') === 'jamaah' && auth_can_access_page('jamaah_detail')): ?>
              <a href="<?= h(app_url('/?page=jamaah_detail&id=' . (int)$invoice['jamaah_id'])) ?>"><?= h((string)$invoice['target_nama']) ?></a>
            <?php else: ?>
              <?= h((string)$invoice['target_nama']) ?>
            <?php endif; ?>
            • Paket: <?= $invoice['paket_nama'] ? h((string)$invoice['paket_nama']) : '—' ?>
          </div>
        <?php else: ?>
          <div class="card-subtitle">Invoice tidak ditemukan.</div>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:8px">
        <?php if ($invoice): ?>
          <a class="btn" href="<?= h(app_url('/?page=invoice_print&id=' . (int)$invoice['id'])) ?>" target="_blank" rel="noopener">Cetak / PDF</a>
          <a class="btn" href="<?= h(app_url('/?page=invoice_pdf&id=' . (int)$invoice['id'])) ?>" target="_blank" rel="noopener">PDF (dompdf)</a>
          <?php if (auth_is_admin()): ?>
            <form method="post" action="<?= h(app_url('/?page=invoice_detail&id=' . (int)$invoice['id'])) ?>" onsubmit="return confirm('Hapus invoice ini? Pembayaran & item akan ikut terhapus.');">
              <?= csrf_input() ?>
              <input type="hidden" name="_action" value="invoice.delete" />
              <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>" />
              <button class="btn danger" type="submit">Hapus</button>
            </form>
          <?php endif; ?>
        <?php endif; ?>
        <a class="btn" href="<?= h(app_url('/?page=invoice')) ?>">Kembali</a>
      </div>
    </div>

    <?php if (!$invoice): ?>
      <div class="muted">Pastikan parameter id tersedia, contoh: /?page=invoice_detail&id=1</div>
    <?php else: ?>
    <section class="grid cols-3">
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((string)$invoice['grand_total'])) ?></div>
          <div class="kpi-label">Total tagihan</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((float)$invoice['paid_total'])) ?></div>
          <div class="kpi-label">Sudah dibayar</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= h(rupiah((float)$invoice['remaining_total'])) ?></div>
          <div class="kpi-label">Sisa</div>
        </div>
      </div>
    </section>

    <div class="hr"></div>

    <div class="card-title">Item</div>
    <div class="card-subtitle">Rincian item pada invoice</div>
    <div style="margin-top:10px">
      <table class="table">
        <thead>
          <tr>
            <th>Label</th>
            <th style="width:110px">Qty</th>
            <th style="width:160px">Harga</th>
            <th style="width:160px">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoice['items'] as $it): ?>
            <tr>
              <td><?= h((string)$it['label']) ?></td>
              <td class="mono"><?= h((string)$it['qty']) ?></td>
              <td class="mono"><?= h(rupiah((string)$it['price'])) ?></td>
              <td class="mono"><?= h(rupiah((string)$it['total'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:650">Subtotal</td>
            <td class="mono"><?= h(rupiah((string)$invoice['subtotal'])) ?></td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:650">Diskon</td>
            <td class="mono"><?= h(rupiah((string)$invoice['diskon'])) ?></td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:650">Pajak</td>
            <td class="mono"><?= h(rupiah((string)$invoice['pajak'])) ?></td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:750">Grand Total</td>
            <td class="mono" style="font-weight:750"><?= h(rupiah((string)$invoice['grand_total'])) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="hr"></div>

    <div class="card-title">Histori Pembayaran</div>
    <div class="card-subtitle">Mendukung pembayaran bertahap (multi payment)</div>

    <div style="margin-top:10px">
      <table class="table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>No Kuitansi</th>
            <th>Metode</th>
            <th>Jumlah</th>
            <?php if ($canVoid): ?>
              <th>Status</th>
            <?php endif; ?>
            <th>Pengirim</th>
            <?php if ($canVoid): ?>
              <th style="width:120px">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (!$invoice['payments']): ?>
            <tr>
              <td colspan="<?= $canVoid ? 7 : 5 ?>" class="muted">Belum ada pembayaran.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($invoice['payments'] as $p): ?>
            <?php $isVoid = $voidSupported && isset($p['voided_at']) && $p['voided_at']; ?>
            <tr>
              <td class="mono"><?= h((string)$p['tanggal']) ?></td>
              <td>
                <?php if (auth_can_access_page('kuitansi_detail')): ?>
                  <a class="mono" href="<?= h(app_url('/?page=kuitansi_detail&id=' . (int)$p['id'])) ?>"><?= h((string)$p['nomor_kuitansi']) ?></a>
                <?php else: ?>
                  <span class="mono"><?= h((string)$p['nomor_kuitansi']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= h((string)$p['metode']) ?></td>
              <td class="mono"><?= h(rupiah((string)$p['amount'])) ?></td>
              <?php if ($canVoid): ?>
                <td>
                  <?php if ($isVoid): ?>
                    <span class="badge danger">VOID</span>
                  <?php else: ?>
                    <span class="badge success">OK</span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
              <td>
                <div class="mono"><?= h((string)($p['pengirim'] ?? '')) ?></div>
                <div class="sub">Outlet: <span class="mono"><?= h((string)($p['outlet'] ?? '')) ?></span> • Sales: <span class="mono"><?= h((string)($p['sales'] ?? '')) ?></span></div>
                <?php if ($isVoid && isset($p['void_reason']) && $p['void_reason']): ?>
                  <div class="sub">Alasan: <?= h((string)$p['void_reason']) ?></div>
                <?php endif; ?>
              </td>
              <?php if ($canVoid): ?>
                <td>
                  <?php if ($isVoid): ?>
                    <span class="muted">—</span>
                  <?php else: ?>
                    <a class="btn danger" href="<?= h(app_url('/?page=payment_void&id=' . (int)$p['id'])) ?>">Void</a>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Tambah Pembayaran</div>
        <div class="card-subtitle">Normalisasi lowercase: Pengirim/Outlet/Sales</div>
      </div>
    </div>

    <?php if (!$invoice): ?>
      <div class="muted">Pilih invoice terlebih dahulu.</div>
    <?php elseif (!$canPaymentCreate): ?>
      <div class="muted">Anda tidak memiliki akses untuk menambah pembayaran.</div>
    <?php else: ?>
      <?php $today = (new DateTimeImmutable('now'))->format('Y-m-d'); ?>
      <form method="post" action="<?= h(app_url('/?page=invoice_detail&id=' . (int)$invoice['id'])) ?>">
        <?= csrf_input() ?>
        <input type="hidden" name="_action" value="payment.create" />
        <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>" />

        <div class="field">
          <div class="label">Tanggal</div>
          <input class="input mono" name="tanggal" placeholder="YYYY-MM-DD" value="<?= h($today) ?>" />
          <div class="help">Input manual, tanpa date picker.</div>
        </div>

        <div class="field" style="margin-top:10px">
          <div class="label">Metode</div>
          <input class="input" name="metode" placeholder="cash / transfer / edc" data-lowercase="true" />
        </div>

        <div class="field" style="margin-top:10px">
          <div class="label">Jumlah</div>
          <input class="input mono" name="amount" placeholder="contoh: 5000000" />
        </div>

        <div class="row" style="margin-top:10px">
          <div class="field">
            <div class="label">Pengirim</div>
            <input class="input" name="pengirim" placeholder="nama pengirim" data-lowercase="true" />
          </div>
          <div class="field">
            <div class="label">Outlet</div>
            <input class="input" name="outlet" placeholder="outlet" data-lowercase="true" />
          </div>
        </div>

        <div class="field" style="margin-top:10px">
          <div class="label">Sales</div>
          <input class="input" name="sales" placeholder="sales" data-lowercase="true" />
        </div>

        <div class="field" style="margin-top:10px">
          <div class="label">Referensi Transfer (opsional)</div>
          <input class="input mono" name="reference" placeholder="contoh: TRX123456" />
        </div>

        <div class="hr"></div>

        <div style="display:flex;gap:8px;justify-content:flex-end">
          <button class="btn" type="reset">Reset</button>
          <button class="btn primary" type="submit">Simpan Pembayaran</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>
