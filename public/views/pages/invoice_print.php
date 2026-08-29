<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
$invoice = null;
if ($id > 0) {
    try {
        $invoice = invoice_find($id);
    } catch (Throwable $e) {
        $invoice = null;
    }
}

$kop = null;
try {
    $kop = settings_get_invoice_kop();
} catch (Throwable $e) {
    $kop = [
        'nama' => 'Siaman',
        'alamat' => '',
        'kontak' => '',
        'kota' => '',
        'penanggung_jawab' => '',
        'logo_path' => '',
    ];
}

$printTime = (new DateTimeImmutable('now'))->format('Y-m-d H:i');

$kopNama = trim((string)($kop['nama'] ?? 'Siaman'));
$kopAlamat = trim((string)($kop['alamat'] ?? ''));
$kopKontak = trim((string)($kop['kontak'] ?? ''));
$kopKota = trim((string)($kop['kota'] ?? ''));
$kopPj = trim((string)($kop['penanggung_jawab'] ?? ''));
$kopLogo = trim((string)($kop['logo_path'] ?? ''));
$bankAccounts = bank_accounts_all();

$titleDoc = $invoice ? ('Invoice ' . (string)$invoice['nomor']) : 'Invoice';

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= h($titleDoc) ?> — Siaman</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= h(app_url('/assets/app.css')) ?>" />
  </head>
  <body style="background:#fff">
    <div class="invoice-page">
      <section class="invoice-paper">
        <?php if (!$invoice): ?>
          <div class="card">
            <div class="muted">Invoice tidak ditemukan.</div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
              <a class="btn" href="<?= h(app_url('/?page=invoice')) ?>">Kembali</a>
            </div>
          </div>
        <?php else: ?>
          <header class="invoice-head">
            <div class="invoice-head-left">
              <?php if ($kopLogo !== ''): ?>
                <img class="invoice-logo" src="<?= h(app_url($kopLogo)) ?>" alt="Logo" />
              <?php else: ?>
                <div class="invoice-logo placeholder"></div>
              <?php endif; ?>
            </div>
            <div class="invoice-head-right">
              <div class="invoice-company"><?= h($kopNama !== '' ? $kopNama : 'Siaman') ?></div>
              <?php if ($kopAlamat !== ''): ?>
                <div class="invoice-meta"><?= nl2br(h($kopAlamat)) ?></div>
              <?php endif; ?>
              <?php if ($kopKontak !== ''): ?>
                <div class="invoice-meta"><?= h($kopKontak) ?></div>
              <?php endif; ?>
            </div>
          </header>

          <div class="invoice-divider"></div>

          <section class="invoice-top">
            <div>
              <div class="invoice-title">INVOICE</div>
              <div class="invoice-no mono"><?= h((string)$invoice['nomor']) ?></div>
            </div>
            <div class="invoice-kv">
              <div class="row">
                <div class="label">Tanggal</div>
                <div class="mono"><?= h((string)$invoice['tanggal']) ?></div>
              </div>
              <div class="row">
                <div class="label">Status</div>
                <div class="mono"><?= h((string)$invoice['status']) ?></div>
              </div>
            </div>
          </section>

          <div class="invoice-divider"></div>

          <section class="invoice-bill">
            <div class="invoice-bill-card">
              <div class="label">Ditagihkan Kepada</div>
              <?php if ((string)($invoice['target_type'] ?? '') === 'client'): ?>
                <div class="invoice-strong" style="margin-top:6px"><?= h((string)$invoice['client_perusahaan']) ?></div>
                <div class="muted" style="margin-top:2px">PIC: <span class="mono"><?= h((string)$invoice['client_pic']) ?></span></div>
                <div class="muted mono" style="margin-top:2px"><?= h((string)$invoice['client_tlp']) ?><?= $invoice['client_email'] ? ' • ' . h((string)$invoice['client_email']) : '' ?></div>
                <div class="muted" style="margin-top:2px"><?= h((string)$invoice['client_alamat']) ?></div>
              <?php else: ?>
                <div class="invoice-strong" style="margin-top:6px"><?= h((string)$invoice['jamaah_nama']) ?></div>
                <div class="muted mono" style="margin-top:2px"><?= h((string)$invoice['jamaah_kode']) ?> • <?= h((string)$invoice['jamaah_daftar']) ?></div>
                <div class="muted mono" style="margin-top:2px"><?= h((string)$invoice['jamaah_hp']) ?></div>
              <?php endif; ?>
            </div>
            <div class="invoice-bill-card">
              <div class="label">Paket</div>
              <div class="invoice-strong" style="margin-top:6px"><?= $invoice['paket_nama'] ? h((string)$invoice['paket_nama']) : '—' ?></div>
              <?php if ($invoice['notes']): ?>
                <div class="muted" style="margin-top:8px"><?= h((string)$invoice['notes']) ?></div>
              <?php endif; ?>
            </div>
          </section>

          <div class="invoice-divider"></div>

          <table class="invoice-table">
            <thead>
              <tr>
                <th style="text-align:left">Item</th>
                <th style="width:80px">Qty</th>
                <th style="width:140px">Harga</th>
                <th style="width:140px">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($invoice['items'] as $it): ?>
                <tr>
                  <td><?= h((string)$it['label']) ?></td>
                  <td class="mono" style="text-align:right"><?= h((string)$it['qty']) ?></td>
                  <td class="mono" style="text-align:right"><?= h(rupiah((string)$it['price'])) ?></td>
                  <td class="mono" style="text-align:right"><?= h(rupiah((string)$it['total'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <section class="invoice-totals">
            <div>
              <?php if ($bankAccounts): ?>
                <div class="invoice-totals-box">
                  <div class="muted" style="font-weight:700">Rekening Pembayaran</div>
                  <div class="invoice-divider" style="margin:8px 0"></div>
                  <?php foreach ($bankAccounts as $b): ?>
                    <div style="margin-top:8px">
                      <div style="font-weight:700"><?= h((string)$b['bank_nama']) ?></div>
                      <div class="mono"><?= h((string)$b['no_rekening']) ?></div>
                      <div class="muted"><?= h((string)$b['nama_rekening']) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="invoice-totals-box">
              <div class="invoice-totals-row">
                <div class="muted">Subtotal</div>
                <div class="mono"><?= h(rupiah((string)$invoice['subtotal'])) ?></div>
              </div>
              <div class="invoice-totals-row">
                <div class="muted">Diskon</div>
                <div class="mono"><?= h(rupiah((string)$invoice['diskon'])) ?></div>
              </div>
              <div class="invoice-totals-row">
                <div class="muted">Pajak</div>
                <div class="mono"><?= h(rupiah((string)$invoice['pajak'])) ?></div>
              </div>
              <div class="invoice-totals-row grand">
                <div>Grand Total</div>
                <div class="mono"><?= h(rupiah((string)$invoice['grand_total'])) ?></div>
              </div>
              <div class="invoice-divider" style="margin:10px 0"></div>
              <div class="invoice-totals-row">
                <div class="muted">Sudah dibayar</div>
                <div class="mono"><?= h(rupiah((float)$invoice['paid_total'])) ?></div>
              </div>
              <div class="invoice-totals-row">
                <div class="muted">Sisa</div>
                <div class="mono"><?= h(rupiah((float)$invoice['remaining_total'])) ?></div>
              </div>
            </div>
          </section>

          <?php if ($invoice['payments']): ?>
            <div class="invoice-divider"></div>
            <div class="invoice-section-title">Histori Pembayaran</div>
            <table class="invoice-table small">
              <thead>
                <tr>
                  <th style="width:120px">Tanggal</th>
                  <th>No Kuitansi</th>
                  <th style="width:110px">Metode</th>
                  <th style="width:140px">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($invoice['payments'] as $p): ?>
                  <?php if (payments_void_supported() && isset($p['voided_at']) && $p['voided_at']) continue; ?>
                  <tr>
                    <td class="mono"><?= h((string)$p['tanggal']) ?></td>
                    <td class="mono"><?= h((string)$p['nomor_kuitansi']) ?></td>
                    <td><?= h((string)$p['metode']) ?></td>
                    <td class="mono" style="text-align:right"><?= h(rupiah((string)$p['amount'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <div class="invoice-divider"></div>

          <footer class="invoice-foot">
            <div class="muted">
              <?php if ($kopKota !== ''): ?>
                <?= h($kopKota) ?>, <?= h((string)$invoice['tanggal']) ?>
              <?php else: ?>
                Tanggal: <span class="mono"><?= h((string)$invoice['tanggal']) ?></span>
              <?php endif; ?>
              <div style="margin-top:4px">Waktu cetak: <span class="mono"><?= h($printTime) ?></span></div>
            </div>
            <div class="invoice-sign">
              <div class="muted" style="text-align:center">Hormat kami</div>
              <div class="invoice-sign-line"></div>
              <div class="invoice-strong" style="text-align:center"><?= $kopPj !== '' ? h($kopPj) : ' ' ?></div>
            </div>
          </footer>

          <div class="invoice-actions no-print">
            <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$invoice['id'])) ?>">Kembali</a>
            <button class="btn primary" onclick="window.print()">Cetak / Save as PDF</button>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </body>
</html>
