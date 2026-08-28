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

if (!$invoice) {
    http_response_code(404);
    echo 'Invoice tidak ditemukan.';
    exit;
}

if (!dompdf_available()) {
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="id">
      <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>PDF belum siap</title></head>
      <body style="font-family:Arial,sans-serif;font-size:14px">
        <h3>Export PDF (dompdf) belum siap</h3>
        <p>Dependency dompdf belum terpasang. Jalankan:</p>
        <pre>composer install</pre>
        <p>Atau sementara gunakan cetak browser: <a href="<?= h(app_url('/?page=invoice_print&id=' . (int)$invoice['id'])) ?>">Invoice Print</a></p>
      </body>
    </html>
    <?php
    exit;
}

$kop = settings_get_invoice_kop();
$logoData = image_data_uri_from_public_path((string)($kop['logo_path'] ?? ''));
$company = trim((string)($kop['nama'] ?? 'Siaman'));
$alamat = trim((string)($kop['alamat'] ?? ''));
$kontak = trim((string)($kop['kontak'] ?? ''));
$kota = trim((string)($kop['kota'] ?? ''));
$pj = trim((string)($kop['penanggung_jawab'] ?? ''));

$items = $invoice['items'] ?? [];
$payments = $invoice['payments'] ?? [];
$voidSupported = payments_void_supported();
$paymentsClean = [];
foreach ($payments as $p) {
    if ($voidSupported && isset($p['voided_at']) && $p['voided_at']) {
        continue;
    }
    $paymentsClean[] = $p;
}

