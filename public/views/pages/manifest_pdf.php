<?php

declare(strict_types=1);

$paketId = (int)($_GET['paket_id'] ?? 0);
if ($paketId <= 0) {
    http_response_code(400);
    echo 'paket_id wajib';
    exit;
}

try {
    $p = paket_find($paketId);
} catch (Throwable $e) {
    $p = null;
}
if (!$p) {
    http_response_code(404);
    echo 'Paket tidak ditemukan';
    exit;
}
$paketNama = (string)($p['nama'] ?? '');

try {
    $rows = jamaah_manifest_by_paket($paketId);
} catch (Throwable $e) {
    $rows = [];
}

ob_start();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <style>
    * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
    h1 { font-size: 14px; margin: 0 0 6px 0; }
    .muted { color: #666; }
    .meta { margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
    th { background: #f2f2f2; font-weight: 700; }
    .mono { font-family: DejaVu Sans Mono, monospace; }
  </style>
</head>
<body>
  <h1>Manifest Jamaah</h1>
  <div class="meta muted">
    Paket: <?= h($paketNama) ?><br/>
    Tanggal export: <?= h((new DateTimeImmutable('now'))->format('Y-m-d H:i')) ?><br/>
    Total: <?= h((string)count($rows)) ?>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:32px">No</th>
        <th>Nama</th>
        <th>Nama Bapak</th>
        <th style="width:90px">JK</th>
        <th style="width:85px">Tgl Lahir</th>
        <th style="width:120px">No KTP</th>
        <th style="width:110px">No Paspor</th>
        <th style="width:85px">Expire</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 0; ?>
      <?php foreach ($rows as $r): ?>
        <?php $no++; ?>
        <tr>
          <td class="mono"><?= (int)$no ?></td>
          <td><?= h((string)$r['nama_lengkap']) ?></td>
          <td><?= h((string)$r['nama_bapak_kandung']) ?></td>
          <td><?= h((string)$r['jenis_kelamin']) ?></td>
          <td class="mono"><?= h((string)$r['tanggal_lahir']) ?></td>
          <td class="mono"><?= h((string)$r['nik']) ?></td>
          <td class="mono"><?= h((string)($r['passport_no'] ?? '')) ?></td>
          <td class="mono"><?= h((string)($r['passport_expire_date'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="8" class="muted">Belum ada jamaah untuk paket ini.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
<?php
$html = (string)ob_get_clean();
$safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', strtolower($paketNama));
$date = (new DateTimeImmutable('now'))->format('Ymd');
$filename = 'manifest_' . $safeName . '_' . $date . '.pdf';
pdf_stream($html, $filename, 'A4', 'landscape');
