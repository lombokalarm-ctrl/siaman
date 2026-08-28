<?php

declare(strict_types=1);

$id = (int)($_GET['id'] ?? 0);
$payment = null;
if ($id > 0) {
    try {
        $payment = payment_find($id);
    } catch (Throwable $e) {
        $payment = null;
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
$kopLogo = trim((string)($kop['logo_path'] ?? ''));

$titleDoc = $payment ? ('Kuitansi ' . (string)$payment['nomor_kuitansi']) : 'Kuitansi';
$isVoid = $payment && payments_void_supported() && isset($payment['voided_at']) && $payment['voided_at'];

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= h($titleDoc) ?> — Siaman</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= h(app_url('/assets/app.css')) ?>" />
    <style>
      @media print{
        @page{size:A5 portrait;margin:8mm}
      }
    </style>
  </head>
  <body style="background:#fff">
    <div class="kuitansi-page">
      <section class="kuitansi-a5">
        <?php if (!$payment): ?>
          <div class="card">
            <div class="muted">Kuitansi tidak ditemukan.</div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
              <a class="btn" href="<?= h(app_url('/?page=kuitansi')) ?>">Kembali</a>
            </div>
          </div>
        <?php else: ?>
          <article class="kuitansi-sheet">
            <header class="kuitansi-head">
              <div class="kuitansi-head-left">
                <?php if ($kopLogo !== ''): ?>
                  <img class="kuitansi-logo" src="<?= h(app_url($kopLogo)) ?>" alt="Logo" />
                <?php else: ?>
                  <div class="kuitansi-logo placeholder"></div>
                <?php endif; ?>
              </div>
              <div class="kuitansi-head-right">
                <div class="kuitansi-company"><?= h($kopNama !== '' ? $kopNama : 'Siaman') ?></div>
                <?php if ($kopAlamat !== ''): ?>
                  <div class="kuitansi-meta"><?= nl2br(h($kopAlamat)) ?></div>
                <?php endif; ?>
                <?php if ($kopKontak !== ''): ?>
                  <div class="kuitansi-meta"><?= h($kopKontak) ?></div>
                <?php endif; ?>
              </div>
            </header>

            <div class="kuitansi-divider"></div>

            <section class="kuitansi-top">
              <div>
                <div class="kuitansi-title">KWITANSI PEMBAYARAN</div>
                <div class="muted" style="margin-top:3px"><?= $isVoid ? 'VOID' : 'Bukti pembayaran' ?></div>
              </div>
              <div style="text-align:right">
                <div class="mono" style="font-weight:800"><?= h((string)$payment['nomor_kuitansi']) ?></div>
                <div class="muted" style="margin-top:3px"><?= h((string)$payment['tanggal']) ?></div>
              </div>
            </section>

            <div class="kuitansi-divider"></div>

            <section class="kuitansi-kv">
              <div class="kuitansi-kv-row">
                <div class="label">Pelanggan</div>
                <div>
                  <?php if ((string)($payment['target_type'] ?? '') === 'client'): ?>
                    <div class="kuitansi-strong"><?= h((string)$payment['client_perusahaan']) ?></div>
                    <div class="muted">PIC: <span class="mono"><?= h((string)$payment['client_pic']) ?></span></div>
                    <div class="muted mono"><?= h((string)$payment['client_tlp']) ?><?= $payment['client_email'] ? ' • ' . h((string)$payment['client_email']) : '' ?></div>
                  <?php else: ?>
                    <div class="kuitansi-strong"><?= h((string)$payment['jamaah_nama']) ?></div>
                    <div class="muted mono"><?= h((string)$payment['jamaah_hp']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="kuitansi-kv-row">
                <div class="label">Invoice</div>
                <div>
                  <div class="mono" style="font-weight:750"><?= h((string)$payment['invoice_nomor']) ?></div>
                  <div class="muted">Paket: <?= $payment['paket_nama'] ? h((string)$payment['paket_nama']) : '—' ?></div>
                </div>
              </div>
              <div class="kuitansi-kv-row">
                <div class="label">Metode</div>
                <div><?= h((string)$payment['metode']) ?></div>
              </div>
            </section>

            <div class="kuitansi-divider"></div>

            <section class="kuitansi-amount">
              <div class="label">Jumlah dibayar</div>
              <div class="kuitansi-amount-value mono"><?= h(rupiah((string)$payment['amount'])) ?></div>
            </section>
            <div class="muted" style="margin-top:6px">Terbilang: <span style="font-weight:650"><?= h(terbilang_rupiah((string)$payment['amount'])) ?></span></div>

            <div class="kuitansi-divider"></div>

            <?php if ($isVoid && isset($payment['void_reason']) && $payment['void_reason']): ?>
              <div class="muted">Alasan void: <span style="font-weight:650"><?= h((string)$payment['void_reason']) ?></span></div>
              <div class="kuitansi-divider"></div>
            <?php endif; ?>

            <table class="kuitansi-table">
              <tbody>
                <tr>
                  <td class="muted">Pengirim</td>
                  <td class="mono" style="text-align:right"><?= $payment['pengirim'] ? h((string)$payment['pengirim']) : '—' ?></td>
                </tr>
                <tr>
                  <td class="muted">Outlet</td>
                  <td class="mono" style="text-align:right"><?= $payment['outlet'] ? h((string)$payment['outlet']) : '—' ?></td>
                </tr>
                <tr>
                  <td class="muted">Sales</td>
                  <td class="mono" style="text-align:right"><?= $payment['sales'] ? h((string)$payment['sales']) : '—' ?></td>
                </tr>
                <tr>
                  <td class="muted">Referensi</td>
                  <td class="mono" style="text-align:right"><?= $payment['reference'] ? h((string)$payment['reference']) : '—' ?></td>
                </tr>
              </tbody>
            </table>

            <footer class="kuitansi-foot">
              <div class="muted">Waktu cetak: <span class="mono"><?= h($printTime) ?></span></div>
              <div class="kuitansi-sign">
                <div class="muted" style="text-align:center">Tanda tangan</div>
                <div class="kuitansi-sign-line"></div>
              </div>
            </footer>
          </article>

          <div class="kuitansi-actions no-print">
            <a class="btn" href="<?= h(app_url('/?page=invoice_detail&id=' . (int)$payment['invoice_id'])) ?>">Kembali</a>
            <button class="btn primary" onclick="window.print()">Cetak / Save as PDF</button>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </body>
</html>
