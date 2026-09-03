<?php

declare(strict_types=1);

auth_require_admin_or_staff();

$id = (int)($_GET['id'] ?? 0);
$roomlist = $id > 0 ? roomlist_find($id) : null;
if (!$roomlist) {
    http_response_code(404);
    echo 'Roomlist tidak ditemukan.';
    exit;
}

$rooms = rooms_by_roomlist($id);
$hotelNama = (string)$roomlist['hotel_nama'];
$paketNama = '';
try {
    $p = paket_find((int)$roomlist['paket_id']);
    $paketNama = (string)($p['nama'] ?? '');
} catch (Throwable $e) {
    $paketNama = '';
}

$chunks = array_chunk($rooms, 10);

?><!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Flashcard Roomlist</title>
    <style>
      @page { size: A4 portrait; margin: 10mm; }
      body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; }
      .meta { margin: 0 0 6mm 0; }
      .meta .t { font-weight: 700; font-size: 14px; margin: 0 0 2mm 0; }
      .meta .s { color: #475569; margin: 0; }
      .page { page-break-after: always; }
      .sheet { display: flex; flex-wrap: wrap; gap: 4mm; }
      .card {
        width: 90mm;
        height: 52mm;
        border: 1px solid #cbd5e1;
        border-radius: 4mm;
        padding: 4mm;
        box-sizing: border-box;
        overflow: hidden;
      }
      .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 4mm; }
      .code { font-weight: 700; font-size: 13px; }
      .key { font-size: 11px; color: #334155; text-align: right; white-space: nowrap; }
      .hotel { margin-top: 2mm; color: #475569; font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .list { margin-top: 3mm; }
      .row { display: flex; gap: 3mm; margin-bottom: 1.3mm; }
      .row .n { width: 6mm; color: #64748b; }
      .row .name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .empty { color: #cbd5e1; }
    </style>
  </head>
  <body>
    <?php if (!$rooms): ?>
      <p>Belum ada kamar untuk roomlist ini.</p>
    <?php else: ?>
      <?php foreach ($chunks as $pageIndex => $pageRooms): ?>
        <div class="page">
          <div class="meta">
            <p class="t">Flashcard Roomlist</p>
            <p class="s">Paket: <?= h($paketNama) ?> • Hotel: <?= h($hotelNama) ?> • Halaman <?= (int)($pageIndex + 1) ?>/<?= (int)count($chunks) ?></p>
          </div>
          <div class="sheet">
            <?php foreach ($pageRooms as $r): ?>
              <?php
                $cap = (int)$r['capacity'];
                $members = (array)($r['members'] ?? []);
                $byPos = [];
                foreach ($members as $m) {
                    $byPos[(int)$m['position']] = (string)$m['nama_lengkap'];
                }
              ?>
              <div class="card">
                <div class="top">
                  <div class="code"><?= h((string)$r['room_code']) ?></div>
                  <div class="key"><?= $r['nomor_kunci'] ? 'Kunci: ' . h((string)$r['nomor_kunci']) : 'Kunci: —' ?></div>
                </div>
                <div class="hotel"><?= h($hotelNama) ?></div>
                <div class="list">
                  <?php for ($i = 1; $i <= $cap; $i++): ?>
                    <?php $nm = $byPos[$i] ?? ''; ?>
                    <div class="row">
                      <div class="n"><?= (int)$i ?>.</div>
                      <div class="name <?= $nm === '' ? 'empty' : '' ?>"><?= $nm !== '' ? h($nm) : '—' ?></div>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <script>window.print()</script>
  </body>
</html>

