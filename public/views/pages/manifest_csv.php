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

$safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', strtolower($paketNama));
$date = (new DateTimeImmutable('now'))->format('Ymd');
$filename = 'manifest_' . $safeName . '_' . $date . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'wb');
if ($out === false) {
    exit;
}

$delimiter = ';';
fputcsv($out, ['Nama', 'Nama Bapak', 'Jenis Kelamin', 'Tanggal Lahir', 'No KTP', 'No Paspor', 'Expire Paspor'], $delimiter);
foreach ($rows as $r) {
    fputcsv($out, [
        (string)$r['nama_lengkap'],
        (string)$r['nama_bapak_kandung'],
        (string)$r['jenis_kelamin'],
        (string)$r['tanggal_lahir'],
        (string)$r['nik'],
        (string)($r['passport_no'] ?? ''),
        (string)($r['passport_expire_date'] ?? ''),
    ], $delimiter);
}
fclose($out);
exit;
