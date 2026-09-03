<?php

declare(strict_types=1);

auth_require_admin_or_staff();

$id = (int)($_GET['id'] ?? 0);
$roomlist = null;
if ($id > 0) {
    try {
        $roomlist = roomlist_find($id);
    } catch (Throwable $e) {
        $roomlist = null;
    }
}
if (!$roomlist) {
    flash_set('error', 'Roomlist tidak ditemukan.');
    redirect(app_url('/?page=roomlist'));
}

$paketId = (int)$roomlist['paket_id'];
$paketNama = '';
try {
    $p = paket_find($paketId);
    $paketNama = (string)($p['nama'] ?? '');
} catch (Throwable $e) {
    $paketNama = '';
}

$cands = [];
try {
    $cands = jamaah_roomlist_candidates_by_paket($paketId);
} catch (Throwable $e) {
    $cands = [];
}
$assigned = [];
try {
    $assigned = roomlist_assigned_jamaah_ids($id);
} catch (Throwable $e) {
    $assigned = [];
}
$assignedMap = [];
foreach ($assigned as $jid) {
    $assignedMap[(int)$jid] = true;
}

$maleCount = 0;
$femaleCount = 0;
foreach ($cands as $c) {
    $jk = strtolower((string)($c['jenis_kelamin'] ?? ''));
    if (in_array($jk, ['laki-laki', 'laki laki', 'l'], true)) $maleCount++;
    if (in_array($jk, ['perempuan', 'p'], true)) $femaleCount++;
}

$unassigned = array_values(array_filter($cands, function ($c) use ($assignedMap) {
    return !isset($assignedMap[(int)$c['id']]);
}));

$rooms = [];
try {
    $rooms = rooms_by_roomlist($id);
} catch (Throwable $e) {
    $rooms = [];
}