$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
  *{box-sizing:border-box}
  body{font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#101828}
  .wrap{padding:18px}
  .head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
  .logo{width:160px;height:64px;object-fit:contain}
  .company{font-size:16px;font-weight:700;text-align:right}
  .meta{color:#475467;margin-top:4px;line-height:1.35;text-align:right}
  .hr{height:1px;background:#e4e7ec;margin:12px 0}
  .top{display:flex;justify-content:space-between;gap:12px}
  .title{font-size:14px;font-weight:800;letter-spacing:.6px}
  .mono{font-variant-numeric:tabular-nums}
  table{width:100%;border-collapse:collapse}
  th,td{border-bottom:1px solid #e4e7ec;padding:8px 6px;vertical-align:top}
  th{background:#f8fafc;text-align:left}
  .right{text-align:right}
  .totals{width:320px;margin-left:auto;border:1px solid #e4e7ec;border-radius:8px;padding:10px}
  .row{display:flex;justify-content:space-between;gap:10px;margin-top:6px}
  .row:first-child{margin-top:0}
  .grand{font-weight:800;margin-top:10px}
  .foot{display:flex;justify-content:space-between;gap:12px;align-items:flex-end;margin-top:14px}
  .sign{width:220px;text-align:center}
  .line{height:34px;border-bottom:1px solid #e4e7ec;margin-top:10px}
</style></head><body><div class="wrap">';

$html .= '<div class="head"><div>';
if ($logoData) {
    $html .= '<img class="logo" src="' . h($logoData) . '" alt="Logo" />';
}
$html .= '</div><div>';
$html .= '<div class="company">' . h($company !== '' ? $company : 'Siaman') . '</div>';
if ($alamat !== '') $html .= '<div class="meta">' . nl2br(h($alamat)) . '</div>';
if ($kontak !== '') $html .= '<div class="meta">' . h($kontak) . '</div>';
$html .= '</div></div>';

$html .= '<div class="hr"></div>';

$html .= '<div class="top"><div><div class="title">INVOICE</div><div class="mono" style="margin-top:4px">' . h((string)$invoice['nomor']) . '</div></div>';
$html .= '<div class="mono right"><div><span style="color:#475467">Tanggal</span> ' . h((string)$invoice['tanggal']) . '</div><div style="margin-top:4px"><span style="color:#475467">Status</span> ' . h((string)$invoice['status']) . '</div></div></div>';

$html .= '<div class="hr"></div>';

$html .= '<table><tbody>';
$billTo = '';
if ((string)($invoice['target_type'] ?? '') === 'client') {
    $billTo .= '<div style="color:#475467">Ditagihkan Kepada</div>';
    $billTo .= '<div style="font-weight:700;margin-top:6px">' . h((string)($invoice['client_perusahaan'] ?? '')) . '</div>';
    $billTo .= '<div style="color:#475467;margin-top:2px">PIC: <span class="mono">' . h((string)($invoice['client_pic'] ?? '')) . '</span></div>';
    $billTo .= '<div class="mono" style="color:#475467;margin-top:2px">' . h((string)($invoice['client_tlp'] ?? '')) . ($invoice['client_email'] ? ' • ' . h((string)$invoice['client_email']) : '') . '</div>';
    $billTo .= '<div style="color:#475467;margin-top:2px">' . nl2br(h((string)($invoice['client_alamat'] ?? ''))) . '</div>';
} else {
    $billTo .= '<div style="color:#475467">Ditagihkan Kepada</div>';
    $billTo .= '<div style="font-weight:700;margin-top:6px">' . h((string)$invoice['jamaah_nama']) . '</div>';
    $billTo .= '<div class="mono" style="color:#475467;margin-top:2px">' . h((string)$invoice['jamaah_kode']) . ' • ' . h((string)$invoice['jamaah_daftar']) . '</div>';
    $billTo .= '<div class="mono" style="color:#475467;margin-top:2px">' . h((string)$invoice['jamaah_hp']) . '</div>';
}
$html .= '<tr><td style="width:50%">' . $billTo . '</td>';
$html .= '<td><div style="color:#475467">Paket</div><div style="font-weight:700;margin-top:6px">' . ($invoice['paket_nama'] ? h((string)$invoice['paket_nama']) : '—') . '</div>';
if ($invoice['notes']) $html .= '<div style="color:#475467;margin-top:6px">' . h((string)$invoice['notes']) . '</div>';
$html .= '</td></tr></tbody></table>';

$html .= '<div class="hr"></div>';

$html .= '<table><thead><tr><th>Item</th><th class="right" style="width:70px">Qty</th><th class="right" style="width:120px">Harga</th><th class="right" style="width:120px">Total</th></tr></thead><tbody>';
foreach ($items as $it) {
    $html .= '<tr><td>' . h((string)$it['label']) . '</td><td class="right mono">' . h((string)$it['qty']) . '</td><td class="right mono">' . h(rupiah((string)$it['price'])) . '</td><td class="right mono">' . h(rupiah((string)$it['total'])) . '</td></tr>';
}
$html .= '</tbody></table>';

$html .= '<div style="margin-top:12px" class="totals">';
$html .= '<div class="row"><div style="color:#475467">Subtotal</div><div class="mono">' . h(rupiah((string)$invoice['subtotal'])) . '</div></div>';
$html .= '<div class="row"><div style="color:#475467">Diskon</div><div class="mono">' . h(rupiah((string)$invoice['diskon'])) . '</div></div>';
$html .= '<div class="row"><div style="color:#475467">Pajak</div><div class="mono">' . h(rupiah((string)$invoice['pajak'])) . '</div></div>';
$html .= '<div class="row grand"><div>Grand Total</div><div class="mono">' . h(rupiah((string)$invoice['grand_total'])) . '</div></div>';
$html .= '<div class="hr" style="margin:10px 0"></div>';
$html .= '<div class="row"><div style="color:#475467">Sudah dibayar</div><div class="mono">' . h(rupiah((float)$invoice['paid_total'])) . '</div></div>';
$html .= '<div class="row"><div style="color:#475467">Sisa</div><div class="mono">' . h(rupiah((float)$invoice['remaining_total'])) . '</div></div>';
$html .= '</div>';

if ($paymentsClean) {
    $html .= '<div class="hr"></div><div style="font-weight:800;margin:6px 0">Histori Pembayaran</div>';
    $html .= '<table><thead><tr><th style="width:90px">Tanggal</th><th>No Kuitansi</th><th style="width:90px">Metode</th><th class="right" style="width:120px">Jumlah</th></tr></thead><tbody>';
    foreach ($paymentsClean as $p) {
        $html .= '<tr><td class="mono">' . h((string)$p['tanggal']) . '</td><td class="mono">' . h((string)$p['nomor_kuitansi']) . '</td><td>' . h((string)$p['metode']) . '</td><td class="right mono">' . h(rupiah((string)$p['amount'])) . '</td></tr>';
    }
    $html .= '</tbody></table>';
}

$html .= '<div class="foot"><div style="color:#475467">';
if ($kota !== '') {
    $html .= h($kota) . ', ' . h((string)$invoice['tanggal']);
} else {
    $html .= 'Tanggal: <span class="mono">' . h((string)$invoice['tanggal']) . '</span>';
}
$html .= '</div><div class="sign"><div style="color:#475467">Hormat kami</div><div class="line"></div><div style="font-weight:700">' . h($pj) . '</div></div></div>';

$html .= '</div></body></html>';

pdf_stream($html, 'invoice-' . (string)$invoice['nomor'] . '.pdf', 'A4', 'portrait');
