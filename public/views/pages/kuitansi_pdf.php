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

if (!$payment) {
    http_response_code(404);
    echo 'Kuitansi tidak ditemukan.';
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
        <p>Atau sementara gunakan cetak browser: <a href="<?= h(app_url('/?page=kuitansi_print&id=' . (int)$payment['id'])) ?>">Kuitansi Print</a></p>
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

$isVoid = payments_void_supported() && isset($payment['voided_at']) && $payment['voided_at'];

$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
  *{box-sizing:border-box}
  body{font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#101828}
  .wrap{padding:14px}
  .head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
  .logo{width:140px;height:50px;object-fit:contain}
  .company{font-size:14px;font-weight:800;text-align:right}
  .meta{color:#475467;margin-top:3px;line-height:1.35;text-align:right;font-size:11px}
  .hr{height:1px;background:#e4e7ec;margin:10px 0}
  .top{display:flex;justify-content:space-between;gap:12px}
  .title{font-size:13px;font-weight:900;letter-spacing:.6px}
  .mono{font-variant-numeric:tabular-nums}
  table{width:100%;border-collapse:collapse}
  td{border-bottom:1px solid #e4e7ec;padding:7px 4px;vertical-align:top}
  .label{color:#475467;width:85px}
  .amount{font-size:14px;font-weight:900}
  .void{color:#b42318;font-weight:900}
  .foot{display:flex;justify-content:space-between;gap:12px;align-items:flex-end;margin-top:12px}
  .sign{width:200px;text-align:center}
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

$html .= '<div class="top"><div><div class="title">KWITANSI PEMBAYARAN</div><div style="color:#475467;margin-top:3px">' . ($isVoid ? '<span class="void">VOID</span>' : 'Bukti pembayaran') . '</div></div>';
$html .= '<div style="text-align:right"><div class="mono" style="font-weight:800">' . h((string)$payment['nomor_kuitansi']) . '</div><div style="color:#475467;margin-top:3px">' . h((string)$payment['tanggal']) . '</div></div></div>';

$html .= '<div class="hr"></div>';

$html .= '<table><tbody>';
$customer = '';
if ((string)($payment['target_type'] ?? '') === 'client') {
    $customer .= '<div style="font-weight:800">' . h((string)($payment['client_perusahaan'] ?? '')) . '</div>';
    $customer .= '<div style="color:#475467;margin-top:2px">PIC: <span class="mono">' . h((string)($payment['client_pic'] ?? '')) . '</span></div>';
    $customer .= '<div class="mono" style="color:#475467;margin-top:2px">' . h((string)($payment['client_tlp'] ?? '')) . ($payment['client_email'] ? ' • ' . h((string)$payment['client_email']) : '') . '</div>';
} else {
    $customer .= '<div style="font-weight:800">' . h((string)$payment['jamaah_nama']) . '</div>';
    $customer .= '<div class="mono" style="color:#475467;margin-top:2px">' . h((string)$payment['jamaah_hp']) . '</div>';
}
$html .= '<tr><td class="label">Pelanggan</td><td>' . $customer . '</td></tr>';
$html .= '<tr><td class="label">Invoice</td><td><div class="mono" style="font-weight:750">' . h((string)$payment['invoice_nomor']) . '</div><div style="color:#475467;margin-top:2px">Paket: ' . ($payment['paket_nama'] ? h((string)$payment['paket_nama']) : '—') . '</div></td></tr>';
$html .= '<tr><td class="label">Metode</td><td>' . h((string)$payment['metode']) . '</td></tr>';
$html .= '<tr><td class="label">Jumlah</td><td class="mono amount">' . h(rupiah((string)$payment['amount'])) . '</td></tr>';
$html .= '<tr><td class="label">Terbilang</td><td>' . h(terbilang_rupiah((string)$payment['amount'])) . '</td></tr>';
if ($isVoid && isset($payment['void_reason']) && $payment['void_reason']) {
    $html .= '<tr><td class="label">Alasan</td><td>' . h((string)$payment['void_reason']) . '</td></tr>';
}
$html .= '<tr><td class="label">Pengirim</td><td class="mono">' . ($payment['pengirim'] ? h((string)$payment['pengirim']) : '—') . '</td></tr>';
$html .= '<tr><td class="label">Outlet</td><td class="mono">' . ($payment['outlet'] ? h((string)$payment['outlet']) : '—') . '</td></tr>';
$html .= '<tr><td class="label">Sales</td><td class="mono">' . ($payment['sales'] ? h((string)$payment['sales']) : '—') . '</td></tr>';
$html .= '<tr><td class="label">Referensi</td><td class="mono">' . ($payment['reference'] ? h((string)$payment['reference']) : '—') . '</td></tr>';
$html .= '</tbody></table>';

$html .= '<div class="foot"><div style="color:#475467"> </div><div class="sign"><div style="color:#475467">Tanda tangan</div><div class="line"></div></div></div>';

$html .= '</div></body></html>';

pdf_stream($html, 'kuitansi-' . (string)$payment['nomor_kuitansi'] . '.pdf', 'A5', 'portrait');