?>
<section class="split">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Roomlist</div>
        <div class="card-subtitle">Paket: <?= h($paketNama) ?></div>
      </div>
      <a class="btn" href="<?= h(app_url('/?page=roomlist&paket_id=' . $paketId)) ?>">Kembali</a>
    </div>

    <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="roomlist.update" />
      <input type="hidden" name="id" value="<?= (int)$id ?>" />
      <div class="field">
        <div class="label">Nama Hotel</div>
        <input class="input" name="hotel_nama" value="<?= h((string)$roomlist['hotel_nama']) ?>" required />
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px">
        <button class="btn primary" type="submit">Simpan</button>
      </div>
    </form>

    <div class="hr"></div>

    <section class="grid cols-3">
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= (int)count($cands) ?></div>
          <div class="kpi-label">Total Jamaah</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= (int)$maleCount ?></div>
          <div class="kpi-label">Laki-laki</div>
        </div>
      </div>
      <div class="card" style="box-shadow:none">
        <div class="kpi">
          <div class="kpi-value mono"><?= (int)$femaleCount ?></div>
          <div class="kpi-label">Perempuan</div>
        </div>
      </div>
    </section>

    <div class="hr"></div>

    <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>" onsubmit="return confirm('Generate template akan RESET semua kamar dan penghuni. Lanjutkan?');">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="roomlist.generate" />
      <input type="hidden" name="id" value="<?= (int)$id ?>" />
      <button class="btn danger" type="submit">Generate Template (Reset)</button>
    </form>

    <div class="hr"></div>

    <div class="card-title">Tambah Kamar Manual</div>
    <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>" style="display:flex;gap:8px;align-items:end;margin-top:10px">
      <?= csrf_input() ?>
      <input type="hidden" name="_action" value="room.create" />
      <input type="hidden" name="roomlist_id" value="<?= (int)$id ?>" />
      <div class="field">
        <div class="label">Tipe</div>
        <select class="input" name="room_type">
          <option value="QD">Quad (QD)</option>
          <option value="QT">Quint (QT)</option>
          <option value="TR">Triple (TR)</option>
          <option value="DB">Double (DB)</option>
        </select>
      </div>
      <div class="field">
        <div class="label">Kategori</div>
        <select class="input" name="room_gender">
          <option value="male">Laki-laki</option>
          <option value="female">Perempuan</option>
          <option value="mix">Mix</option>
        </select>
      </div>
      <button class="btn" type="submit">Tambah Kamar</button>
    </form>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Kamar</div>
        <div class="card-subtitle">Kode: R001-QD, R002-QT, dst.</div>
      </div>
    </div>

    <?php if (!$rooms): ?>
      <div class="muted">Belum ada kamar. Klik Generate Template atau tambah kamar manual.</div>
    <?php else: ?>
      <div class="grid cols-2" style="margin-top:10px">
        <?php foreach ($rooms as $r): ?>
          <?php
            $members = (array)($r['members'] ?? []);
            $cap = (int)$r['capacity'];
            $gender = (string)$r['room_gender'];
            $genderLabel = $gender === 'male' ? 'Laki-laki' : ($gender === 'female' ? 'Perempuan' : 'Mix');
          ?>
          <div class="card" style="box-shadow:none">
            <div class="card-header" style="padding:0 0 10px 0">
              <div>
                <div class="card-title"><?= h((string)$r['room_code']) ?> <span class="sub">• <?= h($genderLabel) ?> • <?= (int)$cap ?> pax</span></div>
                <div class="card-subtitle"><?= h((string)$roomlist['hotel_nama']) ?></div>
              </div>
            </div>

            <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>" style="display:flex;gap:8px;align-items:end">
              <?= csrf_input() ?>
              <input type="hidden" name="_action" value="room.key.update" />
              <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>" />
              <input type="hidden" name="roomlist_id" value="<?= (int)$id ?>" />
              <div class="field" style="flex:1">
                <div class="label">Nomor Kunci</div>
                <input class="input mono" name="nomor_kunci" value="<?= h((string)($r['nomor_kunci'] ?? '')) ?>" placeholder="mis. 1203" />
              </div>
              <button class="btn" type="submit">Simpan</button>
            </form>

            <div class="hr"></div>

            <div class="card-title">Penghuni</div>
            <div style="margin-top:10px">
              <table class="table">
                <thead>
                  <tr>
                    <th style="width:60px">Slot</th>
                    <th>Nama</th>
                    <th style="width:90px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$members): ?>
                    <tr>
                      <td colspan="3" class="muted">Belum ada penghuni.</td>
                    </tr>
                  <?php endif; ?>
                  <?php foreach ($members as $m): ?>
                    <tr>
                      <td class="mono"><?= (int)$m['position'] ?></td>
                      <td><?= h((string)$m['nama_lengkap']) ?></td>
                      <td style="text-align:right">
                        <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>" style="display:inline" onsubmit="return confirm('Hapus penghuni dari kamar ini?');">
                          <?= csrf_input() ?>
                          <input type="hidden" name="_action" value="room.member.remove" />
                          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>" />
                          <input type="hidden" name="roomlist_id" value="<?= (int)$id ?>" />
                          <button class="btn danger" type="submit">Hapus</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div style="margin-top:10px">
              <form method="post" action="<?= h(app_url('/?page=roomlist_detail&id=' . (int)$id)) ?>" style="display:flex;gap:8px;align-items:end">
                <?= csrf_input() ?>
                <input type="hidden" name="_action" value="room.member.add" />
                <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>" />
                <input type="hidden" name="roomlist_id" value="<?= (int)$id ?>" />
                <div class="field" style="flex:1">
                  <div class="label">Tambah Jamaah</div>
                  <select class="input" name="jamaah_id" required>
                    <option value="">Pilih jamaah...</option>
                    <?php foreach ($unassigned as $u): ?>
                      <?php
                        $uJk = strtolower((string)($u['jenis_kelamin'] ?? ''));
                        $uIsMale = in_array($uJk, ['laki-laki', 'laki laki', 'l'], true);
                        $uIsFemale = in_array($uJk, ['perempuan', 'p'], true);
                        $ok = $gender === 'mix' || ($gender === 'male' && $uIsMale) || ($gender === 'female' && $uIsFemale);
                      ?>
                      <?php if (!$ok) continue; ?>
                      <option value="<?= (int)$u['id'] ?>">
                        <?= h((string)$u['nama_lengkap']) ?><?= $u['jenis_kelamin'] ? ' • ' . h((string)$u['jenis_kelamin']) : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button class="btn primary" type="submit">Tambah</button>
              </form>
              <div class="help">Daftar di atas hanya jamaah yang belum ditempatkan di roomlist ini.</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
